<?php
// nagios wrapper for moodle version check
// Jordan Tomkinson <jordan@moodle.com>

define('MOODLE_INTERNAL', true);

define('MATURITY_ALPHA',    50);    // internals can be tested using white box techniques
define('MATURITY_BETA',     100);   // feature complete, ready for preview and testing
define('MATURITY_RC',       150);   // tested, will be released unless there are fatal bugs
define('MATURITY_STABLE',   200);   // ready for production deployment

require_once("version.php");

$nagiosip = "174.120.103.202";

if ($_SERVER['REMOTE_ADDR'] === $nagiosip) {
  echo "Moodle ".$release;
}
