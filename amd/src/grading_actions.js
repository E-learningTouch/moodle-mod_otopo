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
 * Javascript controller for the grading action buttons (Save, Save & Next, Reset).
 *
 * Replaces the mod_assign/grading_actions dependency which changed its expected
 * HTML structure in Moodle 5 and is incompatible with this plugin's template.
 *
 * @module     mod_otopo/grading_actions
 * @copyright  2025 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright  2025 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    /**
     * GradingActions class.
     *
     * @param {String} selector CSS selector for the grade-actions region.
     */
    var GradingActions = function(selector) {
        this._region = $(selector);
        this.registerEventListeners();
    };

    GradingActions.prototype._region = null;

    /**
     * Register click listeners on the Save, Save & Next, and Reset buttons.
     * Each button prevents the default form submission and instead triggers
     * the matching jQuery document event that grading_panel.js listens for.
     */
    GradingActions.prototype.registerEventListeners = function() {
        var region = this._region;

        // Prevent any accidental form submission from this area.
        region.on('submit', 'form', function(e) {
            e.preventDefault();
        });

        region.on('click', '[name="savechanges"]', function(e) {
            e.preventDefault();
            $(document).trigger('save-changes');
        });

        region.on('click', '[name="saveandshownext"]', function(e) {
            e.preventDefault();
            $(document).trigger('save-and-show-next');
        });

        region.on('click', '[name="resetbutton"]', function(e) {
            e.preventDefault();
            $(document).trigger('reset');
        });
    };

    return GradingActions;
});
