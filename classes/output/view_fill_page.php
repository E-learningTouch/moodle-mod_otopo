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

use renderer_base;
use renderable;
use templatable;
use stdClass;
use user_picture;

/**
 * Class defining the view fill page renderer.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_fill_page implements renderable, templatable {
    /** @var object $cm Course module */
    private object $cm;

    /** @var object $otopo Otopo module instance */
    private object $otopo;

    /** @var string $action */
    private string $action;

    /** @var int|null $sessionid */
    private ?int $sessionid;

    /** @var int|null $sessionindex */
    private ?int $sessionindex;

    /** @var object|null $session */
    private ?object $session;

    /** @var $sessionvalid */
    private bool $sessionvalid;

    /** @var $sessionvalidorclosed */
    private bool $sessionvalidorclosed;

    /**
     * Renderer's constructor.
     *
     * @param object $cm Course module instance.
     * @param object $otopo Otopo instance.
     * @param string $action The action.
     * @param int|null $sessionid Session ID.
     * @param int|null $sessionindex Session index.
     * @param bool $sessionvalid Session is valid?
     * @param bool $sessionvalidorclosed Session is valid or closed?
     * @param object|null $session Session instance.
     */
    public function __construct(
        object $cm,
        object $otopo,
        string $action = 'progress',
        ?int $sessionid = null,
        ?int $sessionindex = null,
        bool $sessionvalid = false,
        bool $sessionvalidorclosed = false,
        ?object $session = null
    ) {
        $this->action = $action;
        $this->cm = $cm;
        $this->otopo = $otopo;
        $this->sessionid = $sessionid;
        $this->sessionindex = $sessionindex;
        $this->session = $session;
        $this->sessionvalid = $sessionvalid;
        $this->sessionvalidorclosed = $sessionvalidorclosed;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return object|array
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $PAGE;

        $data = new stdClass();
        $data->progress = false;
        $data->evaluate = false;
        $data->evolution = false;
        switch ($this->action) {
            case 'progress':
                $data->progress = true;
                break;
            case 'evolution':
                $data->evolution = true;
                break;
            case 'evaluate':
                $data->evaluate = true;
                break;
        }
        $data->actionok = $data->progress || $data->evolution || $data->evaluate;
        $data->progressorevolution = $data->progress || $data->evolution;
        $data->cm = $this->cm;
        $data->otopo = $this->otopo;
        $data->otopos = get_user_otopos($this->otopo, $USER);
        $items = get_items_sorted_from_otopo($this->otopo->id);

        $data->nbrdegreesmax = table_items($items);
        $data->items = array_values($items);
        $data->imagemrotopo = $output->image_url('mr_otopo', 'mod_otopo');
        $data->avatar = $output->render(new user_picture($USER));
        $data->fullname = fullname($USER);
        $data->fromdate = userdate($this->otopo->allowsubmissionfromdate, get_string('strftimedatetimeshort', 'core_langconfig'));
        $data->todate = userdate($this->otopo->allowsubmissiontodate, get_string('strftimedatetimeshort', 'core_langconfig'));
        $data->session = $this->session;
        if ($this->session) {
            $data->sessionfromdate = userdate(
                $this->session->allowsubmissionfromdate,
                get_string('strftimedatetimeshort', 'core_langconfig')
            );
            $data->sessiontodate = userdate(
                $this->session->allowsubmissiontodate,
                get_string('strftimedatetimeshort', 'core_langconfig')
            );
        }
        $data->isopen = is_open($this->otopo);
        $data->sessionindex = abs($this->sessionindex);
        $data->displayname = $data->otopo->session && $data->session
            ? $data->session->name
            : get_string('fillautoeval', 'otopo', $data->sessionindex);
        $data->sessionid = $this->sessionid;
        $data->sessionvalid = $this->sessionvalid;
        $data->sessionvalidorclosed = $this->sessionvalidorclosed;
        $data->haslastmodificationonsession = last_modification_on_session($this->otopo, $USER, $this->sessionid);
        $data->lastmodificationonsession = userdate(
            $data->haslastmodificationonsession,
            get_string('strftimedatetimeshort', 'core_langconfig')
        );
        $data->haslastmodification = last_modification($this->otopo, $USER, $this->sessionid);
        $data->lastmodification = userdate($data->haslastmodification, get_string('strftimedatetimeshort', 'core_langconfig'));

        $sessions = prepare_data($this->otopo, $items, $data->otopos, $USER);
        $data->showotopos = !empty($data->otopos)
            || session_is_valid_or_closed($this->otopo->id, $USER, $this->otopo->session && $sessions ? reset($sessions)->id : -1);

        if ($this->action == 'progress') {
            $currentsession = get_current_session($this->otopo, $USER);
            if ($currentsession) {
                $data->currentsessionavailable = true;
            } else {
                $data->currentsessionavailable = false;
            }
            foreach ($items as $item) {
                $item->itemColor = $item->color;
                $item->itemName = $item->name;
            }
            $data->sessions = array_values($sessions);
            foreach ($items as $item) {
                $item->results = [];
                foreach ($sessions as $session) {
                    if (array_key_exists($item->id, $data->otopos) && array_key_exists($session->id, $data->otopos[$item->id])) {
                        $otopo = &$data->otopos[$item->id][$session->id];
                        $otopo->width = ceil($otopo->rank == 0 ? 0 : $otopo->rank / count($item->degrees) * 100);
                        $item->results[] = $otopo;
                    } else {
                        $item->results[] = null;
                    }
                }
            }
            $data->star = $output->image_url('star', 'mod_otopo')->out();
            $data->starcontainer = $output->image_url('star_container', 'mod_otopo')->out();
            $data->help = $output->image_url('help2', 'mod_otopo')->out();
        }

        if ($this->action == 'evolution') {
            $data->visualradar = $this->otopo->sessionvisual == 0 ? true : false;
            $data->visualbar = $this->otopo->sessionvisual == 1 ? true : false;
            $data->star = $output->image_url('star', 'mod_otopo')->out();
        }

        return $data;
    }
}
