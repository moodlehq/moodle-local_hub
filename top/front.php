<?php defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');

$mapping = local_moodle_get_mapping();
if ($mapping) {
    $news = new frontpage_column_news($mapping);
    $useful = new frontpage_column_useful($mapping);
    $events = new frontpage_column_events($mapping);
    $resources = new frontpage_column_resources($mapping);

    echo html_writer::start_tag('div', array('class' => 'row-fluid'));

    echo html_writer::start_tag('div', array('class' => 'span3'));
    echo $OUTPUT->heading('Announcements');
    echo $news->output();
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'span3'));
    echo $OUTPUT->heading('Forum Posts');
    echo $useful->output();
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'span3'));
    echo $OUTPUT->heading('Events');
    echo $events->output();
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'span3'));
    echo $OUTPUT->heading('Recent resources');
    echo $resources->output();
    echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');
} else {
    echo 'No language mapping found :-(';
}
