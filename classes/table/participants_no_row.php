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

global $CFG;
require_once($CFG->dirroot . '/course/modlib.php');

use core_table\dynamic as dynamic_table;
use core_table\local\filter\filterset;
use core_user\table\participants as participants_table;
use moodle_url;
use Traversable;

/**
 * Class of the participants table with no rows.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class participants_no_row extends participants_table implements dynamic_table {
    /** @var int $cmid Course module ID. */
    private $cmid;

    /** @var object $otopo Otopo instance. */
    private object $otopo;

    /** @var int $session Otopo session id. */
    private int $session;

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

        $this->session = $filterset->get_filter('session')->current();

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
            'object' => 'group',
        ]);
    }

    /**
     * Build the table (do nothing?).
     */
    public function build_table() {
        if ($this->rawdata instanceof Traversable) {
            return;
        }
        if (!$this->rawdata) {
            return;
        }
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        // Render the dynamic table header.
        echo $this->get_dynamic_table_html_start();

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $this->print_chart();

        // Render the dynamic table footer.
        echo $this->get_dynamic_table_html_end();
    }

    /**
     * Return the HTML code to print the chart.
     *
     * @return string
     */
    public function print_chart() {
        global $OUTPUT, $PAGE, $DB, $CFG;

        $getid = function ($user) {
            return $user->id;
        };
        $users = array_map($getid, $this->rawdata);
        $usersids = join(',', $users);

        if ($this->session > 0) {
            $distribution = get_distribution_by_item($this->otopo, $users, $this->session);
        } else {
            $distribution = get_distribution_by_item($this->otopo, $users);
        }
        $items = get_items_sorted_from_otopo($this->otopo->id);

        $distributionvis = [];
        $nbrdegreesmax = 0;
        foreach ($items as $item) {
            if (count($item->degrees) > $nbrdegreesmax) {
                $nbrdegreesmax = count($item->degrees);
            }
        }
        foreach (array_values($items) as $key1 => $item) {
            for ($i = 0; $i < $nbrdegreesmax; $i++) {
                if (!array_key_exists($i, $distributionvis)) {
                    $distributionvis[$i] = ['degree' => $i + 1, 'data' => []];
                }
                if (!array_key_exists($key1, $distributionvis[$i]['data'])) {
                    $distributionvis[$i]['data'][$key1] = ['value' => null];
                }
                if (array_key_exists($i, $distribution) && array_key_exists($key1, $distribution[$i])) {
                    $distributionvis[$i]['data'][$key1]['value'] = $distribution[$i][$key1];
                }
            }
        }

        $index = 1;
        foreach ($items as $item) {
            $item->index = $index;
            ++$index;
        }

        // Needed because of a breaking change in chartJS 3 which is introduce in moodle 4.
        $moodleversion = get_config('')->version;
        if ($moodleversion < 2022041906) {
            $moodlepre4 = true;
        } else {
            $moodlepre4 = false;
        }

        $course = $DB->get_record('course', ['id' => $this->otopo->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('otopo', $this->otopo->id, $course->id, false, MUST_EXIST);
        $modulecontext = \context_module::instance($cm->id);

        $PAGE->requires->js_call_amd('mod_otopo/report_group', 'init', [$this->uniqueid, $CFG->wwwroot, $moodlepre4]);
        return $OUTPUT->render_from_template('mod_otopo/report_group', [
            'users' => $usersids,
            'distribution' => $distributionvis,
            'hasdistribution' => !empty($distribution),
            'items' => array_values($items),
            'cmid' => $this->cmid,
            'canexportresults' => has_capability('mod/otopo:exportresults', $modulecontext),
        ]);
    }
}
