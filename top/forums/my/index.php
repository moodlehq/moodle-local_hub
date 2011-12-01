<?php

    require('../../../../../config.php');
    require_once($CFG->dirroot.'/local/moodleorg/top/toplib.php');
    require_once($CFG->dirroot.'/course/lib.php');

    require_login();

    if (!$courses  = enrol_get_my_courses('id, summary', 'visible DESC, fullname DESC')) {
        redirect('http://moodle.org/forums/', get_string('noenrolledcoursesyet', 'local_moodleorg'), 0);
    }

    unset($SESSION->mycoursemenu);  // reset the cache


    $navlinks = array();
    $navlinks[] = array('name' => get_string('nameforums', 'local_moodleorg'), 'link' => 'http://moodle.org/forums/', 'type' => 'misc');
    $strings = array('mycoursesintro');
    print_moodle_page_top('mycourses', $strings, $navlinks);

    echo '<ul class="unlist">';
    foreach ($courses as $course) {
        if ($course->visible == 1 || has_capability('moodle/course:viewhiddencourses',$course->context)) {
            echo '<li>';
            print_course($course);
            //echo '<div class="moodletop link">';
            //echo '<span class="arrow sep">►</span>';
            //echo '<a href="http://moodle.org/course/view.php?id='.$course->id.'">'.$course->fullname.'</a>';
            //echo '</div>';
            echo "</li>\n";
        }
    }
    echo "</ul>\n";

    echo '<div class="moodletop link">';
    echo '<span class="arrow sep">►</span>';
    echo '<a href="http://moodle.org/course/">'.get_string('supportforumslistname', 'local_moodleorg').'</a>';
    echo '</div>';

    print_moodle_page_bottom('mycourses');

