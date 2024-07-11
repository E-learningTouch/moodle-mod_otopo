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
 * Backup activity task.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/otopo/backup/moodle2/backup_otopo_stepslib.php');

/**
 * Choice backup task that provides all the settings and steps to perform one
 * complete backup of the activity.
 */
class backup_otopo_activity_task extends backup_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_otopo_activity_structure_step('otopo_structure', 'otopo.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of otopos.
        $search = "/(" . $base . "\/mod\/otopo\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@OTOPOINDEX*$2@$', $content);

        // Link to choice view by moduleid.
        $search = "/(" . $base . "\/mod\/otopo\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@OTOPOVIEWBYID*$2@$', $content);

        return $content;
    }
}
