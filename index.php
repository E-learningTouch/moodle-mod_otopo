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
 * Display information about all the mod_otopo modules in the requested course.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

/***********
 * Params. *
 ***********/
$id = required_param('id', PARAM_INT);

/***************************
 * Course data from param. *
 ***************************/
$course = $DB->get_record('course', [ 'id' => $id ], '*', MUST_EXIST);

/******************
 * Access checks. *
 ******************/
require_course_login($course);

/***************
 * Page setup. *
 ***************/
$PAGE->set_url('/mod/otopo/index.php', [ 'id' => $id ]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_course::instance($course->id));

$event = \mod_otopo\event\course_module_instance_list_viewed::create_from_course($course);
$event->add_record_snapshot('course', $course);
$event->trigger();

/**********************
 * Prepare the table. *
 **********************/
$otopos = get_all_instances_in_course('otopo', $course);
if (empty($otopos)) {
    notice(get_string('no$otopoinstances', 'mod_otopo'), new moodle_url('/course/view.php', [ 'id' => $course->id ]));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = [ get_string('week'), get_string('name') ];
    $table->align = [ 'center', 'left' ];
} else if ($course->format == 'topics') {
    $table->head  = [ get_string('topic'), get_string('name') ];
    $table->align = [ 'center', 'left', 'left', 'left' ];
} else {
    $table->head  = [ get_string('name') ];
    $table->align = [ 'left', 'left', 'left' ];
}

foreach ($otopos as $otopo) {
    if (!$otopo->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/otopo/view.php', [ 'id' => $otopo->coursemodule ]),
            format_string($otopo->name, true),
            [ 'class' => 'dimmed' ]
        );
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/otopo/view.php', [ 'id' => $otopo->coursemodule ]),
            format_string($otopo->name, true)
        );
    }

    if ($course->format == 'weeks' || $course->format == 'topics') {
        $table->data[] = [ get_section_name($course, $otopo->section), $link ];
    } else {
        $table->data[] = [ $link ];
    }
}

/***********
 * Output. *
 ***********/
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_otopo'));
echo html_writer::table($table);
echo $OUTPUT->footer();
