<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Redirect the user to the appropiate submission related page.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $USER;

// Course module ID.
$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('otopo', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$moduleinstance = $DB->get_record('otopo', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

// Item number may be != 0 for activities that allow more than one grade per user.
$itemnumber = optional_param('itemnumber', 0, PARAM_INT);

// Graded user ID (optional).
$userid = optional_param('userid', 0, PARAM_INT);

$modulecontext = context_module::instance($cm->id);

if (has_capability('mod/otopo:grade', $modulecontext)) {
    redirect('view.php?id=' . $id . '&action=report&object=individual');
} else {
    if ($moduleinstance->session) {
        $session = get_last_session_closed($o);
    } else {
        $otopos = get_users_otopos($moduleinstance, [$USER->id]);
        if (array_key_exists($USER->id, $otopos)) {
            $otopo = $otopos[$USER->id];
            $sessions = array_reverse(array_keys($otopo));
            foreach ($sessions as $s) {
                if (session_is_valid($moduleinstance->id, $USER, $s)) {
                    $session = $s;
                    break;
                }
            }
        }
    }
    if ($session) {
        redirect('view.php?id=' . $id . '&action=evaluate&session=' . $session);
    }
}
redirect('view.php?id=' . $id);
