<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Populate the Particular helpful Moodlers cohort
 *
 * @package     local_moodleorg
 * @subpackage  cli
 * @copyright   2012 Dan Poltawski <dan@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');


mtrace("Generating Particularly Helpful Moodlers for all langs..");
$phms = local_moodleorg_get_phms();
mtrace("Done generating PHMs");

mtrace("Populating phm cohort..");
$cohortmanager = new local_moodleorg_phm_cohort_manager();

foreach ($phms as $userid => $phmdetails) {
    $cohortmanager->add_member($userid);
}
mtrace("Done Populating phm cohort.");
