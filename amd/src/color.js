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
 * Color.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['vuecolor'], function(VueColor) {
    return {
        components: {
            'chrome-picker': VueColor.Chrome,
        },
        template: `
            <div class="input-group color-picker" ref="colorpicker">
            <input type="text" :name="name" class="form-control" v-model="colorValue" @focus="showPicker()"
                @input="updateFromInput"
            />
            <span class="input-group-append color-picker-container">
                <span class="current-color input-group-text" :style="'background-color: ' + colorValue"
                    @click="togglePicker()"
                ></span>
                <chrome-picker :value="colors" @input="updateFromPicker" v-if="displayPicker" />
            </span>
            </div>
        `,
        props: ['color', 'name'],
        data: function() {
            return {
                colors: {
                    hex: '#000000',
                },
                colorValue: '',
                displayPicker: false,
                firstCall: true,
            };
        },
        mounted: function() {
            this.setColor(this.color || '#000000');
        },
        methods: {
            setColor(color) {
                this.updateColors(color);
                this.colorValue = color;
            },
            updateColors(color) {
                if (color.slice(0, 1) == '#') {
                    this.colors = {
                        hex: color
                    };
                } else if (color.slice(0, 4) == 'rgba') {
                    var rgba = color.replace(/^rgba?\(|\s+|\)$/g, '').split(',');
                    var hex = '#' + ((1 * Math.pow(2, 24))
                        + (parseInt(rgba[0]) * Math.pow(2, 16))
                        + (parseInt(rgba[1]) * Math.pow(2, 8))
                        + parseInt(rgba[2])).toString(16).slice(1);
                    this.colors = {
                        hex: hex,
                        a: rgba[3],
                    };
                }
            },
            showPicker() {
                document.addEventListener('click', this.documentClick);
                this.displayPicker = true;
            },
            hidePicker() {
                document.removeEventListener('click', this.documentClick);
                this.displayPicker = false;
            },
            togglePicker() {
                if (this.displayPicker) {
                    this.hidePicker();
                } else {
                    this.showPicker();
                }
            },
            updateFromInput() {
                this.updateColors(this.colorValue);
            },
            updateFromPicker(color) {
                this.colors = color;
                if (color.rgba.a == 1) {
                    this.colorValue = color.hex;
                } else {
                    this.colorValue = 'rgba(' + color.rgba.r + ', '
                        + color.rgba.g + ', '
                        + color.rgba.b + ', '
                        + color.rgba.a + ')';
                }
            },
            documentClick(e) {
                var el = this.$refs.colorpicker,
                    target = e.target;
                if (el !== target && !el.contains(target)) {
                    this.hidePicker();
                }
            }
        },
        watch: {
            colorValue(val) {
                if (val) {
                    this.updateColors(val);
                    if (!this.firstCall) {
                        this.$emit('input', val);
                    }
                    this.firstCall = false;
                }
            }
        },
    };
});
