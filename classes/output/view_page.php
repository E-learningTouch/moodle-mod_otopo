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

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Class defining the view page renderer.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {
    /** @var object $cm Course module. */
    private object $cm;

    /** @var object $otopo Otopo module instance. */
    private object $otopo;

    /** @var string $action Action performed. */
    private string $action;

    /** @var string $object Object. */
    private string $object;

    /** @var string $content Content to insert. */
    private string $content;

    /** @var bool $canadmin Can admin capability. */
    private bool $canadmin;

    /** @var bool $cangrade Can grade capability. */
    private bool $cangrade;

    /** @var bool $canexportresults Can export results capability. */
    private bool $canexportresults;

    /** @var bool $canmanagetemplates Can export results capability. */
    private bool $canmanagetemplates;

    /**
     * Renderer's constructor.
     *
     * @param object $cm Course module instance.
     * @param object $otopo Otopo instance.
     * @param string $action The action.
     * @param string $object The object.
     * @param string $content The content.
     * @param bool $canadmin Can admin?
     * @param bool $cangrade Can grade?
     * @param bool $canexportresults Can export results?
     * @param bool $canmanagetemplates Can manage templates?
     */
    public function __construct(
        object $cm,
        object $otopo,
        string $action = 'edit',
        string $object = '',
        string $content = '',
        bool $canadmin = false,
        bool $cangrade = false,
        bool $canexportresults = false,
        bool $canmanagetemplates = false
    ) {
        $this->action = $action;
        $this->object = $object;
        $this->cm = $cm;
        $this->otopo = $otopo;
        $this->content = $content;
        $this->canadmin = $canadmin;
        $this->cangrade = $cangrade;
        $this->canexportresults = $canexportresults;
        $this->canmanagetemplates = $canmanagetemplates;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return object|array
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->edit = false;
        $data->preview = false;
        $data->sessions = false;
        $data->params = false;
        $data->grids = false;
        $data->templates = false;
        $data->report = false;
        $data->content = $this->content;
        $data->canadmin = $this->canadmin;
        $data->cangrade = $this->cangrade;
        $data->canexportresults = $this->canexportresults;
        $data->canmanagetemplates = $this->canmanagetemplates;
        $data->canconsult = $this->canexportresults || $this->cangrade;
        $data->disabledsessions = !$this->otopo->session;
        switch ($this->action) {
            case 'import':
            case 'create-from-template':
            case 'edit':
                $data->edit = true;
                break;
            case 'preview':
                $data->preview = true;
                break;
            case 'report':
                $data->report = true;
                break;
        }
        $data->actionok = $data->edit || $data->preview || $data->report;
        switch ($this->object) {
            case 'params':
                $data->params = true;
                break;
            case 'sessions':
                $data->sessions = true;
                break;
            case 'grids':
                $data->grids = true;
                break;
            case 'templates':
                if ($this->action == 'import') {
                    $data->grids = true;
                } else {
                    $data->templates = true;
                }
                break;
            case 'individual':
                $data->individual = true;
                break;
            case 'group':
                $data->group = true;
                break;
        }
        $data->cm = $this->cm;
        $data->otopo = $this->otopo;
        return $data;
    }
}
