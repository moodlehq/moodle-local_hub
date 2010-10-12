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
                array('search' => $course->fullname, 'sesskey' => sesskey())));
$PAGE->navbar->add(get_string('coursesettings', 'local_hub'),
        new moodle_url('/local/hub/admin/coursesettings.php', array('id' => $id)));


$coursesettingsform = new hub_course_settings_form('',
        array('id' => $id));
$fromform = $coursesettingsform->get_data();

//Save settings and redirect to search site page
if (!empty($fromform)) {    
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
    //setdefault is currently not supported by editor making this required field not usable MDL-20988
//    $course->creatornotes = $fromform->creatornotes;

    $hub->update_course($course);

    redirect(new moodle_url('/local/hub/admin/managecourses.php',
            array('coursesettings' => $course->fullname, 'sesskey' => sesskey(),
                'search' => $course->fullname, 'visibility' => COURSEVISIBILITY_ALL,
                'lastmodified' => 'all')));
}

//OUTPUT
echo $OUTPUT->header();
$coursesettingsform->display();
echo $OUTPUT->footer();

