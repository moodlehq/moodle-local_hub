<?php
// nagios wrapper for moodle version check
// Jordan Tomkinson <jordan@moodle.com>

DEFINE('MOODLE_INTERNAL', true);
require_once("version.php");

$nagiosip = "174.120.103.202";

if ($_SERVER['REMOTE_ADDR'] === $nagiosip) {
  echo "Moodle ".$release;
}

?>
