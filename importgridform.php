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
 * Form to import grid from a CSV file.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Class of the form used to import grid from a CSV file.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class importgrid_form extends moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;

        $mform = $this->_form;

        $mform->addElement('hidden', 'o', $this->_customdata['o']);
        $mform->setType('o', PARAM_INT);
        $mform->addElement('hidden', 'action', 'import');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'object', 'grids');
        $mform->setType('object', PARAM_TEXT);

        $mform->addElement(
            'filepicker',
            'csv',
            get_string('file'),
            null,
            ['accepted_types' => ['text/csv'], 'max_files' => 1]
        );

        $this->add_action_buttons();
    }
}
