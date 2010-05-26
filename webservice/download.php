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

//this is a temporary file to manage download till file download design is done (most probably ws)

/**
 * This page display content of a course backup (if public only)
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/lib/hublib.php'); //SCREENSHOT_FILE_TYPE and BACKUP_FILE_TYPE
require_once($CFG->dirroot.'/lib/filelib.php');

$courseid = optional_param('courseid', '', PARAM_INTEGER);
$filetype = optional_param('filetype', '', PARAM_ALPHA); //can be screenshots, backup, ...

if (!empty($courseid) and !empty($filetype) and get_config('local_hub', 'hubenabled')) {
    switch ($filetype) {
        case BACKUP_FILE_TYPE:
            //check that the file is downloadable
            $course = $DB->get_record('hub_course_directory', array('id' => $courseid));
            if (!empty($course) && 
                    ($course->privacy or (!empty($USER) and is_siteadmin($USER->id)))) {

                $level1 = floor($courseid / 1000) * 1000;
                $userdir = "hub/$level1/$courseid";
                send_file($CFG->dataroot . '/' . $userdir . '/backup_'.$courseid.".zip", 'backup_'.$courseid.".zip",
                        'default', 0, false, true, '', false);
            }
            break;
    }

}

