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
if (isguestuser()) { //guest can't send messages
    redirect(get_login_url());
}
$id = optional_param('id', 0, PARAM_INT);
$admin = optional_param('admin', 0, PARAM_INT); //access from admin page

$PAGE->set_context(get_system_context());
$PAGE->set_url('/local/hub/sendmessage.php', array('id' => $id));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('frontpage');

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

$hub = new local_hub();
if (!empty($id)) {
    $hubcourse = $hub->get_course($id);
    $publishername = $hubcourse->publishername;
} else {
    $publishername = '';
    $hubcourse->fullname = $SITE->fullname;
}
$coursename = $hubcourse->fullname;
$sendmessageform = new send_message_form('', array('id' => $id, 'admin' => $admin,
        'publishername' => $publishername, 'coursename' => $coursename));

//Cancel operation => redirect to the index/admin course page
$cancel = optional_param('cancel', null, PARAM_ALPHA);
if (!empty($cancel)) {
    if ($admin) {
        redirect(new moodle_url('/local/hub/admin/managecourses.php',
                array('sesskey' => sesskey(), 'courseid' => $id)));
    } else {
        redirect(new moodle_url('/', array('courseid' => $id)));
    }
}

//Send email operation => redirect to the index/admin course page
$data = $sendmessageform->get_data();
if (!empty($data) and confirm_sesskey()) {

    //create feedback
    $feedback = new stdClass();
    $feedback->courseid = $id;
    $feedback->type = $data->type;
    $feedback->text = $data->message;

    //force the user email adress to be displayed:
    //the publisher doesn't have to be a hub user so the email address is his only way to contact back the sender
    //and hub administrator can see all email addresses.
    $fromuser = $USER;
    $fromuser->maildisplay = true;

    switch ($data->sentto) {
        case 'publisher':
            //send email to publisher (message API does not support email as recipient)
            //TODO: if the email exist in user database, send message to the user using message API
            //      => create new kind of message for it: $eventdata->name = 'messageforpublisher';
            $sentouser = new stdClass();
            $sentouser->maildisplay = true;
            $sentouser->email = $hubcourse->publisheremail;
            $sentouser->firstname = $hubcourse->publishername;
            $sentouser->lastname = '';
            $hubcourse->hubname = $SITE->fullname;
            $hubcourse->sitename = $hub->get_site($hubcourse->siteid)->name;
            $hubcourse->huburl = new moodle_url('/');
            $hubcourse->huburl = $hubcourse->huburl->out();
            $hubcourse->hubcourseurl = new moodle_url('/', array('courseid' => $hubcourse->id));
            $hubcourse->hubcourseurl = $hubcourse->hubcourseurl->out();
            $hubcourse->userurl = new moodle_url('/message/index.php', array('id' => $fromuser->id));
            $hubcourse->userurl = $hubcourse->userurl->out();
            $hubcourse->userfullname = $fromuser->firstname . ' ' . $fromuser->lastname;
            $hubcourse->message = $data->message;
            $feedback->senttoemail = $sentouser->email;
            email_to_user($sentouser, $fromuser, get_string('msgforcoursetitle', 'local_hub', $hubcourse->fullname),
                    get_string('msgforcourse', 'local_hub', $hubcourse));
            break;
        case 'hub':
            //send message by message API
            $courseurl = new moodle_url('/local/hub/admin/managecourses.php', array('courseid' => $id));
            $courselink = html_writer::tag('a', $hubcourse->fullname, array('href' => $courseurl));
            $eventdata = new stdClass();
            $eventdata->component = 'local_hub';
            $eventdata->name = 'coursehubmessage';
            $eventdata->userfrom = $fromuser;
            $eventdata->userto = get_admin();
            $eventdata->subject = get_string('msgforcoursetitle', 'local_hub', $hubcourse->fullname);
            $eventdata->fullmessage = $data->message . ' ' . $courseurl;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = $data->message . html_writer::empty_tag('br') . $courselink;
            $eventdata->smallmessage = get_string('msgforcoursetitle', 'local_hub', $courselink)
                    . ': ' .$data->message;
            $eventdata->timecreated = time();
            $feedback->senttouserid = $eventdata->userto->id;
            message_send($eventdata);
            break;
        default:
            break;
    }

    //add feedback
    $hub->add_feedback($feedback);

    //redirect either to the courses manage page either to the front page
    if ($admin) {
        redirect(new moodle_url('/local/hub/admin/managecourses.php',
                array('sesskey' => sesskey(), 'messagesent' => 1, 'courseid' => $id)));
    } else {
        redirect(new moodle_url('/', array('messagesent' => 1, 'courseid' => $id)));
    }
}

//OUTPUT the contact form
echo $OUTPUT->header();
echo $sendmessageform->display();
echo $OUTPUT->footer();
