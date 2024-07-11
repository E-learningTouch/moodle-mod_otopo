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
 * External functions for webservice.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/externallib.php");
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/grade_form.php');

/**
 * Class used to define external functions for webservice.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_otopo_external extends external_api {
    /** @var context|null Current module context instance. */
    protected static ?context $modulecontext = null;

    /**
     * Perform security checks.
     *
     * @param int $otopoid Otopo ID.
     * @param bool $admin Is the required capability 'admin'?
     * @param int $cmid Current course module ID.
     * @return context The course or module context.
     */
    public static function validate_otopo(int $otopoid, bool $admin, int $cmid = 0) {
        global $DB;

        if ($otopoid >= 0) {
            $otopo = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);
            $context = context_course::instance($otopo->course);
            $cm = get_coursemodule_from_instance('otopo', $otopo->id, $otopo->course, false, MUST_EXIST);
            self::$modulecontext = context_module::instance($cm->id);
            self::validate_context($context);
            if ($admin) {
                require_capability('mod/otopo:admin', $context);
            } else {
                require_capability('mod/otopo:view', $context);
            }
        } else {
            self::$modulecontext = $cmid ? context_module::instance($cmid) : context_system::instance();
            self::validate_context(self::$modulecontext);
            require_capability('mod/otopo:managetemplates', self::$modulecontext);
        }

        return $context;
    }

    /**
     * Perform security checks.
     *
     * @param int $otopoid Otopo ID.
     * @param bool $sessionid The session ID to check.
     * @param int $write Is writing?
     */
    public static function validate_user_otopo(int $otopoid, int $sessionid, bool $write) {
        global $DB, $USER;

        if ($otopoid <= 0) {
            throw new invalid_parameter_exception('Cannot evaluate template');
        }

        $otopo = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);

        if ($sessionid > 0) {
            $session = $DB->get_record('otopo_session', [ 'id' => $sessionid ], '*', MUST_EXIST);
            if ($session->otopo != $otopoid) {
                throw new invalid_parameter_exception('Cannot evaluate on session in another activity.');
            }
        }

        if ($write) {
            if (!is_open($otopo)) {
                throw new invalid_parameter_exception('Cannot evaluate on closed otopo.');
            }
            if (session_is_valid_or_closed($otopoid, $USER, $sessionid)) {
                throw new invalid_parameter_exception('Cannot evaluate on already envaluated session.');
            }
        }

        $context = context_course::instance($otopo->course);
        $cm = get_coursemodule_from_instance('otopo', $otopo->id, $otopo->course, false, MUST_EXIST);
        self::$modulecontext = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/otopo:fill', $context);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_items_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_multiple_structure
     */
    public static function get_items_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'item record id'),
                'name' => new external_value(PARAM_TEXT, 'item name'),
                'color' => new external_value(PARAM_TEXT, 'item color'),
                'ord' => new external_value(PARAM_INT, 'item record ord'),
                'degrees' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'degree record id'),
                        'name' => new external_value(PARAM_TEXT, 'degree name'),
                        'description' => new external_value(PARAM_TEXT, 'degree description', VALUE_OPTIONAL),
                        'grade' => new external_value(PARAM_INT, 'degree grade'),
                        'ord' => new external_value(PARAM_INT, 'degree record ord'),
                    ])
                ),
            ])
        );
    }

    /**
     * Get items.
     *
     * @param int $otopo Otopo Id.
     * @return object[] Items of the activity
     */
    public static function get_items(int $otopo, int $cmid = 0) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::get_items_parameters(), [ 'otopo' => $otopo, 'cmid' => $cmid ]);

        $otopoid = $params['otopo'];

        self::validate_otopo($otopoid, false, $params['cmid']);

        return get_items_sorted_from_otopo($otopoid);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function create_item_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'item' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'item name'),
                'color' => new external_value(PARAM_TEXT, 'item color'),
                'ord' => new external_value(PARAM_INT, 'degree record ord'),
            ]),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_value
     */
    public static function create_item_returns() {
        return new external_value(PARAM_INT, 'item record id');
    }

    /**
     * Create item.
     *
     * @param int $otopo Otopo Id.
     * @param array $item The otopo items to create.
     * @param int $cmid Course module ID.
     * @return object Item created.
     */
    public static function create_item(int $otopo, array $item, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(
            self::create_item_parameters(),
            [ 'otopo' => $otopo, 'item' => $item, 'cmid' => $cmid ]
        );

        $otopoid = $params['otopo'];

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (!has_otopo($otopoid)) {
            $params['item']['otopo'] = $params['otopo'];

            $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
            $event->trigger();

            return $DB->insert_record('otopo_item', $params['item']);
        }
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function edit_item_parameters() {
        return new external_function_parameters([
            'item' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'item id'),
                'name' => new external_value(PARAM_TEXT, 'item name'),
                'color' => new external_value(PARAM_TEXT, 'item color'),
                'ord' => new external_value(PARAM_INT, 'degree record ord'),
            ]),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return null
     */
    public static function edit_item_returns() {
        return null;
    }

    /**
     * Edit item.
     *
     * @param array $item Otopo items.
     * @param int $item Course module ID.
     */
    public static function edit_item(array $item, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::edit_item_parameters(), [ 'item' => $item, 'cmid' => $cmid ]);

        $it = $DB->get_record('otopo_item', [ 'id' => $params['item']['id'] ]);
        $otopoid = $it->otopo;

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (has_otopo($otopoid) && $it->ord != $params['item']['ord']) {
            return;
        }

        $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
        $event->trigger();

        $DB->update_record('otopo_item', $params['item']);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function delete_item_parameters() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'item id'),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return null
     */
    public static function delete_item_returns() {
        return null;
    }

    /**
     * Delete item.
     *
     * @param int $itemid Otopo item ID.
     * @param int $cmid Course module ID.
     */
    public static function delete_item(int $itemid, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::delete_item_parameters(), [ 'itemid' => $itemid, 'cmid' => $cmid ]);

        $otopoid = $DB->get_record('otopo_item', [ 'id' => $params['itemid'] ])->otopo;

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (!has_otopo($otopoid)) {
            $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
            $event->trigger();
            $DB->delete_records('otopo_item_degree', [ 'item' => $params['itemid'] ]);
            $DB->delete_records('otopo_item', [ 'id' => $params['itemid'] ]);
        }
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function create_degree_parameters() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'id of the otopo item'),
            'degree' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'degree name'),
                'description' => new external_value(PARAM_TEXT, 'degree description', VALUE_OPTIONAL),
                'grade' => new external_value(PARAM_INT, 'degree grade'),
                'ord' => new external_value(PARAM_INT, 'degree record ord'),
            ]),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_value
     */
    public static function create_degree_returns() {
        return new external_value(PARAM_INT, 'degree record id');
    }

    /**
     * Create degree.
     *
     * @param int $itemid Otopo item ID.
     * @param array $degree Otopo degrees.
     * @param int $cmid Course module ID.
     * @return object The new degree ID.
     */
    public static function create_degree(int $itemid, array $degree, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(
            self::create_degree_parameters(),
            [ 'itemid' => $itemid, 'degree' => $degree, 'cmid' => $cmid ]
        );

        $item = $DB->get_record('otopo_item', [ 'id' => $params['itemid'] ], '*', MUST_EXIST);

        $otopoid = $item->otopo;

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (!has_otopo($otopoid)) {
            $params['degree']['item'] = $params['itemid'];

            $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
            $event->trigger();

            return $DB->insert_record('otopo_item_degree', $params['degree']);
        }
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function edit_degree_parameters() {
        return new external_function_parameters([
            'degree' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'degree id'),
                'name' => new external_value(PARAM_TEXT, 'degree name'),
                'description' => new external_value(PARAM_TEXT, 'degree description', VALUE_OPTIONAL),
                'grade' => new external_value(PARAM_INT, 'degree grade'),
                'ord' => new external_value(PARAM_INT, 'degree record ord'),
            ]),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return null
     */
    public static function edit_degree_returns() {
        return null;
    }

    /**
     * Edit degree.
     *
     * @param array $degree Otopo degrees.
     * @param int $cmid Course module ID.
     */
    public static function edit_degree(array $degree, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::edit_degree_parameters(), [ 'degree' => $degree, 'cmid' => $cmid ]);

        $deg = $DB->get_record('otopo_item_degree', [ 'id' => $params['degree']['id'] ]);
        $itemid = $deg->item;
        $otopoid = $DB->get_record('otopo_item', [ 'id' => $itemid ])->otopo;

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (has_otopo($otopoid) && ($deg->ord != $params['degree']['ord'] || $deg->grade != $params['degree']['grade'])) {
            return;
        }

        $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
        $event->trigger();

        $DB->update_record('otopo_item_degree', $params['degree']);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function delete_degree_parameters() {
        return new external_function_parameters([
            'degreeid' => new external_value(PARAM_INT, 'degree id'),
            'cmid' => new external_value(PARAM_INT, 'cmid of the otopo activity user come from', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return null
     */
    public static function delete_degree_returns() {
        return null;
    }

    /**
     * Delete degree.
     *
     * @param int $degreeid Otopo degree ID.
     * @param int $cmid Course module ID.
     */
    public static function delete_degree(int $degreeid, int $cmid) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::delete_degree_parameters(), [ 'degreeid' => $degreeid, 'cmid' => $cmid ]);

        $itemid = $DB->get_record('otopo_item_degree', [ 'id' => $params['degreeid'] ])->item;
        $otopoid = $DB->get_record('otopo_item', [ 'id' => $itemid ])->otopo;

        self::validate_otopo($otopoid, true, $params['cmid']);

        if (!has_otopo($otopoid)) {
            $event = \mod_otopo\event\activity_updated::create([ 'context' => self::$modulecontext ]);
            $event->trigger();

            $DB->delete_records('otopo_item_degree', [ 'id' => $params['degreeid'] ]);
        }
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_user_otopo_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'session' => new external_value(PARAM_INT, 'id of the otopo activity session'),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_multiple_structure
     */
    public static function get_user_otopo_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'otopo record id', VALUE_OPTIONAL),
                'item' => new external_value(PARAM_INT, 'otopo item'),
                'degree' => new external_value(PARAM_INT, 'otopo degree'),
                'justification' => new external_value(PARAM_TEXT, 'otopo justification'),
                'comment' => new external_value(PARAM_TEXT, 'otopo teacher comment', VALUE_OPTIONAL),
            ])
        );
    }

    /**
     * Get user otopo.
     *
     * @param int $otopo Otopo ID.
     * @param int $session Otopo session ID.
     * @return object Otopo info of the user session.
     */
    public static function get_user_otopo(int $otopo, int $session) {
        global $CFG, $DB, $USER;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::get_user_otopo_parameters(), [ 'otopo' => $otopo, 'session' => $session ]);

        $otopoid = $params['otopo'];
        $sessionid = $params['session'];

        self::validate_user_otopo($otopoid, $sessionid, false);

        $result = $DB->get_records_sql(
            'SELECT {otopo_user_otopo}.id AS id,item,degree,justification,teacher_comment AS comment
              FROM {otopo_user_otopo}
              INNER JOIN {otopo_item} it ON item = it.id
              WHERE userid = :user AND session = :session AND it.otopo = :otopo',
            [ 'user' => intval($USER->id), 'session' => $sessionid, 'otopo' => $otopoid ]
        );

        if ($result) {
            $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);
            if (!$o->showteachercomments) {
                foreach ($result as $r) {
                    unset($r->comment);
                }
            }
        }

        return $result;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function set_user_otopo_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'session' => new external_value(PARAM_INT, 'id of the otopo activity session'),
            'item' => new external_value(PARAM_INT, 'id of the otopo activity item'),
            'degree' => new external_value(PARAM_INT, 'id of the otopo activity item degree'),
            'justification' => new external_value(PARAM_TEXT, 'justification of the otopo'),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return null
     */
    public static function set_user_otopo_returns() {
        return null;
    }

    /**
     * Set user otopo.
     *
     * @param int $otopo Otopo ID.
     * @param int $session Otopo session ID.
     * @param int $item Otopo item ID.
     * @param int|null $degree Otopo degree ID.
     * @param string $justification User justification.
     */
    public static function set_user_otopo(int $otopo, int $session, int $item, ?int $degree, string $justification) {
        global $CFG, $DB, $USER;
        require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(
            self::set_user_otopo_parameters(),
            [ 'otopo' => $otopo, 'session' => $session, 'item' => $item, 'degree' => $degree, 'justification' => $justification ]
        );

        $otopoid = $params['otopo'];
        $sessionid = $params['session'];
        $itemid = $params['item'];
        $degreeid = $params['degree'];
        $justification = $params['justification'];
        $date = new DateTime();
        $lastmodificationdate = $date->getTimestamp();

        self::validate_user_otopo($otopoid, $sessionid, true);

        $userotopo = $DB->get_record('otopo_user_otopo', [ 'userid' => $USER->id, 'session' => $sessionid, 'item' => $itemid ]);
        if ($userotopo) {
            if ($degreeid) {
                $userotopo->degree = $degreeid;
                $userotopo->justification = $justification;
                $userotopo->lastmodificationdate = $lastmodificationdate;
                $DB->update_record('otopo_user_otopo', $userotopo);
            } else {
                $DB->delete_records('otopo_user_otopo', [ 'id' => $userotopo->id ]);
            }
        } else {
            $userotopo = (object) [
                'userid' => $USER->id,
                'session' => $sessionid,
                'item' => $itemid,
                'degree' => $degreeid,
                'justification' => $justification,
                'lastmodificationdate' => $lastmodificationdate,
            ];
            $DB->insert_record('otopo_user_otopo', $userotopo);
        }

        $event = \mod_otopo\event\session_saved::create([ 'context' => self::$modulecontext ]);
        $event->trigger();
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_group_chart_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'users' => new external_multiple_structure(
                new external_value(PARAM_INT, 'id of user')
            ),
            'session' => new external_value(PARAM_INT, 'id of the session', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_single_structure
     */
    public static function get_group_chart_returns() {
        return new external_single_structure([
            'labels' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'label')
            ),
            'fullLabels' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'label')
            ),
            'datasets' => new external_multiple_structure(
                new external_single_structure([
                    'label' => new external_value(PARAM_TEXT, 'label'),
                    'borderWidth' => new external_value(PARAM_TEXT, 'border width'),
                    'data' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'numeric value')
                    ),
                    'dataSource' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'numeric value')
                    ),
                    'backgroundColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex')
                    ),
                    'borderColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex')
                    ),
                    'hoverBackgroundColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex')
                    ),
                ])
            ),
        ]);
    }

    /**
     * Get group chart.
     *
     * @param int $o Otopo ID.
     * @param array $users Users to show.
     * @param int $otopo Otopo session ID.
     */
    public static function get_group_chart(int $o, array $users, int $session) {
        global $DB;

        $params = self::validate_parameters(
            self::get_group_chart_parameters(),
            [ 'otopo' => $o, 'users' => $users, 'session' => $session ]
        );

        $otopoid = $params['otopo'];
        $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);
        $users = $params['users'];
        $session = $params['session'];

        self::validate_otopo($otopoid, true);

        $distribution = get_distribution_by_item($o, $users, $session);
        $items = get_items_sorted_from_otopo($o->id);

        $chart = [ 'labels' => [], 'datasets' => [] ];

        $nbrdegreesmax = 0;
        foreach ($items as $item) {
            if (count($item->degrees) > $nbrdegreesmax) {
                $nbrdegreesmax = count($item->degrees);
            }
        }
        foreach (array_values($items) as $key1 => $item) {
            $chart['labels'][] = get_string('itemitem', 'otopo') . ' ' . ($key1 + 1);
            $chart['fullLabels'][] = $item->name;
            for ($degree = 0; $degree < $nbrdegreesmax; $degree++) {
                if (!array_key_exists($degree, $chart['datasets'])) {
                    $chart['datasets'][$degree]['label'] = get_string('autoevaldegree', 'otopo') . ' ' . ($degree + 1);
                    $chart['datasets'][$degree]['data'] = [];
                    $chart['datasets'][$degree]['dataSource'] = [];
                    $chart['datasets'][$degree]['backgroundColor'] = [];
                    $chart['datasets'][$degree]['hoverBackgroundColor'] = [];
                    $chart['datasets'][$degree]['borderColor'] = [];
                    $chart['datasets'][$degree]['borderWidth'] = 1;
                }
                if (!array_key_exists($key1, $chart['datasets'][$degree]['data'])) {
                    $chart['datasets'][$degree]['dataSource'][$key1] = 0;
                    $chart['datasets'][$degree]['data'][$key1] = 0;
                    $chart['datasets'][$degree]['backgroundColor'][$key1] = "#323e70"
                        . dechex((1 - (($degree + 1) / ($nbrdegreesmax + 2))) * 255);
                    $chart['datasets'][$degree]['hoverBackgroundColor'][$key1] = "#323e70ff";
                    $chart['datasets'][$degree]['borderColor'][$key1] = "#323e70ff";
                    $chart['datasets'][$degree]['toto'] = $item->name;
                }
                if (array_key_exists($degree, $distribution) && array_key_exists($key1, $distribution[$degree])) {
                    $chart['datasets'][$degree]['dataSource'][$key1] = $distribution[$degree][$key1];
                    $chart['datasets'][$degree]['data'][$key1] = ceil($chart['datasets'][$degree]['dataSource'][$key1] * 100
                        / count($users));
                    $chart['datasets'][$degree]['toto'] = $item->name;
                }
            }
        }

        return $chart;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_my_evolution_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'id of the otopo activity'),
            'visual' => new external_value(PARAM_TEXT, 'visual for charts'),
            'item' => new external_value(PARAM_INT, 'id of the item to focus evolution', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_single_structure
     */
    public static function get_chart_return() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'id of session', VALUE_OPTIONAL),
            'grade' => new external_value(PARAM_INT, 'grade', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'comment', VALUE_OPTIONAL),
            'label' => new external_value(PARAM_TEXT, 'label', VALUE_OPTIONAL),
            'fullLabel' => new external_value(PARAM_TEXT, 'full label', VALUE_OPTIONAL),
            'labels' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'label', VALUE_OPTIONAL)
            ),
            'fullLabels' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'label', VALUE_OPTIONAL)
            ),
            'color' => new external_value(PARAM_TEXT, 'color hex', VALUE_OPTIONAL),
            'allowsubmissionfromdate' => new external_value(PARAM_TEXT, 'from date', VALUE_OPTIONAL),
            'allowsubmissiontodate' => new external_value(PARAM_TEXT, 'to date', VALUE_OPTIONAL),
            'datasets' => new external_multiple_structure(
                new external_single_structure([
                    'label' => new external_value(PARAM_TEXT, 'label', VALUE_OPTIONAL),
                    'borderWidth' => new external_value(PARAM_TEXT, 'border width', VALUE_OPTIONAL),
                    'data' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'numeric value', VALUE_OPTIONAL)
                    ),
                    'labels' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'label', VALUE_OPTIONAL)
                    ),
                    'backgroundColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex', VALUE_OPTIONAL)
                    ),
                    'hoverBackgroundColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex', VALUE_OPTIONAL)
                    ),
                    'borderColor' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'color hex', VALUE_OPTIONAL)
                    ),
                ])
            ),
        ]);
    }

    /**
     * Returns description of method returns.
     *
     * @return external_single_structure
     */
    public static function get_my_evolution_returns() {
        return new external_single_structure([
            'currentchart' => self::get_chart_return(),
            'chartitem' => self::get_chart_return(),
            'charts' => new external_multiple_structure(
                self::get_chart_return()
            ),
            'chartitemdegrees' => new external_multiple_structure(
                new external_single_structure([
                    'label' => new external_value(PARAM_TEXT, 'label'),
                    'degree' => new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'id of degree'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_TEXT, 'description'),
                        'grade' => new external_value(PARAM_INT, 'grade'),
                        'ord' => new external_value(PARAM_INT, 'ord'),
                    ]),
                    'color' => new external_value(PARAM_TEXT, 'color hex'),
                    'allowsubmissionfromdate' => new external_value(PARAM_TEXT, 'from date'),
                    'allowsubmissiontodate' => new external_value(PARAM_TEXT, 'to date'),
                    'lastmodificationonsession' => new external_value(PARAM_TEXT, 'last modification'),
                ])
            ),
            'max' => new external_value(PARAM_INT, 'max degrees'),
        ]);
    }

    /**
     * Get group chart.
     *
     * @param int $o Otopo ID.
     * @param string $visual Chart type.
     * @param int|null $item Otopo item ID.
     */
    public static function get_my_evolution(int $o, string $visual, ?int $item) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::get_my_evolution_parameters(),
            [ 'otopo' => $o, 'visual' => $visual, 'item' => $item ]
        );

        $otopoid = $params['otopo'];
        $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);
        $visual = $params['visual'];
        $item = $params['item'];

        self::validate_otopo($otopoid, false);

        $items = get_items_from_otopo($o->id);
        $itemssorted = get_items_sorted_from_otopo($o->id);
        $otopos = get_user_otopos($o, $USER);
        $sessions = prepare_data($o, $itemssorted, $otopos, $USER);

        $data = get_my_evolution($o, $items, $itemssorted, $sessions, $otopos, $USER, $visual, $item);

        $current = get_current_session($o, $USER);

        if ($current && property_exists($data, 'charts') && array_key_exists(abs($current[0]) - 1, $data->charts)) {
            $currentchart = $data->charts[abs($current[0]) - 1];
            unset($data->charts[abs($current[0]) - 1]);
        } else {
            $currentchart = null;
        }

        if ($o->gradeonlyforteacher) {
            if ($currentchart) {
                $currentchart['grade'] = null;
            }
            foreach ($data->charts as &$chart) {
                $chart['grade'] = null;
            }
        }

        return [
            'currentchart' => $currentchart ? $currentchart : ['labels' => [], 'fullLabels' => [], 'datasets' => []],
            'charts' => $data->charts ? $data->charts : [],
            'chartitem' => property_exists($data, 'chartitem')
                ? $data->chartitem :
                ['labels' => [], 'fullLabels' => [], 'datasets' => []],
            'chartitemdegrees' => property_exists($data, 'chartitemdegrees') ? $data->chartitemdegrees : [],
            'max' => $data->max,
        ];
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function list_participants_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'otopo instance id'),
            'filter' => new external_value(PARAM_RAW, 'search string to filter the results'),
            'skip' => new external_value(PARAM_INT, 'number of records to skip', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'maximum number of records to return', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Retrieves the list of students to be graded.
     *
     * @param int $o Otopo ID.
     * @param string $filter A filter.
     * @return array of warnings and status result.
     * @throws moodle_exception
     */
    public static function list_participants(int $o, string $filter) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::list_participants_parameters(), [
            'otopo' => $o,
            'filter' => $filter,
        ]);

        $otopoid = $params['otopo'];
        $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);

        $context = self::validate_otopo($otopoid, false);

        $participants = get_participants($o, self::$modulecontext);

        require_once($CFG->dirroot . '/user/lib.php');
        $userfields = user_get_default_fields();
        // Remove enrolled courses from users fields to be returned.
        $key = array_search('enrolledcourses', $userfields);
        if ($key !== false) {
            unset($userfields[$key]);
        } else {
            throw new moodle_exception('invaliduserfield', 'error', '', 'enrolledcourses');
        }

        $result = [];
        $index = 0;
        foreach ($participants as $record) {
            // Preserve the fullname set by the assignment.
            $fullname = fullname($record, has_capability('moodle/site:viewfullnames', $context));
            $searchable = $fullname;
            $match = false;
            if (empty($filter)) {
                $match = true;
            } else {
                $filter = core_text::strtolower($filter);
                $value = core_text::strtolower($searchable);
                if (is_string($value) && (core_text::strpos($value, $filter) !== false)) {
                    $match = true;
                }
            }
            if ($match) {
                $index++;
                if ($index <= $params['skip']) {
                    continue;
                }
                if (($params['limit'] > 0) && (($index - $params['skip']) > $params['limit'])) {
                    break;
                }
                $userdetails = user_get_user_details($record, $course, $userfields);
                $userdetails['id'] = $record->id;
                $userdetails['fullname'] = $fullname;

                $result[] = $userdetails;
            }
        }
        return $result;
    }

    /**
     * Returns the description of the results of the mod_assign_external::list_participants() method.
     *
     * @return external_description
     */
    public static function list_participants_returns() {
        // Get user description.
        $userdesc = core_user_external::user_description();

        // List unneeded properties.
        $unneededproperties = [ 'auth', 'confirmed', 'lang', 'calendartype', 'theme', 'timezone', 'mailformat' ];

        // Unnecessary check to bypass PSR12 restriction on undefined properties.
        if ($userdesc instanceof external_single_structure) {
            // Remove unneeded properties for consistency with the previous version.
            foreach ($unneededproperties as $prop) {
                unset($userdesc->keys[$prop]);
            }

            // Override property attributes for consistency with the previous version.
            $userdesc->keys['fullname']->type = PARAM_NOTAGS;
            $userdesc->keys['profileimageurlsmall']->required = VALUE_OPTIONAL;
            $userdesc->keys['profileimageurl']->required = VALUE_OPTIONAL;
        }

        // Merge keys.
        return new external_multiple_structure($userdesc);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_participant_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'otopo instance id'),
            'userid' => new external_value(PARAM_INT, 'user id'),
            'session' => new external_value(PARAM_INT, 'session id', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Get the user participating in the given assignment. An error with code 'usernotincourse'
     * is thrown is the user isn't a participant of the given assignment.
     *
     * @param int $o Otopo ID.
     * @param int $userid The user id
     * @param int $session Otopo session ID.
     * @throws moodle_exception
     */
    public static function get_participant(int $o, int $userid, int $session) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/user/lib.php");

        $params = self::validate_parameters(self::get_participant_parameters(), [
            'otopo' => $o,
            'userid' => $userid,
            'session' => $session,
        ]);

        $otopoid = $params['otopo'];
        $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);

        $context = self::validate_otopo($otopoid, false);

        require_capability('mod/otopo:grade', $context);

        $participant = get_participant($o, self::$modulecontext, $userid);

        if (!$participant) {
            // No participant found so we can return early.
            throw new moodle_exception('usernotincourse');
        }

        $items = get_items_from_otopo($o->id);
        $itemssorted = get_items_sorted_from_otopo($o->id);
        $otopos = get_user_otopos($o, $participant);
        $sessions = prepare_data($o, $itemssorted, $otopos, $participant);
        $participantsessions = $participant->sessions;
        $participant->sessions = [];
        foreach ($sessions as $s1) {
            $found = false;
            foreach ($participantsessions as $s2) {
                if ($s1->id == intval($s2['id'])) {
                    $found = true;
                    $s2['id'] = intval($s2['id']);
                    $participant->sessions[] = $s2;
                }
            }
            if (!$found) {
                $isvalid = session_is_valid_or_closed($o->id, $participant, $s1->id);
                if ($isvalid) {
                    $participant->sessions[] = [
                        'id' => $s1->id,
                        'validated' => $isvalid,
                        'not_found' => true,
                    ];
                }
            }
        }

        $sessiono = null;
        $keywithoutnotfound = 1;
        $key = 1;
        foreach ($participant->sessions as &$s) {
            if (!array_key_exists('not_found', $s)) {
                $s['key_without_not_found'] = $keywithoutnotfound;
                ++$keywithoutnotfound;
            }
            $s['key'] = $key;
            ++$key;
            if ($s['id'] == $session) {
                $s['selected'] = true;
                $sessiono = $s;
            } else {
                $s['selected'] = false;
            }
            unset($s['grade']);
            unset($s['comment']);
        }

        $datasessions = get_my_evolution(
            $o,
            $items,
            $itemssorted,
            $sessions,
            $otopos,
            $participant,
            $o->sessionvisual == 0 ? 'radar' : 'bar',
            null
        );
        $lastmodification = last_modification_on_session($o, $participant, $sessiono['id']);
        if ($lastmodification) {
            $sessiono['lastmodification'] = userdate(
                last_modification_on_session($o, $participant, $sessiono['id']),
                get_string('strftimedatetimeshort', 'core_langconfig')
            );
        }
        $sessiono['grade'] = $sessions[$sessiono['key'] - 1]->grade;
        $sessiono['comment'] = $sessions[$sessiono['key'] - 1]->comment;

        $sessionchart = $datasessions->charts[$sessiono['key_without_not_found'] - 1];

        $itemsotopos = [];

        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        foreach ($itemssorted as $item) {
            $itemsotopo = [
                'id' => $item->id,
                'key' => $item->ord,
                'name' => $item->name,
                'color' => $item->color,
            ];
            if (array_key_exists($item->id, $otopos) && array_key_exists($session, $otopos[$item->id])) {
                $otopo = $otopos[$item->id][$session];
                $itemsotopo['otopoid'] = $otopo->id;
                $itemsotopo['otopoteachercomment'] = preg_replace(
                    $url,
                    '<a href="$0" target="_blank" title="$0">$0</a>',
                    $otopo->teacher_comment
                );
                $itemsotopo['otopojustification'] = preg_replace(
                    $url,
                    '<a href="$0" target="_blank" title="$0">$0</a>',
                    $otopo->justification
                );
                $itemsotopo['otopodegreeid'] = $otopo->degree->id;
                $itemsotopo['otopodegreekey'] = $otopo->rank;
                $itemsotopo['otopodegreename'] = $otopo->degree->name;
                $itemsotopo['otopodegreename'] = $otopo->degree->name;
                $itemsotopo['otopodegreedescription'] = $otopo->degree->description;
                $itemsotopo['otopodegreewidth'] = $otopo->rank == 0 ? 0 : $otopo->rank / count($item->degrees) * 100;
            }
            $itemsotopos[] = $itemsotopo;
        }

        $return = [
            'id' => $participant->id,
            'fullname' => $participant->fullname,
            'sessions' => $participant->sessions ? $participant->sessions : [],
            'session' => $sessiono,
            'sessionchart' => $sessionchart ? $sessionchart : ['labels' => [], 'fullLabels' => [], 'datasets' => []],
            'items' => $itemsotopos,
            'max' => $datasessions->max,
        ];

        if ($userdetails = user_get_user_details($participant, $course)) {
            $return['user'] = $userdetails;
        }

        // Needed because of a breaking change in chartJS 3 which is introduce in moodle 4.
        $moodleversion = get_config('')->version;
        if ($moodleversion < 2022041906) {
            $moodlepre4 = true;
        } else {
            $moodlepre4 = false;
        }
        $return['moodlePre4'] = $moodlepre4;

        return $return;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function get_participant_returns() {
        $userdescription = core_user_external::user_description();
        $userdescription->default = [];
        $userdescription->required = VALUE_OPTIONAL;

        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'ID of the user'),
            'fullname' => new external_value(PARAM_NOTAGS, 'The fullname of the user'),
            'user' => $userdescription,
            'sessionchart' => self::get_chart_return(),
            'session' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'session id', VALUE_OPTIONAL),
                'key' => new external_value(PARAM_INT, 'session key', VALUE_OPTIONAL),
                'validated' => new external_value(PARAM_BOOL, 'have they validated their otopo session', VALUE_OPTIONAL),
                'grade' => new external_value(PARAM_INT, 'grade', VALUE_OPTIONAL),
                'comment' => new external_value(PARAM_RAW, 'comment', VALUE_OPTIONAL),
                'selected' => new external_value(PARAM_BOOL, 'selected', VALUE_OPTIONAL),
                'lastmodification' => new external_value(PARAM_TEXT, 'last modification', VALUE_OPTIONAL),
            ]),
            'sessions' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'session id'),
                    'key' => new external_value(PARAM_INT, 'session key'),
                    'selected' => new external_value(PARAM_BOOL, 'selected'),
                    'validated' => new external_value(PARAM_BOOL, 'have they validated their otopo session', VALUE_OPTIONAL),
                ])
            ),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'item id'),
                    'key' => new external_value(PARAM_INT, 'item key'),
                    'name' => new external_value(PARAM_TEXT, 'item name'),
                    'color' => new external_value(PARAM_TEXT, 'item color'),
                    'otopoid' => new external_value(PARAM_INT, 'otopo id', VALUE_OPTIONAL),
                    'otopojustification' => new external_value(PARAM_RAW, 'otopo justification', VALUE_OPTIONAL),
                    'otopoteachercomment' => new external_value(PARAM_RAW, 'otopo comment', VALUE_OPTIONAL),
                    'otopodegreeid' => new external_value(PARAM_INT, 'degree key', VALUE_OPTIONAL),
                    'otopodegreekey' => new external_value(PARAM_INT, 'degree key', VALUE_OPTIONAL),
                    'otopodegreename' => new external_value(PARAM_TEXT, 'degree name', VALUE_OPTIONAL),
                    'otopodegreedescription' => new external_value(PARAM_TEXT, 'degree description', VALUE_OPTIONAL),
                    'otopodegreewidth' => new external_value(PARAM_TEXT, 'degree width', VALUE_OPTIONAL),
                ])
            ),
            'max' => new external_value(PARAM_INT, 'max degrees'),
        ]);
    }

    /**
     * Describes the parameters for submit_grading_form webservice.
     *
     * @return external_function_parameters
     */
    public static function submit_grading_form_parameters() {
        return new external_function_parameters([
            'otopo' => new external_value(PARAM_INT, 'otopo instance id'),
            'userid' => new external_value(PARAM_INT, 'user id'),
            'session' => new external_value(PARAM_INT, 'session id', VALUE_OPTIONAL),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the grading form, encoded as a json array'),
            'itemscomments' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'item id'),
                    'value' => new external_value(PARAM_RAW, 'teacher comment'),
                ])
            ),
        ]);
    }

    /**
     * Submit the logged in users assignment for grading.
     *
     * @param int $o Otopo ID.
     * @param int $userid The user id
     * @param int $session Otopo session ID.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @param array $itemscomments Otopo items comments.
     * @return array of warnings to indicate any errors.
     */
    public static function submit_grading_form(int $o, int $userid, int $session, string $jsonformdata, array $itemscomments) {
        global $DB;

        $params = self::validate_parameters(self::submit_grading_form_parameters(), [
            'otopo' => $o,
            'userid' => $userid,
            'session' => $session,
            'jsonformdata' => $jsonformdata,
            'itemscomments' => $itemscomments,
        ]);

        $otopoid = $params['otopo'];
        $o = $DB->get_record('otopo', [ 'id' => $otopoid ], '*', MUST_EXIST);

        $context = self::validate_otopo($otopoid, false);

        require_capability('mod/otopo:grade', $context);

        // On teste si la session est cloturée.
        if (!session_is_valid_or_closed($otopoid, (object) [ 'id' => $userid ], $session)) {
            return [[
                'item' => 'Invalid session.',
                'itemid' => $otopoid,
                'warningcode' => 'couldnotsavegrade',
                'message' => 'Could not save grade',
            ]];
        }

        // Data is injected into the form by the last param for the constructor.
        $warnings = [];
        $data = [];
        parse_str(json_decode($params['jsonformdata']), $data); // Why URL-encoded AND JSON encoded ;(.

        $mform = new grade_form(null, [ 'otopo' => $o, 'grader' => (object) $data ], 'post', '', null, true, $data);
        if ($validateddata = $mform->get_data()) {
            if (
                $graderid = $DB->get_field('otopo_grader', 'id', [
                    'userid' => $userid,
                    'session' => $session,
                    'otopo' => $o->id,
                ])
            ) {
                $DB->update_record('otopo_grader', [
                    'id' => $graderid,
                    'comment' => $validateddata->comment['text'],
                    'grade' => $validateddata->grade,
                ]);
            } else {
                $DB->insert_record('otopo_grader', [
                    'userid' => $userid,
                    'session' => $session,
                    'otopo' => $o->id,
                    'comment' => $validateddata->comment['text'],
                    'grade' => $validateddata->grade,
                ]);
            }
            otopo_update_grades($o, $userid);
        } else {
            $warnings[] = [
                'item' => 'Form validation failed.',
                'itemid' => $otopoid,
                'warningcode' => 'couldnotsavegrade',
                'message' => 'Could not save grade',
            ];
        }

        foreach ($itemscomments as $itemcomment) {
            $otopo = $DB->get_record('otopo_user_otopo', [
                'userid' => $userid,
                'session' => $session,
                'item' => $itemcomment['id'],
            ]);
            $otopo->teacher_comment = $itemcomment['value'];
            $DB->update_record('otopo_user_otopo', $otopo);
        }

        return $warnings;
    }

    /**
     * Describes the return for submit_grading_form.
     *
     * @return external_function_parameters
     */
    public static function submit_grading_form_returns() {
        return new external_warnings();
    }
}
