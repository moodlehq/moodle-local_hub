<?php 

/// Calculate Moodle en Español PHMs

require('../../../config.php');
require_once(dirname(__FILE__).'/phmlib.php');

$courseid = 20;               // Moodle en Francais course
$groupid = 195;               // Moodle en Francais PHM group id
$scaleids = array(-84, -96);  // Moodle en Francais scales

$days = 60;
$minposts = 1;
$minratings = 14;
$minraters = 8;
$minratio = 0.02;   //  Ratings / posts

$savechanges = true;   // Change the group members?

phm_calculate_users($courseid, $groupid, $scaleids, $days, $minposts, $minratings, $minraters, $minratio, $savechanges);
