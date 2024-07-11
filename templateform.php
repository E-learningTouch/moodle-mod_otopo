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
 * Form to create templates.
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
 * Class of the form used to create templates.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_form extends moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        $mform = $this->_form;

        if (array_key_exists('o', $this->_customdata)) {
            $mform->addElement('hidden', 'o', $this->_customdata['o']);
            $mform->setType('o', PARAM_INT);
            $mform->addElement('hidden', 'object', 'templates');
            $mform->setType('object', PARAM_TEXT);
        } else {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
        }

        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('templatename', 'otopo'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        if (array_key_exists('template', $this->_customdata)) {
            $mform->addElement('html', $this->_customdata['template']);
        }

        $this->add_action_buttons();
    }
}
