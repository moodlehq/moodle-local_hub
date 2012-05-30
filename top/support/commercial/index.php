<?php

    require('../../../../../config.php');
    require_once('../../toplib.php');

    $navlinks = array();
    $navlinks[] = array('name' => get_string('namesupport', 'local_moodleorg'), 'link' => $CFG->wwwroot.'/support/', 'type' => 'misc');

    $strings = array(
                     'moodlepartnersinfo',
                     'http://moodle.com/partners/?mode=search&sector=university|moodlepartner_university',
                     'http://moodle.com/partners/?mode=search&sector=school|moodlepartner_school',
                     'http://moodle.com/partners/?mode=search&sector=corporate|moodlepartner_corporate',
                     'http://moodle.com/partners/?mode=search&sector=other|moodlepartner_other',
                     );

    print_moodle_page('commercial', $strings, NULL, $navlinks);
