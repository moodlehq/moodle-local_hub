<?php  //  Martin Dougiamas   MDLSITE-550

    require('../../../../config.php');

    $meta = '<link rel="alternate" type="application/rss+xml" ';
    $meta .= 'title ="Recently rated Moodle posts" href="http://moodle.org/useful/rss.xml" />';

    $navlinks = array();
    $navlinks[] = array('name' => 'Using Moodle', 'link' => "http://moodle.org/course/view.php?id=5", 'type' => 'misc');
    $navlinks[] = array('name' => 'Recently rated posts', 'link' => "", 'type' => 'misc');

    $PAGE->set_url(new moodle_url('/useful/'));
    $lang = isset($SESSION->lang) ? $SESSION->lang : 'en';

    if (!$mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang))) {
        //FIXME: hack, hack, hack.
        $lang = 'en';
        $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang));
    }

    $PAGE->set_course($DB->get_record('course', array('id'=> $mapping->courseid ), '*', MUST_EXIST));
    //TODO FIXME
    $PAGE->set_title('Recently rated posts');
    $PAGE->set_heading($PAGE->title);

    echo $OUTPUT->header();

    echo '<div style="text-align:right;" class="rsslink">';
    //TODO: fix this link:
    echo '<a href="http://moodle.org/useful/rss.xml">';
    echo 'RSS feed <img src="http://moodle.org/pix/i/rss.gif" title="RSS" alt="RSS"/>';
    echo '</a>';
    echo '</div>';

    $OUTPUT->heading('Posts recently rated as "Useful" in <a href="http://moodle.org/course/view.php?id=5">Using Moodle</a>');

    include($CFG->cachedir.'/moodleorg/useful/content-'.$lang.'.html');

    echo $OUTPUT->footer();
