<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $html = get_string('events', 'local_moodleorg');

    $strings = array('events',
                     'http://moodle.org/calendar/view.php|newscalendar',
                     'http://moodle.org/course/view.php?id=33|conferencecenter',
                     '<div class="moodletop eventsimage"><img src="barcelona.jpg" alt="" /></div>');

    $navlinks = array();

    print_moodle_page('events', $strings);

