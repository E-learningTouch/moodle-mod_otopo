// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript controller for the "Grading" panel at the right of the page.
 *
 * @module     mod_assign/grading_panel
 * @class      GradingPanel
 * @copyright  2016 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.1
 */
define(['jquery', 'core/yui', 'core/notification', 'core/templates', 'core/fragment',
        'core/ajax', 'core/str', 'mod_assign/grading_form_change_checker',
        'mod_assign/grading_events', 'core/event'],
       function($, Y, notification, templates, fragment, ajax, str, checker, GradingEvents, Event) {

    /**
     * GradingPanel class.
     *
     * @class GradingPanel
     * @param {String} selector The selector for the page region containing the user navigation.
     */
    var GradingPanel = function(selector) {
        this._regionSelector = selector;
        this._region = $(selector);
        this._userCache = [];

        this.registerEventListeners();
    };

    /** @type {String} Selector for the page region containing the user navigation. */
    GradingPanel.prototype._regionSelector = null;

    /** @type {Number} Remember the last user id to prevent unnessecary reloads. */
    GradingPanel.prototype._lastUserId = 0;

    /** @type {Number} Remember the last session id to prevent unnessecary reloads. */
    GradingPanel.prototype._lastSession = 0;

    /** @type {JQuery} JQuery node for the page region containing the user navigation. */
    GradingPanel.prototype._region = null;

     /** @type {Number} The id of the next user in the grading list */
    GradingPanel.prototype.nextUserId = null;

     /** @type {Boolean} Next user exists in the grading list */
    GradingPanel.prototype.nextUser = false;

    /**
     * Fade the dom node out, update it, and fade it back.
     *
     * @private
     * @method _niceReplaceNodeContents
     * @param {JQuery} node
     * @param {String} html
     * @param {String} js
     * @return {Deferred} promise resolved when the animations are complete.
     */
    GradingPanel.prototype._niceReplaceNodeContents = function(node, html, js) {
        var promise = $.Deferred();

        node.fadeOut("fast", function() {
            templates.replaceNodeContents(node, html, js);
            node.fadeIn("fast", function() {
                promise.resolve();
            });
        });

        return promise.promise();
    };

    /**
     * Make sure all form fields have the latest saved state.
     * @private
     * @method _saveFormState
     */
    GradingPanel.prototype._saveFormState = function() {
        // Copy data from notify students checkbox which was moved out of the form.
        var checked = $('[data-region="grading-actions-form"] [name="sendstudentnotifications"]').prop("checked");
        $('.gradeform [name="sendstudentnotifications"]').val(checked);
    };

    /**
     * Make form submit via ajax.
     *
     * @private
     * @param {Object} event
     * @param {Number} nextUserId
     * @param {Boolean} nextUser optional. Load next user in the grading list.
     * @method _submitForm
     */
    GradingPanel.prototype._submitForm = function(event, nextUserId, nextUser) {
        // The form was submitted - send it via ajax instead.
        var form = $(this._region.find('form.gradeform'));

        $('[data-region="overlay"]').show();

        // We call this, so other modules can update the form with the latest state.
        form.trigger('save-form-state');

        // Tell all form fields we are about to submit the form.
        Event.notifyFormSubmitAjax(form[0]);

        // Now we get all the current values from the form.
        var data = form.serialize();
        var otopoid = this._region.attr('data-otopoid');
        var itemsComments = [];
        this._region.find('.grade-item textarea').each(function() {
            if ($(this).val()) {
                itemsComments.push({id: $(this).data('item'), value: $(this).val()});
            }
        });

        // Now we can continue...
        ajax.call([{
            methodname: 'mod_otopo_submit_grading_form',
            args: {otopo: otopoid, userid: this._lastUserId, session: this._lastSession, jsonformdata: JSON.stringify(data),
                   itemscomments: itemsComments},
            done: this._handleFormSubmissionResponse.bind(this, data, nextUserId, nextUser),
            fail: notification.exception
        }]);
    };

    /**
     * Handle form submission response.
     *
     * @private
     * @method _handleFormSubmissionResponse
     * @param {Array} formdata - submitted values
     * @param {Number} nextUserId - optional. The id of the user to load after the form is saved.
     * @param {Boolean} nextUser - optional. If true, switch to next user in the grading list.
     * @param {Array} response List of errors.
     */
    GradingPanel.prototype._handleFormSubmissionResponse = function(formdata, nextUserId, nextUser, response) {
        if (typeof nextUserId === "undefined") {
            nextUserId = this._lastUserId;
        }
        if (response.length) {
            // There was an error saving the grade. Re-render the form using the submitted data so we can show
            // validation errors.
            $(document).trigger('reset', [this._lastUserId, this._lastSession, formdata]);
        } else {
            str.get_strings([
                {key: 'changessaved', component: 'core'},
                {key: 'gradechangessaveddetail', component: 'mod_assign'},
            ]).done(function(strs) {
                notification.alert(strs[0], strs[1]);
            }).fail(notification.exception);
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });
            if (nextUserId == this._lastUserId) {
                $(document).trigger('reset', [nextUserId, this._lastSession]);
            } else if (nextUser) {
                $(document).trigger('done-saving-show-next', true);
            } else {
                $(document).trigger('user-changed', nextUserId);
            }
        }
        $('[data-region="overlay"]').hide();
    };

    /**
     * Refresh form with default values.
     *
     * @private
     * @method _resetForm
     * @param {Event} e
     * @param {Number} userid
     * @param {Number} session
     * @param {Array} formdata
     */
    GradingPanel.prototype._resetForm = function(e, userid, session, formdata) {
        // The form was cancelled - refresh with default values.
        var event = $.Event("custom");
        if (typeof userid == "undefined") {
            userid = this._lastUserId;
        }
        this._lastUserId = 0;
        if (typeof session == "undefined") {
            session = this._lastSession;
        }
        this._refreshGradingPanel(event, userid, session, formdata, function() {
            $(document).trigger('session-changed', [userid, session, false, true]);
        });
    };

    /**
     * Add popout buttons
     *
     * @private
     * @method _addPopoutButtons
     * @param {JQuery} selector The region selector to add popout buttons to.
     */
    GradingPanel.prototype._addPopoutButtons = function(selector) {
        var region = $(selector);

        templates.render('mod_assign/popout_button', {}).done(function(html) {
            var parents = region.find('[data-fieldtype="filemanager"],[data-fieldtype="editor"],[data-fieldtype="grading"]')
                    .closest('.fitem');
            parents.addClass('has-popout').find('label').parent().append(html);

            region.on('click', '[data-region="popout-button"]', this._togglePopout.bind(this));
        }.bind(this)).fail(notification.exception);
    };

    /**
     * Make a div "popout" or "popback".
     *
     * @private
     * @method _togglePopout
     * @param {Event} event
     */
    GradingPanel.prototype._togglePopout = function(event) {
        event.preventDefault();
        var container = $(event.target).closest('.fitem');
        if (container.hasClass('popout')) {
            $('.popout').removeClass('popout');
        } else {
            $('.popout').removeClass('popout');
            container.addClass('popout');
            container.addClass('moodle-has-zindex');
        }
    };

    /**
     * Get the user context - re-render the template in the page.
     *
     * @private
     * @method _refreshGradingPanel
     * @param {Event} event
     * @param {Number} userid
     * @param {Number} session
     * @param {String} submissiondata serialised submission data.
     * @param {CallableFunction} callbackUserLoaded
     */
    GradingPanel.prototype._refreshGradingPanel = function(event, userid, session, submissiondata,
                                                           callbackUserLoaded = null) {
        var contextid = this._region.attr('data-contextid');

        if (typeof submissiondata === 'undefined') {
            submissiondata = '';
        }
        // Skip reloading if it is the same user.
        if (this._lastUserId == userid && this._lastSession == session && submissiondata === '') {
            return;
        }
        this._lastUserId = userid;
        this._lastSession = session;
        $(document).trigger('start-loading-user');
        // Tell behat to back off too.
        window.M.util.js_pending('mod-assign-loading-user');
        // First insert the loading template.
        templates.render('mod_assign/loading', {}).done(function(html, js) {
            // Update the page.
            this._niceReplaceNodeContents(this._region, html, js).done(function() {
                if (userid > 0) {
                    this._region.show();
                    // Reload the grading form "fragment" for this user.
                    var params = {
                        userid: userid,
                        session: session,
                        jsonformdata: JSON.stringify(submissiondata)
                    };
                    fragment.loadFragment('mod_otopo', 'gradingpanel', contextid, params).done(function(html, js) {
                        this._niceReplaceNodeContents(this._region, html, js)
                        .done(function() {
                            checker.saveFormState('[data-region="grade-panel"] .gradeform');
                            $(document).on('editor-content-restored', function() {
                                // If the editor has some content that has been restored
                                // then save the form state again for comparison.
                                checker.saveFormState('[data-region="grade-panel"] .gradeform');
                            });
                            this._addPopoutButtons('[data-region="grade-panel"] .gradeform');
                            $(document).trigger('finish-loading-user');
                            if (callbackUserLoaded !== null && typeof callbackUserLoaded === 'function') {
                                callbackUserLoaded();
                            }
                            // Tell behat we are friends again.
                            window.M.util.js_complete('mod-assign-loading-user');
                        }.bind(this))
                        .fail(notification.exception);
                    }.bind(this)).fail(notification.exception);
                    $('[data-region="review-panel"]').show();
                } else {
                    this._region.hide();
                    $('[data-region="review-panel"]').hide();
                    $(document).trigger('finish-loading-user');
                    // Tell behat we are friends again.
                    window.M.util.js_complete('mod-assign-loading-user');
                }
            }.bind(this));
        }.bind(this)).fail(notification.exception);
    };

    /**
     * Get next user data and store it in global variables
     *
     * @private
     * @method _getNextUser
     * @param {Event} event
     * @param {Object} data Next user's data
     */
    GradingPanel.prototype._getNextUser = function(event, data) {
        this.nextUserId = data.nextUserId;
        this.nextUser = data.nextUser;
    };

    /**
     * Handle the save-and-show-next event
     *
     * @private
     * @method _handleSaveAndShowNext
     */
    GradingPanel.prototype._handleSaveAndShowNext = function() {
        this._submitForm(null, this.nextUserId, this.nextUser);
    };

    /**
     * Get the grade panel element.
     *
     * @method getPanelElement
     * @return {jQuery}
     */
    GradingPanel.prototype.getPanelElement = function() {
        return $('[data-region="grade-panel"]');
    };

    /**
     * Hide the grade panel.
     *
     * @method collapsePanel
     */
    GradingPanel.prototype.collapsePanel = function() {
        this.getPanelElement().addClass('collapsed');
    };

    /**
     * Show the grade panel.
     *
     * @method expandPanel
     */
    GradingPanel.prototype.expandPanel = function() {
        this.getPanelElement().removeClass('collapsed');
    };

    /**
     * Register event listeners for the grade panel.
     *
     * @method registerEventListeners
     */
    GradingPanel.prototype.registerEventListeners = function() {
        var docElement = $(document);
        var region = $(this._region);
        // Add an event listener to prevent form submission when pressing enter key.
        region.on('submit', 'form', function(e) {
            e.preventDefault();
        });

        docElement.on('next-user', this._getNextUser.bind(this));
        docElement.on('session-changed', this._refreshGradingPanel.bind(this));
        docElement.on('save-changes', this._submitForm.bind(this));
        docElement.on('save-and-show-next', this._handleSaveAndShowNext.bind(this));
        docElement.on('reset', this._resetForm.bind(this));

        docElement.on('save-form-state', this._saveFormState.bind(this));

        docElement.on(GradingEvents.COLLAPSE_GRADE_PANEL, function() {
            this.collapsePanel();
        }.bind(this));

        // We should expand if the review panel is collapsed.
        docElement.on(GradingEvents.COLLAPSE_REVIEW_PANEL, function() {
            this.expandPanel();
        }.bind(this));

        docElement.on(GradingEvents.EXPAND_GRADE_PANEL, function() {
            this.expandPanel();
        }.bind(this));
    };

    return GradingPanel;
});
