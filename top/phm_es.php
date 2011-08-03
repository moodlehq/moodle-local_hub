<?php 

/// Calculate Moodle en Español PHMs

require('../../../config.php');
require_once('phmlib.php');

$courseid = 11;               // Moodle en Español course
$groupid = 186;               // Moodle en Español PHM group id
$scaleids = array(-82, -92);  // Moodle en Español scales

$days = 60;
$minposts = 1;
$minratings = 14;
$minraters = 8;
$minratio = 0.02;   //  Ratings / posts

$savechanges = true;   // Change the group members?

phm_calculate_users($courseid, $groupid, $scaleids, $days, $minposts, $minratings, $minraters, $minratio, $savechanges);
