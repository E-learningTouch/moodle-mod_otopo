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
 * Define all the restore steps that will be used by the restore_otopo_activity_task.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one otopo activity.
 */
class restore_otopo_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('otopo', '/activity/otopo');
        $paths[] = new restore_path_element('session', '/activity/otopo/nested_sessions/session');
        $paths[] = new restore_path_element('item', '/activity/otopo/items/item');
        $paths[] = new restore_path_element('degree', '/activity/otopo/items/item/degrees/degree');
        if ($userinfo) {
            $paths[] = new restore_path_element('user_otopo', '/activity/otopo/items/item/user_otopos/user_otopo');
            $paths[] = new restore_path_element('user_valid_session', '/activity/otopo/user_valid_sessions/user_valid_session');
            $paths[] = new restore_path_element('grader', '/activity/otopo/graders/grader');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process otopo data.
     *
     * @param object|array $data The data.
     */
    protected function process_otopo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->allowsubmissionfromdate = $this->apply_date_offset($data->allowsubmissionfromdate);
        $data->allowsubmissiontodate = $this->apply_date_offset($data->allowsubmissiontodate);

        $data->timecreated = time();

        // Insert the otopo record.
        $newitemid = $DB->insert_record('otopo', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process session data.
     *
     * @param object|array $data The data.
     */
    protected function process_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->otopo = $this->get_new_parentid('otopo');

        $data->allowsubmissionfromdate = $this->apply_date_offset($data->allowsubmissionfromdate);
        $data->allowsubmissiontodate = $this->apply_date_offset($data->allowsubmissiontodate);

        $newitemid = $DB->insert_record('otopo_session', $data);
        $this->set_mapping('session', $oldid, $newitemid);
    }

    /**
     * Process item data.
     *
     * @param object|array $data The data.
     */
    protected function process_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->otopo = $this->get_new_parentid('otopo');

        $newitemid = $DB->insert_record('otopo_item', $data);
        $this->set_mapping('item', $oldid, $newitemid);
    }

    /**
     * Process degree data.
     *
     * @param object|array $data The data.
     */
    protected function process_degree($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->item = $this->get_new_parentid('item');

        $newitemid = $DB->insert_record('otopo_item_degree', $data);
        $this->set_mapping('degree', $oldid, $newitemid);
    }

    /**
     * Process user data.
     *
     * @param object|array $data The data.
     */
    protected function process_user_otopo($data) {
        global $DB;

        $data = (object)$data;

        $data->item = $this->get_new_parentid('item');
        $data->degree = $this->get_mappingid('degree', $data->degree);
        if ($data->session > 0) {
            $data->session = $this->get_mappingid('session', $data->session);
        }
        $data->userid = $this->get_mappingid('userid', $data->userid);

        $DB->insert_record('otopo_user_otopo', $data);
    }

    /**
     * Process user valid session data.
     *
     * @param object|array $data The data.
     */
    protected function process_user_valid_session($data) {
        global $DB;

        $data = (object)$data;

        $data->otopo = $this->get_new_parentid('otopo');
        if ($data->session > 0) {
            $data->session = $this->get_mappingid('session', $data->session);
        }
        $data->userid = $this->get_mappingid('userid', $data->userid);

        $DB->insert_record('otopo_user_valid_session', $data);
    }

    /**
     * Process grader data.
     *
     * @param object|array $data The data.
     */
    protected function process_grader($data) {
        global $DB;

        $data = (object)$data;

        $data->otopo = $this->get_new_parentid('otopo');
        if ($data->session > 0) {
            $data->session = $this->get_mappingid('session', $data->session);
        }
        $data->userid = $this->get_mappingid('userid', $data->userid);

        $newitemid = $DB->insert_record('otopo_grader', $data);
    }

    /**
     * Do after execution.
     */
    protected function after_execute() {
        $this->add_related_files('mod_otopo', 'intro', null);
    }
}
