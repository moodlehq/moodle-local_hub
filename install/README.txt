==verify config.php contains==
$CFG->logguests = false;
$CFG->extramemorylimit = "1G";
$CFG->cronclionly = true;
date_default_timezone_set('Australia/Perth'); // or something appropriate
$CFG->customfrontpageinclude = 'local/moodleorg/top/front.php';


==install plugins from git repos==
cd local/moodleorg/install
./install.sh


==run pre-upgrade script - otherwise you get db table already exists during upgrade==
cd local/moodleorg/install
php fake_install.php


==Missing bits==
* mod/cvsadmin



==TODO==
* verify site registration from older Moodle versions works
* what does error500 do?
* you may need some new apm or eaccelerator script, add it to local/moodleorg/cli (you can copy the basics rom fake_install.php)
