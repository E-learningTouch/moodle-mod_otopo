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
 * The templates management page.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/templateform.php');
require_once(__DIR__ . '/locallib.php');

// Future improvements: Call $DB in a dedicated class ;-).
global $DB;

/***********
 * Params. *
 ***********/
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'show', PARAM_TEXT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

/******************
 * Access checks. *
 ******************/
// Get the course data and cm instance from $cmid.
if ($cmid) {
    [ $course, $cm ] = get_course_and_cm_from_cmid($cmid);
} else {
    // Fallback to site wide require_login.
    $course = null;
    $cm = null;
}

// Check login and sesskey.
require_login($course, false, $cm);
if ($action !== 'show') {
    // The 'show' action should be the only one that doesn't require sesskey validation.
    confirm_sesskey($sesskey);
}

// Finally, the user must have the proper capability.
$context = $cm ? $cm->context : context_system::instance();
require_capability('mod/otopo:managetemplates', $context);

/*****************************
 * Template data from param. *
 *****************************/
$template = $id ? $DB->get_record('otopo_template', ['id' => $id]) : null;

/***************
 * Page setup. *
 ***************/
$url = new moodle_url('/mod/otopo/templates.php', [
    'id' => $template ? $template->id : null,
    'action' => $action,
    'cmid' => $cmid,
    'returnurl' => $returnurl,
    'sesskey' => $sesskey,
]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'otopo') . ' - ' . get_string('otopo:managetemplates', 'otopo'));
$PAGE->set_heading(get_string('otopo:managetemplates', 'otopo'));

/*******************
 * Some variables. *
 *******************/
$returnurl = $returnurl ? new moodle_url($returnurl) : new moodle_url($url, ['action' => null]);
$mform = null;

/************
 * Actions. *
 ************/
if ($action === 'create') {
    $mform = new template_form(null, ['action' => 'create', 'cmid' => $cmid, 'sesskey' => $sesskey ?? sesskey()]);

    if ($mform->is_cancelled()) {
        redirect($returnurl);
    }

    if ($fromform = $mform->get_data()) {
        $template = (object) ['id' => null, 'name' => $fromform->name];
        $template->id = $DB->insert_record('otopo_template', $template);

        redirect(new moodle_url($url, ['id' => $template->id, 'action' => 'edit']));
    }
} else if ($action === 'edit' && $template) {
    $mform = new template_form(null, ['action' => 'edit', 'cmid' => $cmid, 'sesskey' => $sesskey ?? sesskey()]);

    if ($mform->is_cancelled()) {
        redirect($returnurl);
    }

    if ($fromform = $mform->get_data()) {
        $template->name = $fromform->name;
        $DB->update_record('otopo_template', $template);
    } else {
        $mform->set_data($template);
    }

    include('vue.php');
    $PAGE->requires->js_call_amd('mod_otopo/grids', 'initGrid', [-$template->id, false, $cmid]);
} else if ($action === 'delete' && $template) {
    delete_items(-$template->id);
    $DB->delete_records('otopo_template', ['id' => $template->id]);
    $returnurl->remove_params(['id' => null]);
} else if ($action === 'export' && $template) {
    $items = get_items_sorted_from_otopo(-$template->id);
    csv_from_items($items, 'grids_templates_' . $template->id . '.csv');
}

if (!$mform && $action !== 'show') {
    // The only time the form is not required is when the action is 'show'.
    // Sesskey shouldn't be needed afterwards.
    redirect($returnurl);
}

/***********
 * Output. *
 ***********/
$output = $PAGE->get_renderer('mod_otopo');
$renderable = new mod_otopo\output\templates_page($template, $action, $mform, $cmid);

echo $output->header();
echo $output->render($renderable);
echo $output->footer();
