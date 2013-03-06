<?php  //  Martin Dougiamas   MDLSITE-550

require('../../../../config.php');
require($CFG->dirroot.'/local/moodleorg/locallib.php');

/* TODO:
$meta = '<link rel="alternate" type="application/rss+xml" ';
$meta .= 'title ="Recently rated Moodle posts" href="http://moodle.org/useful/rss.xml" />';

$navlinks = array();
$navlinks[] = array('name' => 'Using Moodle', 'link' => "http://moodle.org/course/view.php?id=5", 'type' => 'misc');
$navlinks[] = array('name' => 'Recently rated posts', 'link' => "", 'type' => 'misc');
 */

$PAGE->set_url(new moodle_url('/useful/'));

if (!$mapping = local_moodle_get_mapping()) {
    throw new moodle_exception('mapping not found..');
}

$PAGE->set_course($DB->get_record('course', array('id'=> $mapping->courseid ), '*', MUST_EXIST));
//TODO FIXME
$PAGE->set_title('Recently rated posts');
$PAGE->set_heading($PAGE->title);

echo $OUTPUT->header();

$OUTPUT->heading('Posts recently rated as "Useful" in <a href="http://moodle.org/course/view.php?id=5">Using Moodle</a>');

$useful = new frontpage_column_useful($mapping);
print_r($useful->get_full_content());

echo $OUTPUT->footer();
