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
 * Otopo session filter.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/datafilter/filtertype'], function(Filter) {
    return class extends Filter {
        constructor(filterType, filterSet) {
            super(filterType, filterSet);
        }

        async addValueSelector() {
            // eslint-disable-line no-empty-function
        }

        /**
         * Get the composed value for this filter.
         *
         * @returns {Object}
         */
        get filterValue() {
            return {
                name: this.name,
                jointype: 1,
                values: [
                    document.getElementById('session-selector') && parseInt(document.getElementById('session-selector').value)
                        ? parseInt(document.getElementById('session-selector').value)
                        : 0
                ],
            };
        }
    };
});
