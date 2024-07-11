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
 * Define all the backup steps that will be used by the backup_otopo_activity_task.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete otopo structure for backup, with file and id annotations.
 */
class backup_otopo_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the structure of the backup workflow.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $otopo = new backup_nested_element('otopo', [ 'id' ], [
            'name', 'intro', 'introformat', 'timemodified',
            'showteachercomments', 'session', 'sessions', 'limit_sessions',
            'grade', 'gradeonlyforteacher',
            'allowsubmissionfromdate', 'allowsubmissiontodate',
            'sessionvisual', 'cohortvisual', 'completionsubmit',
        ]);

        $sessions = new backup_nested_element('nested_sessions');

        $session = new backup_nested_element('session', [ 'id' ], [
            'name',
            'color',
            'allowsubmissionfromdate',
            'allowsubmissiontodate',
        ]);

        $items = new backup_nested_element('items');

        $item = new backup_nested_element('item', [ 'id' ], [ 'name', 'color', 'ord' ]);

        $degrees = new backup_nested_element('degrees');

        $degree = new backup_nested_element('degree', [ 'id' ], [ 'name', 'description', 'grade', 'ord' ]);

        $userotopos = new backup_nested_element('user_otopos');

        $userotopo = new backup_nested_element('user_otopo', [ 'id' ], [
            'user',
            'session',
            'degree',
            'justification',
            'lastmodificationdate',
            'teacher_comment',
        ]);

        $uservalidsessions = new backup_nested_element('user_valid_sessions');

        $uservalidsession = new backup_nested_element('user_valid_session', [ 'id' ], [ 'user', 'session' ]);

        $graders = new backup_nested_element('graders');

        $grader = new backup_nested_element('grader', [ 'id' ], [ 'user', 'session', 'comment', 'grade' ]);

        // Build the tree.
        $otopo->add_child($sessions);
        $sessions->add_child($session);

        $otopo->add_child($items);
        $items->add_child($item);

        $item->add_child($degrees);
        $degrees->add_child($degree);

        $item->add_child($userotopos);
        $userotopos->add_child($userotopo);

        $otopo->add_child($uservalidsessions);
        $uservalidsessions->add_child($uservalidsession);

        $otopo->add_child($graders);
        $graders->add_child($grader);

        // Define sources.
        $otopo->set_source_table('otopo', [ 'id' => backup::VAR_ACTIVITYID ]);
        $session->set_source_table('otopo_session', [ 'otopo' => backup::VAR_ACTIVITYID ]);
        $item->set_source_table('otopo_item', [ 'otopo' => backup::VAR_ACTIVITYID ]);
        $degree->set_source_table('otopo_item_degree', [ 'item' => backup::VAR_PARENTID ]);

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $userotopo->set_source_table('otopo_user_otopo', [ 'item' => backup::VAR_PARENTID ]);
            $uservalidsession->set_source_table('otopo_user_valid_session', [ 'otopo' => backup::VAR_ACTIVITYID ]);
            $grader->set_source_table('otopo_grader', [ 'otopo' => backup::VAR_ACTIVITYID ]);
        }

        // Define id annotations.
        $userotopo->annotate_ids('user', 'user');
        $uservalidsession->annotate_ids('user', 'user');
        $grader->annotate_ids('user', 'user');

        $otopo->annotate_files('mod_otopo', 'intro', null, $contextid = null);

        // Return the root element (otopo), wrapped into standard activity structure.
        return $this->prepare_activity_structure($otopo);
    }
}
