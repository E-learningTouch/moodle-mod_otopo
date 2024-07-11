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
 * Otopo vue modal.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        mounted: function() {
            document.body.classList.add('modal-open');
            var backdropDiv = document.createElement("div");
            backdropDiv.classList.add('modal-backdrop');
            backdropDiv.classList.add('fade');
            backdropDiv.classList.add('show');
            document.body.appendChild(backdropDiv);
        },
        destroyed: function() {
            document.body.classList.remove('modal-open');
            document.body.removeChild(document.body.lastChild);
        },
        data: function() {
            return {
                strings: this.$root.$data.strings,
            };
        },
        template: `
            <transition name="modal">
                <div class="modal fade show" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content rounded">
                            <div class="modal-header rounded-top bg-otopo text-uppercase text-center">
                                <h5 class="modal-title w-100 font-weight-bold text-white">{{ strings.help }}</h5>
                                <button type="button" class="close close-modal" @click="$emit('close')" aria-label="Close">
                                    <i class="icon fa fa-times-circle text-white" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="modal-body rounded-bottom otopo">
                                <h5 class="font-weight-bold">{{ strings.modalsubtitle }}</h5>
                                <p class="mt-2">{{ strings.modalcontent }}
                                    <ul>
                                        <li>{{ strings.modalcontent1 }}</li>
                                        <li>{{ strings.modalcontent2 }}</li>
                                    </ul>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        `
    };
});
