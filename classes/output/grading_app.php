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
 * Renderable that initialises the grading "app".
 *
 * @package    mod_otopo
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otopo\output;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../locallib.php');

use context;
use renderer_base;
use renderable;
use templatable;
use stdClass;

/**
 * Grading app renderable.
 *
 * @package    mod_assign
 * @since      Moodle 3.1
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grading_app implements renderable, templatable {
    /**
     * @var $userid - The initial user id.
     */
    public $userid = 0;

    /**
     * @var $course - The otopo course.
     */
    public $course = null;

    /**
     * @var $cm - The otopo course module.
     */
    public $cm = null;

    /**
     * @var $otopo - The otopo instance.
     */
    public $otopo = null;

    /**
     * @var context $context - The otopo context.
     */
    public context $context = null;

    /**
     * @var int $session - The session.
     */
    public int $session = null;

    /**
     * @var array<int,object> $participants - The participants.
     */
    public array $participants = null;

    /**
     * Constructor for this renderable.
     *
     * @param int $userid The user we will open the grading app too.
     * @param assign $assignment The assignment class
     */
    public function __construct($userid, $course, $cm, $otopo, $context, $session) {
        $this->userid = $userid;
        $this->course = $course;
        $this->cm = $cm;
        $this->otopo = $otopo;
        $this->context = $context;
        $this->session = $session;
        $this->participants = [];
        if (!$this->userid && count($this->participants)) {
            $this->userid = reset($this->participants)->id;
        }
    }

    /**
     * Export this class data as a flat list for rendering in a template.
     *
     * @param renderer_base $output The current page renderer.
     * @return object - Flat list of exported data.
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;

        $export = new stdClass();

        $export->userid = $this->userid;
        $export->otopoid = $this->otopo->id;
        $export->otopovisual = $this->otopo->sessionvisual == 0 ? 'radar' : 'bar';
        $export->cmid = $this->cm->id;
        $export->contextid = $this->context->id;
        $export->name = $this->otopo->name;
        $export->coursename = $this->course->fullname;
        $export->courseid = $this->course->id;
        $export->participants = [];

        $num = 1;
        foreach ($this->participants as $idx => $record) {
            $user = new stdClass();
            $user->id = $record->id;
            $user->fullname = fullname($record);
            $user->requiregrading = $record->requiregrading;
            $user->grantedextension = $record->grantedextension;
            $user->submitted = $record->submitted;
            if ($record->id == $this->userid) {
                $export->index = $num;
                $user->current = true;
            }
            $export->participants[] = $user;
            $num++;
        }

        $export->viewreport = get_string('viewreport', 'mod_otopo');

        $export->caneditsettings = has_capability('mod/otopo:addinstance', $this->context);

        $export->session = $this->session;
        $export->validated = session_is_valid_or_closed($this->otopo->id, (object) [ 'id' => $this->userid ], $this->session);
        $export->showuseridentity = $CFG->showuseridentity;
        $helpicon = new \help_icon('sendstudentnotifications', 'assign');
        $export->helpicon = $helpicon->export_for_template($output);

        $export->rarrow = '►';
        $export->larrow = '◄';

        $export->star = $output->image_url('star', 'mod_otopo')->out();
        $export->help = $output->image_url('help2', 'mod_otopo')->out();
        $export->starcontainer = $output->image_url('star_container', 'mod_otopo')->out();

        return $export;
    }
}
