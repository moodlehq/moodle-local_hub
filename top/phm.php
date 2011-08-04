<?php 

/// Calculate Using Moodle PHMs

require('../../../config.php');
require_once(dirname(__FILE__).'/phmlib.php');

$courseid = 5;           // Using Moodle course
$groupid = 1;            // Using Moodle PHM group id
$scaleids = array(-88);  // Using Moodle scale ids

$days = 60;
$minposts = 1;
$minratings = 14;
$minraters = 8;
$minratio = 0.02;   //  Ratings / posts

$savechanges = true;   // Change the group members?

phm_calculate_users($courseid, $groupid, $scaleids, $days, $minposts, $minratings, $minraters, $minratio, $savechanges);
