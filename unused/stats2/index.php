<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Include the nessecary config file :) */

require_once("../config.php");
/** Include the stats graphlib */
require_once('./graphlib.php');
/** Include the google charts stats graphlib */
require_once('./googlecharts.php');
/** Include the XML lib so we can parse the downloads xml */
require_once($CFG->libdir.'/phpxml/xml.php');

/**
 * Can be used to set the current directory
 * @global string $GLOBALS['currentdir']
 * @name $currentdir
 */
$GLOBALS['currentdir'] = 'stats2';

/**
 * Can be used to force generation of a new chart every time
 * @global bool $GLOBALS['forcegeneration']
 * @name forcegeneration
 */
$GLOBALS['forcegeneration'] = false;

if (optional_param('forcegeneration')) {
    $forcegeneration = true;
}

$site = get_site();
$strresources = get_string("modulenameplural", "resource");

$navlinks = array();
$navlinks[] = array('name' => 'Statistics', 'link' => "/stats", 'type' => 'misc');

print_header("Moodle.org: Moodle Statistics", "moodle", build_navigation($navlinks), "", "", true, false);

$moodle = get_record('registry', 'host', 'moodle.org', '', '', '', '', 'id, users');
$moodle->users = count_records('user', 'deleted', 0);
update_record('registry', $moodle);

print_heading('Moodle Statistics');

/**
 * Display the all registered sites graph
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
$stats = get_record_sql('SELECT
    count(DISTINCT id) as count,
    sum(courses) as courses,
    sum(users) as users,
    sum(enrolments) as enrolments,
    sum(teachers) as teachers,
    sum(posts) as posts,
    sum(resources) as resources,
    sum(questions) as questions
FROM registry
WHERE
    users > 0 AND (
        (unreachable<2 OR override BETWEEN 1 AND 3)
    ) AND confirmed=1');
$largest = get_record_sql('SELECT users,courses,sitename,url,host,public FROM `'.$CFG->prefix.'registry` WHERE users=(SELECT MAX(users) FROM '.$CFG->prefix.'registry WHERE public > 0) AND public > 0 AND (timeunreachable=0 OR override BETWEEN 1 AND 3) AND  confirmed=1');

if ($largest->public == 1) {
    $largest->link = "$largest->sitename";
} else {
    $largest->link = "<a href=\"$largest->url\">$largest->sitename</a>";
}

$countrycount = get_record_sql(sprintf('SELECT 1 as id, COUNT(DISTINCT `country`) AS countrycount FROM %sregistry WHERE confirmed=1 AND (timeunreachable=0 OR override BETWEEN 1 AND 3) AND users>0', $CFG->prefix));
echo "<p class='mdl-align'><img src='".all_sites_graph()."' alt='All Moodle Registrations over time' /></p>";
echo "<p class='mdl-align' style='font-size:0.8em;'>We perform <a href=\"http://docs.moodle.org/en/Usage\">regular bulk checking of sites</a> to make sure they still exist, so occasionally you may see reductions in the count</p>";
$table = new stdClass;
$table->width ='400px';
$table->align = Array('left','right');
$table->data = Array();
$table->data[] = Array('Registered validated sites', "<a href=\"http://moodle.org/sites/\">".number_format($stats->count)."</a>");
$table->data[] = Array('Number of countries', "<a href=\"http://moodle.org/sites/\">".number_format($countrycount->countrycount)."</a>");
$table->data[] = Array('Courses', number_format($stats->courses));
$table->data[] = Array('Users', number_format($stats->users));
$table->data[] = Array('Teachers', number_format($stats->teachers));
$table->data[] = Array('Enrolments', number_format($stats->enrolments));
$table->data[] = Array('Forum posts', number_format($stats->posts));
$table->data[] = Array('Resources', number_format($stats->resources));
$table->data[] = Array('Quiz questions', number_format($stats->questions));
print_table($table);
print_simple_box_end();

/**
 * Display the new registrations graph
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
echo "<p class='mdl-align'><img src='".new_registrations_graph()."' alt='Moodle new registrations' /></p>";
print_simple_box_end();

/**
 * Display the download summary graph
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
echo "<p class='mdl-align'><img src='".download_summary_graph()."' /></p>";
print_simple_box_end();

/**
 * Display the two users graphs
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
echo "<p class='mdl-align' style='font-size:0.8em;color:#555;'><img src='sites.bar.png' alt='sites' />&nbsp;<img style='vertical-align:middle;' src='".moodle_users_per_site()."' alt='Moodle users v site comparison' /></p>";
$top10usersitesresults = get_records_sql(sprintf("SELECT id, url, sitename, public, users, courses FROM `%sregistry` WHERE confirmed=1 AND (timeunreachable=0 OR override BETWEEN 1 AND 3) AND users>0 AND public IN (1,2) ORDER BY users DESC LIMIT 10", $CFG->prefix));
print_heading('Top 10 sites by users');
$table = new stdClass;
$table->width = '800px';
$table->size = Array('360px','80px','80px','280px');
$table->head = Array('Site', 'Users', 'Courses');
$table->data = Array();
$table->align = Array('left','right','right');
foreach ($top10usersitesresults as $row) {
    $data = Array();
    if ($row->public=='2') {
        $data[] = "<a href='".htmlspecialchars($row->url)."'>".clean_text($row->sitename)."</a>";
    } else {
        $data[] = clean_text($row->sitename);
    }
    $data[] = number_format($row->users);
    $data[] = number_format($row->courses);
    $table->data[] = $data;
}
print_table($table);

$top10coursesitesresults = get_records_sql(sprintf("SELECT id, url, sitename, public, users, courses FROM `%sregistry` WHERE confirmed=1 AND (timeunreachable=0 OR override BETWEEN 1 AND 3) AND users>0 AND public IN (1,2) ORDER BY courses DESC LIMIT 10", $CFG->prefix));
print_heading('Top 10 sites by courses');
$table = new stdClass;
$table->width = '800px';
$table->size = Array('360px','80px','80px','280px');
$table->head = Array('Site', 'Users', 'Courses');
$table->data = Array();
$table->align = Array('left','right','right');
foreach ($top10coursesitesresults as $row) {
    $data = Array();
    if ($row->public=='2') {
        $data[] = "<a href='".htmlspecialchars($row->url)."'>".clean_text($row->sitename)."</a>";
    } else {
        $data[] = clean_text($row->sitename);
    }
    $data[] = number_format($row->users);
    $data[] = number_format($row->courses);
    $table->data[] = $data;
}
print_table($table);
print_simple_box_end();

/**
 * Display the moodle populationg graph
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
echo "<p class='mdl-align'><img src='".moodle_population()."' alt='Moodle Population Graph'></p>";
$lastday = time() - (24 * 3600);
$lastmonth = time() - (30 * 24 * 3600);
$usercount = count_records_select('user', "firstaccess > $lastday");
$table = new stdClass;
$table->width ='400px';
$table->align = Array('left','right');
$table->data = Array();
$table->data[] = Array('Registered users total:', number_format($moodle->users));
$table->data[] = Array('New users in the past 24 hours:', number_format($usercount));
$table->data[] = Array('Registered users accessed in past 24 hours:', number_format(count_records_select('user', "lastaccess > $lastday")));
$table->data[] = Array('Registered users accessed in past month:', number_format(count_records_select('user', "lastaccess > $lastmonth")));
print_table($table);
print_simple_box_end();

/**
 * Display the implementation map chart
 */
print_simple_box_start("center", "501", "#FFFFFF", 20);
print_heading('Moodle locations');
echo "<p class='mdl-align'><img src='".moodle_implementation_map_graph()."' alt='Moodle Registration Map' /></p>";
$table = new stdClass;
$table->width = '400px';
$table->head = Array('Country', 'Registrations');
$table->data = Array();
$table->align = Array('left','right');
$top10countries = get_records_sql(sprintf('SELECT country, COUNT(DISTINCT `id`) AS countrycount FROM %sregistry WHERE confirmed=1 AND (timeunreachable=0 OR override BETWEEN 1 AND 3) AND users>0 GROUP BY country ORDER BY CountryCount DESC LIMIT 10', $CFG->prefix));
$countrynames = get_list_of_countries();
foreach ($top10countries as $row) {
    $data = Array($countrynames[$row->country], number_format($row->countrycount));
    $table->data[] = $data;
}
//print_heading("Top 10 countries from $countrycount->countrycount total");
print_table($table);
if ($countrycount->countrycount) {
    echo "<p class='mdl-align' style='font-size:0.8em;'>Top 10 from registered sites in ".$countrycount->countrycount." countries</p>";
}
print_simple_box_end();

/**
 * Display the major and minor registrations for the past 6 months
 */
$partialminorchartexists = check_for_existing_cached_chart($CFG->dirroot.'/'.$currentdir.'/cache/partial.minor.versions.'.date('Ymd').'.png');
$fullminorchartexists = check_for_existing_cached_chart($CFG->dirroot.'/'.$currentdir.'/cache/full.minor.versions.'.date('Ymd').'.png');
if (!$partialminorchartexists || !$fullminorchartexists) {
    // This is a VERY costly query... VERRRRRRRRY costly
    // Only run this query is we NEED to generate
    $versioninfo = gather_version_information(0,6,0);
    $fullversioninfo = gather_version_information(10,0,0);
} else {
    // Generate a dummy array we won't generate the graph anyway
    $tempversion = Array();
    $tempversion['version'] = '2.0.0';
    $tempversion['major'] = '2';
    $tempversion['minor'] = '0';
    $tempversion['release'] = '0';
    $tempversion['count'] = 1;
    $versioninfo = Array($tempversion);
    $fullversioninfo = Array($tempversion);
}
print_simple_box_start("center", "501", "#FFFFFF", 20);
print_heading('Moodle versions');
echo "<p class='mdl-align'>";
echo "<img src='".minor_version_pie_graph($versioninfo, 'Registrations in the past 6 months')."' alt='Moodle versions registered in the past 6 months' />";
echo "<img src='".minor_version_pie_graph($fullversioninfo, 'All current registrations', 'full')."' alt='All Moodle versions registered' />";
echo "</p>";
print_simple_box_end();

print_footer();

?>
