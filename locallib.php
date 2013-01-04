<?php

define('LOCAL_MOODLEORG_FRONTPAGEITEMS', '6');

function local_moodleorg_frontpage_forumpost($post, $course) {


    global $OUTPUT;

    // Build an object that represents the posting user
    $postuser = new stdClass;
    $postuser->id        = $post->userid;
    $postuser->firstname = $post->firstname;
    $postuser->lastname  = $post->lastname;
    $postuser->imagealt  = $post->imagealt;
    $postuser->picture   = $post->picture;
    $postuser->email     = $post->email;

    $by = new stdClass();
    $by->name = fullname($postuser);
    $by->date = userdate($post->modified, get_string('strftimedaydate', 'core_langconfig'));

    $postlink = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
    $postlink->set_anchor('p'.$post->id);


    $obj = new stdClass;
    $obj->image = $OUTPUT->user_picture($postuser, array('courseid'=>$course->id));
    $obj->link = html_writer::link($postlink, s($post->subject));
    $obj->smalltext = get_string('bynameondate', 'forum', $by);

    return local_moodleorg_frontpage_li($obj);
 }


function local_moodleorg_frontpage_li($obj) {
    $o = '';
    $o.= html_writer::start_tag('li')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'float: left; margin: 3px;'))."\n";
    $o.= $obj->image."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'display:block;'))."\n";
    $o.= $obj->link . "<br />\n";
    $o.= html_writer::start_tag('span', array('style'=>'font-size:0.8em; color: grey;'));
    $o.= $obj->smalltext;
    $o.= html_writer::end_tag('span')."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= '<br />';
    $o.= html_writer::end_tag('li')."\n";
    return $o;
}
