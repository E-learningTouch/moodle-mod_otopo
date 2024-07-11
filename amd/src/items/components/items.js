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
 * Items vue items.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'mod_otopo/items/components/item',
    'mod_otopo/items/store'
], function(
    Item,
    store
) {
    return {
        components: {
            'Item': Item,
        },
        props: {
            otopo: {
                type: Number
            },
        },
        data: function() {
            return {
                items: this.$root.$data.state.items,
                strings: this.$root.$data.strings,
                hasOtopo: this.$root.$data.hasOtopo,
            };
        },
        methods: {
            addItem() {
                if (!this.hasOtopo) {
                    store.addItem({'id': null, 'name': '', color: '#000000', 'degrees': [],
                                   'ord': this.items.length > 0 ? this.items[this.items.length - 1].ord + 1 : 0});
                }
            },
        },
        template: `
            <div class="items">
                <Item
                    v-for="(item, index) in items"
                    :key="item.id ? item.id : 'new' + index"
                    :item="item"
                    :index="index"
                    :otopo="otopo"
                />
                <button
                    v-on:click="addItem"
                    class="btn btn-primary mt-2"
                    :disabled="hasOtopo"
                >
                    {{ strings.additem }}
                </button>
            </div>
        `
    };
});
