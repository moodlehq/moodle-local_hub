<?php

require('../../../../config.php');

require_login();

redirect($CFG->wwwroot.'/network/');
