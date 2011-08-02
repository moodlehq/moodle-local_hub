<?php 

/// Calculate Moodle en Español Developers

include_once('config.php');
include_once('devellib.php');

$courseid = 11;          // Moodle en Español course
$groupid = 188;          // Moodle en Español Developers group id

$savechanges = true;     // Change the group members?

devel_calculate_users($courseid, $groupid, $savechanges);

?>
