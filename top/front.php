<?php defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$lang = isset($SESSION->lang) ? $SESSION->lang : 'en';
if (!$mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang))) {
    //FIXME: hack, hack, hack.
    $lang = 'en';
    $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang));
}

?>
<div style="width: 100%; overflow: hidden;">
<div style="width: 25%; float: left;">
<h1>Announcements</h1>
<?php echo latest_news($SITE) ?>
</div>
<div style="width: 25%; float: left;">
<h1>Useful Posts</h1>
<?php
$cache = cache::make('local_moodleorg', 'usefulposts');

if ($content = $cache->get('frontpage_'.$lang)) {
    echo $content;
}
?>
</div>
<div style="width: 25%; float: left;">
<h1>Events</h1>
<?php
    echo latest_events($mapping->courseid);
?>
</div>
<div style="width: 25%; float: left;">
<h1>Recent Resources</h1>
<ul>
<li>One</li>
<li>Two</li>
<li>Three</li>
</ul>
</div>
</div>
<?php

function latest_events($courseid) {
    global $DB, $OUTPUT;

    // Preload course context dance..
    list ($select, $join) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
    $sql = "SELECT c.* $select
        FROM {course} c
        $join
        WHERE EXISTS (SELECT 1 FROM {event} e WHERE e.courseid = c.id)
        AND c.id = ?";
    $courses = $DB->get_records_sql($sql, array($courseid));
    foreach ($courses as $course) {
        context_helper::preload_from_record($course);
    }

    list($courses, $group, $user) = calendar_set_filters($courses);
    $events = calendar_get_upcoming($courses, $group, $user, 365, 6);

    $o = '';
    $o.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));

    // Define the base url for clendar linking..
    $baseurl = new moodle_url('/calendar/view.php', array('view' => 'day', 'course'=> $courseid));
    foreach ($events as $event) {
        $ed = usergetdate($event->timestart);
        $linkurl = calendar_get_link_href($baseurl, $ed['mday'], $ed['mon'], $ed['year']);
        $linkurl->set_anchor('event_'.$event->id);

        $o.= html_writer::start_tag('li')."\n";
        $o.= html_writer::start_tag('div', array('style'=>'float: left; margin: 3px;'))."\n";
        $o.= $OUTPUT->pix_icon('i/siteevent', get_string('globalevent', 'calendar'), 'moodle', array('class'=>'iconlarge'));
        $o.= html_writer::end_tag('div')."\n";
        $o.= html_writer::start_tag('div', array('style'=>'display:block;'))."\n";
        $o.= html_writer::link($linkurl, $event->name)."<br />\n";
        $o.= html_writer::start_tag('span', array('style'=>'font-size:0.8em; color: grey;'));
        $o.= userdate($event->timestart, get_string('strftimedaydate', 'core_langconfig'));
        $o.= html_writer::end_tag('span')."\n";
        $o.= html_writer::end_tag('div')."\n";
        $o.= '<br />';
        $o.= html_writer::end_tag('li')."\n";
    }
    $o.= html_writer::end_tag('ul');
    return $o;
}

function latest_news($course) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this


    if (!$forum = forum_get_course_forum(1, 'news')) {
        return '';
    }
    $modinfo = get_fast_modinfo($course);
    if (empty($modinfo->instances['forum'][$forum->id])) {
        return '';
    }
    $cm = $modinfo->instances['forum'][$forum->id];

    $discussions = forum_get_discussions($cm, 'p.modified DESC', false, -1, 4);
    $strftimerecent = get_string('strftimerecent');
    $strmore = get_string('more', 'forum');

    $text = '';
    $text.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));
    foreach ($discussions as $discussion) {
        $text.= local_moodleorg_frontpage_li($discussion, $course);
    }
    $text.= html_writer::end_tag('ul');
    return $text;
}
