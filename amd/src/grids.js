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
 * Grids.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax',
    'mod_otopo/items/components/items',
    'vue',
    'mod_otopo/items/store',
    'mod_otopo/items/ajax',
    'core/log'
], function(
    Ajax,
    Items,
    Vue,
    store,
    ajax,
    Log
) {
    return {
        initGrid: function(otopo, hasOtopo, cmid = 0) {

            /**
             * Get strings.
             *
             * @returns {Object}
             */
            async function getStrings() { // eslint-disable-line no-unused-vars
                var strings = {};
                var promises = Ajax.call([
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemadditem'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemdeleteitem'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemchooseitemcolor'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemitem'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemdegree'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemadddegree'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemdeletedegree'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemdegreegrade'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'itemduplicateitem'
                        }
                    },
                    {
                        methodname: 'core_get_string',
                        args: {
                            component: 'otopo',
                            stringid: 'stringlimit255'
                        }
                    },
                ]);
                strings.additem = await promises[0];
                strings.deleteitem = await promises[1];
                strings.chooseitemcolor = await promises[2];
                strings.item = await promises[3];
                strings.degree = await promises[4];
                strings.adddegree = await promises[5];
                strings.deletedegree = await promises[6];
                strings.degreegrade = await promises[7];
                strings.duplicateitem = await promises[8];
                strings.stringlimit255 = await promises[9];

                return strings;
            }

            /**
             * Init the store.
             *
             * @param {Number} otopo
             * @param {Number} cmid
             */
            async function initStore(otopo, cmid) {
                var items = await ajax.getItems(otopo, cmid);
                store.state.items = items;
            }

            initStore(otopo, cmid).then(() => {
                otopo = parseInt(otopo);
                getStrings().then((strings) => {
                    new Vue({
                        el: '#otopo-grid',
                        components: {
                            'Items': Items,
                        },
                        template: '<Items :otopo="otopo"></Items>',
                        data: {
                            otopo: otopo,
                            cmid: cmid,
                            hasOtopo: hasOtopo,
                            strings: strings,
                            state: store.state
                        }
                    });
                    return true;
                }).catch(Log.error);
                return true;
            }).catch(Log.error);
        }
    };
});
