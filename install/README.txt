<?php
die(); //We do not want anybody to read this via web, right?
?>


==verify config.php contains==
$CFG->logguests = false;
$CFG->extramemorylimit = "1G";
$CFG->cronclionly = true;
date_default_timezone_set('Australia/Perth'); // or something appropriate


==How to set up moodle.org web site==
* local/phpmyadmin
* mod/cvsadmin
* make /stats/cache and /stats/xml writable
* run the http://moodle.org/local/moodleorg/fake_install.php or insert the plugin record into the config_plugin table directly



==TODO==
* what does error500 do?
* you may need some new apm or eaccelerator script, add it to local/moodleorg/
* cron shoudl run only from CLI, there shoudl not be necessary to use the .htaccess
* maybe we could put the htaccess to the local/moodleorg/mainhtaccess and symlink it from the root - this would
  allow us to put the moodleorg local plugin into a separate git repository
* David should move the 'moodle.org' lang pack to 'local_moodleorg' in AMOS, then we need to search/replace all places
* I did not want to touch donations, sites, stats, and registration dirs - it looks like a fragile legacy code


/// Moodle.org hack, include alternate front page here
    include('local/moodleorg/top/front.php');
