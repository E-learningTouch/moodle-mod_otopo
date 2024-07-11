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

namespace mod_otopo\output;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../locallib.php');
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class defining the templates page renderer.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class templates_page implements renderable, templatable {
    /** @var object|null $template Otopo template. */
    private ?object $template;

    /** @var string $action Action performed. */
    private string $action = 'show';

    /** @var moodleform|null $form to show. */
    private ?moodleform $form;

    /** @var int $cmid from module. */
    private int $cmid;

    /**
     * Renderable constructor.
     *
     * @param object|null $template The template info.
     * @param string $action The action.
     * @param moodleform|null $form The form.
     * @param int $cmid Course module ID.
     */
    public function __construct(?object $template, string $action = 'show', ?moodleform $form = null, int $cmid = 0) {
        $this->template = $template;
        $this->action = $action;
        $this->form = $form;
        $this->cmid = $cmid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return object|array
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->show = false;
        $data->edit = false;
        $data->create = false;
        $data->form = $this->form ? $this->form->render() : "";
        $data->template = $this->template;
        $data->templates = null;
        $data->items = null;
        $data->cmid = $this->cmid;
        if (!$this->template) {
            $data->templates = array_values(get_templates());
        } else {
            $items = get_items_sorted_from_otopo(-$this->template->id);
            $data->nbrdegreesmax = table_items($items);
            $data->items = array_values($items);
        }
        if ($this->action == 'edit') {
            $data->edit = true;
        } else if ($this->action == 'create') {
            $data->create = true;
        } else {
            $data->show = true;
        }
        return $data;
    }
}
