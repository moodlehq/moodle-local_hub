<?php

require('../../../../config.php');

$siteid = required_param('siteid', PARAM_INT);

/* This script sends out email : requiring login helps block spam.
 * TODO : find a way to let anonymous user send out email without opening
 * the door to spam.
 */
require_login(null, false);
if (isguestuser()) {
    redirect('http://moodle.org/sites/', 'Guests cannot contact sites this way.');
}

if (!$site = $DB->get_record('hub_site_directory', array('id'=>$siteid))) {
    redirect('http://moodle.org/sites/', 'You can\'t call this script directly');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/sites/contact.php', array('siteid'=>$siteid)));
$PAGE->set_title('Contact a Moodle site');
$PAGE->set_heading($PAGE->title);
$PAGE->navbar->add('Sites', new moodle_url('/sites/'));
$PAGE->navbar->add($PAGE->title);

echo $OUTPUT->header();

$currentaddress = getremoteaddr();
$exceptions = array(
           '203.29.181.6'   // Chad Outten
);
if (in_array($currentaddress, $exceptions)) {
    $SESSION->registrycontactmessagesent = 0;
}


if (isset($SESSION->registrycontactmessagesent) && $SESSION->registrycontactmessagesent >= 3) {
    print_error('errormaxmessages', 'local_moodleorg');
}

// You'll need to build a little fake $userto object to pass to email_to_user()
// using adminname and adminuser

if (!$site) {

    echo $OUTPUT->box('The site you requested cannot be found or displayed.  Please contact the administrator if you believe this is an error.');

} else if ($site->mailme != 1) {

    /* there shouldn't be a link to this script if $site->mailme isn't on.. but just
     * in case people fool around with the siteid variable...
     */

    $error_message = 'This site doesn\'t wish to be contacted directly.';
    if ($site->public && $site->url) {
        $error_message .= 'Please <a href="'.$site->url.'">visit the site</a> : you may find the contact information there.';
    }
    echo $OUTPUT->box($error_message);
    /*
     * This is in case we let not lo
     */

} else if (($frm = data_submitted()) && confirm_sesskey()) {

    /* set up a fake user for the destination site */
    $userto->firstname = $site->adminname;
    $userto->email = $site->adminemail;

    /* following code strongly inspired by message/lib.php */
    $message = clean_text($frm->contacttext, FORMAT_PLAIN);
    $messagesubject = clean_text($frm->contactsubject, FORMAT_PLAIN);
    $messagetext = format_text_email($message, FORMAT_PLAIN) ."\n\n--\nThis message was sent to you via the contact form at http://moodle.org/sites, where you are registered as site administrator for a Moodle site ({$site->url}) who doesn't mind being contacted.  To change these settings, use the registration button in your Moodle on the Admin page.\n";

    $userfrom = clone($USER);
    $userfrom->maildisplay = true;

    if (email_to_user($userto, $userfrom, $messagesubject, $messagetext)) {
        if (isset($SESSION->registrycontactmessagesent)) {
            $SESSION->registrycontactmessagesent++;
        } else {
            $SESSION->registrycontactmessagesent = 1;
        }

    } else {
        print_error('errorsendingmail', 'local_moodleorg');
    }

    /* will have to choose the right way to end... */
    /* Do an automatic redirect after 2 seconds. */
    //redirect('/sites', 'Your message was sent, thanks.', 2);
    /* Display a close button, if we're in a popup */
    //close_window_button();
    /* Do a non-automatic redirect */
    echo $OUTPUT->box('Your message was sent via email to that site administrator.', 'generalbox', 'notice');
    echo $OUTPUT->continue_button(new moodle_url('/sites/'));

} else {
    /* print out the form */
    echo $OUTPUT->heading('Send a private email to the administrator of: '.$site->sitename);
    echo $OUTPUT->box('Your name and email address will be automatically included as the sender', 'generalbox', 'notice');
    include('contact.html');

}

echo $OUTPUT->footer();
