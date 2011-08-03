<?php
die(); //We do not want anybody to read this via web, right?
?>


==verify config.php contains==
$CFG->logguests = false;
$CFG->extramemorylimit = "1G";
$CFG->cronclionly = true;
date_default_timezone_set('Australia/Perth'); // or something appropriate
$CFG->customfrontpageinclude = 'local/moodleorg/top/front.php';


==How to set up moodle.org web site==
* mod/cvsadmin
* make /stats/cache and /stats/xml writable
* run the http://moodle.org/local/moodleorg/fake_install.php or insert the plugin record into the config_plugin table directly



==TODO==
* what does error500 do?
* you may need some new apm or eaccelerator script, add it to local/moodleorg/
* cron shoudl run only from CLI, there shoudl not be necessary to use the .htaccess
* David should move the 'moodle.org' lang pack to 'local_moodleorg' in AMOS, then we need to search/replace all places
