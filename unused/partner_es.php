<?php 

/// Calculate Moodle en Español Partners

include_once('config.php');
include_once('partnerlib.php');

$courseid = 11;          // Moodle en Español course
$groupid = 191;          // Moodle en Español Partners group id

$savechanges = true;     // Change the group members?

partner_calculate_users($courseid, $groupid, $savechanges);

?>
