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
 * Individual report.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        init: function(cmid, wwwroot) {

            /**
             * Get sessions inputs.
             *
             * @returns {NodeList}
             */
            function getSessionsInputs() {
                return document.querySelectorAll(".sessions > input");
            }

            /**
             * Get enabled sessions.
             *
             * @returns {Array}
             */
            function getSessionsEnabled() {
                const sessions = [];
                const sessionsInputs = getSessionsInputs();
                for (var i = 0; i < sessionsInputs.length; i++) {
                    if (sessionsInputs[i].checked) {
                        sessions.push(sessionsInputs[i].parentNode.dataset.user + '_' + sessionsInputs[i].value);
                    }
                }
                return sessions;
            }

            /**
             * Toggle a session.
             */
            function toggleBtn() {
                const exportBtn = document.getElementById('export-csv');
                if (exportBtn) {
                    const sessionsEnabled = getSessionsEnabled();
                    if (sessionsEnabled.length === 0) {
                        exportBtn.classList.add('disabled');
                    } else {
                        exportBtn.classList.remove('disabled');
                    }
                }
            }

            toggleBtn();

            const sessionsInputs = getSessionsInputs();
            for (var i = 0; i < sessionsInputs.length; i++) {
                sessionsInputs[i].addEventListener('click', function() {
                    toggleBtn();
                });
            }

            const exportBtn = document.getElementById('export-csv');
            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!exportBtn.classList.contains('disabled')) {
                        const sessionsEnabled = getSessionsEnabled();
                        location.href = wwwroot + "/mod/otopo/view.php?id=" + cmid + "&action=export&object=individual&sessions="
                            + sessionsEnabled.join(',');
                    }
                });
            }
        }
    };
});
