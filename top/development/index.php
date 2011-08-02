<?php

    require('../../../../config.php');
    require_once('../toplib.php');


    $strings = array('developmentintro',
                     'developerstitle',
                     'developers',
                     'http://moodle.org/cvs|developerlist',
                     'http://docs.moodle.org/en/Credits|developercontributors',
                     'http://moodle.org/mod/forum/view.php?id=55|generaldeveloperforum',
                     'http://docs.moodle.org/en/Developer_meetings|developermeetings',
                     'developmentdocstitle',
                     'developmentdocs',
                     'http://docs.moodle.org/en/Development|developmentdocstitle',
                     'http://docs.moodle.org/en/Roadmap|roadmap',
                     'moodletrackertitle',
                     'developmenttracker',
                     'http://tracker.moodle.org/|moodletrackertitle',
                     'http://tracker.moodle.org/browse/MDL?report=com.atlassian.jira.plugin.system.project:popularissues-panel|popularissues',
                     'sourcecodetitle',
                     'http://cvs.moodle.org/moodle/|browsecvstitle',
                     'http://download.moodle.org/|downloadcoretitle',
                     'http://moodle.org/mod/data/view.php?id=6009|downloadmodulestitle',
                     );

    print_moodle_page('development', $strings);

