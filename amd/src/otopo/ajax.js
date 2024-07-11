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
 * Otopo ajax.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {
    return {
        getUserOtopo: async function(otopo, session) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_get_user_otopo',
                args: {otopo: otopo, session: session},
            }]);
            var otopos = await promises[0];
            return otopos;
        },
        setUserOtopo: async function(otopo, session, item, degree, justification) {
            var promises = Ajax.call([{
                methodname: 'mod_otopo_set_user_otopo',
                args: {
                    otopo: otopo,
                    session: session,
                    item: item,
                    degree: degree,
                    justification: justification
                },
            }]);
            await promises[0];
        }
    };
});
