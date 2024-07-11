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

global $DB, $USER;

require_login();

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'show', PARAM_TEXT);

$systemcontext = context_system::instance();
$cmid = optional_param('cmid', 0, PARAM_INT);
if ($cmid) {
    $context = context_module::instance($cmid);
} else {
    $context = $systemcontext;
}

require_capability('mod/otopo:managetemplates', $context);

if ($id) {
    $template = $DB->get_record('otopo_template', ['id' => $id]);
} else {
    $template = null;
}
$form = null;

$PAGE->set_url('/mod/otopo/templates.php', ['id' => $template ? $template->id : null]);
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'otopo') . ' - ' . get_string('otopo:managetemplates', 'otopo'));
$PAGE->set_heading(get_string('otopo:managetemplates', 'otopo'));

$output = $PAGE->get_renderer('mod_otopo');

if ($action == 'create') {
    $mform = new template_form(null, ['action' => 'create', 'cmid' => $cmid]);

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/mod/otopo/templates.php', ['cmid' => $cmid]));
    } else if ($fromform = $mform->get_data()) {
        $template = (object) ['id' => null, 'name' => $fromform->name];
        $template->id = $DB->insert_record('otopo_template', $template);

        redirect(new moodle_url('/mod/otopo/templates.php', ['id' => $template->id, 'action' => 'edit', 'cmid' => $cmid]));
    }

    $form = $mform;
} else if ($action == 'edit' && $template) {
    $mform = new template_form(null, ['action' => 'edit', 'cmid' => $cmid]);

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/mod/otopo/templates.php', ['id' => $template->id, 'cmid' => $cmid]));
    } else if ($fromform = $mform->get_data()) {
        $template->name = $fromform->name;
        $DB->update_record('otopo_template', $template);
    } else {
        $mform->set_data($template);
    }

    $form = $mform;

    include('vue.php');
    $PAGE->requires->js_call_amd('mod_otopo/grids', 'initGrid', [-$template->id, false, $cmid]);
} else if ($action == 'delete' && $template) {
    delete_items(-$template->id);

    $DB->delete_records('otopo_template', ['id' => $template->id]);

    redirect(new moodle_url('/mod/otopo/templates.php', ['cmid' => $cmid]));
} else if ($action == 'export' && $template) {
    $items = get_items_sorted_from_otopo(-$template->id);

    csv_from_items($items, 'grids_templates_' . $template->id . '.csv');

    return;
}

echo $output->header();

$renderable = new mod_otopo\output\templates_page($template, $action, $form, $cmid);

echo $output->render($renderable);

echo $output->footer();
