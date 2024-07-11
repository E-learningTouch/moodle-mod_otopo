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
 * Javascript controller for the "User summary" panel at the top of the page.
 *
 * @module     mod_assign/grading_navigation_user_info
 * @class      UserInfo
 * @copyright  2016 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.1
 */
define(['jquery', 'core/notification', 'core/ajax', 'core/templates', 'core/str'],
       function($, notification, ajax, templates, str) {

    /**
     * UserInfo class.
     *
     * @class UserInfo
     * @param {String} selector The selector for the page region containing the user navigation.
     * @param {String|JQuery} sessionSelector
     */
    var UserInfo = function(selector, sessionSelector) {
        this._regionSelector = selector;
        this._region = $(selector);
        this._sessionRegionSelector = sessionSelector;
        this._sessionRegion = $(sessionSelector);

        $(document).on('user-changed', this._refreshUserInfoAndLoadSessions.bind(this));
        $(document).on('session-changed', this._refreshUserInfo.bind(this));

        str.get_strings([
            {key: 'lastmodification', component: 'otopo'},
        ]).done((function(strs) {
            this._lastModificationStr = strs[0];
        }).bind(this)).fail(notification.exception);
    };

    /** @type {String} Selector for the page region containing the user navigation. */
    UserInfo.prototype._regionSelector = null;

    /** @type {String} Selector for the page region containing the user navigation. */
    UserInfo.prototype._sessionRegionSelector = null;

    /** @type {JQuery} JQuery node for the page region containing the user navigation. */
    UserInfo.prototype._region = null;

    /** @type {JQuery} JQuery node for the page region containing the user navigation. */
    UserInfo.prototype._sessionRegion = null;

    /** @type {Number} Remember the last user id to prevent unnecessary reloads. */
    UserInfo.prototype._lastUserId = 0;

    /** @type {Number} Remember the last session id to prevent unnecessary reloads. */
    UserInfo.prototype._lastSessionId = 0;

    UserInfo.prototype._lastModificationStr = '';

    /**
     * Get the assignment id
     *
     * @private
     * @method _getOtopoId
     * @return {Number} otopo id
     */
    UserInfo.prototype._getOtopoId = function() {
        return this._region.attr('data-otopoid');
    };

    UserInfo.prototype._refreshUserInfoAndLoadSessions = function(event, userid) {
        var session = this._lastSessionId > 0 ?
            this._lastSessionId :
            $('[data-region="grading-navigation-panel"]').data('first-session');
        this._refreshUserInfo(event, userid, session, true);
    };
    /**
     * Get the user context - re-render the template in the page.
     *
     * @private
     * @method _refreshUserInfo
     * @param {Event} event
     * @param {Number} userid
     * @param {Number} session
     * @param {Boolean} loadSessions
     * @param {Boolean} force
     */
    UserInfo.prototype._refreshUserInfo = function(event, userid, session, loadSessions = false, force = false) {
        var promise = $.Deferred();

        // Put the current user ID in the DOM so yui can access it.
        this._region.attr('data-userid', userid);

        // Skip reloading if it is the same user.
        if (this._lastUserId == userid && session && this._lastSessionId == session && !force) {
            return;
        }
        this._lastUserId = userid;
        this._lastSessionId = session;

        // First insert the loading template.
        templates.render('mod_assign/loading', {}).done(function(html, js) {
            // Update the page.
            this._region.fadeOut("fast", function() {
                templates.replaceNodeContents(this._region, html, js);
                this._region.fadeIn("fast");
            }.bind(this));

            if (userid < 0) {
                // Render the template.
                templates.render('mod_assign/grading_navigation_no_users', {}).done(function(html, js) {
                    if (userid == this._lastUserId) {
                        // Update the page.
                        this._region.fadeOut("fast", function() {
                            templates.replaceNodeContents(this._region, html, js);
                            this._region.fadeIn("fast");
                        }.bind(this));
                    }
                }.bind(this)).fail(notification.exception);
                return;
            }

            // Load context from ajax.
            var otopoId = this._getOtopoId();
            var requests = ajax.call([{
                methodname: 'mod_otopo_get_participant',
                args: {
                    userid: userid,
                    otopo: otopoId,
                    session: session
                }
            }]);

            requests[0].done(function(participant) {
                if (!participant.hasOwnProperty('id')) {
                    promise.reject('No users');
                } else {
                    promise.resolve(participant);

                }
            }).fail(notification.exception);

            promise.done(function(context) {
                $(document).trigger('participant-loaded', context);

                $('#grading-actions-buttons button').prop('disabled', !context.session.validated);

                var identityfields = $('[data-showuseridentity]').data('showuseridentity').split(','),
                    identity = [];

                // Render the template.
                context.courseid = $('[data-region="grading-navigation-panel"]').attr('data-courseid');

                if (context.user) {
                    // Build a string for the visible identity fields listed in showuseridentity config setting.
                    $.each(identityfields, function(i, k) {
                        if (typeof context.user[k] !== 'undefined' && context.user[k] !== '') {
                            context.hasidentity = true;
                            identity.push(context.user[k]);
                        }
                    });
                    context.identity = identity.join(', ');

                    // Add profile image url to context.
                    if (context.user.profileimageurl) {
                        context.profileimageurl = context.user.profileimageurl;
                    }
                }

                templates.render('mod_otopo/grading_navigation_user_summary', context).done(function(html, js) {
                    // Update the page.
                    if (userid == this._lastUserId) {
                        this._region.fadeOut("fast", function() {
                            templates.replaceNodeContents(this._region, html, js);
                            this._region.fadeIn("fast");
                        }.bind(this));
                    }
                }.bind(this)).fail(notification.exception);

                if (loadSessions) {
                    var firstSession = $('[data-region="grading-navigation-panel"]').data('first-session');
                    templates.render('mod_otopo/grading_navigation_session_selector', context).done(function(html, js) {
                        if (userid == this._lastUserId) {
                            this._sessionRegion.fadeOut("fast", function() {
                                templates.replaceNodeContents(this._sessionRegion, html, js);
                                this._setLastModification(context.session.lastmodification);
                                this._sessionRegion.fadeIn("fast");
                                this._sessionRegion.find('[data-action="change-session"]')
                                    .on('change', this._handleChangeSession.bind(this));
                            }.bind(this));
                        }
                    }.bind(this)).fail(notification.exception);
                    $(document).trigger('session-changed', [this._lastUserId, firstSession]);
                } else {
                    this._setLastModification(context.session.lastmodification);
                }
            }.bind(this)).fail(function() {
                // Render the template.
                templates.render('mod_assign/grading_navigation_no_users', {}).done(function(html, js) {
                    // Update the page.
                    this._region.fadeOut("fast", function() {
                        templates.replaceNodeContents(this._region, html, js);
                        this._region.fadeIn("fast");
                    }.bind(this));
                }.bind(this)).fail(notification.exception);
            }
            .bind(this));
        }.bind(this)).fail(notification.exception);
    };

    UserInfo.prototype._setLastModification = function(lastmodification) {
        if (lastmodification) {
            this._sessionRegion.find('#otopo_lastmodification')
                .html(this._lastModificationStr + ': ' + lastmodification);
        } else {
            this._sessionRegion.find('#otopo_lastmodification')
                .html('');
        }
    };

    UserInfo.prototype._handleChangeSession = function() {
        var select = this._sessionRegion.find('[data-action=change-session]');
        var session = parseInt(select.val(), 10);

        var url = new URL(window.location);
        url.searchParams.set('session', session);
        // We do this so a browser refresh will return to the same user.
        window.history.replaceState({}, "", url);

        $(document).trigger('session-changed', [this._lastUserId, session]);
    };

    return UserInfo;
});
