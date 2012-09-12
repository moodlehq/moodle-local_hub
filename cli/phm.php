<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once(dirname(__FILE__).'/lib/phmlib.php');

if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

mtrace("Generating Particularly Helpful Moodlers for all langs..");
$mappings = $DB->get_records('moodleorg_useful_coursemap');
foreach ($mappings as $map) {
    if (empty($map->scaleid)) {
        mtrace("{$map->lang}...SKIPPING - no scale set.");
        continue;
    }

    if (empty($map->phmgroupid)) {
        mtrace("{$map->lang}... SKIPPING - lang as no group set.");
        continue;
    }

    mtrace("{$map->lang}... generating PHM [Course: {$map->courseid} Scale: {$map->scaleid} Group: {$map->phmgroupid}]");

    $managers = array();
    if (!empty($map->coursemanagerslist)) {
        list($insql, $params) = $DB->get_in_or_equal(explode(',', $map->coursemanagerslist));
        $sql = "SELECT u.* FROM {user} u WHERE u.deleted = 0 AND u.id $insql";
        $managers = $DB->get_records_sql($sql, $params);
    }
    $result = phm_calculate_users($managers, $map->courseid, $map->phmgroupid, $map->scaleid);
    if ($result) {
        mtrace("{$map->lang}... DONE.");
    } else {
        mtrace("{$map->lang}... FAILED.");
    }
}
mtrace("Done generating PHMs");
