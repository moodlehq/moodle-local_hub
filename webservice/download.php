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
require_once($CFG->dirroot . '/lib/hublib.php'); //HUB_SCREENSHOT_FILE_TYPE and HUB_BACKUP_FILE_TYPE
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/local/hub/lib.php'); //HUBLOGOIMAGEWIDTH, HUBLOGOIMAGEHEIGHT

$courseid = optional_param('courseid', '', PARAM_INTEGER);
$filetype = optional_param('filetype', '', PARAM_ALPHA); //can be screenshots, backup, ...
$screenshotnumber = optional_param('screenshotnumber', 1, PARAM_INT); //the screenshot number of this course
$imagewidth = optional_param('imagewidth', HUB_SITEIMAGEWIDTH, PARAM_ALPHANUM); //the screenshot width, can be set to 'original' to forcce original size
$imageheight = optional_param('imageheight', HUB_SITEIMAGEHEIGHT, PARAM_INT); //the screenshot height

if (!empty($courseid) and !empty($filetype) and get_config('local_hub', 'hubenabled')) {
    switch ($filetype) {
        case HUB_BACKUP_FILE_TYPE:
            //check that the file is downloadable
            $course = $DB->get_record('hub_course_directory', array('id' => $courseid));
            if (!empty($course) &&
                    ($course->privacy or (!empty($USER) and is_siteadmin($USER->id)))) {

                $level1 = floor($courseid / 1000) * 1000;
                $userdir = "hub/$level1/$courseid";
                send_file($CFG->dataroot . '/' . $userdir . '/backup_' . $courseid . ".zip", 'backup_' . $courseid . ".zip",
                        'default', 0, false, true, '', false);
            }
            break;
        case HUB_SCREENSHOT_FILE_TYPE:
            //check that the file is downloadable
            $course = $DB->get_record('hub_course_directory', array('id' => $courseid));
            if (!empty($course) &&
                    ($course->privacy or (!empty($USER) and is_siteadmin($USER->id)))) {

                $level1 = floor($courseid / 1000) * 1000;
                $userdir = "hub/$level1/$courseid";
                $filepath = $CFG->dataroot . '/' . $userdir . '/screenshot_' . $courseid . "_" . $screenshotnumber;
                $imageinfo = getimagesize($filepath, $info);

                //TODO: make a way better check the requested size
                if (($imagewidth != HUB_SITEIMAGEWIDTH and $imageheight != HUB_SITEIMAGEHEIGHT)
                        and $imagewidth != 'original') {
                    throw new moodle_exception('wrongimagesize');
                }

                //check if the screenshot exists in the requested size           
                require_once($CFG->dirroot . "/repository/flickr_public/image.php");
                if ($imagewidth == 'original') {
                    $newfilepath = $filepath . "_original"; //need to be done if ever the picture changed
                } else {
                    $newfilepath = $filepath . "_" . $imagewidth . "x" . $imageheight;
                }

                //if the date of original newer than thumbnail all recreate a thumbnail
                if (!file_exists($newfilepath) or
                        (filemtime($filepath) > filemtime($newfilepath))) {
                    $image = new moodle_image($filepath);
                    if ($imagewidth != 'original') {
                        $image->resize($imagewidth, $imageheight);
                    }
                    $image->saveas($newfilepath);
                }
                send_file($newfilepath, 'image', 'default', 0, false, true, $imageinfo['mime'], false);
            }
            break;
    }
} else {
    //always give hub logo to anybody
    if ($filetype == HUB_HUBSCREENSHOT_FILE_TYPE) {
        $userdir = "hub/0";
        $filepath = $CFG->dataroot . '/' . $userdir . '/hublogo';
        $imageinfo = getimagesize($filepath, $info);
        
        //check if the screenshot exists in the requested size
        require_once($CFG->dirroot . "/repository/flickr_public/image.php");
        $newfilepath = $filepath . "_" . HUBLOGOIMAGEWIDTH . "x" . HUBLOGOIMAGEHEIGHT;

        if (!file_exists($newfilepath) or
                (filemtime($filepath) > filemtime($newfilepath))) {
            $image = new moodle_image($filepath);
            $image->resize(HUBLOGOIMAGEWIDTH, HUBLOGOIMAGEHEIGHT);
            $image->saveas($newfilepath);
        }

        send_file($newfilepath, 'image', 'default', 0, false, true, $imageinfo['mime'], false);
    }
}

