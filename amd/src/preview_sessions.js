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
 * Preview sessions.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'vue',
    'mod_otopo/preview_sessions/components/items',
    'mod_otopo/items/ajax',
    'mod_otopo/utils',
    'core/log'
], function(
    Vue,
    Items,
    ajax,
    utils,
    Log
) {
    return {
        init: function(otopo, showComments, starPng, starContainerPng, helpPng, plusPng, minusPng) {
            var images = {
                'star': starPng,
                'starContainer': starContainerPng,
                'help': helpPng,
                'plus': plusPng,
                'minus': minusPng
            };

            ajax.getItems(otopo).then((items) => {
                utils.getAutoEvalStrings().then((strings) => {
                    new Vue({
                        el: '#preview-sessions',
                        components: {
                            'Items': Items,
                        },
                        template: '<Items></Items>',
                        data: {
                            showComments: showComments,
                            items: items,
                            strings: strings,
                            images: images
                        }
                    });

                    return true;
                }).catch(Log.error);

                return true;
            }).catch(Log.error);
        }
    };
});
