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
 * Form to add/remove and edit sessions.
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
 * Class of the form used to add/remove and edit sessions.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sessions_form extends moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;

        $mform = $this->_form;

        $mform->addElement('hidden', 'o', $this->_customdata['o']);
        $mform->setType('o', PARAM_INT);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'object', 'sessions');
        $mform->setType('object', PARAM_TEXT);

        $defaultsdate = [
            'optional' => false,
            'startyear' => 2020,
            'stopyear'  => 2040,
            'timezone'  => 99,
            'step'      => 5,
        ];

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'name', get_string('sessionname', 'otopo'), ['size' => '64']);
        $colorel = $mform->createElement(
            'text',
            'color',
            get_string('sessioncolor', 'otopo'),
            ['class' => 'input-colorpicker']
        );
        $repeatarray[] = $colorel;
        $repeatarray[] = $mform->createElement('hidden', 'id', 0);
        $repeatarray[] = $mform->createElement(
            'date_time_selector',
            'allowsubmissionfromdate',
            get_string('sessionallowsubmissionfromdate', 'otopo'),
            $defaultsdate
        );
        $repeatarray[] = $mform->createElement(
            'date_time_selector',
            'allowsubmissiontodate',
            get_string('sessionallowsubmissiontodate', 'otopo'),
            $defaultsdate
        );
        $repeatarray[] = $mform->createElement(
            'button',
            'delete',
            get_string("sessiondelete", 'otopo'),
            ['class' => 'deletesession']
        );

        $repeatno = $this->_customdata['count_sessions'];
        if ($repeatno == 0) {
            $repeatno = $this->_customdata['sessions'];
        }

        $repeateloptions = [];

        $mform->setType('name', PARAM_TEXT);
        $mform->setType('color', PARAM_TEXT);
        $mform->setType('id', PARAM_INT);

        $repeateloptions['allowsubmissionfromdate']['rule'] = 'required';
        $repeateloptions['allowsubmissiontodate']['rule'] = 'required';

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            1,
            get_string('sessionadd', 'otopo'),
            true
        );

        $this->add_action_buttons();
    }

    /**
     * Set the form's default data.
     *
     * @param array Form's default data.
     */
    public function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }

        if (!$defaultvalues) {
            $defaultvalues = [];
        }
        if (!array_key_exists('name', $defaultvalues)) {
            $defaultvalues['name'] = [];
        }
        if (!array_key_exists('color', $defaultvalues)) {
            $defaultvalues['color'] = [];
        }
        for ($i = 0; $i < $this->_form->_constantValues['option_repeats']; $i++) {
            if (!array_key_exists($i, $defaultvalues['name'])) {
                $defaultvalues['name'][$i] = "Session " . strval($i + 1);
            }
            if (!array_key_exists($i, $defaultvalues['color'])) {
                $defaultvalues['color'][$i] = '#000000';
            }
        }
        parent::set_data($defaultvalues);
    }

    /**
     * Validate the form's data.
     *
     * @param array $data The form's data.
     * @param array $files The form's files.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        for ($i = 0; $i < $data['option_repeats']; $i++) {
            if (
                $data['allowsubmissionfromdate'][$i] && $data['allowsubmissiontodate'][$i]
                && $data['allowsubmissionfromdate'][$i] >= $data['allowsubmissiontodate'][$i]
            ) {
                $errors["allowsubmissiontodate[{$i}]"] = get_string('allowsubmissiondateerror', 'otopo');
            }
        }
        return $errors;
    }
}
