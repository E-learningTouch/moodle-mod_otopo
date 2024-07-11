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
 * Utils.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {
    return {
        debounce: function(func, timeout = 1000) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(this, args), timeout);
            };
        },
        getAutoEvalStrings: async function() {
            var strings = {};
            var promises = Ajax.call([
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoeval'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalhelp'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalyourjustification'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalnoteachercomment'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalmodalsubtitle'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalmodalcontent'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalmodalcontent1'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevalmodalcontent2'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevaldesc'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevaldegree'
                    }
                },
                {
                    methodname: 'core_get_string',
                    args: {
                        component: 'otopo',
                        stringid: 'autoevaldescription'
                    }
                }
            ]);
            strings.autoeval = await promises[0];
            strings.help = await promises[1];
            strings.yourjustification = await promises[2];
            strings.noteachercomment = await promises[3];
            strings.modalsubtitle = await promises[4];
            strings.modalcontent = await promises[5];
            strings.modalcontent1 = await promises[6];
            strings.modalcontent2 = await promises[7];
            strings.desc = await promises[8];
            strings.degree = await promises[9];
            strings.description = await promises[10];

            return strings;
        }
    };
});
