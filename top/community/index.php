<?php

    require('../../../../config.php');
    require_once('../toplib.php');


    $data->usercount = $DB->count_records('user', array('deleted' => 0));
    $data->langcount = 78;
    $data->countrycount = $DB->count_records_sql('SELECT count(DISTINCT country) FROM {registry} WHERE unreachable = 0');

    $strings = array('<div class="moodletop intro communityintro">'.get_string('communityintro', 'moodle.org', $data).'</div>',
                     'supportforumstitle',
                     'supportforums',
                     'http://moodle.org/support/forums/|supportforumslistname',
                     'eventstitle',
                     'events',
                     'http://moodle.org/calendar/view.php|newscalendar',
                     'http://moodle.org/course/view.php?id=33|conferencecenter',
                     'registeredsitestitle',
                     'registeredsites',
                     'http://moodle.org/sites/|registeredsitestitle',
                     'connectedsitestitle',
                     'connectedsites',
                     'http://moodle.org/network/|connectedsitestitle',
                     'jobstitle',
                     'jobs',
                     'http://moodle.org/mod/data/view.php?id=7232|jobstitle',
                     'recentparttitle',
                     'recentpart',
                     'http://moodle.org/userpics/|recentparttitle',
                     'donationstitle',
                     'donations',
                     'http://moodle.org/donations/|donationstitle',
                     'shoptitle',
                     'shop',
                     'http://www.cafepress.com/moodle/|shoptitle',
                     );

    print_moodle_page('community', $strings);
