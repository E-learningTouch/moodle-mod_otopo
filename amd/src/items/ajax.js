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
 * Items ajax.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {
    return {
        getItems: async function(otopo, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_get_items',
                args: {otopo: otopo, cmid: cmid},
            }]);
            var items = await promises[0];
            return items;
        },

        createItem: async function(otopo, item, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_create_item',
                args: {otopo: otopo, item: item, cmid: cmid},
            }]);
            var itemId = await promises[0];
            return itemId;
        },

        editItem: async function(item, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_edit_item',
                args: {item: item, cmid: cmid},
            }]);
            await promises[0];
        },

        deleteItem: async function(itemId, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_delete_item',
                args: {itemid: itemId, cmid: cmid},
            }]);
            await promises[0];
        },

        createDegree: async function(itemId, degree, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_create_degree',
                args: {itemid: itemId, degree: degree, cmid: cmid},
            }]);
            var degreeId = await promises[0];
            return degreeId;
        },

        editDegree: async function(degree, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_edit_degree',
                args: {degree: degree, cmid: cmid},
            }]);
            await promises[0];
        },

        deleteDegree: async function(degreeId, cmid) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_delete_degree',
                args: {degreeid: degreeId, cmid: cmid},
            }]);
            await promises[0];
        }
    };
});
