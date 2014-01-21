<?php

/**
 * Display security announcements
 */

require(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot .'/mod/forum/lib.php');

$PAGE->set_url(new moodle_url('/security/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('securitytitle', 'local_moodleorg'));
$PAGE->set_heading($PAGE->title);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add($PAGE->heading);

$forum = $DB->get_record('forum', array('id' => 996), '*', MUST_EXIST);
$numarticles = 10;
$CFG->forum_longpost = 320000;

echo $OUTPUT->header();
echo $OUTPUT->heading($forum->name);

if (!empty($USER->id)) {
    forum_set_return();

    if (forum_is_subscribed($USER->id, $forum)) {
        $subtext = get_string('unsubscribe', 'forum');
    } else {
        $subtext = get_string('subscribe', 'forum');
    }

    echo html_writer::div(
        html_writer::link(new moodle_url('/mod/forum/subscribe.php', array('id' => $forum->id)), $subtext),
        'subscribelink');
}

forum_print_latest_discussions($SITE, $forum, $numarticles, 'plain', 'p.modified DESC');

echo $OUTPUT->footer();
