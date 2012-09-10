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

    return true;
}
