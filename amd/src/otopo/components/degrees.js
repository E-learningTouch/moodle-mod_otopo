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
 * Otopo vue degrees.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['mod_otopo/otopo/components/degree-modal'], function(DegreeModalHelp) {
    return {
        components: {
            'DegreeModalHelp': DegreeModalHelp,
        },
        props: {
            degrees: {
                type: Array
            },
            itemName: {
                type: String,
            },
            color: {
                type: String,
            },
            degree: {
                type: Number,
                'default': null,
            },
            disabled: {
                type: Boolean,
                'default': false,
            }
        },
        data: function() {
            return {
                images: this.$root.$data.images,
                strings: this.$root.$data.strings,
                selected: this.degree ? parseInt(Object.keys(this.degrees)
                    .find(key => this.degrees[key].id === this.degree)) : null,
                showModal: false
            };
        },
        methods: {
            selectNext() {
                if (this.disabled) {
                    return;
                }
                if (this.selected === null) {
                    this.selected = 0;
                } else {
                    ++this.selected;
                }
                this.$emit('changed', this.degrees[this.selected].id);
            },
            selectPrevious() {
                if (this.disabled) {
                    return;
                }
                if (this.selected > 0) {
                    --this.selected;
                    this.$emit('changed', this.degrees[this.selected].id);
                } else {
                    this.selected = null;
                    this.$emit('changed', null);
                }
            }
        },
        computed: {
            starWidth: function() {
                if (this.selected === null) {
                    return 0;
                }
                return (this.selected + 1) * 100 / this.degrees.length;
            },
            degreeName: function() {
                if (this.selected === null) {
                    return this.strings.autoeval;
                }
                return this.strings.degree + " " + (this.selected + 1);
            },
            name: function() {
                if (this.selected === null) {
                    return this.strings.desc;
                }
                return this.degrees[this.selected].name;
            },
            description: function() {
                if (this.selected === null) {
                    return "";
                }
                return this.degrees[this.selected].description.replace(/\n/g, "<br />");
            },
        },
        template: `
            <div class="row" v-if="this.degrees.length > 0">
                <div class="col-xl-6 no-print">
                    <div class="d-flex flex-column justify-content-center degree-star-container">
                        <div class="d-flex flex-row align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-center degree-action degree-action-left">
                                <img :src="images.minus" class="img-responsive invisible" />
                            </div>
                            <div class="d-flex align-items-center justify-content-center degree-container">
                                <div
                                    class="degree-star-mask"
                                    :style="'width: ' + starWidth + '%;\
                                        -webkit-mask-image: url(' + images.star + ');\
                                        mask-image: url(' + images.star + ');'"
                                >
                                    <div
                                        :style="'background-color: ' + color + ';\
                                            background: rgb(255,255,255);\
                                            background: linear-gradient(90deg, rgba(255,255,255,1) 0%, ' + color + ' 100%);'"
                                    >
                                        <img :src="images.starContainer" class="img-responsive invisible" />
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center degree-action degree-action-right">
                                <img :src="images.plus" class="img-responsive invisible" />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-center degree-background-container">
                        <div class="d-flex align-items-center justify-content-center degree-action degree-action-left">
                            <img :src="images.minus" class="img-responsive" v-if="selected != null" v-on:click="selectPrevious" />
                        </div>
                        <div class="d-flex align-items-center justify-content-center degree-container">
                            <img :src="images.starContainer" class="img-responsive" />
                        </div>
                        <div class="d-flex align-items-center justify-content-center degree-action degree-action-right">
                            <img :src="images.plus" class="img-responsive"
                                v-if="selected == null || selected < this.degrees.length - 1" v-on:click="selectNext"
                            />
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="degree-action degree-action-bottom">
                            <img :src="images.help" class="img-responsive" v-if="selected != null" @click="showModal = true" />
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 degree-desc">
                    <h3>{{ degreeName }}</h3>
                    <p>{{ name }}</p>
                </div>
                <DegreeModalHelp v-if="showModal" @close="showModal = false" :itemName="itemName" :color="color"
                    :degreeName="degreeName" :name="name" :description="description"
                />
            </div>
        `
    };
});
