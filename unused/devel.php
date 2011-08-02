<?php 

/// Calculate Using Moodle Developers

include_once('config.php');
include_once('devellib.php');

$courseid = 5;           // Using Moodle course
$groupid = 172;          // Using Moodle Developers group id

$savechanges = true;     // Change the group members?

devel_calculate_users($courseid, $groupid, $savechanges);

?>
