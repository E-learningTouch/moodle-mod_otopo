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
 * Library of interface functions and constants.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once(__DIR__ . '/grade_form.php');
require_once(__DIR__ . '/locallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->libdir . '/formslib.php');

define('OTOPO_EVENT_TYPE_ACTIVITY', 'otopo_activity');
define('OTOPO_EVENT_TYPE_SESSION', 'otopo_session');

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function otopo_supports(string $feature) {
    // This for compatibility with 3.X version of moodle.
    if (defined('FEATURE_MOD_PURPOSE') && $feature === FEATURE_MOD_PURPOSE) {
        return MOD_PURPOSE_CONTENT;
    }

    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_otopo into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_otopo_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function otopo_add_instance(object $moduleinstance, mod_otopo_mod_form $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $moduleinstance->id = $DB->insert_record('otopo', $moduleinstance);

    $event = new stdClass();
    $event->eventtype = OTOPO_EVENT_TYPE_ACTIVITY;
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->name = $moduleinstance->name . ' - ' . get_string('start', 'otopo');
    $event->format = FORMAT_HTML;
    $event->courseid = $moduleinstance->course;
    $event->groupid = 0;
    $event->userid = 0;
    $event->modulename = 'otopo';
    $event->instance = $moduleinstance->id;
    $event->timestart = $moduleinstance->allowsubmissionfromdate;
    $event->timeduration = 0;
    $event->visible = instance_is_visible('otopo', $moduleinstance);

    $event = calendar_event::create($event, false);

    $moduleinstance->event_start = $event->id;

    $event = new stdClass();
    $event->eventtype = OTOPO_EVENT_TYPE_ACTIVITY;
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->name = $moduleinstance->name . ' - ' . get_string('end', 'otopo');
    $event->format = FORMAT_HTML;
    $event->courseid = $moduleinstance->course;
    $event->groupid = 0;
    $event->userid = 0;
    $event->modulename = 'otopo';
    $event->instance = $moduleinstance->id;
    $event->timestart = $moduleinstance->allowsubmissiontodate;
    $event->timeduration = 0;
    $event->visible = instance_is_visible('otopo', $moduleinstance);

    $event = calendar_event::create($event, false);

    $moduleinstance->event_end = $event->id;

    $DB->update_record('otopo', $moduleinstance);

    return $moduleinstance->id;
}

/**
 * Updates an instance of the mod_otopo in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_otopo_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function otopo_update_instance(object $moduleinstance, mod_otopo_mod_form $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    $sessions = $DB->get_records('otopo_session', [ 'otopo' => $moduleinstance->id ], 'allowsubmissionfromdate', '*');
    if (count($sessions) > 0) {
        $moduleinstance->sessions = count($sessions);
    }

    $oldinstance = $DB->get_record('otopo', [ 'id' => $moduleinstance->id ]);
    if ($DB->update_record('otopo', $moduleinstance)) {
        $moduleinstance = $DB->get_record('otopo', [ 'id' => $moduleinstance->id ]);

        $course = $DB->get_record('course', [ 'id' => $moduleinstance->course ], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('otopo', $moduleinstance->id, $course->id, false, MUST_EXIST);
        $modulecontext = context_module::instance($cm->id);

        $event = \mod_otopo\event\activity_updated::create([ 'context' => $modulecontext ]);
        $event->trigger();

        otopo_update_grades($moduleinstance);

        if ($moduleinstance->event_start) {
            $event = calendar_event::load($moduleinstance->event_start);
            if ($event) {
                $event->name = $moduleinstance->name . ' - ' . get_string('start', 'otopo');
                $event->timestart = $moduleinstance->allowsubmissionfromdate;
                $event->visible = instance_is_visible('otopo', $moduleinstance);
                $event->update($event, false);
            }
        } else if ($oldinstance->event_start) {
            $event = calendar_event::load($moduleinstance->event_start);
            if ($event) {
                $event->delete();
            }
        }
        if ($moduleinstance->event_end) {
            $event = calendar_event::load($moduleinstance->event_end);
            if ($event) {
                $event->name = $moduleinstance->name . ' - ' . get_string('end', 'otopo');
                $event->timestart = $moduleinstance->allowsubmissiontodate;
                $event->visible = instance_is_visible('otopo', $moduleinstance);
                $event->update($event, false);
            }
        } else if ($oldinstance->event_end) {
            $event = calendar_event::load($moduleinstance->event_end);
            if ($event) {
                $event->delete();
            }
        }

        return true;
    }

    return false;
}

/**
 * Removes an instance of the mod_otopo from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function otopo_delete_instance(int $id) {
    global $DB;

    $exists = $DB->get_record('otopo', [ 'id' => $id ]);
    if (!$exists) {
        return false;
    }

    $sessions = $DB->get_records('otopo_session', [ 'otopo' => $id ]);
    foreach ($sessions as $session) {
        if ($session->event_start) {
            $event = calendar_event::load($session->event_start);
            if ($event) {
                $event->delete();
            }
        }
        if ($session->event_end) {
            $event = calendar_event::load($session->event_end);
            if ($event) {
                $event->delete();
            }
        }
    }
    $DB->delete_records('otopo_session', [ 'otopo' => $id ]);
    $items = $DB->get_records('otopo_item', [ 'otopo' => $id ]);
    if (!empty($items)) {
        [$insql, $params] = $DB->get_in_or_equal(array_keys($items));
        $DB->delete_records_select('otopo_item_degree', "item $insql", $params);
        $DB->delete_records_select('otopo_user_otopo', "item $insql", $params);
    }
    $DB->delete_records('otopo_item', [ 'otopo' => $id ]);
    $DB->delete_records('otopo_user_valid_session', [ 'otopo' => $id ]);
    $DB->delete_records('otopo_grader', [ 'otopo' => $id ]);

    $moduleinstance = $DB->get_record('otopo', [ 'id' => $id ]);

    if ($moduleinstance->event_start) {
        $event = calendar_event::load($moduleinstance->event_start);
        if ($event) {
            $event->delete();
        }
    }
    if ($moduleinstance->event_end) {
        $event = calendar_event::load($moduleinstance->event_end);
        if ($event) {
            $event->delete();
        }
    }

    $DB->delete_records('otopo', [ 'id' => $id ]);

    return true;
}

/**
 * Is a given scale used by the instance of mod_otopo?
 *
 * This function returns if a scale is being used by one mod_otopo
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_otopo instance.
 */
function otopo_scale_used(int $moduleinstanceid, int $scaleid) {
    global $DB;
    return $scaleid && $DB->record_exists('otopo', [ 'id' => $moduleinstanceid, 'grade' => -$scaleid ]);
}

/**
 * Checks if scale is being used by any instance of mod_otopo.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_otopo instance.
 */
function otopo_scale_used_anywhere(int $scaleid) {
    global $DB;
    return $scaleid && $DB->record_exists('otopo', [ 'grade' => -$scaleid ]);
}

/**
 * Creates or updates grade item for the given mod_otopo instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param object $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param array|string $grades Grades in the gradebook.
 * @return void.
 */
function otopo_grade_item_update(object $moduleinstance, $grades = []) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = [];
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($grades === 'reset') {
        $item['reset'] = true;
        $grades = null;
    }

    grade_update('/mod/otopo', $moduleinstance->course, 'mod', 'otopo', $moduleinstance->id, 0, $grades, $item);
}

/**
 * Delete grade item for given mod_otopo instance.
 *
 * @param object $moduleinstance Instance object.
 * @return grade_item.
 */
function otopo_grade_item_delete(object $moduleinstance) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update(
        '/mod/otopo',
        $moduleinstance->course,
        'mod',
        'otopo',
        $moduleinstance->id,
        0,
        null,
        [ 'deleted' => 1 ]
    );
}

/**
 * Update mod_otopo grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param object $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function otopo_update_grades(object $moduleinstance, int $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($moduleinstance->gradeonlyforteacher) {
        otopo_grade_item_update($moduleinstance, 'reset');
        return;
    }

    $grades = [];

    $course = $DB->get_record('course', [ 'id' => $moduleinstance->course ], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('otopo', $moduleinstance->id, $course->id, false, MUST_EXIST);
    $modulecontext = context_module::instance($cm->id);

    $items = get_items_sorted_from_otopo($moduleinstance->id);
    $participants = get_participants($moduleinstance, $modulecontext);

    // Populate array of grade objects indexed by userid.
    if ($userid == 0) {
        foreach ($participants as $participant) {
            $otopos = get_user_otopos($moduleinstance, $participant);
            $sessions = prepare_data($moduleinstance, $items, $otopos, $participant);
            foreach (array_reverse($sessions) as $session) {
                if ($session->isvalid) {
                    $grades[$participant->id] = convert_grade_to_gradebook($participant->id, $session->grade, $session->comment);
                    break;
                }
            }
        }
    } else {
        $participant = (object) [ 'id' => $userid ];
        $otopos = get_user_otopos($moduleinstance, $participant);
        $sessions = prepare_data($moduleinstance, $items, $otopos, $participant);
        foreach (array_reverse($sessions) as $session) {
            if ($session->isvalid) {
                $grades[$participant->id] = convert_grade_to_gradebook($participant->id, $session->grade, $session->comment);
                break;
            }
        }
    }
    otopo_grade_item_update($moduleinstance, $grades);
}

/**
 * Serve the grading panel as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function mod_otopo_output_fragment_gradingpanel(array $args) {
    global $CFG, $DB, $OUTPUT;

    $context = $args['context'];

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    $cm = get_coursemodule_from_id('otopo', $context->instanceid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', [ 'id' => $cm->course ], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('otopo', [ 'id' => $cm->instance ], '*', MUST_EXIST);

    require_capability('mod/otopo:grade', $context);

    $sessionid = $args['session'];
    $userid = $args['userid'];

    $otoposforms = [];

    $user = (object) [ 'id' => $userid ];

    $grader = $DB->get_record('otopo_grader', [ 'userid' => $userid, 'session' => $sessionid, 'otopo' => $moduleinstance->id ]);
    $otopos = get_user_otopos($moduleinstance, $user);
    $items = get_items_from_otopo($moduleinstance->id);
    $itemssorted = get_items_sorted_from_otopo($moduleinstance->id);
    $sessions = prepare_data($moduleinstance, $itemssorted, $otopos, $user);
    if ($moduleinstance->session) {
        $session = $sessions[$sessionid];
    } else {
        $session = $sessions[abs($sessionid) - 1];
    }
    foreach ($otopos as $itemid => $otopossession) {
        if (array_key_exists($sessionid, $otopossession)) {
            $otoposforms[$items[$itemid]->ord] = [
                'title' => $items[$itemid]->name,
                'id' => $itemid,
                'comment' => $otopossession[$sessionid]->teacher_comment,
            ];
        }
    }
    $otoposforms = array_values($otoposforms);

    $globalform = [
        'title' => get_string('autoeval', 'otopo') . ' ' . $session->key,
        'form' => (new grade_form(null, [
            'otopo' => $moduleinstance,
            'grader' => $grader,
            'default_grade' => $session->grade,
            'disabled' => !$session->isvalidorclosed,
        ]))->render(),
        'validated' => $session->isvalidorclosed,
        'notevaluated' => !$grader->grade,
    ];

    return $OUTPUT->render_from_template('mod_otopo/grade_form', [
        'globalform' => $globalform,
        'otoposforms' => $otoposforms,
        'disabled' => !$session->isvalidorclosed,
    ]);
}

/**
 * Retrieve the course completion state.
 *
 * @param object $course Course.
 * @param object $cm Course module.
 * @param int $userid The user ID.
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions).
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function otopo_get_completion_state(object $course, object $cm, int $userid, bool $type) {
    global $CFG, $DB;

    $otopo = $DB->get_record('otopo', [ 'id' => $cm->instance ], '*', MUST_EXIST);

    if ($otopo->completionsubmit) {
        if ($otopo->session) {
            $lastsession = get_last_session_closed($otopo);
            return session_is_valid_or_closed($otopo->id, (object) [ 'id' => $userid ], $lastsession->id);
        } else {
            $otopos = get_users_otopos($otopo, [$userid]);
            if (array_key_exists($userid, $otopos)) {
                foreach ($otopos[$userid] as $session => $os) {
                    if (session_is_valid($otopo->id, (object) [ 'id' => $userid ], $session)) {
                        return true;
                    }
                }
            }
            return false;
        }
    } else {
        return $type;
    }
}

/**
 * Is the event visible?
 *
 * This is used to determine global visibility of an event in all places throughout Moodle. For example,
 * the ASSIGN_EVENT_TYPE_GRADINGDUE event will not be shown to students on their calendar, and
 * ASSIGN_EVENT_TYPE_DUE events will not be shown to teachers.
 *
 * @param calendar_event $event Calendar event.
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function mod_otopo_core_calendar_is_event_visible(calendar_event $event) {
    global $DB;

    $moduleinstance = $DB->get_record('otopo', [ 'id' => $event->instance ], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('otopo', $event->instance, $event->courseid, false, MUST_EXIST);
    $modulecontext = context_module::instance($cm->id);

    if ($event->eventtype == OTOPO_EVENT_TYPE_ACTIVITY && $moduleinstance->session) {
        return false;
    }
    if ($event->eventtype == OTOPO_EVENT_TYPE_SESSION) {
        if (!$moduleinstance->session) {
            return false;
        }
        return has_capability('mod/otopo:fill', $modulecontext);
    }
    return true;
}

/**
 * Provide calendar event action.
 *
 * @param calendar_event $event Calendar event.
 * @param \core_calendar\action_factory $factory Calendar factory.
 */
function mod_otopo_core_calendar_provide_event_action(calendar_event $event, \core_calendar\action_factory $factory) {
    global $USER;

    $cm = get_coursemodule_from_instance('otopo', $event->instance, $event->courseid, false, MUST_EXIST);

    if ($event->eventtype == OTOPO_EVENT_TYPE_SESSION) {
        return $factory->create_instance(
            get_string('fill', 'otopo'),
            new \moodle_url('/mod/otopo/view.php', [ 'id' => $cm->id, 'action' => 'evaluate', 'session' => $event->repeatid ]),
            1,
            !session_is_valid_or_closed($event->instance, $USER, $event->repeatid)
        );
    } else {
        return $factory->create_instance(
            get_string('otopo:view', 'otopo'),
            new \moodle_url('/mod/otopo/view.php', [ 'id' => $cm->id ]),
            1,
            true
        );
    }
}

/**
 * Add elements to the course form.
 *
 * @param MoodleQuickForm $mform The form.
 */
function otopo_reset_course_form_definition(MoodleQuickForm &$mform) {
    $mform->addElement('header', 'otopoheader', get_string('modulenameplural', 'otopo'));
    $mform->addElement('advcheckbox', 'reset_otopo_user_otopo', get_string('deleteotopos', 'otopo'));
    $mform->addElement('advcheckbox', 'reset_otopo_grader', get_string('deletegrader', 'otopo'));
}

/**
 * Implements callback to reset course.
 *
 * @param object $data User data.
 * @return boolean|array
 */
function otopo_reset_userdata(object $data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'otopo');
    $status = [];

    // Get the wiki(s) in this course.
    if (!$otopos = $DB->get_records('otopo', [ 'course' => $data->courseid ])) {
        return false;
    }
    if (empty($data->reset_otopo_user_otopo) && empty($data->reset_otopo_grader)) {
        return $status;
    }

    foreach ($otopos as $otopo) {
        if (!$cm = get_coursemodule_from_instance('otopo', $otopo->id, $data->courseid)) {
            continue;
        }

        if (!empty($data->reset_otopo_user_otopo)) {
            $items = $DB->get_records('otopo_item', [ 'otopo' => $otopo->id ]);
            if (!empty($items)) {
                [$insql, $params] = $DB->get_in_or_equal(array_keys($items));
                $DB->delete_records_select('otopo_user_otopo', "item $insql", $params);
            }
            $DB->delete_records('otopo_user_valid_session', [ 'otopo' => $otopo->id ]);
            $status[] = [ 'component' => $componentstr, 'item' => get_string('deleteotopos', 'otopo'), 'error' => false ];
        }

        if (!empty($data->reset_otopo_grader)) {
            $DB->delete_records('otopo_grader', [ 'otopo' => $otopo->id ]);
            $status[] = [ 'component' => $componentstr, 'item' => get_string('deletegrader', 'otopo'), 'error' => false ];
        }
    }

    return $status;
}
