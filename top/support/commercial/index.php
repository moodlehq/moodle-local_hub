<?php

    require('../../../../../config.php');
    require_once('../../toplib.php');

    $navlinks = array();
    $navlinks[] = array('name' => get_string('namesupport', 'moodle.org'), 'link' => $CFG->wwwroot.'/support/', 'type' => 'misc');

    $strings = array(
                     'supportcommercial',
                     'http://moodle.com/hosting/|moodlehosting',
                     'http://moodle.com/support/|moodlesupport',
                     'http://moodle.com/consulting/|moodleconsulting',
                     'http://moodle.com/installation/|moodleinstallation',
                     'http://moodle.com/integration/|moodleintegrations',
                     'http://moodle.com/custom/|moodlecustomisation',
                     'http://moodle.com/courseware/|moodlecourseware',
                     'http://moodle.com/training/|moodletraining',
                     'http://moodle.com/themes/|moodlethemes',
                     'http://moodle.com/certification/|moodlecertification'
                     );

    print_moodle_page('commercial', $strings, NULL, $navlinks);
