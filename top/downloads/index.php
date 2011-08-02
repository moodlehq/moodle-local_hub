<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array('downloadintro',
                     'downloadcoretitle',
                     'downloadcore',
                     'http://download.moodle.org/|downloadcoretitle',
                     'downloadcvs',
                     'http://docs.moodle.org/en/CVS_for_Administrators|downloadcvstitle',
                     'downloadmactitle',
                     'downloadmac',
                     'http://download.moodle.org/macosx/|downloadmactitle',
                     'downloadwintitle',
                     'downloadwin',
                     'http://download.moodle.org/windows/|downloadwintitle',
                     'downloadmodulestitle',
                     'downloadmodules',
                     'http://moodle.org/mod/data/view.php?id=6009|downloadmodulestitle',
                     'downloadthemestitle',
                     'downloadthemes',
                     'http://moodle.org/mod/data/view.php?id=6552|downloadthemestitle',
                     'downloadlangtitle',
                     'downloadlang',
                     'http://download.moodle.org/lang16/|downloadlangtitle');

    print_moodle_page('downloads', $strings);
