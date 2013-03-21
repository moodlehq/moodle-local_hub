<?php

define('CLI_SCRIPT', true);

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');


mtrace("Generating Particularly Helpful Moodlers for all langs..");
$phms = local_moodleorg_get_phms();
mtrace("Done generating PHMs");

mtrace("Populating phm cohort..");
$cohortmanager = new local_moodleorg_phm_cohort_manager();

foreach ($phms as $userid => $phmdetails) {
    $cohortmanager->add_member($userid);
}
mtrace("Done Populating phm cohort.");
