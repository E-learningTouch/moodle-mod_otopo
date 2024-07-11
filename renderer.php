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
 * Defines renderers classes.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_otopo\output\grading_app;
use mod_otopo\output\templates_page;
use mod_otopo\output\view_fill_page;
use mod_otopo\output\view_page;

/**
 * Class defining the main renderer.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_otopo_renderer extends plugin_renderer_base {
    /**
     * Defer to template.
     *
     * @param view_page $page
     *
     * @return string html for the page
     */
    public function render_view_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_otopo/view_page', $data);
    }

    /**
     * Defer to template.
     *
     * @param templates_page $page
     *
     * @return string html for the page
     */
    public function render_templates_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_otopo/templates_page', $data);
    }

    /**
     * Defer to template.
     *
     * @param view_fill_page $page
     *
     * @return string html for the page
     */
    public function render_view_fill_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_otopo/view_fill_page', $data);
    }

    /**
     * Defer to template..
     *
     * @param grading_app $app - All the data to render the grading app.
     */
    public function render_grading_app(grading_app $app) {
        $context = $app->export_for_template($this);
        return $this->render_from_template('mod_otopo/grading_app', $context);
    }
}
