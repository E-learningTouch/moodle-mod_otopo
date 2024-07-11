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
 * Form to grade a user.
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
 * Class of the form used to grade a user.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_form extends moodleform {
    /** @var object Otopo instance. */
    private object $otopo;

    /**
     * Add elements to form.
     */
    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;

        $this->otopo = $this->_customdata['otopo'];
        $data = $this->_customdata['grader'];

        $mform = $this->_form;
        $mform->setAttributes(['class' => 'gradeform']);

        if ($this->_customdata['disabled']) {
            $mform->addElement(
                'textarea',
                'comment',
                get_string('comment', 'otopo'),
                $this->_customdata['disabled'] ? 'disabled' : ''
            );
            $mform->setType('comment', PARAM_RAW);
        } else {
            $mform->addElement(
                'editor',
                'comment',
                get_string('comment', 'otopo'),
                ['enable_filemanagement' => false, 'h5p' => false]
            );
            $mform->setType('comment', PARAM_RAW);
        }
        if ($this->otopo->grade > 0) {
            $name = get_string('gradeoutof', 'assign', $this->otopo->grade);
            $gradingelement = $mform->addElement('text', 'grade', $name, $this->_customdata['disabled'] ? 'disabled' : '');
            $mform->addHelpButton('grade', 'gradeoutofhelp', 'assign');
            $mform->setType('grade', PARAM_RAW);
            $mform->setDefault('grade', $this->_customdata['default_grade']);
        } else {
            $grademenu = [-1 => get_string("nograde")] + make_grades_menu($this->otopo->grade);
            if (count($grademenu) > 1) {
                $gradingelement = $mform->addElement(
                    'select',
                    'grade',
                    get_string('grade') . ':',
                    $grademenu,
                    $this->_customdata['disabled'] ? 'disabled' : ''
                );
                if (!empty($data->grade)) {
                    $data->grade = (int)unformat_float($data->grade);
                }
                $mform->setType('grade', PARAM_INT);
            }
        }

        if (!empty($data->comment)) {
            $comment = $data->comment;
            $data->comment = ['text' => $comment];
        }

        if ($data) {
            $this->set_data($data);
        }
    }

    /**
     * This is required so when using "Save and next", each form is not defaulted to the previous form.
     * Giving each form a unique identitifer is enough to prevent this
     * (include the rownum in the form name).
     *
     * @return string - The unique identifier for this form.
     */
    protected function get_form_identifier() {
        $grader = $this->_customdata['grader'];
        return get_class($this) . '_' . $grader->userid . '_' . $grader->session;
    }

    /**
     * Validate the form's data.
     *
     * @param array $data The form's data.
     * @param array $files The form's files.
     * @return array of errors.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $instance = $this->otopo;

        // Advanced grading.
        if (!array_key_exists('grade', $data)) {
            return $errors;
        }

        if ($instance->grade > 0) {
            if (unformat_float($data['grade'], true) === false && (!empty($data['grade']))) {
                $errors['grade'] = get_string('invalidfloatforgrade', 'assign', $data['grade']);
            } else if (unformat_float($data['grade']) > $instance->grade) {
                $errors['grade'] = get_string('gradeabovemaximum', 'assign', $instance->grade);
            } else if (unformat_float($data['grade']) < 0) {
                $errors['grade'] = get_string('gradebelowzero', 'assign');
            }
        } else {
            // This is a scale.
            if ($scale = $DB->get_record('scale', ['id' => -($instance->grade)])) {
                $scaleoptions = make_menu_from_list($scale->scale);
                if ((int)$data['grade'] !== -1 && !array_key_exists((int)$data['grade'], $scaleoptions)) {
                    $errors['grade'] = get_string('invalidgradeforscale', 'assign');
                }
            }
        }
        return $errors;
    }
}
