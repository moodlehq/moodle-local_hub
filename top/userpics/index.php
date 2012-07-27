<?php

require('../../../../config.php');

$PAGE->set_context(get_system_context());
$PAGE->set_url(new moodle_url('/userpics/'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Moodle: Recent participants');
$PAGE->set_heading('Recent participants');
$PAGE->add_alternate_version('gallery', new moodle_url('userpics/rss.php'), 'application/rss+xml');
$PAGE->navbar->add($PAGE->heading);

$records_to_show = 150;
$countries = get_string_manager()->get_list_of_countries();

// We cache the recent users page to avoid DOS attacks here
$timenow = time();
if (empty($SESSION->userpics) || ($SESSION->userpicstime + 300 < $timenow)) {
    $fields = user_picture::fields('', array('id','firstname','lastname','lastaccess','country'));
    $users = $DB->get_records("user", array("picture" => "1"), "lastaccess DESC", $fields, 0, $records_to_show);
    $SESSION->userpics = $users;
    $SESSION->userpicstime = $timenow;
} else {
    $users = $SESSION->userpics;
}

$isadmin = is_siteadmin();

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);


//print_heading(count($users)." users with custom pictures");

$now = time();
$string = new stdClass();
$string->day         = get_string("day");
$string->days        = get_string("days");
$string->hour        = get_string("hour");
$string->hours       = get_string("hours");
$string->min         = get_string("min");
$string->mins        = get_string("mins");
$string->sec         = get_string("sec");
$string->secs        = get_string("secs");


$time = "";
$count = 0;
echo '<div style="text-align:center">';
foreach ($users as $user) {
   //$time = format_time($now - $user->lastaccess, $string);
   $message = "$user->firstname from ".$countries[$user->country];
   echo "<acronym title=\"$message\">";
   echo $OUTPUT->user_picture($user, array('link'=>$isadmin, 'size'=>100));
   echo "</acronym>\n";
}
echo '</div>';

echo $OUTPUT->footer();
