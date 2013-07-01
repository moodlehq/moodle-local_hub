<?php

define('CLI_SCRIPT', true);

require(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/phmlib.php');

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

// hacky, hardcoded list of users to mail!
$helen = $DB->get_record('user', array('id' => 24152), '*', MUST_EXIST);    // Helen Foster
$severin = $DB->get_record('user', array('id' => 64739), '*', MUST_EXIST);  // Severin Terrier
$nicolas = $DB->get_record('user', array('id' => 6406), '*', MUST_EXIST);   // Nicolas Martignoni
$eloy = $DB->get_record('user', array('id' => 3176), '*', MUST_EXIST);      // Eloy Lafuente

/// Calculate Moodle en PHMs
$courseid = 5;           // Using Moodle course
$groupid = 1;            // Using Moodle PHM group id
$scaleids = array(-88);  // Using Moodle scale ids

phm_calculate_users(array($helen), $courseid, $groupid, $scaleids);

/// Calculate Moodle en FR PHMs
$courseid = 20;               // Moodle en Francais course
$groupid = 195;               // Moodle en Francais PHM group id
$scaleids = array(-84, -96);  // Moodle en Francais scales

phm_calculate_users(array($helen, $severin, $nicolas), $courseid, $groupid, $scaleids);

/// Calculate Moodle en Español PHMs

$courseid = 11;               // Moodle en Español course
$groupid = 186;               // Moodle en Español PHM group id
$scaleids = array(-82, -92);  // Moodle en Español scales

phm_calculate_users(array($helen, $eloy), $courseid, $groupid, $scaleids);
