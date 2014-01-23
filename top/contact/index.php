<?php

/**
 * Provides the content of the moodle.org/contact/ page
 */

require(__DIR__.'/../../../../config.php');

$PAGE->set_url(new moodle_url('/contact/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Page under construction');
$PAGE->set_heading($PAGE->title);

$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo $OUTPUT->footer();
