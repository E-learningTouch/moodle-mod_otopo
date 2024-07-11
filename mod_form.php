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
 * Form to create/edit activities.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/otopo/lib.php');

/**
 * Class of the form used to create/edit activities.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_otopo_mod_form extends moodleform_mod {
    /**
     * Add elements to form.
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        // -------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'otopo'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'otopo'));

        $mform->addElement('advcheckbox', 'showteachercomments', '', get_string('showteachercomments', 'otopo'), null, [0, 1]);
        $mform->setDefault('showteachercomments', get_config('mod_otopo', 'default_showteachercomments'));
        // -------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------
        $this->standard_grading_coursemodule_elements();

        $mform->addElement('advcheckbox', 'gradeonlyforteacher', '', get_string('gradeonlyforteacher', 'otopo'), null, [0, 1]);
        $mform->setDefault('gradeonlyforteacher', get_config('mod_otopo', 'default_gradeonlyforteacher'));
        // -------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------
        $mform->addElement('header', 'session_options', get_string('sessionoptions', 'otopo'));

        $mform->addElement('select', 'session', get_string('session', 'otopo'), [
            get_string('sessionlimited', 'otopo'),
            get_string('sessionopen', 'otopo'),
        ]);
        $mform->addHelpButton('session', 'session', 'otopo');
        $mform->setDefault('session', 1);

        $mform->addElement(
            'select',
            'sessions',
            get_string('sessions', 'otopo'),
            array_slice(range(0, get_config('mod_otopo', 'default_limit_sessions')), 1, null, true),
            ''
        );
        $mform->setDefault('sessions', get_config('mod_otopo', 'default_sessions'));
        $mform->hideIf('sessions', 'session', 'eq', 0);
        $mform->disabledIf('sessions', 'session', 'eq', 0);
        if ($this->current && $this->current->id) {
            $sessions = $DB->get_records('otopo_session', ['otopo' => $this->current->id], 'allowsubmissionfromdate', '*');
            if (count($sessions) > 0) {
                $mform->disabledIf('sessions', 'name', 'neq', '0');
            }
        }
        $mform->addElement(
            'select',
            'limit_sessions',
            get_string('limitsessions', 'otopo'),
            array_slice(range(0, get_config('mod_otopo', 'default_limit_sessions')), 1, null, true),
            ''
        );
        $mform->setDefault('limit_sessions', get_config('mod_otopo', 'default_limit_sessions'));
        $mform->hideIf('limit_sessions', 'session', 'eq', 1);
        $mform->disabledIf('limit_sessions', 'session', 'eq', 1);
        // -------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------
        $mform->addElement('header', 'disponibility', get_string('disponibility', 'otopo'));
        $defaultsdate = [
            'optional' => true,
            'startyear' => 2020,
            'stopyear'  => 2040,
            'timezone'  => 99,
            'step'      => 5,
        ];

        $mform->addElement(
            'date_time_selector',
            'allowsubmissionfromdate',
            get_string('allowsubmissionfromdate', 'otopo'),
            $defaultsdate
        );
        $mform->addElement(
            'date_time_selector',
            'allowsubmissiontodate',
            get_string('allowsubmissiontodate', 'otopo'),
            $defaultsdate
        );
        if (get_config('mod_otopo', 'default_sessionscalendar')) {
            $mform->setDefault('allowsubmissionfromdate', time());
            $mform->setDefault('allowsubmissiontodate', time());
        }

        // -------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------
        $mform->addElement('header', 'visual', get_string('visual', 'otopo'));

        $sessionvisuals = [];
        $sessionvisuals[] = $mform->createElement(
            'radio',
            'sessionvisual',
            '',
            '<img src="' . $OUTPUT->image_url('radar', 'mod_otopo') . '">',
            0,
            ['class' => 'visual']
        );
        $sessionvisuals[] = $mform->createElement(
            'radio',
            'sessionvisual',
            '',
            '<img src="' . $OUTPUT->image_url('bar', 'mod_otopo') . '">',
            1,
            ['class' => 'visual']
        );
        $mform->addGroup($sessionvisuals, 'sessionvisual', get_string('sessionvisual', 'otopo'), [' '], false);
        $mform->setDefault('sessionvisual', get_config('mod_otopo', 'default_sessionvisual'));

        $cohortvisuals = [];
        $cohortvisuals[] = $mform->createElement(
            'radio',
            'cohortvisual',
            '',
            '<img src="' . $OUTPUT->image_url('stacked_bar', 'mod_otopo') . '">',
            0,
            ['class' => 'visual']
        );
        $mform->addGroup($cohortvisuals, 'cohortvisual', get_string('cohortvisual', 'otopo'), [' '], false);
        $mform->setDefault('cohortvisual', get_config('mod_otopo', 'default_cohortvisual'));
        // -------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        $this->apply_admin_defaults();
        // -------------------------------------------------------------------------------

        $this->add_action_buttons();
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
        if (
            $data['allowsubmissionfromdate'] && $data['allowsubmissiontodate']
            && $data['allowsubmissionfromdate'] >= $data['allowsubmissiontodate']
        ) {
            $errors['allowsubmissiontodate'] = get_string('allowsubmissiondateerror', 'otopo');
        }
        return $errors;
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param object $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Set up completion section even if checkbox is not ticked.
        if (!empty($data->completionunlocked)) {
            if (empty($data->completionsubmit)) {
                $data->completionsubmit = 0;
            }
        }
    }

    /**
     * Add completion rules to the form.
     *
     * @return array of rules.
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement(
            'checkbox',
            'completionsubmit',
            get_string('otopoconditioncompletion', 'otopo'),
            get_string('completionsubmit', 'otopo')
        );
        $mform->addHelpButton('completionsubmit', 'completionsubmit', 'otopo');
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return ['completionsubmit'];
    }

    /**
     * Return if completion rules are enabled.
     *
     * @return bool True if rules are enabled, false otherwise.
     */
    public function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}
