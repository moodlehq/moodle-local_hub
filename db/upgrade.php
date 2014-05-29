<?php

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @global moodle_database $DB
 * @param int $oldversion
 */
function xmldb_local_moodleorg_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2012091000) {
        // Define table moodleorg_useful_coursemap to be created
        $table = new xmldb_table('moodleorg_useful_coursemap');

        // Adding fields to table moodleorg_useful_coursemap
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('scaleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table moodleorg_useful_coursemap
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN_UNIQUE, array('courseid'), 'course', array('id'));
        $table->add_key('scaleid', XMLDB_KEY_FOREIGN, array('scaleid'), 'scale', array('id'));

        // Conditionally launch create table for moodleorg_useful_coursemap
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // moodleorg savepoint reached
        upgrade_plugin_savepoint(true, 2012091000, 'local', 'moodleorg');
    }

    if ($oldversion < 2012091001) {

        // Define key lang (unique) to be added to moodleorg_useful_coursemap
        $table = new xmldb_table('moodleorg_useful_coursemap');
        $key = new xmldb_key('lang', XMLDB_KEY_UNIQUE, array('lang'));

        // Launch add key lang
        $dbman->add_key($table, $key);

        // moodleorg savepoint reached
        upgrade_plugin_savepoint(true, 2012091001, 'local', 'moodleorg');
    }

    if ($oldversion < 2012091100) {

        // Define field phmgroupid to be added to moodleorg_useful_coursemap
        $table = new xmldb_table('moodleorg_useful_coursemap');
        $field = new xmldb_field('phmgroupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'scaleid');

        // Conditionally launch add field phmgroupid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key phmgroupid (foreign) to be added to moodleorg_useful_coursemap
        $table = new xmldb_table('moodleorg_useful_coursemap');
        $key = new xmldb_key('phmgroupid', XMLDB_KEY_FOREIGN, array('phmgroupid'), 'groups', array('id'));

        // Launch add key phmgroupid
        $dbman->add_key($table, $key);

        // moodleorg savepoint reached
        upgrade_plugin_savepoint(true, 2012091100, 'local', 'moodleorg');
    }

    if ($oldversion < 2012091200) {

        // Define field coursemanagerslist to be added to moodleorg_useful_coursemap
        $table = new xmldb_table('moodleorg_useful_coursemap');
        $field = new xmldb_field('coursemanagerslist', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'phmgroupid');

        // Conditionally launch add field coursemanagerslist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // moodleorg savepoint reached
        upgrade_plugin_savepoint(true, 2012091200, 'local', 'moodleorg');
    }

    if ($oldversion < 2013031100) {

        // Define field phmgroupid to be dropped from moodleorg_useful_coursemap.
        $table = new xmldb_table('moodleorg_useful_coursemap');
        $key = new xmldb_key('phmgroupid', XMLDB_KEY_FOREIGN, array('phmgroupid'), 'groups', array('id'));

        $dbman->drop_key($table, $key);

        $field = new xmldb_field('phmgroupid');
        // Conditionally launch drop field phmgroupid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Moodleorg savepoint reached.
        upgrade_plugin_savepoint(true, 2013031100, 'local', 'moodleorg');
    }
// commented to not provide false records for any local deployments as it will break. 
// This is here for the record. (Helen had mapped the courses on clone.moodle.org around 2013/12.)
// 
//        if ($oldversion < 201312XX00) {
//
//        // Define field phmgroupid to be dropped from moodleorg_useful_coursemap.
//        $table = new xmldb_table('moodleorg_useful_coursemap');
//
//        if ($dbman->table_exists($table)) {
//            // initial values for moodle.org and clone.moodle.org course mappings.
//            // as created by Helen on clone.moodle.org
//            // INSERT INTO `moodleorg_useful_coursemap` VALUES 
//            $mappingrecords = array(
//                array(5,'en',88,NULL),
//                array(11,'es',92,NULL),
//                array(13,'nl',123,NULL),
//                array(14,'ja',115,NULL),
//                array(17219,'he',113,NULL),
//                array(18,'de',112,NULL),
//                array(16,'ar',131,NULL),
//                array(17,'tr',129,NULL),
//                array(20,'fr',96,NULL),
//                array(21,'ko',134,NULL),
//                array(22,'pl',125,NULL),
//                array(23,'it',121,NULL),
//                array(24,'pt',120,NULL),
//                array(25,'ru',126,NULL),
//                array(26,'sv',128,NULL),
//                array(35,'pt_br',119,NULL),
//                array(36,'th',122,NULL),
//                array(39,'ca',NULL,NULL),
//                array(40,'id',114,NULL),
//                array(42,'eu',136,NULL),
//                array(43,'bg',111,NULL),
//                array(45,'vi',130,NULL),
//                array(53,'sr',127,NULL),
//                array(54,'nn',124,NULL),
//                array(1008,'fa',132,NULL)
//                );
//
//            $table = 'moodleorg_useful_coursemap';
//
//            foreach ($mappingrecords as $mappingrecord) {
//                if( !$DB->record_exists($table, array('courseid' => $mappingrecord[0]))) {
//                    $obj = new stdClass();
//                    $obj->courseid = $mappingrecord[0];
//                    $obj->lang = $mappingrecord[1];
//                    $obj->scaleid = $mappingrecord[2];
//                    $obj->coursemanagerslist = $mappingrecord[3];
//                    $DB->insert_record('moodleorg_useful_coursemap', $obj);
//                }
//            }
//        }
//
//        // Moodleorg savepoint reached.
//        upgrade_plugin_savepoint(true, 201312XX00, 'local', 'moodleorg');
//    }

    if ($oldversion < 2014052800) {

        // port over data for 1.9 sites for the 'public' field that we're going to delete
        // -> to the 'privacy' field , mapping also used in local/moodleorg/top/register/index.php
        $sql = "UPDATE {registry} SET privacy =
                ELT(public+1, 'notdisplayed', 'named', 'linked')
                WHERE hubid is null and privacy is null";
        $DB->execute($sql);

        // Define field privacy to be dropped from registry.
        $table = new xmldb_table('registry');
        $field = new xmldb_field('public');

        // Conditionally launch drop field 'public' (which is a reserved word btw).
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field); //uncomment this when all 19 reg code is refactored
        }

        $index = new xmldb_index('country', XMLDB_INDEX_NOTUNIQUE, array('country'));
        // Conditionally launch drop index countrycode.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // lots of renaming to refactor the table to sync easily with moodle.net's {hub_site_directory}
        $field = new xmldb_field('sitename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'url');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'name');
        }
        $field = new xmldb_field('lang', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'ip');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'language');
        }
        $field = new xmldb_field('country', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'trusted');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'countrycode'); // 'country' is a reserved word.
        }
        $field = new xmldb_field('adminname', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'geolocation');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'contactname');
        }
        $field = new xmldb_field('adminemail', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'contactname');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'contactemail');
        }
        $field = new xmldb_field('adminphone', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'contactemail');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'contactphone');
        }
        $field = new xmldb_field('ipaddress', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'host');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'ip');
        }
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'timeregistered');
        }
        $field = new xmldb_field('timeupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'confirmed');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'timemodified');
        }

        $index = new xmldb_index('countrycode', XMLDB_INDEX_NOTUNIQUE, array('countrycode'));
        // Conditionally launch add index confirmed. (recreate)
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Moodleorg savepoint reached.
        upgrade_plugin_savepoint(true, 2014052800, 'local', 'moodleorg');
    }

    if ($oldversion < 2014052801) {

        // Define field timelastsynced to be added to registry.
        $table = new xmldb_table('registry');
        $field = new xmldb_field('timelastsynced', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'longitude');

        // Conditionally launch add field timelastsynced.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Moodleorg savepoint reached.
        upgrade_plugin_savepoint(true, 2014052801, 'local', 'moodleorg');
    }

    return true;
}