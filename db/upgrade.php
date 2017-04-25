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

     if ($result && $oldversion < 2010062300) {

    /// Define field deleted to be added to hub_site_directory
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0', 'participantnumberaverage');

    /// Conditionally launch add field deleted
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010062300, 'local', 'hub');
    }

    if ($result && $oldversion < 2010062302) {

    /// Define field deleted to be added to hub_communications
        $table = new xmldb_table('hub_communications');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'confirmed');

    /// Conditionally launch add field deleted
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint($result, 2010062302, 'local', 'hub');
    }

    if ($oldversion < 2010071500) {

    /// Define field timepublished to be added to hub_course_directory
        $table = new xmldb_table('hub_course_directory');
        $field = new xmldb_field('timepublished', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'screenshots');

    /// Conditionally launch add field timepublished
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// hub savepoint reached
        upgrade_plugin_savepoint(true, 2010071500, 'local', 'hub');
    }

    if ($oldversion < 2010080400) {

        // Define field publicationmax to be added to hub_site_directory
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('publicationmax', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'deleted');

        // Conditionally launch add field publicationmax
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010080400, 'local', 'hub');
    }

    if ($oldversion < 2010091300) {

         // Rename field userid on table hub_course_feedbacks to NEWNAMEGOESHERE
        $table = new xmldb_table('hub_course_feedbacks');
        $field = new xmldb_field('rating', XMLDB_TYPE_INTEGER, '10', false, null, null, null, 'text');

        // Launch rename field userid
        $dbman->rename_field($table, $field, 'userid');

        // Define field time to be added to hub_course_feedbacks
        $table = new xmldb_table('hub_course_feedbacks');
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'userid');

        // Conditionally launch add field time
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010091300, 'local', 'hub');

    }

    if ($oldversion < 2010092800) {

        // Rename field token on table hub_site_directory to NEWNAMEGOESHERE
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('token', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'url');

        // Launch rename field token
        $dbman->rename_field($table, $field, 'secret');

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010092800, 'local', 'hub');
    }

     if ($oldversion < 2010110900) {

        // Define field senttouserid to be added to hub_course_feedbacks
        $table = new xmldb_table('hub_course_feedbacks');
        $field = new xmldb_field('senttouserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'time');

        // Conditionally launch add field senttouserid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         // Define field senttoemail to be added to hub_course_feedbacks
        $table = new xmldb_table('hub_course_feedbacks');
        $field = new xmldb_field('senttoemail', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'senttouserid');

        // Conditionally launch add field senttoemail
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010110900, 'local', 'hub');
    }

    if ($oldversion < 2010110901) {
        global $DB;
        $messageprovider = new stdClass();
        $messageprovider->name = 'coursehubmessage';
        $messageprovider->component = 'local/hub';
        $DB->insert_record('message_providers', $messageprovider);
        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010110901, 'local', 'hub');
    }

    if ($oldversion < 2010111200) {

        //give 'local/hub:viewsmallinfo' capability to registered sites
        //TODO: this is a wrong way to get role, it should be by shortname
        $role = $DB->get_record('role', array('name' => 'Registered Hub User'));
        if (!empty($role)) {
            //TODO: wrong way: we should not assign capability into upgrade script - see MDL-25222
            assign_capability('local/hub:viewsmallinfo', CAP_ALLOW, $role->id, get_system_context()->id);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2010111200, 'local', 'hub');
    }

    if ($oldversion < 2011022500) {

        // Define table hub_stolen_site_secrets to be created
        $table = new xmldb_table('hub_stolen_site_secrets');

        // Adding fields to table hub_stolen_site_secrets
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('secret', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('siteurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('blockeddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table hub_stolen_site_secrets
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for hub_stolen_site_secrets
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2011022500, 'local', 'hub');
    }

    if ($oldversion < 2011030101) {

        //update all stolen secret to md5
        $stolensecrets = $DB->get_records('hub_stolen_site_secrets');
        if (!empty($stolensecrets)) {
            foreach ($stolensecrets as $stolensecret) {
                $stolensecret->secret = md5($stolensecret->secret);
                $DB->update_record('hub_stolen_site_secrets', $stolensecret);
            }
        }

        //update all site secret to md5
        $sites = $DB->get_records('hub_site_directory');
        if (!empty($sites)) {
            foreach ($sites as $site) {
                $site->secret = md5($site->secret);
                $DB->update_record('hub_site_directory', $site);
            }
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2011030101, 'local', 'hub');
    }

    if ($oldversion < 2011042100) {
        //create a new scale called featured
        $scale = new stdClass();
        $scale->courseid = 0;
        $admin = get_admin();
        $scale->userid = $admin->id;
        $scale->name = 'coursefeatured';
        $scale->scale = get_string('featured', 'local_hub');
        $scale->description = get_string('featureddesc', 'local_hub');
        $scale->descriptionformat = 1;
        $scale->timemodified = time();
        $scale->id = $DB->insert_record('scale', $scale);
        //save the scale id into the config table
        set_config('courseratingscaleid', $scale->id, 'local_hub');

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2011042100, 'local', 'hub');
    }

    if ($oldversion < 2011081200) {
        //check for local_hub plugin version where featured scale weren't added
        //during the local_hub plugin installation.
        $courseratingscaleid = get_config('courseratingscaleid', 'local_hub');
        if (empty($courseratingscaleid)) {
            //create a new scale called featured
            $scale = new stdClass();
            $scale->courseid = 0;
            $admin = get_admin();
            $scale->userid = $admin->id;
            $scale->name = 'coursefeatured';
            $scale->scale = get_string('featured', 'local_hub');
            $scale->description = get_string('featureddesc', 'local_hub');
            $scale->descriptionformat = 1;
            $scale->timemodified = time();
            $scale->id = $DB->insert_record('scale', $scale);
            //save the scale id into the config table
            set_config('courseratingscaleid', $scale->id, 'local_hub');
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2011081200, 'local', 'hub');
    }

    if ($oldversion < 2011100500) {

        // Changing type of field moodleversion on table hub_site_directory to char
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('moodleversion', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'geolocation');

        // Launch change of type for field moodleversion
        $dbman->change_field_type($table, $field);

        // Changing precision of field moodleversion on table hub_site_directory to (20)
        $field = new xmldb_field('moodleversion', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'geolocation');

        // Launch change of precision for field moodleversion
        $dbman->change_field_precision($table, $field);

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2011100500, 'local', 'hub');
    }

    if ($oldversion < 2012022100) {

        // Set the new recaptcha option to true
        set_config('hubrecaptcha', 1, 'local_hub');

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2012022100, 'local', 'hub');
    }

    if ($oldversion < 2012051600) {

        // Set the extendedusernamechars option to true
        set_config('extendedusernamechars', 1);

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2012051600, 'local', 'hub');
    }

    if ($oldversion < 2013040913) {
        $messageprovider = $DB->get_record('message_providers',
            array('component' => 'local/hub', 'name' => 'coursehubmessage'));

        if (!empty($messageprovider)) {
            $messageprovider->component = 'local_hub';
            $DB->update_record('message_providers', $messageprovider);
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2013040913, 'local', 'hub');
    }

    if ($oldversion < 2013040914) {
        $processors = $DB->get_records('message_processors');
        foreach($processors as $processor) {
            // update permitted
            $processorconfig = $DB->get_record('config_plugins', array('plugin' => 'message',
                'name' => $processor->name.'_provider_local/hub_coursehubmessage_permitted'));
            if (!empty($processorconfig)) {
                $processorconfig->name = $processor->name.'_provider_local_hub_coursehubmessage_permitted';
                $DB->update_record('config_plugins', $processorconfig);
            }

            // loggedin
            $processorconfig = $DB->get_record('config_plugins', array('plugin' => 'message',
                'name' => $processor->name.'_provider_local/hub_coursehubmessage_loggedin'));
            if (!empty($processorconfig)) {
                $processorconfig->name = $processor->name.'_provider_local_hub_coursehubmessage_loggedin';
                $DB->update_record('config_plugins', $processorconfig);
            }

            //loggedoff
            $processorconfig = $DB->get_record('config_plugins', array('plugin' => 'message',
                'name' => $processor->name.'_provider_local/hub_coursehubmessage_loggedoff'));
            if (!empty($processorconfig)) {
                $processorconfig->name = $processor->name.'_provider_local_hub_coursehubmessage_loggedoff';
                $DB->update_record('config_plugins', $processorconfig);
            }
        }

        // hub savepoint reached
        upgrade_plugin_savepoint(true, 2013040914, 'local', 'hub');
    }

     if ($oldversion < 2013050614) {

        // Define field badges to be added to hub_site_directory.
        $table = new xmldb_table('hub_site_directory');
        $badgesfield = new xmldb_field('badges', XMLDB_TYPE_INTEGER, '10', null, null, null, '-1', 'publicationmax');

        // Conditionally launch add field badges.
        if (!$dbman->field_exists($table, $badgesfield)) {
            $dbman->add_field($table, $badgesfield);
        }

        $issuedbadgesfield = new xmldb_field('issuedbadges', XMLDB_TYPE_INTEGER, '10', null, null, null, '-1', 'badges');

        // Conditionally launch add field issuedbadges.
        if (!$dbman->field_exists($table, $issuedbadgesfield)) {
            $dbman->add_field($table, $issuedbadgesfield);
        }

        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2013050614, 'local', 'hub');
    }

    if ($oldversion < 2014031201) {
        // Here we're adding link checker fields. These were originally in the registry table on moodle.org and also a copy elsewheres. Now this data is just on 2 server.
        // (1) the hub here (2) synced to moodle.org for display/stats/etc

        // Define field unreachable to be added to hub_site_directory.
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('unreachable', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'issuedbadges');

        // Conditionally launch add field unreachable.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timeunreachable', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'unreachable');

        // Conditionally launch add field timeunreachable.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('score', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timeunreachable');

        // Conditionally launch add field score.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('errormsg', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'score');

        // Conditionally launch add field errormsg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timelinkchecked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'errormsg');

        // Conditionally launch add field timelinkchecked.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('serverstring', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'timelinkchecked');

        // Conditionally launch add field serverstring.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('override', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'serverstring');

        // Conditionally launch add field override.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('fingerprint', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'override');

        // Conditionally launch add field fingerprint.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2014031201, 'local', 'hub');
    }

    if ($oldversion < 2014031202) {

        // Changing type of field serverstring on table hub_site_directory to text. (some server strings are really long -> avoids failed DB sql)
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('serverstring', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timelinkchecked');

        // Launch change of type for field serverstring.
        $dbman->change_field_type($table, $field);

        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2014031202, 'local', 'hub');
    }

    if ($oldversion < 2014041000) {

        // Changing precision of field ip on table hub_site_directory to (45).
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('ip', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'moodlerelease');

        // Launch change of precision for field ip.
        $dbman->change_field_precision($table, $field);

        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2014041000, 'local', 'hub');
    }

    if ($oldversion < 2014041700) {

        // Define index idxurl (not unique) to be added to hub_site_directory.
        $table = new xmldb_table('hub_site_directory');
        $index = new xmldb_index('idxurl', XMLDB_INDEX_NOTUNIQUE, array('url'));

        // Conditionally launch add index idxurl.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2014041700, 'local', 'hub');
    }

    if ($oldversion < 2014063000) {

        // Define field cool to be added to hub_site_directory.
        $table = new xmldb_table('hub_site_directory');
        $field = new xmldb_field('cool', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'issuedbadges');

        // Conditionally launch add field cool.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('cooldate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'cool');

        // Conditionally launch add field cooldate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Hub savepoint reached.
        upgrade_plugin_savepoint(true, 2014063000, 'local', 'hub');
    }

    if ($oldversion < 2016071400) {
        // Fix the missing email address in accounts representing registered
        // sites to prevent the "user not fully set up" error.
        $users = $DB->get_records('user', ['auth' => 'webservice', 'email' => ''], null, 'id,username');

        foreach ($users as $user) {
            $DB->set_field('user', 'email', sha1($user->username).'@example.com', ['id' => $user->id]);
        }

        upgrade_plugin_savepoint(true, 2016071400, 'local', 'hub');
    }

    if ($oldversion < 2017060600) {

        // Add new mobile related information.
        $table = new xmldb_table('hub_site_directory');
        $newfield = new xmldb_field('mobileservicesenabled', XMLDB_TYPE_INTEGER, '1', null, null, null, '-1', 'cooldate');

        // Conditionally launch add new field.
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        $newfield = new xmldb_field('mobilenotificacionsenabled', XMLDB_TYPE_INTEGER, '1', null, null, null, '-1', 'mobileservicesenabled');

        // Conditionally launch add new field.
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        $newfield = new xmldb_field('registereduserdevices', XMLDB_TYPE_INTEGER, '10', null, null, null, '-1', 'mobilenotificacionsenabled');

        // Conditionally launch add new field.
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        $newfield = new xmldb_field('registeredactiveuserdevices', XMLDB_TYPE_INTEGER, '10', null, null, null, '-1', 'registereduserdevices');

        // Conditionally launch add new field.
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        upgrade_plugin_savepoint(true, 2017060600, 'local', 'hub');
    }

    return true;
}
