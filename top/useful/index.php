<?php  //  Martin Dougiamas   MDLSITE-550
require('../../../../config.php');
require($CFG->dirroot.'/local/moodleorg/locallib.php');

$PAGE->set_url(new moodle_url('/useful/'));
if (!$mapping = local_moodleorg_get_mapping()) {
    throw new moodle_exception('mapping not found..');
}

$PAGE->set_course($DB->get_record('course', array('id'=> $mapping->courseid), '*', MUST_EXIST));
$PAGE->set_title(get_string('recentlyratedposts', 'local_moodleorg'));
$PAGE->set_heading($PAGE->title);

$useful = new frontpage_column_useful($mapping);

echo $OUTPUT->header();
echo $useful->get_full_content();
echo $OUTPUT->footer();
