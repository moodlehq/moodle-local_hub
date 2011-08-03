<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/top/sites/siteslib.php');

/********************************
require_login();
if (!ismoodlesiteadmin()) {
    print_error('erroradminonly', 'moodle.org');
}
*********************************/

if (!$sites = $DB->count_records("registry")) {
    mtrace("No sites found!");
    exit;
}

$rs = $DB->get_recordset('registry');
foreach ($rs as $site) {
    if ($site->url and $site->public == 2) {
        flush();
        $newsite = NULL;
        $newsite->id = $site->id;
        if (linkcheck("$site->url")) {
            $newsite->unreachable = 0;
            $newsite->timelinkchecked = time();
        } else {
            $newsite->unreachable = 1;
            if (!$site->timelinkchecked and !$site->unreachable) {
                $site->timelinkchecked = $newsite->timelinkchecked = time();

            }
            mtrace("UNREACHABLE SINCE ".userdate($site->timelinkchecked)." - id=$site->id '$site->sitename' $site->url");
        }
        $DB->update_record("registry", $newsite);
        flush();
    }
}
$rs->close();