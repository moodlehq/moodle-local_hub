<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array('aboutintro',
                     'aboutoverviewtitle',
                     'aboutoverview',
                     'http://docs.moodle.org/en/About_Moodle|moodledocs',
                     'aboutdemositetitle',
                     'aboutdemosite',
                     'http://demo.moodle.org/|aboutdemositetitle',
                     'aboutstatisticstitle',
                     'aboutstatistics',
                     'http://moodle.org/stats|aboutstatisticstitle',
                     'http://moodle.org/sites|registeredsitestitle',
                     '<div class="aboutvideo"><object title="About Moodle" class="mediaplugin mediaplugin_youtube" type="application/x-shockwave-flash"
                    data="http://www.youtube.com/v/I4mmMeMDMic&amp;fs=1&amp;rel=0&amp;fmt=22" width="560" height="340"><param name="movie" value="http://www.youtube.com/v/WvCIv5KCbeE&amp;fs=1&amp;rel=0&amp;fmt=22" /><param name="FlashVars" value="playerMode=embedded" /><param name="wmode" value="transparent" /><param name="allowFullScreen" value="true" /></object></div>');

    print_moodle_page('about', $strings);
