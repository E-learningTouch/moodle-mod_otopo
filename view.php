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
 * The view page.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This page should be completely reworked.
 * ;-(
 *
 * E.g:
 * - Instead of using `_customdata` and hidden elements in mforms, it would be better to use the actual url of the page or a
 *   moodle_url.
 * - Instead of merging everything into a single page, it would be better to have separate pages for each concept
 *   (manage_session, manage_template, manage_grid, etc.), one for each form so to speak.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/sessionsform.php');
require_once(__DIR__ . '/fromtemplateform.php');
require_once(__DIR__ . '/importgridform.php');
require_once(__DIR__ . '/templateform.php');
require_once(__DIR__ . '/mod_form.php');
require_once(__DIR__ . '/locallib.php');

global $CFG;

require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->libdir . '/xsendfilelib.php');

use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use mod_otopo\output\grading_app;

define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

/***********
 * Params. *
 ***********/
$o = optional_param('o', 0, PARAM_INT); // Activity instance id.
$action = optional_param('action', '', PARAM_TEXT);
$object = optional_param('object', 'params', PARAM_TEXT);
$sectionreturn = optional_param('sr', null, PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

/******************
 * Access checks. *
 ******************/
if ($o) {
    $id = 0;
    $moduleinstance = $DB->get_record('otopo', ['id' => $o], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('otopo', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else if ($id = optional_param('id', 0, PARAM_INT)) {
    [ $course, $cm ] = get_course_and_cm_from_cmid($id);
    $moduleinstance = $DB->get_record('otopo', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    // Oh oh something seems wrong.
    throw new \coding_exception("Missing 'o' or 'id' parameter.");
}

require_login($course, false, $cm);

$modulecontext = context_module::instance($cm->id);
require_capability('mod/otopo:view', $modulecontext);

$canadmin = has_capability('mod/otopo:admin', $modulecontext);
$cangrade = has_capability('mod/otopo:grade', $modulecontext);
$canexportresults = has_capability('mod/otopo:exportresults', $modulecontext);
$canmanagetemplates = has_capability('mod/otopo:managetemplates', $modulecontext);

/***************
 * Page setup. *
 ***************/
$url = new moodle_url('/mod/otopo/view.php', [
    'o' => $moduleinstance->id,
    'id' => $cm->id,
    'action' => $action,
    'object' => $object,
    '$sectionreturn' => $sectionreturn,
    'perpage' => $perpage,
    'sesskey' => $sesskey,
]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$event = \mod_otopo\event\activity_viewed::create(['context' => $modulecontext]);
$event->trigger();

/*******************
 * Prepare output. *
 *******************/
$output = $PAGE->get_renderer('mod_otopo');
$body = "";

if ($canadmin || $cangrade || $canexportresults) {
    $content = null;
    if (!$action) {
        $action = 'edit';

        // This is a special case where the user has some capabilities and can therefore edit the activity settings, but accesses
        // the activity “normally” (that's why $action can be null/empty).
        //
        // This is one of the reasons why this page should be reworked, as some capabilities and actions should only be checks in
        // edit mode or something like that.
        //
        // We could have automatically redirected the user with a valid sesskey (but it's ugly and bad, and ugly!) or modified the
        // url using the `cm_info_dynamic` callback to add the sesskey, but it turns out that the url is escaped by mustache and so
        // hello escaped HTML entities ;-(. And yes, it could have worked, since `$cm->url` returns an instance of moodle_url.
        // ---
        // Anyway, it's better than nothing... For the time being.
        $sesskey = sesskey();
    }

    if ($action !== 'preview' && $action !== 'evolution' && $action !== 'progress') {
        // The 'preview', 'evolution' and 'progress' actions do not require this check.
        // To understand why this check is here, you need to dig into the code ;-).
        // It's not the clearest way to do it, but this whole page would have to be redesigned to make it clearer.
        confirm_sesskey($sesskey);
    }

    if ($action === 'grader') {
        require_capability('mod/otopo:grade', $modulecontext);

        $PAGE->set_pagelayout('embedded');
        $PAGE->activityheader->disable();

        $userid = optional_param('user', 0, PARAM_INT);
        $session = optional_param('session', null, PARAM_INT);

        $body = $output->render(new grading_app($userid, $course, $cm, $moduleinstance, $modulecontext, $session));
    } else if ($canadmin) {
        if ($action === 'edit') {
            if ($object == "sessions") {
                // The $sesskey param should be set, no need to gen a new one when redirecting.

                include('vue.php');

                $sessiondelete = optional_param('session-delete', -1, PARAM_INT);

                $PAGE->requires->js_call_amd('mod_otopo/sessions', 'initDeleteSession', ['wwwroot' => $CFG->wwwroot]);
                $PAGE->requires->js_call_amd('mod_otopo/sessions', 'initColorPicker');

                $sessions = $DB->get_records('otopo_session', ['otopo' => $moduleinstance->id], 'allowsubmissionfromdate', '*');

                $toform = toform_from_sessions($sessions);
                if ($sessiondelete >= 0) {
                    if ($toform && array_key_exists($sessiondelete, $toform->id)) {
                        $session = $DB->get_record('otopo_session', ['id' => $toform->id[$sessiondelete]]);
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

                        $DB->delete_records('otopo_session', ['id' => $toform->id[$sessiondelete]]);
                        $sessions = $DB->get_records(
                            'otopo_session',
                            ['otopo' => $moduleinstance->id],
                            'allowsubmissionfromdate',
                            '*'
                        );

                        $items = $DB->get_records('otopo_item', ['otopo' => $moduleinstance->id]);
                        if (!empty($items)) {
                            [$insql, $params] = $DB->get_in_or_equal(array_keys($items));
                            $params[] = $toform->id[$sessiondelete];

                            $DB->delete_records_select('otopo_user_otopo', "item $insql AND session = ?", $params);
                            $DB->delete_records('otopo_user_valid_session', [
                                'otopo' => $moduleinstance->id,
                                'session' => $toform->id[$sessiondelete],
                            ]);
                        }

                        $toform = toform_from_sessions($sessions);
                        $moduleinstance->instance = $moduleinstance->id;
                        otopo_update_instance($moduleinstance);
                    }
                }

                $mform = new sessions_form(null, [
                    'o' => $moduleinstance->id,
                    'sessions' => $moduleinstance->sessions, // Not used in form?
                    'count_sessions' => count($sessions), // Not used in form?
                    'sesskey' => $sesskey,
                ]);

                if ($mform->is_cancelled()) {
                    // Handle form cancel operation, if cancel button is present on form.
                    redirect(new moodle_url('/mod/otopo/view.php', [
                        'o' => $moduleinstance->id,
                        'action' => 'edit',
                        'object' => 'sessions',
                        'sesskey' => $sesskey,
                    ]));
                }

                if ($fromform = $mform->get_data()) {
                    // In this case you process validated data. $mform->get_data() returns data posted in form.

                    for ($i = 0; $i < $fromform->option_repeats; $i++) {
                        $session = new stdClass();
                        $session->otopo = intval($moduleinstance->id);
                        $session->name = $fromform->name[$i];
                        $session->color = $fromform->color[$i];
                        $session->allowsubmissionfromdate = $fromform->allowsubmissionfromdate[$i];
                        $session->allowsubmissiontodate = $fromform->allowsubmissiontodate[$i];
                        if (!empty($fromform->id[$i])) {
                            $session->id = $fromform->id[$i];
                            $DB->update_record('otopo_session', $session);
                            $session = $DB->get_record('otopo_session', ['id' => $toform->id[$i]]);

                            if ($session->event_start) {
                                $event = calendar_event::load($session->event_start);
                                if ($event) {
                                    $event->name = $moduleinstance->name
                                        . ' - ' . $session->name
                                        . ' - ' . get_string('start', 'otopo');
                                    $event->timestart = $session->allowsubmissionfromdate;
                                    $event->timesort = $session->allowsubmissionfromdate;
                                    $event->visible = instance_is_visible('otopo', $moduleinstance);
                                    $event->update($event, false);
                                }
                            }
                            if ($session->event_end) {
                                $event = calendar_event::load($session->event_end);
                                if ($event) {
                                    $event->name = $moduleinstance->name
                                        . ' - ' . $session->name
                                        . ' - ' . get_string('end', 'otopo');
                                    $event->timeend = $session->allowsubmissiontodate;
                                    $event->timesort = $session->allowsubmissiontodate;
                                    $event->visible = instance_is_visible('otopo', $moduleinstance);
                                    $event->update($event, false);
                                }
                            }
                        } else {
                            $fromform->id = $DB->insert_record('otopo_session', $session);
                            $session->id = $fromform->id;

                            $event = new stdClass();
                            $event->eventtype = OTOPO_EVENT_TYPE_SESSION;
                            $event->type = CALENDAR_EVENT_TYPE_ACTION;
                            $event->name = $moduleinstance->name . ' - ' . $session->name . ' - ' . get_string('start', 'otopo');
                            $event->format = FORMAT_HTML;
                            $event->courseid = $moduleinstance->course;
                            $event->groupid = 0;
                            $event->userid = 0;
                            $event->modulename = 'otopo';
                            $event->instance = $moduleinstance->id;
                            $event->repeatid = $session->id;
                            $event->timestart = $session->allowsubmissionfromdate;
                            $event->timeduration = 0;
                            $event->timesort = $session->allowsubmissionfromdate;
                            $event->visible = instance_is_visible('otopo', $moduleinstance);

                            $event = calendar_event::create($event, false);

                            $session->event_start = $event->id;

                            $event = new stdClass();
                            $event->eventtype = OTOPO_EVENT_TYPE_SESSION;
                            $event->type = CALENDAR_EVENT_TYPE_ACTION;
                            $event->name = $moduleinstance->name . ' - ' . $session->name . ' - ' . get_string('end', 'otopo');
                            $event->format = FORMAT_HTML;
                            $event->courseid = $moduleinstance->course;
                            $event->groupid = 0;
                            $event->userid = 0;
                            $event->modulename = 'otopo';
                            $event->instance = $moduleinstance->id;
                            $event->repeatid = $session->id;
                            $event->timestart = $session->allowsubmissiontodate;
                            $event->timeduration = 0;
                            $event->timesort = $session->allowsubmissiontodate;
                            $event->visible = instance_is_visible('otopo', $moduleinstance);

                            $event = calendar_event::create($event, false);

                            $session->event_end = $event->id;

                            $DB->update_record('otopo_session', $session);
                        }
                    }

                    $moduleinstance->instance = $moduleinstance->id;
                    otopo_update_instance($moduleinstance);

                    redirect(new moodle_url('/mod/otopo/view.php', [
                        'o' => $moduleinstance->id,
                        'action' => 'edit',
                        'object' => 'sessions',
                        'sesskey' => $sesskey,
                    ]));
                } else {
                    // This branch is executed if the form is submitted but the data doesn't validate
                    // and the form should be redisplayed or on the first display of the form.

                    // Set default data (if any).
                    $mform->set_data($toform);
                }

                $content = $output->heading(get_string('sessionssettings', 'otopo')) . $mform->render();
            } else if ($object == 'grids') {
                include('vue.php');

                $PAGE->requires->js_call_amd('mod_otopo/grids', 'initGrid', [
                    $moduleinstance->id,
                    has_otopo($moduleinstance->id),
                ]);

                $content = $OUTPUT->render_from_template('mod_otopo/grids', [
                    'cm' => $cm,
                    'canmanagetemplates' => $canmanagetemplates,
                    'hasotopo' => has_otopo($moduleinstance->id),
                ]);
            } else if ($object == 'templates') {
                require_capability('mod/otopo:managetemplates', $modulecontext);

                $items = get_items_sorted_from_otopo($moduleinstance->id);

                $nbrdegreesmax = table_items($items);

                $template = $OUTPUT->render_from_template('mod_otopo/grids_table', [
                    'items' => array_values($items),
                    'nbrdegreesmax' => $nbrdegreesmax,
                ]);

                $mform = new template_form(null, [
                    'o' => $moduleinstance->id,
                    'template' => $template,
                    'action' => 'edit',
                    'cmid' => $cm->id,
                    'sesskey' => $sesskey,
                ]);

                if ($mform->is_cancelled()) {
                    redirect(new moodle_url('/mod/otopo/view.php', [
                        'id' => $cm->id,
                        'action' => 'preview',
                        'object' => 'grids',
                    ]));
                } else if ($fromform = $mform->get_data()) {
                    $template = (object) ['id' => null, 'name' => $fromform->name];
                    $template->id = $DB->insert_record('otopo_template', $template);

                    copy_items(-$template->id, $items);

                    redirect(new moodle_url('/mod/otopo/templates.php', ['id' => $template->id, 'cmid' => $cm->id]));
                }

                $content = $mform->render();
            }
        } else if ($action == 'preview') {
            $items = get_items_sorted_from_otopo($moduleinstance->id);
            if ($object == 'grids') {
                $nbrdegreesmax = table_items($items);

                $content = $OUTPUT->render_from_template('mod_otopo/preview_grids', [
                    'cm' => $cm,
                    'items' => array_values($items),
                    'nbrdegreesmax' => $nbrdegreesmax,
                    'canmanagetemplates' => $canmanagetemplates,
                ]);
            } else if ($object == 'sessions') {
                include('vue.php');

                $args = [
                    $moduleinstance->id,
                    (bool)$moduleinstance->showteachercomments,
                    $OUTPUT->image_url('star', 'mod_otopo')->out(),
                    $OUTPUT->image_url('star_container', 'mod_otopo')->out(),
                    $OUTPUT->image_url('help', 'mod_otopo')->out(),
                    $OUTPUT->image_url('plus', 'mod_otopo')->out(),
                    $OUTPUT->image_url('minus', 'mod_otopo')->out(),
                ];

                $PAGE->requires->js_call_amd('mod_otopo/preview_sessions', 'init', $args);

                $content = $OUTPUT->render_from_template('mod_otopo/preview_sessions', []);
            }
        } else if ($action == 'export') {
            if ($object == 'grids') {
                $items = get_items_sorted_from_otopo($moduleinstance->id);

                csv_from_items($items, 'grids_' . $moduleinstance->id . '.csv');

                return;
            } else if ($object == 'group') {
                require_capability('mod/otopo:exportresults', $modulecontext);

                $users = optional_param('users', 'params', PARAM_TEXT);
                if (!empty($users)) {
                    $users = explode(',', $users);
                } else {
                    $users = [];
                }

                $distribution = get_distribution_by_item($moduleinstance, $users);
                $items = get_items_sorted_from_otopo($moduleinstance->id);

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="group_exports_' . $moduleinstance->id . '.csv";');

                $f = fopen('php://output', 'w');

                $nbrdegreesmax = 0;
                foreach ($items as $item) {
                    if (count($item->degrees) > $nbrdegreesmax) {
                        $nbrdegreesmax = count($item->degrees);
                    }
                }

                $header = ['degree'];
                foreach (array_values($items) as $key1 => $item) {
                    $header[] = 'item' . ($key1 + 1);
                }
                fputcsv($f, $header, ',');

                for ($i = 0; $i < $nbrdegreesmax; $i++) {
                    $row = ['degree' => 'degree' . ($i + 1)];
                    foreach (array_values($items) as $key1 => $item) {
                        if (array_key_exists($i, $distribution) && array_key_exists($key1, $distribution[$i])) {
                            $row['item' . ($key1 + 1)] = $distribution[$i][$key1];
                        } else {
                            $row['item' . ($key1 + 1)] = '';
                        }
                    }
                    fputcsv($f, $row, ',');
                }

                fclose($f);

                return;
            } else if ($object == 'individual') {
                require_capability('mod/otopo:exportresults', $modulecontext);

                $sessions = optional_param('sessions', 'params', PARAM_TEXT);
                if (!empty($sessions)) {
                    $sessionsparam = explode(',', $sessions);
                    $sessions = [];
                    foreach ($sessionsparam as $param) {
                        $arr = explode('_', $param);
                        if (count($arr) == 2) {
                            $user = $arr[0];
                            $session = $arr[1];
                            if (!array_key_exists($user, $sessions)) {
                                $sessions[$user] = [];
                            }
                            $sessions[$user][] = $session;
                        }
                    }
                } else {
                    $sessions = [];
                }

                $userssessionswithotopos = get_users_sessions_with_otopos($moduleinstance);
                $graders = get_graders($moduleinstance);

                header('Content-Type: text/css; charset=utf-8');
                header('Content-Disposition: attachment; filename="individual_exports_' . $moduleinstance->id . '.csv";');

                $f = fopen('php://output', 'w');

                $header = ['fullname', 'session', 'grade', 'comment'];
                fputcsv($f, $header, ',');

                if ($moduleinstance->session) {
                    $sessionsids = get_sessions($moduleinstance);
                }
                foreach ($sessions as $user => $usersessions) {
                    if (array_key_exists($user, $userssessionswithotopos)) {
                        $userobj = $DB->get_record('user', ['id' => $user]);
                        $fullname = fullname($userobj);
                        foreach ($usersessions as $session) {
                            if (array_key_exists($session, $userssessionswithotopos[$user])) {
                                if ($moduleinstance->session) {
                                    $i = 1;
                                    foreach (array_keys($sessionsids) as $id) {
                                        if ($id == $session) {
                                            $idabs = $i;
                                            break;
                                        }
                                        ++$i;
                                    }
                                } else {
                                    $idabs = abs($session);
                                }
                                if (
                                    array_key_exists($user, $graders)
                                    && array_key_exists($session, $graders[$user])
                                    && $graders[$user][$session]->grade
                                ) {
                                    $grade = $graders[$user][$session]->grade;
                                } else {
                                    $grade = '';
                                }
                                if (
                                    array_key_exists($user, $graders)
                                    && array_key_exists($session, $graders[$user])
                                    && $graders[$user][$session]->comment
                                ) {
                                    $comment = $graders[$user][$session]->comment;
                                } else {
                                    $comment = '';
                                }
                                $row = [
                                    'fullname' => $fullname,
                                    'session' => get_string('session', 'otopo') . ' ' . $idabs,
                                    'grade' => $grade,
                                    'comment' => strip_tags($comment),
                                ];
                                fputcsv($f, $row, ',');
                            }
                        }
                    }
                }

                fclose($f);

                return;
            }
        } else if ($action == 'import') {
            // The $sesskey param should be set, no need to gen a new one when redirecting.

            if (!has_otopo($moduleinstance->id)) {
                if ($object == 'grids') {
                    $mform = new importgrid_form(null, [
                        'o' => $moduleinstance->id,
                        'sesskey' => $sesskey,
                    ]);
                    if ($mform->is_cancelled()) {
                        redirect(new moodle_url('/mod/otopo/view.php', [
                            'id' => $cm->id,
                            'action' => 'edit',
                            'object' => 'grids',
                            'sesskey' => $sesskey,
                        ]));
                    } else if ($fromform = $mform->get_data()) {
                        $content = $mform->get_file_content('csv');

                        $csv = parse_csv($content);

                        if (!empty($csv)) {
                            $header = array_shift($csv);
                            unset($csv[count($csv) - 1]);

                            foreach ($csv as $i => $line) {
                                $item = [];
                                $degrees = [];
                                foreach ($line as $j => $value) {
                                    if (substr($header[$j], 0, 6) == 'degree') {
                                        $matches = [];
                                        preg_match('/^degree(\d+)_(\w*)$/', $header[$j], $matches);
                                        $k = intval($matches[1]);
                                        $field = $matches[2];
                                        if (array_key_exists($k, $degrees)) {
                                            $degrees[$k - 1] = [];
                                        }
                                        $degrees[$k - 1][$field] = $value;
                                    } else {
                                        $item[$header[$j]] = $value;
                                    }
                                }
                                if (!empty($degrees)) {
                                    $item['degrees'] = $degrees;
                                }
                                $items[] = $item;
                            }

                            if (!empty($items)) {
                                delete_items($moduleinstance->id);

                                foreach ($items as $item) {
                                    $newitem = ['name' => $item['name'], 'ord' => $item['ord'], 'otopo' => $moduleinstance->id];
                                    $itemid = $DB->insert_record('otopo_item', $newitem);
                                    foreach ($item['degrees'] as $degree) {
                                        $newdegree = [
                                            'name' => $degree['name'],
                                            'description' => $degree['description'],
                                            'grade' => intval($degree['grade']),
                                            'ord' => intval($degree['ord']),
                                            'item' => $itemid,
                                        ];
                                        $DB->insert_record('otopo_item_degree', $newdegree);
                                    }
                                }
                            }

                            redirect(new moodle_url('/mod/otopo/view.php', [
                                'id' => $cm->id,
                                'action' => 'edit',
                                'object' => 'grids',
                                'sesskey' => $sesskey,
                            ]));
                        }
                    }
                    $content = $mform->render();
                } else if ($object == 'templates') {
                    $templates = get_templates();

                    $mform = new fromtemplate_form(null, [
                        'templates' => $templates,
                        'id' => $cm->id,
                        'action' => 'edit',
                        'sesskey' => $sesskey,
                    ]);
                    if ($mform->is_cancelled()) {
                        redirect(new moodle_url('/mod/otopo/view.php', [
                            'id' => $cm->id,
                            'action' => 'edit',
                            'object' => 'grids',
                            'sesskey' => $sesskey,
                        ]));
                    } else if ($fromform = $mform->get_data()) {
                        $templateid = intval($fromform->template);

                        $items = get_items_from_otopo(-$templateid);

                        delete_items($moduleinstance->id);
                        copy_items($moduleinstance->id, $items);

                        redirect(new moodle_url('/mod/otopo/view.php', [
                            'id' => $cm->id,
                            'action' => 'edit',
                            'object' => 'grids',
                            'sesskey' => $sesskey,
                        ]));
                    }

                    $content = $mform->render();
                }
            }
        } else if ($action == 'report') {
            if ($object == 'individual') {
                $participanttable = new \mod_otopo\table\participants("user-index-participants-{$course->id}");
                $filterset = new \mod_otopo\table\participants_filterset();
            } else if ($object == 'group') {
                $participanttable = new \mod_otopo\table\participants_no_row("user-index-participants-{$course->id}");
                $filterset = new \mod_otopo\table\participants_no_row_filterset();
                $filterset->add_filter(new integer_filter('session', filter::JOINTYPE_DEFAULT, [ 0 ])); // ToDo - unused?
            }
            // For moodle needs, the course ID is required...? There's no "easy/logical" way around it.
            $filterset->add_filter(new integer_filter('courseid', filter::JOINTYPE_DEFAULT, [ (int) $course->id ]));
            $filterset->add_filter(new integer_filter('otopo', filter::JOINTYPE_DEFAULT, [ (int) $moduleinstance->id ]));

            $participanttable->set_filterset($filterset);

            // Render the user filters.
            $content .= '<div class="no-print">';
            $renderable = new \core_user\output\participants_filter(
                context_course::instance($course->id),
                $participanttable->uniqueid
            );
            $templatecontext = $renderable->export_for_template($OUTPUT);
            $templatecontext->otopo = $moduleinstance;
            if ($object == 'group') {
                $templatecontext->sessionfilter = true;
                $templatecontext->sessions = array_values(get_sessions($moduleinstance));
                $templatecontext->hassessions = !empty($templatecontext->sessions);
            } else {
                $templatecontext->sessionfilter = false;
            }

            // Needed because of a breaking change in moodle user plugin in 4.1.
            $moodleversion = get_config('')->version;
            if ($moodleversion < 2022112800) {
                $templatecontext->moodlepre41 = true;
            }

            $content .= $OUTPUT->render_from_template('mod_otopo/participantsfilter', $templatecontext);
            $content .= '</div>';

            $content .= '<div class="userlist">';

            // Do this so we can get the total number of rows.
            ob_start();
            $content .= $participanttable->out($perpage, true);
            $participanttablehtml = ob_get_contents();
            ob_end_clean();

            $content .= html_writer::start_tag('form', [
                'action' => 'action_redir.php',
                'method' => 'post',
                'id' => 'participantsform',
                'data-course-id' => $course->id,
                'data-otopo' => $moduleinstance->id,
                'data-table-unique-id' => $participanttable->uniqueid,
                'data-table-default-per-page' => ($perpage < DEFAULT_PAGE_SIZE) ? $perpage : DEFAULT_PAGE_SIZE,
            ]);
            $content .= '<div>';
            $content .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
            $content .= '<input type="hidden" name="returnto" value="' . s($PAGE->url->out(false)) . '" />';

            $content .= html_writer::tag(
                'p',
                get_string('countparticipantsfound', 'core_user', $participanttable->totalrows),
                [
                    'data-region' => 'participant-count',
                    'class' => 'no-print',
                ]
            );

            $content .= $participanttablehtml;

            $perpagesize = DEFAULT_PAGE_SIZE;
            $perpagevisible = false;
            $perpagestring = '';

            $perpageurl = new moodle_url('/mod/otopo/view.php', [
                'id' => $cm->id,
                'action' => 'report',
                'object' => 'individual',
            ]);

            if ($perpage == SHOW_ALL_PAGE_SIZE && $participanttable->totalrows > DEFAULT_PAGE_SIZE) {
                $perpageurl->param('perpage', $participanttable->totalrows);
                $perpagesize = SHOW_ALL_PAGE_SIZE;
                $perpagevisible = true;
                $perpagestring = get_string('showperpage', '', DEFAULT_PAGE_SIZE);
            } else if ($participanttable->get_page_size() < $participanttable->totalrows) {
                $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
                $perpagesize = SHOW_ALL_PAGE_SIZE;
                $perpagevisible = true;
                $perpagestring = get_string('showall', '', $participanttable->totalrows);
            }

            $perpageclasses = '';
            if (!$perpagevisible) {
                $perpageclasses = 'hidden';
            }
            $content .= $OUTPUT->container(html_writer::link(
                $perpageurl,
                $perpagestring,
                [
                    'data-action' => 'showcount',
                    'data-target-page-size' => $perpagesize,
                    'class' => $perpageclasses,
                ]
            ), [], 'showall');
            $bulkoptions = (object) [
                'uniqueid' => $participanttable->uniqueid,
            ];

            $content .= '</form>';

            if ($object == 'individual') {
                $content .= '<div class="d-flex justify-content-end mt-3">';
                if (has_capability('mod/otopo:exportresults', $modulecontext)) {
                    $content .= '<a class="btn-otopo ml-5 mr-2 no-print" href="#" onclick="window.print()">'
                        . get_string('print', 'otopo') . '</a>';
                    $content .= '<a id="export-csv" class="btn-otopo ml-2 mr-5 no-print" href="#">' .
                        get_string('exportascsv', 'otopo') . '</a>';
                } else {
                    $content .= '<a class="btn-otopo ml-5 mr-5 no-print" href="#" onclick="window.print()">' .
                        get_string('print', 'otopo') . '</a>';
                }
                $content .= '</div>';
            }

            $PAGE->requires->js_call_amd('core_user/participants', 'init', [$bulkoptions]);
            if ($object == 'individual') {
                $PAGE->requires->js_call_amd('mod_otopo/report_individual', 'init', [
                    'cmid' => $cm->id,
                    'wwwroot' => $CFG->wwwroot,
                ]);
            }
            $content .= '</div>';

            $content .= '</div>';
        }

        if (!$content) {
            $content = $OUTPUT->render_from_template('mod_otopo/params', ['cm' => $cm]);
        }

        $body .= $output->render(new mod_otopo\output\view_page(
            $cm,
            $moduleinstance,
            $action,
            $object,
            $content,
            $canadmin,
            $cangrade,
            $canexportresults,
            $canmanagetemplates,
            has_otopo($moduleinstance->id)
        ));
    }
}

if (has_capability('mod/otopo:fill', $modulecontext)) {
    $sessionvalid = false;
    $sessionvalidorclosed = false;
    $sessiono = null;
    $sessionkey = 0;
    $session = 0;
    $mform = null;

    if (($action == 'edit' && $object == 'params') || $action == '') {
        $action = 'progress';
    } else if ($action !== 'preview' && $action !== 'evolution' && $action !== 'progress') {
        // The 'preview', 'evolution' and 'progress' actions do not require this check.
        // To understand why this check is here, you need to dig into the code ;-).
        // It's not the clearest way to do it, but this whole page would have to be redesigned to make it clearer.
        confirm_sesskey($sesskey);
    }

    if ($action == 'evaluate') {
        $session = optional_param('session', 0, PARAM_INT);
        if ($session == 0 && $currentsession = get_current_session($moduleinstance, $USER)) {
            $sessionkey = $currentsession[0];
            if ($currentsession[1] > 0) {
                $sessiono = $currentsession[2];
            } else {
                $sessiono = null;
            }
            $session = $currentsession[1];
        } else if ($moduleinstance->session) {
            $sessions = get_sessions($moduleinstance);
            if (!array_key_exists($session, $sessions)) {
                $session = 0;
            } else {
                $sessiono = $sessions[$session];
                $i = 1;
                foreach ($sessions as $s) {
                    if ($s->id == $session) {
                        $sessionkey = $i;
                        break;
                    }
                    ++$i;
                }
            }
        } else if (abs($session) <= $moduleinstance->limit_sessions) {
            $sessionkey = abs($session);
        } else {
            $session = 0;
        }
        $sessionvalid = session_is_valid($moduleinstance->id, $USER, $session);
        $sessionvalidorclosed = session_is_valid_or_closed($moduleinstance->id, $USER, $session);
        $isopen = is_open($moduleinstance);

        include('vue.php');

        $args = [
            $moduleinstance->id,
            (bool)$moduleinstance->showteachercomments,
            $session,
            $sessionvalidorclosed,
            $isopen,
            $OUTPUT->image_url('star', 'mod_otopo')->out(),
            $OUTPUT->image_url('star_container', 'mod_otopo')->out(),
            $OUTPUT->image_url('help', 'mod_otopo')->out(),
            $OUTPUT->image_url('plus', 'mod_otopo')->out(),
            $OUTPUT->image_url('minus', 'mod_otopo')->out(),
        ];

        $PAGE->requires->js_call_amd('mod_otopo/evaluate', 'init', $args);
    } else if ($action == 'validate') {
        $session = optional_param('session', 0, PARAM_INT);
        $sessionvalid = session_is_valid($moduleinstance->id, $USER, $session);
        if (!$sessionvalid) {
            $DB->insert_record('otopo_user_valid_session', [
                'userid' => $USER->id,
                'session' => $session,
                'otopo' => $moduleinstance->id,
            ]);
        }
        otopo_update_grades($moduleinstance, $USER->id);

        $event = \mod_otopo\event\session_closed::create(['context' => $modulecontext]);
        $event->trigger();

        redirect(new moodle_url('/mod/otopo/view.php', ['id' => $cm->id, 'action' => 'progress']));
    } else if ($action == 'evolution') {
        $PAGE->requires->js_call_amd('mod_otopo/evolution', 'init', [
            $moduleinstance->id,
            get_string('autoevaldegree', 'otopo'),
        ]);
    }

    $body .= $output->render(new mod_otopo\output\view_fill_page(
        $cm,
        $moduleinstance,
        $action,
        $session,
        $sessionkey,
        $sessionvalid,
        $sessionvalidorclosed,
        $sessiono
    ));
}

/***********
 * Output. *
 ***********/
echo $OUTPUT->header();
echo $body;
echo $OUTPUT->footer();
