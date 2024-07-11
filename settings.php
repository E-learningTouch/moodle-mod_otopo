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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settingspage = new admin_settingpage('modsettingotopo', new lang_string('settings', 'mod_otopo'), 'moodle/site:config');

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext(
            'mod_otopo/default_sessions',
            new lang_string('defaultsessions', 'mod_otopo'),
            new lang_string('defaultsessions_desc', 'mod_otopo'),
            3,
            PARAM_INT
        ));
        $settingspage->add(new admin_setting_configtext(
            'mod_otopo/default_limit_sessions',
            new lang_string('defaultlimitsessions', 'mod_otopo'),
            new lang_string('defaultlimitsessions_desc', 'mod_otopo'),
            25,
            PARAM_INT
        ));
        $settingspage->add(new admin_setting_configcheckbox(
            'mod_otopo/default_showteachercomments',
            new lang_string('defaultshowteachercomments', 'mod_otopo'),
            new lang_string('defaultshowteachercomments_desc', 'mod_otopo'),
            1
        ));
        $settingspage->add(new admin_setting_configcheckbox(
            'mod_otopo/default_gradeonlyforteacher',
            new lang_string('defaultgradeonlyforteacher', 'mod_otopo'),
            new lang_string('defaultgradeonlyforteacher_desc', 'mod_otopo'),
            0
        ));
        $settingspage->add(new admin_setting_configcheckbox(
            'mod_otopo/default_sessionscalendar',
            new lang_string('defaultsessionscalendar', 'mod_otopo'),
            new lang_string('defaultsessionscalendar_desc', 'mod_otopo'),
            0
        ));
        $settingspage->add(new admin_setting_configselect(
            'mod_otopo/default_sessionvisual',
            new lang_string('defaultsessionvisual', 'mod_otopo'),
            new lang_string('defaultsessionvisual_desc', 'mod_otopo'),
            0,
            [new lang_string('radar', 'mod_otopo'), new lang_string('bar', 'mod_otopo')]
        ));
        $settingspage->add(new admin_setting_configselect(
            'mod_otopo/default_cohortvisual',
            new lang_string('defaultcohortvisual', 'mod_otopo'),
            new lang_string('defaultcohortvisual_desc', 'mod_otopo'),
            0,
            [new lang_string('stackedbar', 'mod_otopo')]
        ));
    }

    $ADMIN->add('modsettings', new admin_category(
        'modotopofolder',
        new lang_string('pluginname', 'mod_otopo'),
        $module->is_enabled() === false
    ));
    $ADMIN->add('modotopofolder', $settingspage);

    $templatespage = new admin_externalpage(
        'templatesotopo',
        new lang_string('otopo:managetemplates', 'mod_otopo'),
        "$CFG->wwwroot/mod/otopo/templates.php",
        'mod/otopo:managetemplates'
    );

    $ADMIN->add('modotopofolder', $templatespage);

    $settings = null; // We do not want standard settings link.
}
