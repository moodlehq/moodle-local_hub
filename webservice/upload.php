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
 * this is a temporary file to manage upload till file upload design is done (most probably ws)
 * no time spend on identified the right course ID (we will probably need a new course secret string and
 * a new db field, or maybe return the real id during metadata record)
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot.'/lib/hublib.php'); //SCREENSHOT_FILE_TYPE and BACKUP_FILE_TYPE


$filename  = optional_param('filename', '', PARAM_ALPHANUMEXT);
$courseshortname = optional_param('courseshortname', '', PARAM_ALPHANUMEXT);
$token = optional_param('token', '', PARAM_ALPHANUM);
$filetype = optional_param('filetype', '', PARAM_ALPHA); //can be screenshots, backup, ...

// check the communication token
$hub = new local_hub();
$communication = $hub->get_communication(WSSERVER, REGISTEREDSITE, '', $token);
if (!empty($communication) and get_config('local_hub', 'hubenabled')) {
    // (course unique ref not used)
    $sql = 'SELECT *
            FROM {hub_site_directory} as sd, {hub_course_directory} as cd
            WHERE sd.id = cd.siteid AND cd.shortname = :courseshortname
                  AND sd.url = :siteurl';
    $params = array('siteurl' => $communication->remoteurl, 'courseshortname' => $courseshortname);
    $course = $DB->get_record_sql($sql, $params);
    if (!empty($_FILES)) {
        switch ($filetype) {
            case BACKUP_FILE_TYPE:
                $hub->add_backup($_FILES['file'], $filename, $course);
            break;
            case SCREENSHOT_FILE_TYPE:
                $hub->add_screenshot($_FILES['file'], $filename, $course);
            break;
        }
    }
}

