<?php

/**
 * Display site news
 */

require(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot .'/mod/forum/lib.php');

$PAGE->set_url(new moodle_url('/news/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('newstitle', 'local_moodleorg'));
$PAGE->set_heading(get_string('newstitle', 'local_moodleorg'));

$PAGE->navbar->add($PAGE->heading);

if (! $mainforum = forum_get_course_forum($SITE->id, 'news')) {
    redirect('/error');
    die();
}

$numarticles = 10;
$CFG->forum_longpost = 320000;

echo $OUTPUT->header();
echo $OUTPUT->heading($mainforum->name);

if (!empty($USER->id)) {
    forum_set_return();

    if (forum_is_subscribed($USER->id, $mainforum)) {
        $subtext = get_string('unsubscribe', 'forum');
    } else {
        $subtext = get_string('subscribe', 'forum');
    }

    echo html_writer::div(
        html_writer::link(new moodle_url('/mod/forum/subscribe.php', array('id' => $mainforum->id)), $subtext),
        'subscribelink');
}

forum_print_latest_discussions($SITE, $mainforum, $numarticles, 'plain', 'p.modified DESC');

echo $OUTPUT->footer();
