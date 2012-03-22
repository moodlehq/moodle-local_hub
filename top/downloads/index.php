<?php

    require('../../../../config.php');
    require_once('../toplib.php');

    $strings = array('downloadintro',
                     'downloadcoretitle',
                     'downloadcore',
                     'http://download.moodle.org/|downloadcoretitle',
                     'downloadviagit',
                     'downloadlangtitle',
                     'downloadlang',
                     'http://download.moodle.org/lang16/|downloadlangtitle');

    print_moodle_page('downloads', $strings);
