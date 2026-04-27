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
 * @module     mod_assign/grading_review_panel
 * @class      UserInfo
 * @copyright  2016 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.1
 */
define(['jquery', 'mod_assign/grading_events', 'core/notification', 'core/templates', 'core/str', 'core/chartjs'],
       function($, GradingEvents, notification, templates, str, Chart) {

    /**
     * UserInfo class.
     *
     * @class UserInfo
     * @param {String} selector The selector for the page region containing the user navigation.
     */
    var GradingReviewPanel = function(selector) {
        this._regionSelector = selector;
        this._region = $(selector);

        this.registerEventListeners();

        $(document).on('participant-loaded', this._refreshReviewPanel.bind(this));

        str.get_strings([
            {key: 'autoevaldegree', component: 'otopo'}
        ]).done(function(strs) {
            this._degreeStr = strs[0];
        }).fail(notification.exception);

        this._visual = this._region.data('otopovisual');
        this._star = this._region.data('star');
        this._starContainer = this._region.data('star-container');
        this._help = this._region.data('help');
    };

    /** @type {String} Selector for the page region containing the user navigation. */
    GradingReviewPanel.prototype._regionSelector = null;

    /** @type {JQuery} JQuery node for the page region containing the user navigation. */
    GradingReviewPanel.prototype._region = null;

    GradingReviewPanel.prototype._visual = 'radar';

    GradingReviewPanel.prototype._degreeStr = '';

    GradingReviewPanel.prototype._star = '';

    GradingReviewPanel.prototype._starContainer = '';

    GradingReviewPanel.prototype._help = '';

    GradingReviewPanel.prototype._refreshReviewPanel = function(event, context) {
        if (context.session.id) {
            context.star = this._star;
            context.help = this._help;
            context.starcontainer = this._starContainer;
            context.items.forEach(function(item) {
                if ('otopodegreedescription' in item) {
                    item.otopodegreedescription = item.otopodegreedescription.replace(/\n/g, "<br />");
                }
            });
            if (!context.sessionchart.label) {
                context.sessionchart = null;
            }
            templates.render('mod_otopo/grading_review', context).done(function(html, js) {
                this._region.fadeOut("fast", function() {
                    templates.replaceNodeContents(this._region, html, js);
                    let options;
                    if (this._visual == 'radar') {
                        options = {
                            scale: {
                                ticks: {
                                    min: 0,
                                    max: context.max,
                                    stepSize: 1
                                }
                            },
                            scales: {
                                r: {
                                    beginAtZero: true
                                }
                            }
                        };
                    } else {
                        const yScale = {
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                max: context.max,
                                stepSize: 1,
                                callbacks: function(value) {
                                    if (value === 0) {
                                        return "";
                                    }
                                    return this._degreeStr + " " + value;
                                }
                            }
                        };
                        const legend = {display: false};
                        if (context.moodlePre4) {
                            options = {
                                scales: {
                                    yAxes: [yScale]
                                }
                            };
                            options.legend = legend;
                        } else {
                            options = {
                                scales: {
                                    y: yScale
                                }
                            };
                            options.plugins = {};
                            options.plugins.legend = legend;
                        }
                    }
                    if (context.moodlePre4) {
                        options.tooltips = {
                            displayColors: false,
                            callbacks: {
                                title: function(tooltipItems, chart) {
                                    return chart.fullLabels[tooltipItems[0].index];
                                },
                                label: function(tooltipItem, data) {
                                    let label = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                                    label += data.datasets[tooltipItem.datasetIndex].labels[tooltipItem.index] ?
                                        (' - ' + data.datasets[tooltipItem.datasetIndex].labels[tooltipItem.index]) : '';
                                    return label;
                                }
                            }
                        };
                    } else {
                        if (!options.plugins) {
                            options.plugins = {};
                        }
                        options.plugins.tooltip = {
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    if (context.length > 0 && context[0].label) {
                                        return context[0].label;
                                    }
                                    return null;
                                },
                                label: function(context) {
                                    if (context) {
                                        let label = context.formattedValue || '0';
                                        label += context.dataset.labels[context.dataIndex] ?
                                            (' - ' + context.dataset.labels[context.dataIndex]) : '';
                                        return label;
                                    }
                                    return null;
                                }
                            }
                        };
                    }
                    const sessionChartElement = document.getElementById('sessionChart');
                    if (sessionChartElement && context.sessionchart) {
                        const config = {
                            type: this._visual,
                            data: context.sessionchart,
                            options: options
                        };
                        new Chart(
                            sessionChartElement,
                            config
                        );
                    }

                    this._region.fadeIn("fast");
                }.bind(this));
            }.bind(this)).fail(notification.exception);
        } else {
            this._region.html('');
        }
    };

    /**
     * Get the toggle review panel button.
     *
     * @method getTogglePanelButton
     * @return {jQuery}
     */
    GradingReviewPanel.prototype.getTogglePanelButton = function() {
        return this.getPanelElement().find('[data-region="review-panel-toggle"]');
    };

    /**
     * Get the review panel element.
     *
     * @method getPanelElement
     * @return {jQuery}
     */
    GradingReviewPanel.prototype.getPanelElement = function() {
        return $('[data-region="review-panel"]');
    };

    /**
     * Get the review panel content element.
     *
     * @method getPanelContentElement
     * @return {jQuery}
     */
    GradingReviewPanel.prototype.getPanelContentElement = function() {
        return $('[data-region="review-panel-content"]');
    };

    /**
     * Show/Hide the review panel.
     *
     * @method togglePanel
     */
    GradingReviewPanel.prototype.togglePanel = function() {
        if (this.getPanelElement().hasClass('collapsed')) {
            $(document).trigger(GradingEvents.EXPAND_REVIEW_PANEL);
        } else {
            $(document).trigger(GradingEvents.COLLAPSE_REVIEW_PANEL);
        }
    };

    /**
     * Hide the review panel.
     *
     * @method collapsePanel
     */
    GradingReviewPanel.prototype.collapsePanel = function() {
        this.getPanelElement().addClass('collapsed').removeClass('grade-panel-collapsed');
        this.getPanelContentElement().attr('aria-hidden', true);
    };

    /**
     * Show the review panel.
     *
     * @method expandPanel
     */
    GradingReviewPanel.prototype.expandPanel = function() {
        this.getPanelElement().removeClass('collapsed');
        this.getPanelContentElement().removeAttr('aria-hidden');
    };

    /**
     * Register event listeners for the review panel.
     *
     * @method registerEventListeners
     */
    GradingReviewPanel.prototype.registerEventListeners = function() {
        var toggleReviewPanelButton = this.getTogglePanelButton();
        toggleReviewPanelButton.click(function(e) {
            this.togglePanel();
            e.preventDefault();
        }.bind(this));

        toggleReviewPanelButton.keydown(function(e) {
            if (!e.metaKey && !e.shiftKey && !e.altKey && !e.ctrlKey) {
                if (e.keyCode === 13 || e.keyCode === 32) {
                    this.togglePanel();
                    e.preventDefault();
                }
            }
        }.bind(this));

        var docElement = $(document);
        docElement.on(GradingEvents.COLLAPSE_REVIEW_PANEL, function() {
            this.collapsePanel();
        }.bind(this));

        // Need special styling when grade panel is collapsed.
        docElement.on(GradingEvents.COLLAPSE_GRADE_PANEL, function() {
            this.expandPanel();
            this.getPanelElement().addClass('grade-panel-collapsed');
        }.bind(this));

        docElement.on(GradingEvents.EXPAND_REVIEW_PANEL, function() {
            this.expandPanel();
        }.bind(this));

        docElement.on(GradingEvents.EXPAND_GRADE_PANEL, function() {
            this.getPanelElement().removeClass('grade-panel-collapsed');
        }.bind(this));
    };

    return GradingReviewPanel;
});
