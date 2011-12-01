<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array(
                     'supportforums',
                     'supportforumsenglish',
                     $CFG->wwwroot.'/course/view.php?id=5|supportforumsenglishname',
                     'supportforumslang',
                     get_string('supportforumslangurl', 'local_moodleorg').'|supportforumslangname',
                     'supportforumslist',
                     $CFG->wwwroot.'/course/|supportforumslistname'
                     );

    print_moodle_page('forums', $strings);
