<?php

require('../../../../config.php');

$records_to_show = 150;

$COUNTRIES = get_string_manager()->get_list_of_countries(false);

// We cache the recent users page to avoid DOS attacks here
$timenow = time();
if (empty($SESSION->userpics) || ($SESSION->userpicstime + 300 < $timenow)) {
    if (!$users = $DB->get_records("user", array("picture"=>"1"), "lastaccess DESC", "id,firstname,lastname,lastaccess,country", 0, $records_to_show)) {
        error("no users!");
    }
    $SESSION->userpics = $users;
    $SESSION->userpicstime = $timenow;
} else {
    $users = $SESSION->userpics;
}

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'."\n";
echo '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
echo '<channel>'."\n";

$count = 0;

foreach ($users as $user) {
    $message = "$user->firstname from ".$COUNTRIES[$user->country];

    echo '<item>'."\n";
    echo ' <title>'.$message.'</title>'."\n";
    echo ' <link>'."$CFG->wwwroot/user/pix.php/$user->id/f1.jpg".'</link>'."\n";
    echo ' <media:thumbnail url="'."$CFG->wwwroot/user/pix.php/$user->id/f1.jpg".'"/>'."\n";
    echo ' <media:content url="'."$CFG->wwwroot/user/pix.php/$user->id/f1.jpg".'"/>'."\n";
    echo '</item>'."\n\n";
}

echo '</channel>'."\n";
echo '</rss>'."\n";
