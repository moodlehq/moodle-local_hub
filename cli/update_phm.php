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
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(array('help' => null, 'verbose' => null), array('h' => 'help', 'v' => 'verbose'));

if ($options['help']) {
    mtrace("
This script is looking for new particularly helpful moodlers among the authors
of posts in forums. The forum must use one of mapped scales for rating in order
to be searched.

Execute with --verbose to get detailed output for debugging
");
    exit(1);
}

cli_separator();
mtrace(date('Y-m-d H:i', time()));

if ($options['verbose']) {
    mtrace("Generating the list of particularly helpful moodlers ...");
}

$phms = local_moodleorg_get_phms(array('verbose' => $options['verbose']));

if ($options['verbose']) {
    mtrace("Updating the PHM cohort members ...");
}

$cohortmanager = new local_moodleorg_phm_cohort_manager();

foreach ($phms as $userid => $phmdetails) {
    $cohortmanager->add_member($userid);
}

$oldmembers = $cohortmanager->old_users();
$newmembers = $cohortmanager->new_users();

mtrace(sprintf("Removing %d old members %s", count($oldmembers), implode(',', array_keys($oldmembers))));
mtrace(sprintf("Adding %d new members %s", count($newmembers), implode(',', array_keys($newmembers))));

$cohortmanager->remove_old_users();
