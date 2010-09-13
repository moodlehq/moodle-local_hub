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
 * User send message to the publisher on this page
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');

require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/forms.php");

require_login();

$hub = new local_hub();
$id = optional_param('id', 0, PARAM_INTEGER);
$admin = optional_param('admin', 0, PARAM_INTEGER); //access from admin page
$hubcourse = $hub->get_course($id);

$PAGE->set_url('/local/hub/sendmessage.php', array('id' => $id));
$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');
$PAGE->set_context(get_system_context());

//Spam detection (only 10 messages to publishers per day per user)
if (!is_siteadmin ()) {
    $sentmessagestotal = $DB->count_records_select('hub_course_feedbacks',
            'userid = :userid AND time > :time',
            array('userid' => $USER->id, 'time' => strtotime("-1 day")));
    if ($sentmessagestotal > 9) {
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('maxmessagesent', 'local_hub'));
        echo $OUTPUT->footer();
        exit();
    }
}

$sendmessageform = new send_message_form('', array('id' => $id, 'admin' => $admin,
        'publishername' => $hubcourse->publishername, 'coursename' => $hubcourse->fullname));

if ($data = $sendmessageform->get_data() and confirm_sesskey()) {

    //add feedback
    $feedback = new stdClass();
    $feedback->courseid = $id;
    $feedback->type = $data->type;
    $feedback->text = $data->message;
    $hub->add_feedback($feedback);
    
    //send email
    $publisher = new object;
    $publisher->email = $hubcourse->publisheremail ;
    $publisher->firstname = $hubcourse->publishername;
    $publisher->lastname = '';
    $publisher->maildisplay = true;
    $hubcourse->hubname =  $SITE->fullname;
    $hubcourse->sitename = $hub->get_site($hubcourse->siteid)->name;
    $hubcourse->huburl = new moodle_url('/');
    $hubcourse->huburl = $hubcourse->huburl->out();
    $hubcourse->hubcourseurl = new moodle_url('/', array('courseid' => $hubcourse->id));
    $hubcourse->hubcourseurl = $hubcourse->hubcourseurl->out();
    $hubcourse->userurl = new moodle_url('/message/index.php', array('id' => $USER->id));
    $hubcourse->userurl = $hubcourse->userurl->out();
    $hubcourse->userfullname = $USER->firstname . ' ' . $USER->lastname;
    $hubcourse->message =  $data->message;
    email_to_user($publisher, $USER, get_string('msgforcoursetitle', 'local_hub', $hubcourse),
            get_string('msgforcourse', 'local_hub', $hubcourse));

    //redirect either to the courses manage page either to the front page
    if ($admin) {
        redirect(new moodle_url('/local/hub/admin/managecourses.php',
                array('sesskey' => sesskey(), 'messagesent' => 1, 'courseid' => $id)));
    } else {
        redirect(new moodle_url('/', array('messagesent' => 1, 'courseid' => $id)));
    }
}

echo $OUTPUT->header();
echo $sendmessageform->display();

echo $OUTPUT->footer();