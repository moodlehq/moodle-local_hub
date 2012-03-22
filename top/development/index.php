<?php

    require('../../../../config.php');
    require_once('../toplib.php');


    $strings = array('developmentintro',
                     'developerstitle',
                     'developers',
                     'http://moodle.org/dev/|developercredits',
                     'http://moodle.org/mod/forum/view.php?id=55|generaldeveloperforum',
                     'http://docs.moodle.org/dev/Developer_meetings|developermeetings',
                     'developmentdocstitle',
                     'developmentdocs',
                     'http://docs.moodle.org/dev/Developer_documentation|developmentdocstitle',
                     'http://docs.moodle.org/dev/Roadmap|roadmap',
                     'moodletrackertitle',
                     'developmenttracker',
                     'http://tracker.moodle.org/|moodletrackertitle',
                     'sourcecodetitle',
                     'http://git.moodle.org/|browsegittitle',
                     'http://download.moodle.org/|downloadcoretitle',
                     'http://moodle.org/plugins|downloadmodulestitle',
                     );

    print_moodle_page('development', $strings);

