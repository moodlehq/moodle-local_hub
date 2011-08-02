<?php
require 'config.php';

moodle_setlocale('cs_CZ.UTF-8');
echo userdate(time())."<br>";
moodle_setlocale('en.UTF-8');
echo userdate(time())."<br>"; 
moodle_setlocale('en_AU.UTF-8');
echo userdate(time())."<br>"; 

?>
