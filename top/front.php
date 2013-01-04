<?php defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

// Get the users current lang.
$userlang = isset($SESSION->lang) ? $SESSION->lang : 'en';

// We will to english, unless a mapping is found.
$lang = null;

// Get the depdencies of the users lang and see if a mapping exists
// for the current language or its parents..
$langdeps = get_string_manager()->get_language_dependencies($userlang);

// Add to english to the start of the array as get_language_dependencies() goes
// in least specific order first.
array_unshift($langdeps, 'en');

list($insql, $inparams) = $DB->get_in_or_equal($langdeps);
$sql = "SELECT lang, courseid FROM {moodleorg_useful_coursemap} WHERE lang $insql";
$mappings = $DB->get_records_sql($sql, $inparams);

$mapping = null;
while (!empty($langdeps) and empty($mapping)) {
    $thislang = array_pop($langdeps);

    if (isset($mappings[$thislang])) {
        $mapping = $mappings[$thislang];
    }
}

if ($mapping) {
?>
<div style="width: 100%; overflow: hidden;">
<div style="width: 25%; float: left;">
<h1>Announcements</h1>
<?php echo latest_news($SITE) ?>
</div>
<div style="width: 25%; float: left;">
<h1>Forum Posts</h1>
<?php
$cache = cache::make('local_moodleorg', 'usefulposts');

if ($content = $cache->get('frontpage_'.$mapping->lang)) {
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
<?php
echo recent_resources();
?>
</div>
</div>
<?php
} else {
    echo 'No language mapping found :-(';
}

function recent_resources() {
    global $OUTPUT;

    //FIXME :)
    $feed = new moodle_simplepie('http://pipes.yahoo.com/pipes/pipe.run?_id=2a7f5e44ac0ae95e1fa10bc5ee09149e&_render=rss');

    $feeditems = $feed->get_items(0, LOCAL_MOODLEORG_FRONTPAGEITEMS);

    $o = '';
    $o.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));
    foreach ($feeditems as $item) {
        $title = $item->get_title();
        if (preg_match('/^Plugins: /', $title)) {
            $image = $OUTPUT->pix_icon('icon', 'Plugins', 'mod_lti', array('style'=>'width:35px; height: 35px'));
        } else if (preg_match('/^Jobs: /', $title)) {
            $image = $OUTPUT->pix_icon('icon', 'Jobs', 'mod_feedback', array('style'=>'width:35px; height: 35px'));
        } else if (preg_match('/^Course: /', $title)) {
            $image = $OUTPUT->pix_icon('icon', 'Jobs', 'mod_imscp', array('style'=>'width:35px; height: 35px'));
        } else {
            $image = $OUTPUT->pix_icon('icon', 'Buzz', 'mod_label', array('style'=>'width:35px; height: 35px'));
        }

        $obj = new stdClass;
        $obj->image = $image;
        $obj->link = html_writer::link($item->get_link(), $item->get_title());
        $obj->smalltext = userdate($item->get_date('U'), get_string('strftimedaydate', 'core_langconfig'));
        $o.= local_moodleorg_frontpage_li($obj);
    }
    $o.= html_writer::end_tag('ul');

    return $o;
}

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
    $events = calendar_get_upcoming($courses, $group, $user, 365, LOCAL_MOODLEORG_FRONTPAGEITEMS);


    // Define the base url for clendar linking..
    $baseurl = new moodle_url('/calendar/view.php', array('view' => 'day', 'course'=> $courseid));

    $o = '';
    $o.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));
    foreach ($events as $event) {
        $ed = usergetdate($event->timestart);
        $linkurl = calendar_get_link_href($baseurl, $ed['mday'], $ed['mon'], $ed['year']);
        $linkurl->set_anchor('event_'.$event->id);

        $obj = new stdClass;
        $obj->image = $OUTPUT->pix_icon('i/siteevent', get_string('globalevent', 'calendar'), 'moodle', array('style'=>'width:35px; height: 35px;'));
        $obj->link = html_writer::link($linkurl, $event->name);
        $obj->smalltext = userdate($event->timestart, get_string('strftimedaydate', 'core_langconfig'));

        $o.= local_moodleorg_frontpage_li($obj);
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

    $discussions = forum_get_discussions($cm, 'p.modified DESC', false, -1, LOCAL_MOODLEORG_FRONTPAGEITEMS);
    $strftimerecent = get_string('strftimerecent');
    $strmore = get_string('more', 'forum');

    $text = '';
    $text.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));
    foreach ($discussions as $discussion) {
        $text.= local_moodleorg_frontpage_forumpost($discussion, $course);
    }
    $text.= html_writer::end_tag('ul');
    return $text;
}
