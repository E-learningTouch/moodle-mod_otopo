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
 * Plugin services.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'otoposervice' => [
        'functions' => ['mod_otopo_get_items'],
        'enabled' => 1,
        'downloadfiles' => 0,
        'uploadfiles'  => 0,
    ],
    'otoposervice_admin' => [
        'functions' => [
            'mod_otopo_create_item',
            'mod_otopo_edit_item',
            'mod_otopo_delete_item',
            'mod_otopo_create_degree',
            'mod_otopo_edit_degree',
            'mod_otopo_delete_degree',
            'mod_otopo_get_group_chart',
            'mod_otopo_list_participants',
            'mod_otopo_get_participant',
            'mod_otopo_submit_grading_form',
        ],
        'requiredcapability' => 'mod/otopo:admin',
        'enabled' => 1,
        'downloadfiles' => 0,
        'uploadfiles'  => 0,
    ],
    'otoposervice_user' => [
        'functions' => [
            'mod_otopo_get_user_otopo',
            'mod_otopo_set_user_otopo',
            'mod_otopo_get_my_evolution',
        ],
        'requiredcapability' => 'mod/otopo:fill',
        'enabled' => 1,
        'downloadfiles' => 0,
        'uploadfiles'  => 0,
    ],
];

$functions = [
    'mod_otopo_get_items' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'get_items',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get all items of an Otopo activity.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_otopo_create_item' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'create_item',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Create item in an Otopo activity.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_edit_item' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'edit_item',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Edit item in an Otopo activity.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_delete_item' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'delete_item',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Delete item in an Otopo activity.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_create_degree' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'create_degree',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Create degree in an Otopo item.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_edit_degree' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'edit_degree',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Edit degree in an Otopo item.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_delete_degree' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'delete_degree',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Delete degree in an Otopo item.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_get_user_otopo' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'get_user_otopo',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get all my otopo in an Otopo activity for specific session.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:fill',
    ],
    'mod_otopo_set_user_otopo' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'set_user_otopo',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Set my otopo in an Otopo activity for specific session/item.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:fill',
    ],
    'mod_otopo_get_group_chart' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'get_group_chart',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get chart data of Otopo activity.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:admin',
    ],
    'mod_otopo_get_my_evolution' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'get_my_evolution',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get my evolution.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:fill',
    ],
    'mod_otopo_list_participants' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'list_participants',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get participants.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:view',
    ],
    'mod_otopo_get_participant' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'get_participant',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Get participant.',
        'type'        => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:view',
    ],
    'mod_otopo_submit_grading_form' => [
        'classname'   => 'mod_otopo_external',
        'methodname'  => 'submit_grading_form',
        'classpath'   => 'mod/otopo/externallib.php',
        'description' => 'Submit grading form.',
        'type'        => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'capabilities' => 'mod/otopo:grade',
    ],
];
