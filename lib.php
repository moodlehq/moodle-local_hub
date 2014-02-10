<?php

function local_moodleorg_cron() {
    global $DB;
    include ('locallib.php');

    //update registry table from moodle.net
    $token = '4fde6b68a062e616d39a6ba4b97bd5b8';
    $moodleneturl = 'http://moodle.net';
    $fromid = (int)$DB->get_field_sql('SELECT MAX(hubid) from {registry}');
    $newsites = local_moodleorg_get_moodlenet_stats($token, $moodleneturl, (int)$fromid, 100); //small for testing.

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