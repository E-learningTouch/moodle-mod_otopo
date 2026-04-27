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
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr>
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com>
 * @copyright   2022 Kosmos <moodle@kosmos.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class sessions_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'o', $this->_customdata['o']);
        $mform->setType('o', PARAM_INT);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'object', 'sessions');
        $mform->setType('object', PARAM_TEXT);
        $mform->addElement('hidden', 'sesskey', $this->_customdata['sesskey']);
        $mform->setType('sesskey', PARAM_TEXT);

        // Default configuration for date selectors.
        $currenttimestamp = time();
        $startyear = (int)date('Y', strtotime('-5 year', $currenttimestamp));
        $stopyear = (int)date('Y', strtotime('+10 years', $currenttimestamp));
        
        $defaultsdate = [
            'optional' => false,
            'startyear' => $startyear,
            'stopyear'  => $stopyear,
            'timezone'  => 99,
            'step'      => 5,
        ];

        $repeatno = $this->_customdata['count_sessions'];
        if ($repeatno == 0) {
            $repeatno = $this->_customdata['sessions'];
        }

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'name', get_string('sessionname', 'otopo'), ['size' => '64']);
        $colorel = $mform->createElement('text', 'color', get_string('sessioncolor', 'otopo'), ['class' => 'input-colorpicker']);
        $repeatarray[] = $colorel;
        $repeatarray[] = $mform->createElement('hidden', 'id', 0);
        $repeatarray[] = $mform->createElement('date_time_selector', 'allowsubmissionfromdate', get_string('sessionallowsubmissionfromdate', 'otopo'), $defaultsdate);
        $repeatarray[] = $mform->createElement('date_time_selector', 'allowsubmissiontodate', get_string('sessionallowsubmissiontodate', 'otopo'), $defaultsdate);
        $repeatarray[] = $mform->createElement('textarea', 'helpbubbletext', get_string('sessionhelpbubbletext', 'otopo'), 'wrap="virtual" rows="5" cols="10"');
        $repeatarray[] = $mform->createElement('button', 'delete', get_string("sessiondelete", 'otopo'), ['class' => 'deletesession']);

        $repeateloptions = [];
        $mform->setType('name', PARAM_TEXT);
        $mform->setType('color', PARAM_TEXT);
        $mform->setType('id', PARAM_INT);
        $mform->setType('helpbubbletext', PARAM_TEXT);

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

        $defaultvalues = [];

        for ($i = 0; $i < $repeatno; $i++) {
            $default_from_date = $currenttimestamp;
            $default_to_date = strtotime('+7 days', $default_from_date);
            $defaultvalues['allowsubmissionfromdate'][$i] = $default_from_date;
            $defaultvalues['allowsubmissiontodate'][$i] = $default_to_date;
            $defaultvalues['name'][$i] = "Session " . ($i + 1);
            $defaultvalues['color'][$i] = '#000000';
            $defaultvalues['helpbubbletext'][$i] = get_string("sessionhelptextdefaulttext", 'otopo');
        }

        $this->set_data($defaultvalues);
        $this->add_action_buttons();
    }

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
        if (!array_key_exists('helpbubbletext', $defaultvalues)) {
            $defaultvalues['helpbubbletext'] = [];
        }
        for ($i = 0; $i < $this->_form->_constantValues['option_repeats']; $i++) {
            if (!array_key_exists($i, $defaultvalues['name'])) {
                $defaultvalues['name'][$i] = "Session " . strval($i + 1);
            }
            if (!array_key_exists($i, $defaultvalues['color'])) {
                $defaultvalues['color'][$i] = '#000000';
            }
            if (!array_key_exists($i, $defaultvalues['helpbubbletext'])) {
                $defaultvalues['helpbubbletext'][$i] = get_string("sessionhelptextdefaulttext", 'otopo');
            }
        }
        parent::set_data($defaultvalues);
    }

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
