<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require($CFG->dirroot.'/local/moodleorg/locallib.php');

$USER = guest_user();
load_all_capabilities();

$mappings = $DB->get_records('moodleorg_useful_coursemap');
foreach ($mappings as $mapping) {
    mtrace("Generating feed for {$mapping->lang} [course: {$mapping->courseid}, scale: {$mapping->scaleid}]...");
    $USER->lang = $mapping->lang;

    $news = new frontpage_column_news($mapping);
    $news->update();
    $useful = new frontpage_column_useful($mapping);
    $useful->update();
    $events = new frontpage_column_events($mapping);
    $events->update();
    $resources = new frontpage_column_resources($mapping);
    $resources->update();
    mtrace("Done.");
}
die;
