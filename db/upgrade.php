<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Upgrade code for the hub plugin
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_hub_upgrade($oldversion) {
    global $CFG, $USER, $DB, $OUTPUT;

    require_once($CFG->libdir.'/db/upgradelib.php'); // Core Upgrade-related functions

    $result = true;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes


    if ($result && $oldversion < 2010031610) {

    /// Define field sitecourseid to be added to hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('sitecourseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'siteid');

    /// Conditionally launch add field sitecourseid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010031610, 'local', 'hub');
    }


    if ($result && $oldversion < 2010051800) {

    /// Field 'trusted' to be dropped from hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('trusted');

    /// Conditionally launch drop field
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010051800, 'local', 'hub');
    }

    if ($result && $oldversion < 2010051802) {

    /// Define field publisheremail to be added to hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('publisheremail', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'downloadcostcurrency');

    /// Conditionally launch add field publisheremail
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// Define field deleted to be added to hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'publisheremail');

    /// Conditionally launch add field deleted
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010051802, 'local', 'hub');
    }

    if ($result && $oldversion < 2010061000) {

    /// Define field description to be dropped from hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('screenshotsids');

    /// Conditionally launch drop field description
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    /// Define field screenshots to be added to hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('screenshots', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'deleted');

    /// Conditionally launch add field screenshots
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010061000, 'local', 'hub');
    }



    return $result;
}
