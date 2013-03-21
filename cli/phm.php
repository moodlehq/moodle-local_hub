<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once(dirname(__FILE__).'/lib/phmlib.php');

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

mtrace("Generating Particularly Helpful Moodlers for all langs..");
phm_calculate_users();
mtrace("Done generating PHMs");
