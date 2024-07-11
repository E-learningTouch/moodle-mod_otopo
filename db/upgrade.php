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
 * Plugin upgrade logic.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Upgrade the plugin.
  *
  * @param int $oldversion The old version.
  */
function xmldb_otopo_upgrade(int $oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022062101) {
        $table = new xmldb_table('otopo');
        $field = new xmldb_field('event', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('otopo_session');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2022062101, 'otopo');
    }

    if ($oldversion < 2022070500) {
        $table = new xmldb_table('otopo');
        $field = new xmldb_field('event', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $fieldstart = new xmldb_field('event_start', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $fieldend = new xmldb_field('event_end', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        if (!$dbman->field_exists($table, $fieldstart)) {
            $dbman->add_field($table, $fieldstart);
        }
        if (!$dbman->field_exists($table, $fieldend)) {
            $dbman->add_field($table, $fieldend);
        }

        $table = new xmldb_table('otopo_session');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        if (!$dbman->field_exists($table, $fieldstart)) {
            $dbman->add_field($table, $fieldstart);
        }
        if (!$dbman->field_exists($table, $fieldend)) {
            $dbman->add_field($table, $fieldend);
        }

        // Assignment savepoint reached.
        upgrade_mod_savepoint(true, 2022070500, 'otopo');
    }

    if ($oldversion < 2023020100) {
        $table = new xmldb_table('otopo_user_otopo');
        $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $oldindex = new xmldb_index('user', XMLDB_INDEX_NOTUNIQUE, ['user']);
        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Drop old index.
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }
        // Rename field.
        $dbman->rename_field($table, $field, 'userid');
        // Create new index.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('otopo_user_valid_session');

        // Drop old index.
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }
        // Rename field.
        $dbman->rename_field($table, $field, 'userid');
        // Create new index.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('otopo_grader');

        // Drop old index.
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }
        // Rename field.
        $dbman->rename_field($table, $field, 'userid');
        // Create new index.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Otopo savepoint reached.
        upgrade_mod_savepoint(true, 2023020100, 'otopo');
    }

    return true;
}
