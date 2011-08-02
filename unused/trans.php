<?php 

/// Calculate Using Moodle Developers

include_once('config.php');
include_once('translib.php');

$courseid = 5;           // Using Moodle course
$groupid = 173;          // Using Moodle Translators group id

$savechanges = true;     // Change the group members?

trans_calculate_users($courseid, $groupid, $savechanges);

?>
