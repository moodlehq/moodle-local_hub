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
 * @copyright   2014 Aparup Banerjee
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodleorg\task;

defined('MOODLE_INTERNAL') || die();

class sync_sites extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncregistrationstask', 'local_moodleorg');
    }
    /**
     * Execute the web service call to sync changes
     */
    function execute() {
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

        mtrace('Processing new 1.x registration data sync of sites to hub..');
        $newdatasince = $DB->get_records_sql('SELECT * FROM {registry} WHERE timelastsynced = 0 OR timelastsynced < timemodified LIMIT 2');
        print_r($newdatasince);
        mtrace('Processing new 1.x registration data sync to '. $moodleneturl. ' for '. count($newdatasince). ' updated/new sites.');
        // just send 1.9 registration data to moodle.net (receivein reply the confirmation time of successful sync)
        $syncresult = local_moodleorg_send_moodlenet_stats_19_sites($token, $moodleneturl, $newdatasince ); // returns timesynced, reghubidmap 
    print_r($syncresult);  
        // update the above synced records with newsynctime (not returned/populated if failed.)
    //    $DB->update_record_raw('UPDATE {registry} SET timelastsynced = '. clean_param($newtimesynced, PARAM_INT)); //clean externally received data.
    //    if ($newtimesynced <= 0) {
    //        error_log('error with local_moodleorg_cron: local_moodleorg_send_moodlenet_stats_19_sites() - sending data $mintimelastsynced='. $mintimelastsynced );
    //    }
    }
}