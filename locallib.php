<?php

function local_moodleorg_frontpage_li($post, $course) {
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
    $by->date = userdate($post->modified);

    $postlink = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
    $postlink->set_anchor('p'.$post->id);
    $o = '';
    $o.= html_writer::start_tag('li')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'float: left; margin: 3px;'))."\n";
    $o.= $OUTPUT->user_picture($postuser, array('courseid'=>$course->id))."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'display:block;'))."\n";
    $o.= html_writer::link($postlink, s($post->subject))."<br />\n";
    $o.= html_writer::start_tag('span', array('style'=>'font-size:0.8em; color: grey;'));
    $o.= get_string('bynameondate', 'forum', $by);
    $o.= html_writer::end_tag('span')."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= '<br />';
    $o.= html_writer::end_tag('li')."\n";
    return $o;
}
