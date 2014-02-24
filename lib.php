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
    $fromid = (int)$DB->get_field_sql('SELECT MAX(hubid) from {registry}');
    $newsites = local_moodleorg_get_moodlenet_stats($token, $moodleneturl, (int)$fromid, 1000);

    // attempt to insert fetched data into registry now.
    foreach ($newsites as $site) {
//        $site->confirmed = 1; //this is linkcheckers job really.
        if (isset($site->hubid)) {
            $DB->insert_record('registry', $site, false, true);
        } else {
            error_log('error with local_moodleorg_cron: local_moodleorg_get_moodlenet_stats()');
        }
    }
}