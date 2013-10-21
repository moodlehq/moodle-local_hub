<?php

require('../../../../config.php');

$params['cx'] = optional_param('cx',  '', PARAM_RAW);
$params['cof'] = optional_param('cof',  '', PARAM_RAW);
$params['ie'] = optional_param('ie',  '', PARAM_RAW);
$params['q'] = optional_param('q',  '', PARAM_RAW);

redirect(new moodle_url('/public/search/', $params)); // nothing to see here