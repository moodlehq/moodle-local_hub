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

/** Used to put a timestamp on cached charts */
define('STATS_CACHE_FILE_NAME_ID', date('Ymt'));
/** Used when calling {@link logical_top_point} Rounds [up] to the the first digit */
define('STATS_LOGICALTOP_FIRSTDIGIT', 10);
/** Used when calling {@link logical_top_point} Rounds [up] to the the first two digit */
define('STATS_LOGICALTOP_FIRSTTWODIGIT', 100);
/** Used when calling {@link logical_top_point} Rounds [up] to the the first three digit */
define('STATS_LOGICALTOP_FIRSTTHREEDIGIT', 1000);

/**
 * Returns an associative array of version information
 *
 * @global moodle_database $DB
 * @param int $years The number of years to look back
 * @param int $months The number of months to look back
 * @param int $days The number of days to look back
 * @return array
 */
function gather_version_information($years=1, $months=0, $days=0) {
    global $CFG, $DB;
    $start = microtime(true);
    $fromtime = mktime(0,0,0,date('m')-$months, date('d')-$days, date('Y')-$years);
    
    $sql = 'SELECT moodlerelease, COUNT(DISTINCT id) releasecount FROM {registry} WHERE confirmed=1 AND timecreated>? AND users>0 GROUP BY moodlerelease';
    $resultingversions = $DB->get_records_sql($sql, array($fromtime));
    if (!is_array($resultingversions)) {
        return false;
    }
    $versions = Array();
    foreach ($resultingversions as $row) {
        if (preg_match('#(\d{1,2})\.(\d{1,2})(\.(\d{1,2}))?#', $row->moodlerelease, $matches)) {
            $fullversion = $matches[0];
            if (array_key_exists($fullversion, $versions)) {
                $versions[$fullversion]['count'] += $row->releasecount;
            } else {
                $version = Array();
                $version['version'] = $fullversion;
                $version['major'] = $matches[1];
                if (array_key_exists(2, $matches)) {
                    $version['minor'] = $matches[2];
                } else {
                    $version['minor'] = 0;
                }
                if (array_key_exists(3, $matches)) {
                    $version['release'] = $matches[4];
                } else {
                    $version['release'] = 0;
                }
                $version['count'] = $row->releasecount;
                $versions[$fullversion] = $version;
            }
        }
    }
    ksort($versions);
    return $versions;
}

/**
 * Produces a download summary bar graph from the xml file in ./xml/
 *
 * Uses the phpxml/xml.php class to parse the XML
 *
 * @global object
 * @global string
 * @uses STATS_CACHE_FILE_NAME_ID
 */
function download_summary_graph() {
    global $CFG;

    $graph = new google_charts_bar_graph();
    $graph->set_chart_title("Downloads per month");
    $graph->set_bar_limit(0);
    $graph->use_second_xlabel();
    $filename = 'download.summary.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $file = $CFG->dirroot.'/'.STATS_DIR.'/xml/alldownloads_cumu.xml';
    if (!file_exists($file)) {
        return '/'.STATS_DIR.'/cache/'.$graph->find_latest_cached_graph();
    }
    $rawxml = file_get_contents($file);
    $xml = @XML_unserialize($rawxml);
    
    $labelcount = count($xml['chart']['chart_data']['row'][0]['string']);
    $datacount = count($xml['chart']['chart_data']['row'][1]['number']);
    for ($i=0;$i<$datacount;$i++) {
        $value = $xml['chart']['chart_data']['row'][1]['number'][$i];
        $label = $xml['chart']['chart_data']['row'][0]['string'][$i];
        $label2 = '';
        if (preg_match('/^(\w).*(\d{4})/s', $label, $matches)) {
            $label = $matches[1];
            $label2 = $matches[2];
        }
        $graph->add_value(Array($value, $label, $label2));
    }
    return $graph;
}

/**
 * Returns a pie graph showing percentages of minor version downloads
 *
 * @uses STATS_CACHE_FILE_NAME_ID
 * @param array $versions A copy of the {@link gather_version_information} array
 * @return google_charts_pie_graph The graph object that can be echoed out into
 *                                  the src attribute
 */
function minor_version_pie_graph($versions, $title, $filenameprefix='partial') {
    $minorversions = Array();
    foreach ($versions as $version) {
        $minorversionstr = $version['major'].'.'.$version['minor'];
        if (array_key_exists($minorversionstr, $minorversions)) {
            $minorversions[$minorversionstr] += $version['count'];
        } else {
            $minorversions[$minorversionstr] = $version['count'];
        }
    }

    $graph = new google_charts_pie_graph();
    $graph->set_chart_title($title);
    
    $graph->add_colour('F68E00');
    $graph->add_colour('FFEAB3');

    $filename = $filenameprefix.'.minor.versions.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    foreach ($minorversions as $version=>$count) {
        $graph->add_value($count, $version.'.x');
    }
    return $graph;
}


/**
 * Return a graph object for new registrations
 *
 * This function generates a {@link google_charts_bar_graph} object that can be
 * used to then display a graph.
 *
 * Use this function in the following way
 * <code>
 * $graph = new_registrations_graph();
 * echo "<p><img src='$graph' alt='Unable to display the graph sorry' /></p>
 * </code>
 *
 * The graph is printable by the __toString method so you are able to use it
 * directly in an output statement sych as echo.
 *
 * @see google_charts_bar_graph
 * @global object
 * @uses GOOGLE_CHARTS_BAR_VERTICAL_GROUPED
 * @return google_charts_bar_graph
 */
function new_registrations_graph() {
    global $CFG, $DB;

    $graph = new google_charts_bar_graph();
    $graph->set_style(GOOGLE_CHARTS_BAR_VERTICAL_GROUPED);
    $graph->set_chart_title("New registrations per month");
    $graph->set_bar_limit(36);
    $graph->use_second_xlabel();
    $filename = 'new_registrations.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $sql = "SELECT 
                r.monthdate,
                r.month,
                r.year,
                COUNT(r.id) AS count
            FROM (
                SELECT
                    id,
                    FROM_UNIXTIME(timecreated, '%b %Y') AS monthdate,
                    FROM_UNIXTIME(timecreated, '%Y%m') AS dateorder,
                    FROM_UNIXTIME(timecreated, '%b') AS month,
                    FROM_UNIXTIME(timecreated, '%Y') AS year
                FROM {registry}
                WHERE timecreated!=0 AND confirmed=1 AND users>0 AND (timeunreachable=0 OR override BETWEEN 1 AND 3)
            ) r
            GROUP BY r.monthdate
            ORDER BY r.dateorder ASC";
    $monthresults = $DB->get_records_sql($sql);
    foreach ($monthresults as $row) {
        if ($row->month==date('M') && $row->year==date('Y')) continue;
        $graph->add_value(array($row->count, $row->month, $row->year));
    }
    return $graph;
}

/**
 * Returns a graph object for known registered sites
 *
 * This function returns a {@link google_charts_bar_graph} object that can be used
 * to display a graph of all known registered sites using the google chart API
 *
 * Use this function in the following way
 * <code>
 * $graph = all_sites_graph();
 * echo "<p><img src='$graph' alt='Unable to display the graph sorry' /></p>
 * </code>
 *
 * The graph is printable by the __toString method so you are able to use it
 * directly in an output statement sych as echo.
 *
 * @see google_charts_bar_graph
 * @global object
 * @return google_charts_bar_graph
 */
function all_sites_graph() {
    global $CFG, $DB;
    
    $graph = new google_charts_bar_graph();
    $graph->set_chart_title("Total known sites");
    $graph->add_legend('Total registrations');
    $graph->add_legend('New registrations');
    $graph->set_bar_limit(0);
    $graph->use_second_xlabel();
    $graph->use_second_xvalue();
    $filename = 'all_sites_graph.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $sql = "SELECT
                r.orderfield,
                r.month,
                r.year,
                r.created,
                IFNULL(`registry_lost`.unreachable,0) as unreachable
            FROM (
                SELECT
                    COUNT(`timecreated`) AS created,
                    FROM_UNIXTIME(timecreated, '%Y%m') AS orderfield,
                    FROM_UNIXTIME(timecreated, '%b') AS month,
                    FROM_UNIXTIME(timecreated, '%Y') AS year
                FROM {registry}
                WHERE timecreated!=0 AND confirmed=1 AND users > 0
                GROUP BY orderfield
            ) r
            LEFT JOIN (
                SELECT
                    COUNT(`timeunreachable`) AS unreachable,
                    FROM_UNIXTIME(timeunreachable, '%Y%m') AS orderfield
                FROM `".$CFG->prefix."registry`
                WHERE timecreated!=0 AND (timeunreachable!=0 OR override NOT BETWEEN 1 AND 3) AND users >0 AND confirmed=1 AND unreachable > 1
                GROUP BY orderfield
            ) as registry_lost ON registry_lost.orderfield=r.orderfield
            ORDER BY r.orderfield";
    $monthresults = $DB->get_records_sql($sql);
    $thismonth = date('F Y');
    $totalcreatedsofar = 0;
    $totalremovedsofar = 0;
    foreach ($monthresults as $row) {
        if ($row->month==date('M') && $row->year==date('Y')) continue;
	$totalcreatedsofar += (int)$row->created;
        $totalremovedsofar += (int)$row->unreachable;
        $totaltoshow = ($totalcreatedsofar-$totalremovedsofar) - $row->created;
        $graph->add_value(array($totaltoshow, $row->created, substr($row->month,0,1), $row->year));
    }
    return $graph;
}

/**
 * Returns a map graph object for the implementations by country
 *
 * This function returns a {@link google_charts_map_graph} object that can be used
 * to display a world map with countries highlighted by the number of registered
 * implementations we know about.
 *
 * Use this function in the following way
 * <code>
 * $graph = moodle_implementation_map_graph();
 * echo "<p><img src='$graph' alt='Unable to display the graph sorry' /></p>
 * </code>
 *
 * The graph is printable by the __toString method so you are able to use it
 * directly in an output statement sych as echo.
 *
 * @global object
 * @global array
 * @return google_charts_map_graph
 */
function moodle_implementation_map_graph() {
    global $CFG, $DB;

    $graph = new google_charts_map_graph();
    $graph->set_chart_title("Total known sites");
    $filename = 'implementations_map_graph.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $sql = 'SELECT 
                country, 
                COUNT(DISTINCT `id`) AS countrycount
            FROM {registry}
            WHERE confirmed=1 AND users>0 AND (timeunreachable=0 OR override BETWEEN 1 AND 3)
            GROUP BY country 
            ORDER BY CountryCount DESC';
    $countryresults = $DB->get_records_sql($sql);
    $count = 0;
    while($count<20) {
        $country=array_shift($countryresults);
        $graph->add_value($country->country, 100-($count*5));
        $count++;
    }
    foreach ($countryresults as $country) {
        $graph->add_value($country->country, 5);
    }
    
    return $graph;
}

/**
 * Returns a graph object for the number of users per site
 *
 * This function returns a {@link google_charts_line_graph} graph object that can be used
 * to display a graph grouping the number of the users per site.
 * You are able specify a start and end number of users to limit the graph to a
 * reasonable size range for usefull display.
 *
 * Use this function in the following way
 * <code>
 * $graph = number_of_users_to_site_size("Large implementations", 10000, 1000000, 5);
 * echo "<p><img src='$graph' alt='Unable to display the graph sorry' /></p>
 * </code>
 *
 * The graph is printable by the __toString method so you are able to use it
 * directly in an output statement sych as echo.
 *
 * @global object
 * @uses GOOGLE_CHARTS_LEGEND_HBOTTOM
 * @param string $title The title to print on the graph
 * @param int $start The lower user limit, sites with less users that this will
 *                  not be factored in
 * @param int $end The upper user limit, sites with more users than this will not
 *                  be factored in
 * @param int $rounder Controls the size of the groups by rounding backwards by
 *                  the passed number
 * @return google_charts_line_graph
 */
function number_of_users_to_site_size($title, $start=0, $end=1000000, $rounder=4) {
    global $CFG;
    
    $graph = new google_charts_bar_graph();
    $graph->set_chart_title($title);
    $graph->set_style(GOOGLE_CHARTS_BAR_VERTICAL_GROUPED);
    $graph->set_bar_limit(24);
    if ($start>=10000) $graph->set_x_label_interval(4);
    $graph->set_dimensions(400,375);
    $graph->use_second_xlabel();
    $filename = 'user_size_'.(string)$start.'_'.(string)$end.'.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $sql = 'SELECT 
                users,
                COUNT(DISTINCT id) as sitecount
            FROM (
                SELECT id, ROUND(users, -?) users FROM {registry} WHERE users>? AND users<=? AND confirmed=1 AND (timeunreachable=0 OR override IN (1 AND 3))
            ) AS registry GROUP BY users ORDER BY users';
    $sitesizeresults = $DB->get_records_sql($sql, array($rounder, $start, $end));
    $xlabel = true;
    $secondxlabelpos = ceil(count($sitesizeresults)/2);
    $count = 0;
    foreach ($sitesizeresults as $row) {
        $count++;
        if ($secondxlabelpos==$count) {
            $graph->add_value(array($row->sitecount, number_format($row->users), 'Users'));
        } else {
            $graph->add_value(array($row->sitecount, number_format($row->users)));
        }
    }
    return $graph;
}


/**
 * Returns a graph object for the moodle population showing growth
 *
 * This function returns a {@link google_charts_bar_graph} object that can be used to
 * display a graph of the number of moodle.org users as it grows month by month.
 *
 * Use this function in the following way
 * <code>
 * $graph = number_of_users_to_site_size("Large implementations", 10000, 1000000, 5);
 * echo "<p><img src='$graph' alt='Unable to display the graph sorry' /></p>
 * </code>
 *
 * The graph is printable by the __toString method so you are able to use it
 * directly in an output statement sych as echo.
 *
 * @global object
 * @return google_charts_bar_graph
 */
function moodle_population() {
    global $CFG, $DB;

    $graph = new google_charts_bar_graph();
    $graph->set_chart_title("Moodle.org population");
    $graph->add_legend('Total moodle.org users');
    $graph->add_legend('New moodle.org users');
    $graph->set_bar_limit(80);
    $graph->use_second_xlabel();
    $graph->use_second_xvalue();
    $filename = 'moodle_population.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }

    $sql = "SELECT
                dateorder,
                month,
                year,
                COUNT(id) count
            FROM (
                SELECT
                    id,
                    FROM_UNIXTIME(firstaccess, '%Y%m') dateorder,
                    FROM_UNIXTIME(firstaccess, '%b') month,
                    FROM_UNIXTIME(firstaccess, '%Y') year
                FROM {user}
                WHERE firstaccess!=0
                ) user
            GROUP BY user.dateorder
            ORDER BY dateorder ASC";
    $monthresults = $DB->get_records_sql($sql);

    $totalsofar = 0;
    foreach ($monthresults as $row) {
        if ($row->month==date('M') && $row->year==date('Y')) continue;
        $totaltoshow = $totalsofar + (int)$row->count;
        $graph->add_value(array($totaltoshow, $row->count, substr($row->month,0,1), $row->year));
        $totalsofar += (int)$row->count;
    }
    return $graph;
}

function moodle_users_per_site() {
    global $CFG, $DB;

    $graph = new google_charts_bar_graph();
    $graph->set_chart_title("Moodle users per site comparison");
    $graph->set_style(GOOGLE_CHARTS_BAR_VERTICAL_GROUPED);
    $graph->set_dimensions(800,375);
    $graph->use_second_xlabel();
    $filename = 'users_per_site.'.STATS_CACHE_FILE_NAME_ID.'.png';
    if (!$graph->set_filename($filename)) {
        // There is an error setting the filename probably that we were unable
        // to create the stats directory in moodledata
    }
    if ($graph->check_if_graph_exists()) {
        return $graph;
    }


    $range = array();
    $range[] = array('start'=>0, 'end'=>9);
    $range[] = array('start'=>10, 'end'=>99);
    $range[] = array('start'=>100, 'end'=>499);
    $range[] = array('start'=>500, 'end'=>999);
    $range[] = array('start'=>1000, 'end'=>4999);
    $range[] = array('start'=>5000, 'end'=>9999);
    $range[] = array('start'=>10000, 'end'=>19999);
    $range[] = array('start'=>20000, 'end'=>29999);
    $range[] = array('start'=>30000, 'end'=>39999);
    $range[] = array('start'=>40000, 'end'=>49999);
    $range[] = array('start'=>50000, 'end'=>99999);
    $range[] = array('start'=>100000, 'end'=>199999);
    $range[] = array('start'=>200000, 'end'=>299999);
    $range[] = array('start'=>300000, 'end'=>399999);
    $range[] = array('start'=>400000, 'end'=>499999);
    $range[] = array('start'=>500000, 'end'=>999999);
    $range[] = array('start'=>1000000, 'end'=>1999999);

    $graph->set_bar_limit(count($range)+1);

    $sql = 'SELECT COUNT(id) sitecount FROM {registry} r WHERE r.users>? AND r.users<=? AND (r.unreachable < 2 OR r.override IN (1, 2, 3)) AND r.confirmed=1';

    $secondxlabelpos = ceil(count($range)/2);
    $count = 0;

    $xlabelcount = count($range);
    $graph->overridelabelposition[] = 0;
    $graph->overridelabelposition[] = 0;
    foreach ($range as $key=>$group) {
        $sitesizeresults = $DB->get_record_sql($sql, array($group['start'], $group['end']));
        $count++;
        $label = number_format($group['start']);
        $graph->overridelabelposition[] = round((100/$xlabelcount)*($key+1), 1);
        if ($key == count($range)-1) {
            $label = array($label, number_format($group['end']+1));
            #$graph->overridelabelposition[] = 100;
        }
        if ($secondxlabelpos==$count) {
            $graph->add_value(array($sitesizeresults->sitecount, $label, 'Users'));
        } else {
            $graph->add_value(array($sitesizeresults->sitecount, $label));
        }
    }
    return $graph;
}

/**
 * This function localises a number and returns the formatted string
 *
 * @param int|float|string $number The number to format
 * @param int $numdec The number of decimals to format to
 * @return string The formatted number
 */
function localisenumber($number, $numdec = 2) {
    $decimalsep = get_string('decsep');
    $thousandssep = get_string('thousandssep');

    if (!is_numeric($number)) {
        return $number;
    }

    if ((float)$number == (int)$number) { /// Integer number, no decimals
        return (string)number_format($number, 0, $decimalsep, $thousandssep);
    } else { /// Float number, apply decimals
        return (string)number_format($number, $numdec, $decimalsep, $thousandssep);
    }
}

/**
 * Calculates the highest logical point to display on the graph
 *
 * This function calculates the highest logical point to display on the graph
 * by rounding the given [top] value up to the its next highest point by the
 * precision argument (defaults to 10)
 *
 * STATS_LOGICALTOP_FIRSTDIGIT or 10 rounds up so that the first digit of the 
 * value are used and all other digits are zero'd
 * e.g. 67867 becomes 70000
 *      485324 becomes 500000
 * 
 * STATS_LOGICALTOP_FIRSTTWODIGIT or 100 rounds to the first two digits
 * e.g. 67867 becomes 68000
 *      485324 becomes 490000
 * 
 * STATS_LOGICALTOP_FIRSTTHREEDIGIT or 1000 rounds to the first three digits
 * e.g. 67867 becomes 67900
 *      485324 becomes 486000
 *
 * @uses STATS_LOGICALTOP_FIRSTDIGIT
 * @uses STATS_LOGICALTOP_FIRSTTWODIGIT
 * @uses STATS_LOGICALTOP_FIRSTTHREEDIGIT
 * @param int $value The value to round up
 * @param int $precision The precision to round to
 * @return int The rounded top int
 */
function logical_top_point($value, $precision=10) {
    if (!is_numeric($value)) return $value;
    $value = (int)$value;
    $count = 0;
    while ($value>$precision) {
        $value /= 10;
        $count++;
    }
    $value = ceil($value)*pow(10,$count);
    return $value;
}

/**
 * Checks if the requested file exists or not
 *
 * @param string $file The file to check for
 * @return bool True for success else false
 */
function check_for_existing_cached_chart($file) {
    // Check if the file exists and return bool
    $forcegeneration = optional_param('forcegeneration', false, PARAM_BOOL);
    if ($forcegeneration==true) {
        return false;
    }
    return (file_exists($file) && is_readable($file));
}

/**
 * Used to find the latest cached version of the chart to display
 *
 * Usually this will only be called should there be an error with CURL
 * such that google charts didn't return an image, or couldn't be reached!
 *
 * @param string $filepath The directory where cached images exist
 * @param string $filename The filename for the cached images
 * @return string The new filename tp request
 */
function find_latest_cached_graph($filepath, $filename) {
    // First check if it already exists, saves us time if it does
    $fileexists = check_for_existing_cached_chart($filepath.$filename);
    if ($fileexists===true) {
        return $filename;
    }
    // The cache directory doesn't exist we can't even proceed here
    if (!file_exists($filepath) || !is_dir($filepath) || !is_readable($filepath)) {
        return false;
    }
    // Open the directory for browsing
    $dir = dir($filepath);
    $possibilities = Array();
    // Build the regular expression to recognise similar files
    $regexp = preg_replace('#\.\d{6,8}\.png$#', '.(\d){6,8}.png', $filename);
    $regexp = '#'.preg_replace('#([\+\.\-\_\#])#', '\\\$1', $regexp).'#';
    // Recurse the directory
    while (false !== ($file = $dir->read())) {
       if ($file=='.'||$file=='..') continue;
       // Check if each file is a matches the regexp
       if (preg_match($regexp, $file) && is_file($filepath.$file)) {
           $possibilities[] = $file;
       }
    }
    // Sort the possibile files and then return the top one :)
    if (count($possibilities)==0) return $filename;
    rsort($possibilities);
    return $possibilities[0];
}

/**
 * This is the abstract class that Graph's should extend to ensure
 * that all graphs share similar properties and functions
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class graph {
    /** @var int Width of the image in pixels */
    protected $imagewidth = 800;
    /** @var int Height of the image in pixels */
    protected $imageheight = 375;
    /** @var sting The path to the file */
    protected $imagefilepath = '';
    /** @var bool Whether to override and existing file or not */
    protected $overrideexisting = true;
    /** @var bool True if an error has occured */
    protected $haserror = false;
    /** @var array Array of error messages */
    protected $errormessages = Array();
    /** @var bool Whether to display a chart title or not */
    protected $usecharttitle = false;

    /** @var string Title for the chart */
    protected $charttitle = 'My+Chart';
    /** @var string Hex colour to use for the title */
    protected $charttitlecolour = '333333';
    /** @var string Font size to use for the title */
    protected $charttitlefontsize = '16';
    /** @var array An array of strings to use as legends */
    protected $legends = Array();

    /** @var string The filename to same the graph as, save to cache */
    protected $filename = null;
    /** @var string The path of the directory to to save the graph into */
    protected $filepath = null;
    /** @var string The webpath to the graph so we can display it in HTML */
    protected $webpath = null;
    /** @var bool Force the graph to generate even if cached */
    protected $forcegeneration = false;

    public function __construct() {
        global $CFG;
        $this->webpath = '/'.STATS_DIR.'/cache/';
        $this->forcegeneration = optional_param('forcegeneration', false, PARAM_BOOL);;
    }

    /**
     * Used to add the values for the graph
     */
    public function add_values() {
        
    }

    public function check_if_graph_exists() {
        global $CFG;
        if ($this->forcegeneration==true) {
            return false;
        }
        if ($this->filename===null || $this->filepath===null) {
            return $this->set_error("You must set a filename both testing if a file exists");
        }
        return (file_exists($this->filepath.$this->filename) && is_readable($this->filepath.$this->filename));
    }

    public function find_latest_cached_graph() {
        if ($this->filename===null || $this->filepath===null) {
            return $this->set_error("You must set a filename both testing if a file exists");
        }
        // First check if it already exists, saves us time if it does
        $fileexists = $this->check_if_graph_exists($this->filepath.$this->filename);
        if ($fileexists===true) {
            return $this->filename;
        }
        // The cache directory doesn't exist we can't even proceed here
        if (!file_exists($this->filepath) || !is_dir($this->filepath) || !is_readable($this->filepath)) {
            return false;
        }
        // Open the directory for browsing
        $dir = dir($this->filepath);
        $possibilities = Array();
        // Build the regular expression to recognise similar files
        $regexp = preg_replace('#\.\d{6,8}\.png$#', '.(\d){6,8}.png', $this->filename);
        $regexp = '#'.preg_replace('#([\+\.\-\_\#])#', '\\\$1', $regexp).'#';
        // Recurse the directory
        while (false !== ($file = $dir->read())) {
           if ($file=='.'||$file=='..') continue;
           // Check if each file is a matches the regexp
           if (preg_match($regexp, $file) && is_file($this->filepath.$file)) {
               $possibilities[] = $file;
           }
        }
        // Sort the possibile files and then return the top one :)
        if (count($possibilities)==0) return $this->filename;
        rsort($possibilities);
        return $possibilities[0];
    }
    /**
     * Sets the filename to save as
     *
     * @global object
     * @param string $filename
     * @return bool True for success, false for error
     */
    public function set_filename($filename) {
        global $CFG;

        $this->filepath = $CFG->dirroot.$this->webpath;
        if (!file_exists($this->filepath) || !is_dir($this->filepath)) {
            $outcome = mkdir($this->filepath, $CFG->directorypermissions);
            if (!$outcome) {
                return $this->seterror("Failed to create the stats directory");
            }
        }
        $this->filename = $filename;
        return true;
    }

    /**
     * Toggle force_generation on and off
     *
     * When set on forces graphs to be re-generated even if cached
     *
     * @param bool $setting Optional defaults to true
     * @return bool Always true
     */
    public function force_generation($setting=true) {
        if ($setting) {
            $this->forcegeneration = true;
        } else {
            $this->forcegeneration = false;
        }
        return true;
    }

    /**
     * Add a legend to display on the graph
     *
     * @param string $legend The legend to display
     * @return bool True for success, false for failure
     */
    public function add_legend($legend) {
        $legend = str_replace(' ', '+', $legend);
        $this->legends[] = preg_replace('/[^a-zA-Z0-9\-\_\+\.]+/', '', $legend);
        return true;
    }
    
    /**
     * Must return a link to the image, and if not created create
     * 
     * @return string
     */
    abstract function __toString();

    /**
     * Sets the chart title to display, also sets usecharttitle to true
     *
     * @param string $title The title for the chart
     * @return bool True for success, false for failure
     */
    public function set_chart_title($title) {
        $title = str_replace(' ', '+', $title);
        $this->charttitle = preg_replace('/[^a-zA-Z0-9\-\_\+\.]+/', '', $title);
        $this->usecharttitle = true;
        return true;
    }

    /**
     * Sets the colour for the chart title
     *
     * @param string $charttitlecolour A hex colour e.g. FFCB44 (moodle orange)
     * @return bool True for success, false for failure
     */
    public function set_chart_title_colour($charttitlecolour) {
        $this->charttitlecolour = $charttitlecolour;
        return true;
    }

    /**
     * Sets the dimensions fo the graph
     *
     * @param int $width The width of the graph in pixels
     * @param int $height The height of the graph in pixels
     * @return bool True for success, false for failure
     */
    public function set_dimensions($width, $height) {
        if (!is_int($width)) $this->set_error("You must specify an integer for width");
        if (!is_int($height)) $this->set_error("You must specify an integer for height");
        if ($this->haserror) {
            return false;
        }
        $this->imagewidth = $width;
        $this->imageheight = $height;
        return true;
    }

    /**
     * Sets the object to have an error
     *
     * @param string $message
     * @return bool Always false so caller can return immediatly
     */
    private function set_error($message) {
        $this->haserror = true;
        $this->errormessages[] = $message;
        return false;
    }
}

/**
 * Class to extend for bar graphs
 * 
 * Presently designed to handle up to two grouped/stacked bars
 * but could be expanded in the future to support more
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class bar_graph extends graph {
    /** @var int The bottom y-axis-value for the graph */
    protected $ybottom = 0;
    /** @var int The bottom y-axis-value for the secondary values */
    protected $ybottomsecondary = 0;
    /** @var int The number of y axis labels to use */
    protected $ylabelsteps = 10;
    /** @var int An interval for x labels */
    protected $xinterval = 1;
    /** @var array An array of value objects */
    protected $xvalues = Array();
    /** @var string A hex colour to use for the primary value bars */
    protected $barcolour = 'ffcb44';
    /** @var string A hex colour to use for the secondary value bars */
    protected $secondbarcolour = 'f68e00';
    /** @var int Width of the bars on the graph 0=auto */
    protected $barwidth = 0;
    /** @var bool If true then display a second series of labels on the x axis */
    protected $usesecondxlabel = false;
    /** @var bool If true then display a second series of labels on the y axis */
    protected $usesecondylabel = false;
    /** @var bool If true then display a second set of values on the graph */
    protected $usesecondxvalue = false;
    /** @var bool If true then display values above the bars */
    protected $titlesonbars = false;

    /**
     * Set the number of y-label steps to use for the graph
     *
     * @param int $steps
     * @return bool
     */
    public function set_y_label_steps($steps) {
        $this->ylabelsteps = (int)$steps;
        return true;
    }

    /**
     * Sets the x-label interval for display
     */
    public function set_x_label_interval($interval) {
        $this->xinterval = (int)$interval;
    }

    /**
     * Add a value to the graph
     *
     * @param mixed $xvalue Can be either a graph_value object, an array,
     *                       or a straigh value
     * @return bool True for success false otherwise
     */
    public function add_value($xvalue) {
        if (is_object($xvalue) && get_class($xvalue)==='graph_value') {
            $this->xvalues[] = $xvalue;
            $bargraphvalue = $xvalue;
        } else if (is_array($xvalue)) {
            $value = array_shift($xvalue);
            if ($this->usesecondxvalue===true) {
                if (count($xvalue)>0) {
                    $secondvalue = array_shift($xvalue);
                } else {
                    $secondvalue = 0;
                }
            }
            $label = null;
            $secondlabel = null;
            if (count($xvalue)>0) {
                $label = array_shift($xvalue);
            }
            if (count($xvalue)>0) {
                $secondlabel = array_shift($xvalue);
            }
            $bargraphvalue = new graph_value($value, $label, $secondlabel);
            if ($this->usesecondxvalue===true) {
                $bargraphvalue->set_second_value($secondvalue);
                
            }
            $this->xvalues[] = $bargraphvalue;
        } else {
            $bargraphvalue = new graph_value($xvalue);
            $this->xvalues[] = $bargraphvalue;
        }
        return true;
    }

    /**
     * Adds an array of values to the graph
     *
     * @param array $xvalues
     * @return int The number of values successfully added
     */
    public function add_values($xvalues) {
        $count = 0;
        foreach ($xvalues as $xvalue) {
            $outcome = $this->add_value($xvalue);
            if ($outcome) $count++;
        }
        return $count;
    }

    /**
     * Tell the graph to display a second series of x-labels
     */
    public function use_second_xlabel() {
        $this->usesecondxlabel = true;
    }

    /**
     * Tell the graph to display a second series of x-values
     */
    public function use_second_xvalue() {
        $this->usesecondxvalue = true;
    }

    /**
     * Return the highest value on the graph, or if two
     * sets of values are used then the highest combined values
     *
     * @return int;
     */
    public function get_top_value() {
        $top = 0;
        $secondarytop = 0;
        foreach ($this->xvalues as $value) {
            if ($value->Value>$top) {
                $top = $value->Value;
            }
            if ($this->usesecondxvalue) {
                if ($value->secondvalue>$secondarytop) {
                    $secondarytop = $value->secondvalue;
                }
            }
        }
        return ($top+$secondarytop);
    }
}

/**
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class line_graph extends graph {
    protected $ybottom = 0;
    protected $ylabelsteps = 10;
    protected $xinterval = 1;
    protected $xvalues = Array();
    protected $barcolour = 'f68e00';
    protected $secondbarcolour = 'ffcb44';
    /** @var int Width of the bars on the graph 0=auto */
    protected $barwidth = 0;
    protected $usesecondxlabel = false;
    protected $usesecondxvalue = false;

    public function set_y_label_steps($steps) {
        $this->ylabelsteps = (int)$steps;
    }

    public function set_x_label_interval($interval) {
        $this->xinterval = (int)$interval;
    }

    public function add_value($xvalue) {
        if (is_object($xvalue) && get_class($xvalue)==='graph_value') {
            $this->xvalues[] = $xvalue;
        } else if (is_array($xvalue)) {
            $value = array_shift($xvalue);
            if ($this->usesecondxvalue===true) {
                if (count($xvalue)>0) {
                    $secondvalue = array_shift($xvalue);
                } else {
                    $secondvalue = 0;
                }
            }
            $label = null;
            $secondlabel = null;
            if (count($xvalue)>0) {
                $label = array_shift($xvalue);
            }
            if (count($xvalue)>0) {
                $secondlabel = array_shift($xvalue);
            }
            $bargraphvalue = new graph_value($value, $label, $secondlabel);
            if ($this->usesecondxvalue===true) {
                $bargraphvalue->set_second_value($secondvalue);
            }
            $this->xvalues[] = $bargraphvalue;
        } else {
            $bargraphvalue = new graph_value($xvalue);
            $this->xvalues[] = $bargraphvalue;
        }
    }

    public function add_values(array $xvalues) {
        foreach ($xvalues as $xvalue) {
            $this->add_value($xvalue);
        }
    }

    public function use_second_xlabel() {
        $this->usesecondxlabel = true;
    }

    public function use_second_xvalue() {
        $this->usesecondxvalue = true;
    }
}

/**
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class scatter_plot_graph extends graph {
    protected $points = Array();
    protected $pointcolour = 'ffcb44';
    protected $ybottom = 0;
    public function add_point($x, $y, $size = null) {
        $point = new graph_point($x, $y);
        if ($size!==null) {
            $point->set_size($size);
        }
        $this->points[] = $point;
    }
    public function x_point_array() {
        $xpoints = array();
        foreach ($this->points as $point) {
            $xpoints[] = $point->get_x();
        }
        return $xpoints;
    }
    public function y_point_array() {
        $ypoints = array();
        foreach ($this->points as $point) {
            $ypoints[] = $point->get_y();
        }
        return $ypoints;
    }
}


/**
 * Abstract Pie Graph Class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class pie_graph extends graph {
    protected $datasets = array();
    protected $coloursets = array();
    public function add_colour($colour, $dataset=1) {
        if (!array_key_exists($dataset, $this->coloursets)) {
            $this->coloursets[$dataset] = Array();
        }
        $this->coloursets[$dataset][] = $colour;
    }
    public function add_value($value, $label='null', $dataset=1, $colour=null) {
        if (!array_key_exists($dataset, $this->datasets)) {
            $this->datasets[$dataset] = Array();
        }
        $data = Array();
        $data['value'] = $value;
        $data['label'] = $label;
        $data['colour'] = $colour;
        $this->datasets[$dataset][] = $data;
    }
    /**
     * Converts the values to a series of percentages
     *
     * This should be called before building/requesting the graph to ensure
     * that you get a reasonable output
     *
     * This function also groups minimal values (less than 0.5%) into a group
     * called other to ensure that clutter is removed. Can be disabled 
     *
     */
    protected function set_percentages($grouptinyvals = true) {
        $keystounset = Array();
        $other = Array();
        foreach ($this->datasets as $datasetkey=>$dataset) {
            $max = 0;
            foreach ($dataset as $data) {
                $max += (int)$data['value'];
            }
            foreach ($dataset as $key=>$data) {
                $percentage = round(($data['value']/$max)*100,2);
                if ($percentage<0.5 && strpos($data['label'],'2.0')===false) {
                    $keystounset[] = Array('dataset'=>$datasetkey, 'key'=>$key);
                    if (array_key_exists($datasetkey, $other)) {
                        $other[$datasetkey]['value'] += $percentage;
                    } else {
                        $other[$datasetkey] = Array(
                                'value'=>$percentage,
                                'label'=>($data['label']=='')?'':'Other',
                                'datasetkey'=>$datasetkey);
                    }

                }
                if ($percentage<0.5) {
                    $percentage=0.5;
                }
                $this->datasets[$datasetkey][$key]['value'] = $percentage;
            }
        }
        if ($grouptinyvals===true && count($keystounset)>2) {
            foreach ($keystounset as $keys) {
                unset($this->datasets[$keys['dataset']][$keys['key']]);
            }
            foreach ($other as $otherset) {
                if ($otherset['value']<1.0) {
                    $otherset['value'] = 1;
                }
                $key = $otherset['datasetkey'];
                $data['value'] = $otherset['value'];
                $data['label'] = $otherset['label'];
                $data['colour'] = null;
                array_unshift($this->datasets[$key],$data);
            }
        }
        return true;
    }
}

/**
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graph_point {
    /** @var int|float The x-coordinate for this point */
    private $xcoord = 0;
    /** @var int|float The y-coordinate for this point */
    private $ycoord = 0;
    /** @var int|float The size of this point [scatter graphs only] */
    private $size = null;
    /**
     * Constructors rule
     *
     * @param int|float|string $x The x-coordinate for this point
     * @param int|float|string $y The y-coordinate for this point
     */
    public function __construct($x, $y) {
        if (is_int($x) || is_float($x)) {
            $this->xcoord = $x;
        } else if (is_string($x)) {
            if (strpos($x, '.')!==false) {
                $this->xcoord = (float)$x;
            } else {
                $this->xcoord = (int)$x;
            }
        }
        if (is_int($y) || is_float($y)) {
            $this->ycoord = $y;
        } else if (is_string($y)) {
            if (strpos($y, '.')!==false) {
                $this->ycoord = (float)$y;
            } else {
                $this->ycoord = (int)$y;
            }
        }
    }
    /**
     * Sets the size for the point
     * 
     * @param int|float $size
     */
    public function set_size($size=null) {
        if (is_int($size)) {
            $this->size = $size;
        } else if (is_string($size)) {
            $this->size = (int)$size;
        }
    }
    /**
     * Gets the x-co-ord
     * @return int|float
     */
    public function get_x() {
        return $this->xcoord;
    }
    /**
     * Gets the y-co-ord
     * @return int|float
     */
    public function get_y() {
        return $this->ycoord;
    }
}

/**
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graph_value {
    /** @var string The label for this point */
    private $label = null;
    /** @var string A secondary label for this point */
    private $secondlabel = null;
    /** @var string|int|float The value for this point */
    private $value;
    /** @var string|int|float The secondary value for this point */
    private $secondvalue = null;
    /**
     * Constructors rule
     *
     * @param mixed $value
     * @param string $label Optional
     * @param string $secondlabel Optional
     */
    public function __construct($value, $label=null, $secondlabel=null) {
        if (is_int($value) || is_float($value)) {
            $this->value = $value;
        } else if (is_string($value)) {
            if (strpos($value, '.')!==false) {
                $this->value = (float)$value;
            } else {
                $this->value = (int)$value;
            }
        }
        if ($label!==null) {
            $this->label = $label;
        }
        if ($secondlabel!==null && is_string($secondlabel)) {
            $this->secondlabel = $secondlabel;
        }
    }
    /**
     * Sets the second value for this point
     *
     * @param mixed $value
     */
    public function set_second_value($value) {
        if (is_int($value) || is_float($value)) {
            $this->secondvalue = $value;
        } else if (is_string($value)) {
            if (strpos($value, '.')!==false) {
                $this->secondvalue = (float)$value;
            } else {
                $this->secondvalue = (int)$value;
            }
        }
    }
    /**
     * Get Magic Method to get values and labels
     *
     * @param string $key The thing to get
     * @return mixed Whatever it is
     */
    public function __get($key) {
        $key = strtolower($key);
        switch ($key) {
            case 'largestvalue':
                if ($this->value===null && $this->secondvalue===null) {
                    return 0;
                } else if ($this->secondvalue===null || $this->value>$this->secondvalue) {
                    return $this->value;
                } else {
                    return $this->secondvalue;
                }
            case 'label':
                if ($this->label===null) {
                    $this->label = '';
                }
                return $this->label;
            case 'secondlabel':
                if ($this->secondlabel===null) {
                    $this->secondlabel = '';
                }
                return $this->secondlabel;
            case 'secondvalue':
                return $this->secondvalue;
            default:
                return $this->value;
        }
        if ($key=='label') {
            if ($this->label===null) {
                $this->label = '';
            }
            return $this->label;
        } else if ($key=='secondlabel') {
            if ($this->secondlabel===null) {
                $this->secondlabel = '';
            }
            return $this->secondlabel;
        } else if ($key==='secondvalue') {
            return $this->secondvalue;
        } else {
            return $this->value;
        }
    }
}

?>
