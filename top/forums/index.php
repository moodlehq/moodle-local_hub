<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array(
                     'supportforums',
                     'supportforumsenglish',
                     $CFG->wwwroot.'/course/view.php?id=5|supportforumsenglishname',
                     'supportforumslist',
                     $CFG->wwwroot.'/course/|supportforumslistname'
                     );

    print_moodle_page('forums', $strings);
