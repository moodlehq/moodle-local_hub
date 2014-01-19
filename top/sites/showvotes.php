<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/top/sites/siteslib.php');

require_login();

if (!ismoodlesiteadmin()) {
    print_error('erroradminonly', 'local_moodleorg');
}

$siteid = required_param('id', PARAM_INT);
$site = $DB->get_record('registry', array('id' => $siteid), '*', MUST_EXIST);

$PAGE->set_pagelayout('embedded');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('moodle.org: View site votes');
$PAGE->set_heading("Votes for $site->sitename");
$PAGE->set_url(new moodle_url('/sites/showvotes.php', array('id'=>$siteid)));

echo $OUTPUT->header();
echo $OUTPUT->heading(s($site->sitename));

echo '<center>'.$site->url.'</center>';

echo '<br />';

if ($votes = $DB->get_records('registry_votes', array('siteid' =>$site->id))) {
    echo '<table width="100%" border="1">';
    foreach ($votes as $vote) {
        $user = $DB->get_record('user', array('id' => $vote->userid), '*', MUST_EXIST);
        echo '<tr><td width="100">';
        echo $OUTPUT->user_picture($user, array('courseid'=>1, 'size'=>100));
        echo '</td><td>';
        echo fullname($user, true).'<br />';
        echo $user->email.'<br />';
        echo $user->url.'<br />';
        echo '</td><td>';
        if ($vote->vote > 0) {
            echo '<img src="/pix/s/yes.gif" height="17" width="14" alt="" border="0">';
        } else if ($vote->vote < 0) {
            echo '<img src="/pix/s/no.gif" height="15" width="12" alt="" border="0">';
        } else {
            echo '0';
        }
        echo '</td></tr>';
    }
    echo '</table>';
}

echo $OUTPUT->footer();