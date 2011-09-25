<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array('supportintro',
                     'supportdocstitle',
                     'supportdocs1',
                     'supportdocs2',
                     'http://docs.moodle.org/?lang='.current_language().'|moodledocs',
                     'http://docs.moodle.org/overview/|supportdocslang',
                     'http://docs.moodle.org/en/Category:FAQ|supportdocsfaq',
                     'supportforumstitle',
                     'supportforums',
                     'supportforumsenglish',
                     'http://moodle.org/course/view.php?id=5|supportforumsenglishname',
                     'supportforumslang',
                     get_string('supportforumslangurl', 'local_moodleorg').'|supportforumslangname',
                     'supportforumslist',
                     'http://moodle.org/course/|supportforumslistname',
                     'supportbookstitle',
                     'supportbooks',
                     'http://moodle.org/mod/data/view.php?id=7246|supportbookstitle',
                     'supportcommercialtitle',
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

    print_moodle_page('support', $strings, 1013);
