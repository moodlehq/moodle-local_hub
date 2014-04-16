<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * On this page administrator can change site settings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/admin/forms.php');

admin_externalpage_setup('hubcoursesettings');

$id = optional_param('id', 0, PARAM_INT);
$hub = new local_hub();
$course = $hub->get_course($id, MUST_EXIST);

//define nav bar
$PAGE->set_url('/local/hub/admin/coursesettings.php', array('id' => $id));
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('hub', 'local_hub'));
$PAGE->navbar->add(get_string('managecourses', 'local_hub'),
        new moodle_url('/local/hub/admin/managecourses.php',
                array('courseid' => $course->id, 'sesskey' => sesskey())));
$PAGE->navbar->add(get_string('coursesettings', 'local_hub'),
        new moodle_url('/local/hub/admin/coursesettings.php', array('id' => $id)));


$coursesettingsform = new hub_course_settings_form('',
        array('id' => $id));
$fromform = $coursesettingsform->get_data();

//Save settings and redirect to search site page
if (!empty($fromform) and confirm_sesskey()) {

    //update the course values
    $course->fullname = $fromform->fullname;
    $course->description = $fromform->description;
    $course->language = $fromform->language;
    if (isset($fromform->courseurl)) {
        $course->courseurl = $fromform->courseurl;
    } else {
        $course->demourl = $fromform->demourl;
    }
    $course->publishername = $fromform->publishername;
    $course->publisheremail = $fromform->publisheremail;
    $course->creatorname = $fromform->creatorname;
    $course->contributornames = $fromform->contributornames;
    $course->coverage = $fromform->coverage;
    $course->licenceshortname = $fromform->licence;
    $course->subject = $fromform->subject;
    $course->audience = $fromform->audience;
    $course->educationallevel = $fromform->educationallevel;
    $course->creatornotes = $fromform->creatornotes['text'];
    $course->creatornotesformat = $fromform->creatornotes['format'];
    $course->privacy = empty($fromform->visible)?0:$fromform->visible;

    //delete screenshots that are not needed anymore
    for ($screenshotnumber = 1; $screenshotnumber <= $course->screenshots; $screenshotnumber++) {
        if(!isset($fromform->{'screenshot_' . $screenshotnumber})) {
            if ($hub->screenshot_exists($course->id, $screenshotnumber)) {
                $hub->delete_screenshot($course->id, $screenshotnumber);
            }
        }
    }

    //sanitize course screenshots
    $screenshottotal = $hub->sanitize_screenshots($course->id);

    //save the new screenshots and update the course screenshots value
    if (!empty($fromform->addscreenshots)) {
        $screenshots = $fromform->addscreenshots;
        $fs = get_file_storage();
        $ctx = context_user::instance($USER->id);
        $files = $fs->get_area_files( $ctx->id, 'user', 'draft', $screenshots);
        if (!empty($files)) {
            $level1 = floor($course->id / 1000) * 1000;
            $directory = "hub/$level1/$course->id";
            foreach ($files as $file) {
                if ($file->is_valid_image()) {
                    $screenshottotal = $screenshottotal + 1;
                    if ($screenshottotal <= MAXSCREENSHOTSNUMBER) {
                        $pathname = $CFG->dataroot . '/' . $directory . '/screenshot_' . $course->id . "_" . $screenshottotal;
                        $file->copy_content_to($pathname);
                    } else {
                        throw new moodle_exception('trytoaddtoomanyscreenshots', 'local_hub');
                    }
                }
            }
        }
    }
    $course->screenshots = $screenshottotal;

    //update the course in the DB
    $hub->update_course($course);

    //redirect to the search form
    redirect(new moodle_url('/local/hub/admin/managecourses.php',
            array('coursesettings' => $course->fullname,
                'sesskey' => sesskey(), 'courseid' => $course->id)));
}

//OUTPUT
echo $OUTPUT->header();
$coursesettingsform->display();
echo $OUTPUT->footer();

