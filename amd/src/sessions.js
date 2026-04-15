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
 * Sessions.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['vue', 'mod_otopo/color'], function(Vue, ColorPicker) {
    return {
        initDeleteSession: function(wwwroot) {
            Array.from(document.querySelectorAll('.deletesession button')).forEach(el => el.addEventListener('click', () => {
                var session = el.getAttribute('id').split('_').slice(-1)[0];
                var o = document.getElementsByName('o')[0].value;
                var id = document.getElementsByName('id[' + session + ']')[0].value;
                if (id == 0) {
                    el.remove();
                    document.getElementById('fitem_id_name_' + session).remove();
                    document.getElementById('fitem_id_color_' + session).remove();
                    document.getElementById('fitem_id_allowsubmissionfromdate_' + session).remove();
                    document.getElementById('fitem_id_allowsubmissiontodate_' + session).remove();
                    document.getElementsByName('option_repeats')[0].value--;
                } else {
                    location.href = wwwroot + '/mod/otopo/view.php?o=' + o
                        + "&action=edit&object=sessions&session-delete=" + session
                        + "&sesskey=" + M.cfg.sesskey;
                }
            }));
        },
        initColorPicker: function() {
            Array.from(document.getElementsByClassName('input-colorpicker')).forEach(el => {
                /**
                 * Initialise the colour picker :) Hoorah
                 */
                var input = el.querySelector('input');
                new Vue({
                    el: '#' + input.id,
                    components: {
                        'ColorPicker': ColorPicker,
                    },
                    data: function() {
                        return {
                            name: input.name,
                            value: input.value,
                        };
                    },
                    template: '<ColorPicker :name="name" :color="value"></ColorPicker>',
                });
            });
        }
    };
});
