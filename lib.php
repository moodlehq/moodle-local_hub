<?php

// This file is part of Moodle - http://moodle.org/
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
 * @package     local_moodleorg
 * @copyright   2011 Sam Hemelryk
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute the plugin's crob tasks
 */
function local_moodleorg_cron() {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/local/moodleorg/locallib.php');

    //update registry table from moodle.net
    $token = '4fde6b68a062e616d39a6ba4b97bd5b8';
    $moodleneturl = 'http://moodle.net';

    // allow override from $CFG for token and moodleneturl (for testing: next.* sites)
    // note : avoided a whole settings page etc - this is just here, for one next.* so its ok special knowledge of the devs!
    if (isset($CFG->moodleneturl)) { //if in config.php
        $moodleneturl = $CFG->moodleneturl;
    }
    if (isset($CFG->moodlenettoken)) { //if in config.php
        $token = $CFG->moodlenettoken;
    }

    //update old data then later get new data.
    $fromtime = (int)$DB->get_field_sql('SELECT MAX(timelinkchecked) from {registry}');
    $updatedsites = local_moodleorg_get_moodlenet_stats($token, $moodleneturl, 0, $fromtime, 10000);
    mtrace('Processing updates for '. count($updatedsites). ' sites from '. $moodleneturl);
    // attempt to insert fetched data into registry now.
    foreach ($updatedsites as $site) {
        $dbsite = $DB->get_record('registry', array('hubid' => $site->hubid) );
        if (isset($dbsite->hubid)) {
            foreach (get_object_vars($site) as $field=>$val) {
                $dbsite->$field = $site->$field;
            }
            $DB->update_record('registry', $dbsite, true);
        } else {
            error_log('error with local_moodleorg_cron: local_moodleorg_get_moodlenet_stats() - (updating site) This is weird, theres no hubid with a site called: '. $site->sitename);
        }
    }

    $fromid = (int)$DB->get_field_sql('SELECT MAX(hubid) from {registry}');
    $newsites = local_moodleorg_get_moodlenet_stats($token, $moodleneturl, (int)$fromid, 0, 1000);
    mtrace('Processing new registrations for '. count($newsites). ' sites from '. $moodleneturl);

    // attempt to insert fetched data into registry now.
    foreach ($newsites as $site) {
//        $site->confirmed = 1; //this is linkcheckers job really.
        if (isset($site->hubid)) {
            $DB->insert_record('registry', $site, false, true);
        } else {
            error_log('error with local_moodleorg_cron: local_moodleorg_get_moodlenet_stats() - (new site) This is weird, theres no hubid with a site called: '. $site->sitename);
        }
    }
}