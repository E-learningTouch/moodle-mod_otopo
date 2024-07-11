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
 * Otopo vue item.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'mod_otopo/otopo/components/modal',
    'mod_otopo/otopo/components/degrees',
    'mod_otopo/otopo/ajax',
    'mod_otopo/utils'
], function(
    ModalHelp,
    Degrees,
    ajax,
    utils
) {
    return {
        components: {
            'ModalHelp': ModalHelp,
            'Degrees': Degrees
        },
        props: {
            item: {
                type: Object
            },
            index: {
                type: Number
            }
        },
        methods: {
            setUserOtopo() {
                if (this.$root.$data.otopo === null) {
                    // Must be preview. Ignore sending data.
                    return;
                }
                ajax.setUserOtopo(
                    this.$root.$data.otopo,
                    this.$root.$data.session,
                    this.item.id,
                    this.degree,
                    this.justification
                );
            },
            degreeChanged(degree) {
                this.degree = degree;
                this.setUserOtopo();
            }
        },
        data: function() {
            return {
                processChange: utils.debounce(() => this.setUserOtopo()),
                strings: this.$root.$data.strings,
                showModal: false,
                degree: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].degree : null,
                justification: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].justification : "",
                comment: this.$root.$data.session && this.item.id in this.$root.$data.otopos ?
                    this.$root.$data.otopos[this.item.id].comment : "",
            };
        },
        computed: {
            disabledDegree: function() {
                if (!this.$root.$data.session) {
                    return false;
                }
                return !this.$root.$data.active;
            },
            disabledJustification: function() {
                if (!this.$root.$data.session) {
                    return false;
                }
                return !this.$root.$data.active || !this.degree;
            },
            justificationHtml: function() {
                var expression = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/gi; // eslint-disable-line max-len
                var regex = new RegExp(expression);

                return this.justification.replace(regex, '$1'.link('$1'));
            },
            commentHtml: function() {
                if (!this.comment) {
                    return this.strings.noteachercomment;
                }

                var expression = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/gi; // eslint-disable-line max-len
                var regex = new RegExp(expression);

                return this.comment.replace(regex, '$1'.link('$1'));
            }
        },
        template: `
            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <h4 class="font-weight-bold" :style="'color:' + item.color + ';'">{{ item.name }}</h4>
                </div>
                <div class="col-md-4">
                    <Degrees
                        :degree="degree"
                        :itemName="item.name"
                        :color="item.color"
                        :degrees="item.degrees" @changed="degreeChanged"
                        :disabled="disabledDegree"
                    />
                </div>
                <div :class="$root.$data.showComments ? 'col-md-4' : 'col-md-8'">
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm no-print">
                        <div class="input-group mb-3">
                            <textarea
                                v-model="justification"
                                :disabled="disabledJustification"
                                class="form-control border-0"
                                :placeholder="strings.yourjustification"
                                @input="processChange()"
                                rows="5"
                            ></textarea>
                        </div>
                    </div>
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm only-print">
                        <div class="input-group mb-3">
                            <p v-html="justificationHtml"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button id="show-help-modal" class="bg-light rounded pl-2 pr-3 pt-1 pb-1 border shadow-sm"
                            @click="showModal = true"
                        >
                            <i class="icon fa fa-question" aria-hidden="true"></i>
                            {{ strings.help }}
                        </button>
                        <ModalHelp v-if="showModal" @close="showModal = false" />
                    </div>
                </div>
                <div :class="$root.$data.showComments ? 'col-md-4' : 'd-none'">
                    <div class="comment border rounded pt-1 pl-3 pr-3 mb-2 shadow-sm">
                        <p v-html="commentHtml"></p>
                    </div>
                </div>
            </div>
        `
    };
});
