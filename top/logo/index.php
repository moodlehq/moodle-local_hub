<?php

/**
 * Provides the content of the moodle.org/logo/ page
 */

require(__DIR__.'/../../../../config.php');

$PAGE->set_url(new moodle_url('/logo/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('logotitle', 'local_moodleorg'));
$PAGE->set_heading(get_string('logotitle', 'local_moodleorg'));

$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::div(format_text(get_string('logoinfo', 'local_moodleorg'), FORMAT_MARKDOWN), 'logoinfo');
echo html_writer::empty_tag('hr');

foreach (array(
    'moodle-logo',
    'moodle-logo-grey-hat',
    'moodle-logo-white'
) as $logo) {
    echo html_writer::div(
        html_writer::empty_tag('img', array('src' => $logo.'.png', 'alt' => $logo)),
        'logo '.$logo);
}

echo $OUTPUT->footer();
