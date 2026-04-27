<?php
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
 * Privacy API implementation.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @author      Oara <n.lebars@elearningtouch.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otopo\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\helper;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation class.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @author      Oara <n.lebars@elearningtouch.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements core_userlist_provider, metadata_provider, plugin_provider {
    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
            'otopo_user_otopo',
            [
                'userid' => 'privacy:metadata:otopo_user_otopo:userid',
                'session' => 'privacy:metadata:otopo_user_otopo:session',
                'item' => 'privacy:metadata:otopo_user_otopo:item',
                'degree' => 'privacy:metadata:otopo_user_otopo:degree',
                'justification' => 'privacy:metadata:otopo_user_otopo:justification',
                'lastmodificationdate' => 'privacy:metadata:otopo_user_otopo:lastmodificationdate',
                'teacher_comment' => 'privacy:metadata:otopo_user_otopo:teacher_comment',
            ],
            'privacy:metadata:otopo_user_otopo'
        );

        $collection->add_database_table(
            'otopo_user_valid_session',
            [
                'userid' => 'privacy:metadata:otopo_user_valid_session:userid',
                'otopo' => 'privacy:metadata:otopo_user_valid_session:otopo',
                'session' => 'privacy:metadata:otopo_user_valid_session:session',
            ],
            'privacy:metadata:otopo_user_valid_session'
        );

        $collection->add_database_table(
            'otopo_grader',
            [
                'userid' => 'privacy:metadata:otopo_grader:userid',
                'session' => 'privacy:metadata:otopo_grader:session',
                'otopo' => 'privacy:metadata:otopo_grader:otopo',
                'comment' => 'privacy:metadata:otopo_grader:comment',
                'grade' => 'privacy:metadata:otopo_grader:grade',
            ],
            'privacy:metadata:otopo_grader'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $contextlist->add_from_sql('
            SELECT DISTINCT ctx.id
            FROM {modules} m
            JOIN {course_modules} cm ON cm.module = m.id
            JOIN {context} ctx ON ctx.instanceid = cm.id
            JOIN {otopo_session} os ON os.otopo = cm.instance
            JOIN {otopo_user_otopo} ouo ON ouo.session = os.id
            WHERE m.name = :modulename AND ctx.contextlevel = :modulelevel AND ouo.userid = :userid
        ', [
            'modulename' => 'otopo',
            'modulelevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, context_module::class)) {
            return;
        }

        $userlist->add_from_sql('userid', '
            SELECT DISTINCT ouo.userid
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            JOIN {otopo_session} os ON os.otopo = cm.instance
            JOIN {otopo_user_otopo} ouo ON ouo.session = os.id
            WHERE cm.id = :instanceid AND m.name = :modulename
        ', [
            'instanceid'    => $context->instanceid,
            'modulename'    => 'otopo',
        ]);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $cmids = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->instanceid;
            }
            return $carry;
        }, []);
        if (empty($cmids)) {
            return;
        }

        $user = $contextlist->get_user();

        // Export the grades and related comments.
        [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cmid');
        $records = $DB->get_recordset_sql("
            SELECT DISTINCT cm.id, o.name AS modulename, os.name AS sessionname, ouo.justification, og.grade, ouo.teacher_comment
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            JOIN {otopo} o ON o.id = cm.instance
            JOIN {otopo_session} os ON os.otopo = o.id
            JOIN {otopo_user_otopo} ouo ON ouo.session = os.id
            LEFT JOIN {otopo_grader} og ON og.session = ouo.session AND og.userid = ouo.userid
            WHERE cm.id {$insql} AND m.name = :modulename AND ouo.userid = :userid
            ORDER BY o.id, os.id
        ", array_merge($inparams, [ 'modulename' => 'otopo', 'userid' => $user->id ]));

        $data = [];
        foreach ($records as $record) {
            $data[$record->id][] = [
                'activity_name' => $record->modulename,
                'session_name' => $record->sessionname,
                'justification' => $record->justification,
                'grade' => $record->grade,
                'teacher_comment' => $record->teacher_comment,
            ];
        }

        foreach ($data as $cmid => $exportdata) {
            $context = context_module::instance($cmid);
            $contextdata = helper::get_context_data($context, $user);
            $exportdata = array_merge($contextdata, [ 'sessions' => $exportdata ]);

            helper::export_context_files($context, $user);
            writer::with_context($context)->export_data([], (object) $exportdata);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('otopo', $context->instanceid);
        if (!$cm) {
            return;
        }

        // Fetch sessions from the otopo instance.
        $sessions = $DB->get_field('otopo_session', 'id', [ 'otopo' => $cm->instance ]);
        [$insql, $inparams] = $DB->get_in_or_equal($sessions, SQL_PARAMS_NAMED, 'session');

        // Delete with session ids.
        $select = "session {$insql}";
        $params = $inparams;

        $DB->delete_records_select('otopo_user_otopo', $select, $params);
        $DB->delete_records_select('otopo_user_valid_session', $select, $params);
        $DB->delete_records_select('otopo_grader', $select, $params);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $cmids = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->instanceid;
            }
            return $carry;
        }, []);

        if (!$cmids) {
            return;
        }

        // Fetch sessions from otopo instances.
        [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cmid');
        $sessions = $DB->get_field_select('otopo_session', 'id', "otopo {$insql}", $inparams);
        [$insql, $inparams] = $DB->get_in_or_equal($sessions, SQL_PARAMS_NAMED, 'session');

        // Delete with session ids and user id.
        $select = "session {$insql} AND userid = :userid";
        $params = array_merge($inparams, [ 'userid' => $contextlist->get_user()->id ]);

        $DB->delete_records_select('otopo_user_otopo', $select, $params);
        $DB->delete_records_select('otopo_user_valid_session', $select, $params);
        $DB->delete_records_select('otopo_grader', $select, $params);
    }


    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', [ 'id' => $context->instanceid ]);

        // Fetch sessions from the otopo instance.
        $sessions = $DB->get_field('otopo_session', 'id', [ 'otopo' => $cm->instance ]);
        [$insql, $inparams] = $DB->get_in_or_equal($sessions, SQL_PARAMS_NAMED, 'session');

        // Delete with session ids and user ids.
        [$userinsql, $userinparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED, 'userid');

        $select = "session {$insql} AND userid {$userinsql}";
        $params = array_merge($inparams, $userinparams);

        $DB->delete_records_select('otopo_user_otopo', $select, $params);
        $DB->delete_records_select('otopo_user_valid_session', $select, $params);
        $DB->delete_records_select('otopo_grader', $select, $params);
    }
}
