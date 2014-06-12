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
        // LIMIT: may exceed max_input_vars php.ini grr..
        // We've about 50 fields per record in {registry} - going by 1000 (default)) input fields we should be ok at a limit of 10 for 500
        $newdatasince = $DB->get_records_sql('SELECT * FROM {registry} WHERE timelastsynced = 0 OR timelastsynced < timemodified LIMIT 10');
        $status = 0;
        foreach ($newdatasince as $site) {
            $otpnull = md5(time(). rand(999, 9999));
            foreach (get_object_vars($site) as $prop => $val) {
            if (is_null($val) || (is_string($val) && empty($val) && $val != '0')) { //it must've been allowed in the DB. , convert old blank strings to null as well
                    $site->$prop = $otpnull;
                    $site->otpnull = $otpnull;
                }
            }
        }
        mtrace('Processing new 1.x registration data sync to '. $moodleneturl. ' for '. count($newdatasince). ' updated/new sites.');
        // just send 1.9 registration data to moodle.net (receivein reply the confirmation time of successful sync)
        $syncresult = local_moodleorg_send_moodlenet_stats_19_sites($token, $moodleneturl, $newdatasince ); // returns timesynced, reghubidmap
        if (isset($syncresult->exception)) {
            $errrec = $newdatasince[0];
            var_dump($errrec);
            $DB->update_record('registry',array('id'=>$errrec->id, 'hubid'=> null, 'timelastsynced'=>time()));
            throw new \moodle_exception('There has been an error during sync - initial record in web service call is failing.'. $syncresult->exception);
        }
        // update the above synced records with new sync time (not returned/populated if failed.)
        $syncresult->timesynced = clean_param($syncresult->timesynced, PARAM_INT);
        foreach ($syncresult->reghubidmap as $recnum => $syncrec) {
            $DB->update_record('registry',array('id'=>$syncrec->id, 'hubid'=> $syncrec->hubid, 'timelastsynced'=>$syncresult->timesynced));
            if (!$syncrec->hubid > 0) {
                mtrace('Error syncing registry table record '. $syncrec->id. '. It will be tried again when the timemodifed shows it has been updated.');
                var_dump($syncrec);
                $status = 1;
            }
        }
        if ($status || isset($syncresult->exception)) {
            throw new \moodle_exception('There has been an error during sync - the remote server has responded but there are sync records with error hubid received (or not synced). ('. $syncresult->exception.')');
        }
        // throw an exception always to indicate in scheduled tasks list (2.7) if there are any records that failed sync in the whole table. This also triggers the sync - good for a quick catch up of sync.
        $unsyncedcount = $DB->count_records_select('registry', 'timelastsynced = 0 OR timelastsynced < timemodified');
        if ( $unsyncedcount > 0) {
            throw new \moodle_exception('There are '. $unsyncedcount. ' unsynced unattempted records.');
        }
        $unsyncedcount = $DB->count_records_select('registry', 'hubid is null and timelastsynced > timemodified');
        if ( $unsyncedcount > 0) {
            throw new \moodle_exception('There are '. $unsyncedcount. ' unsynced failing records that are unchanged since last attempt.');
        }
    }
}