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
 * @package     local_moodleorg
 * @subpackage  cli
 * @copyright   2012 Dan Poltawski <dan@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require($CFG->dirroot.'/local/moodleorg/locallib.php');

$USER = guest_user();
load_all_capabilities();

$mappings = $DB->get_records('moodleorg_useful_coursemap');
foreach ($mappings as $mapping) {
    mtrace("Generating feed for {$mapping->lang} [course: {$mapping->courseid}, scale: {$mapping->scaleid}]...");
    $USER->lang = $mapping->lang;

    $news = new frontpage_column_news($mapping);
    $news->update();
    $useful = new frontpage_column_useful($mapping);
    $useful->update();
    $events = new frontpage_column_events($mapping);
    $events->update();
    $resources = new frontpage_column_resources($mapping);
    $resources->update();
    mtrace("Done.");
}
die;
