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
 * Used to import VueJS.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

$config = [
    "paths" => [
        "lodash" => "https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash",
        "vue" => "https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue"
            . (property_exists($CFG, 'vuejsdev') && $CFG->vuejsdev ? '' : '.min'),
        "vuecolor" => "https://unpkg.com/vue-color/dist/vue-color.min",
    ],
    "shim" => [
        "vue" => [
            "exports" => "Vue",
        ],
    ],
];
$requirejs = 'require.config(' . json_encode($config) . ')';
$PAGE->requires->js_amd_inline($requirejs);
