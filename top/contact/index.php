<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    print_moodle_page_top('contact', '');

    $contactintro = array('contactintro');
    $contactcommunity = array('contactsupporttitle',
                              'contactsupport',
                              'http://moodle.org/support/|contactsupporttitle',
                              'contactmoodlecomtitle',
                              'contactmoodlecom',
                              'http://moodle.com/helpdesk/|contactmoodlecomhelpdesk',
                              'contactbugstitle',
                              'contactbugs',
                              'contactsecurity',
                              'http://tracker.moodle.org/|moodletrackertitle',
                              'contactmoodleorgtitle',
                              'contactmoodleorgemail',
                              '<div class="moodletop link"><span class="arrow sep">►</span> <a href="mailto:s%75%70po%72%74%40%6d%6f%6f%64l%65%2e%6f%72%67">support@moodle.org</a></div>'
                             );
    if (isloggedin()) {
        $contactcommunity[] = 'contactmoodleorgother';
    } else {
        $contactcommunity[] = 'contactlogin';
        $contactcommunity[ ]= 'http://moodle.org/login/|contactmoodleorglogin';
    }
    $contactsent = array('contactsent');

    /* This script sends out email : requiring login helps block spam.
     * TODO : find a way to let anonymous user send out email without opening
     * the door to spam.
     */

    if (isloggedin()) {

        if (($frm = data_submitted()) && confirm_sesskey()) {

            $userto = $DB->get_record('user', array('id' => 24152), '*', MUST_EXIST);    // helen

            $message = clean_param($frm->contacttext, PARAM_TEXT);
            $messagesubject = stripslashes($frm->contactsubject);
            $messagetext = format_text_email($message, FORMAT_PLAIN) ."\n\n--\nThis message was sent to you via the contact form at http://moodle.org/contact.\n";

            $userfrom = clone($USER);
            $userfrom->maildisplay = true;

            if (email_to_user($userto, $userfrom, $messagesubject, $messagetext)) {
                print_moodle_content($contactsent);
            } else {
                notify('There was an error while sending out the message. Please try again later.');
            }

        } else { /* print out the form */
            print_moodle_content($contactintro);
            print_moodle_content($contactcommunity);
            echo "<br /><br />";

            include('contact.html');
        }

    } else  {
        print_moodle_content($contactintro);
        print_moodle_content($contactcommunity);
    }


    print_moodle_page_bottom('contact');
