<?php


//this file fakes installation of local plugin because the tables were already installed from local/db/install.xml in 1.9x

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

$installedversion = get_config('local_moodleorg', 'version');

if (empty($installedversion)) {
    set_config('version', '2011070100', 'local_moodleorg');
}

echo "ok\n\n";