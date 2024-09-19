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
 * Plugin tables.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otopo\table;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../locallib.php');

use core_table\dynamic as dynamic_table;
use core_table\local\filter\filterset;
use core_user\table\participants as participants_table;
use html_writer;
use moodle_url;

/**
 * Class of the participants table.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class participants extends participants_table implements dynamic_table {
    /** @var int $cmid Course module ID. */
    private $cmid;

    /** @var object $otopo Otopo instance. */
    private object $otopo;

    /** @var array Users sessions. */
    private array $userssessionswithotopos;

    /** @var array Graders. */
    private array $graders;

    /** @var array Auto grades. */
    private array $autogrades;

    /**
     * Render the participants table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $CFG, $OUTPUT, $PAGE, $DB;

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $this->userssessionswithotopos = get_users_sessions_with_otopos($this->otopo);
        $this->graders = get_graders($this->otopo);
        $this->autogrades = [];
        $items = get_items_sorted_from_otopo($this->otopo->id);
        $modulecontext = \context_module::instance($this->cmid);
        foreach (get_participants($this->otopo, $modulecontext) as $participant) {
            $otopos = get_user_otopos($this->otopo, $participant);
            $sessions = prepare_data($this->otopo, $items, $otopos, $participant);
            foreach (prepare_data($this->otopo, $items, $otopos, $participant) as $session) {
                if ($this->otopo->session || session_is_valid($this->otopo->id, $participant, $session->id)) {
                    $this->autogrades[$participant->id][$session->id] = $session->grade;
                }
            }
        }

        $headers[] = get_string('fullname');
        $columns[] = 'fullname';

        $headers[] = get_string('menusessions', 'otopo');
        $columns[] = 'sessions';

        $headers[] = get_string('grade', 'otopo');
        $columns[] = 'grades';

        $headers[] = get_string('sessionscomments', 'otopo');
        $columns[] = 'comments';

        $this->define_columns($columns);
        $this->define_headers($headers);

        // The name column is a header.
        $this->define_header_column('fullname');

        // Make this table sorted by last name by default.
        $this->sortable(true, 'lastname');

        $this->no_sorting('select');
        $this->no_sorting('sessions');
        $this->no_sorting('grades');
        $this->no_sorting('comments');
        $this->set_attribute('id', 'participants');

        get_parent_class(get_parent_class($this))::out($pagesize, $useinitialsbar, $downloadhelpbutton);
    }

    /**
     * Set filters and build table structure.
     *
     * @param filterset $filterset The filterset object to get the filters from.
     */
    public function set_filterset(filterset $filterset): void {
        global $DB;
        $otopoid = $filterset->get_filter('otopo')->current();
        $this->otopo = $DB->get_record('otopo', ['id' => $otopoid], '*', MUST_EXIST);

        $course = $DB->get_record('course', ['id' => $this->otopo->course], '*', MUST_EXIST);
        $this->cmid = get_coursemodule_from_instance('otopo', $this->otopo->id, $course->id, false, MUST_EXIST)->id;

        // Process the filterset.
        parent::set_filterset($filterset);
    }

    /**
     * Guess the base url for the participants table.
     */
    public function guess_base_url(): void {
        $this->baseurl = new moodle_url('/mod/otopo/view.php', [
            'id' => $this->cmid,
            'action' => 'report',
            'object' => 'individual',
            'sesskey' => sesskey(),
        ]);
    }

    /**
     * Get the sessions column from data.
     *
     * @param object $data The data.
     * @return string
     */
    public function col_sessions(object $data) {
        if (!array_key_exists($data->id, $this->autogrades)) {
            return '';
        }
        $sessions = '';
        if ($this->otopo->session) {
            $sessionsids = get_sessions($this->otopo);
        }
        foreach ($this->autogrades[$data->id] as $sessionid => $grade) {
            $usersession = $usersession = $this->userssessionswithotopos[$data->id][$sessionid] ?? null;

            if ($this->otopo->session) {
                $i = 1;
                foreach (array_keys($sessionsids) as $id) {
                    if ($id == $sessionid) {
                        $idabs = $i;
                        break;
                    }
                    ++$i;
                }
            } else {
                $idabs = abs($sessionid);
            }

            $attributes = null;
            $label = get_string('session', 'otopo') . ' ' . $idabs;
            $labelattributes = null;

            if ($usersession) {
                $label = html_writer::link(
                    new moodle_url('/mod/otopo/view.php', [
                        'id' => $this->cmid,
                        'action' => 'grader',
                        'user' => $data->id,
                        'session' => $sessionid,
                        'sesskey' => sesskey(),
                    ]),
                    $label,
                    $usersession['validated'] ? null : [ 'class' => 'text-dark' ]
                );
            } else {
                $attributes = [ 'disabled' => 1 ];
                $labelattributes = [ 'class' => 'text-muted' ];
            }

            $sessions .= html_writer::div(
                html_writer::checkbox('ddd', $sessionid, false, '&nbsp;' . $label, $attributes, $labelattributes),
                'sessions',
                [ 'data-user' => $data->id ]
            );
        }
        return $sessions;
    }

    /**
     * Get the grades column from data.
     *
     * @param object $data The data.
     * @return string
     */
    public function col_grades(object $data) {
        if (!array_key_exists($data->id, $this->autogrades)) {
            return '';
        }
        $grades = '';
        foreach ($this->autogrades[$data->id] as $sessionid => $grade) {
            $usersession = $usersession = $this->userssessionswithotopos[$data->id][$sessionid] ?? null;

            if (
                array_key_exists($data->id, $this->graders)
                && array_key_exists($sessionid, $this->graders[$data->id])
                && $this->graders[$data->id][$sessionid]->grade
            ) {
                $grades .= html_writer::tag('h5', $this->graders[$data->id][$sessionid]->grade, [ 'class' => 'mb-2' ]);
            } else {
                $str = get_string('notevaluated', 'otopo');
                if (
                    array_key_exists($data->id, $this->autogrades)
                    && array_key_exists($sessionid, $this->autogrades[$data->id])
                ) {
                    $str .= " ({$this->autogrades[$data->id][$sessionid]})";
                }
                $grades .= html_writer::tag('p', $str, [ 'class' => 'mb-2 text-muted' ]);
            }
        }
        return $grades;
    }

    /**
     * Get the comments column from data.
     *
     * @param object $data The data.
     * @return string
     */
    public function col_comments(object $data) {
        if (!array_key_exists($data->id, $this->autogrades)) {
            return '';
        }
        $comments = '';
        foreach ($this->autogrades[$data->id] as $sessionid => $grade) {
            $usersession = $this->userssessionswithotopos[$data->id][$sessionid] ?? null;
            if (
                array_key_exists($data->id, $this->graders)
                && array_key_exists($sessionid, $this->graders[$data->id])
                && $this->graders[$data->id][$sessionid]->comment
            ) {
                $comments .= html_writer::tag(
                    'h5',
                    mb_strimwidth(strip_tags($this->graders[$data->id][$sessionid]->comment), 0, 20, '...'),
                    [ 'class' => 'mb-2' ]
                );
            } else {
                $comments .= html_writer::tag('p', get_string('nocomments', 'otopo'), [ 'class' => 'mb-2 text-muted' ]);
            }
        }
        return $comments;
    }
}
