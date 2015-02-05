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

defined('MOODLE_INTERNAL') || die;

define('STATS_MAX_UNREACHABLE', 2);

/**
 *
 * @global moodle_database $DB
 */
function local_hub_stats_get_registry_stats() {
    global $DB;
    list($where, $params) = local_hub_stats_get_confirmed_sql();
    $sql = 'SELECT
                COUNT(DISTINCT r.id) registrycount,
                SUM(r.courses) courses,
                SUM(r.users) users,
                SUM(r.enrolments) enrolments,
                SUM(r.posts) posts,
                SUM(r.resources) resources,
                SUM(r.questions) questions,
                COUNT(DISTINCT r.countrycode) countrycount
            FROM {hub_site_directory} r
            WHERE '.$where;
    $stats = $DB->get_record_sql($sql, $params);

    return $stats;
}

/**
 *
 * @global moodle_database $DB
 */
function local_hub_stats_top_10_sites_by_users() {
    global $DB;
    list($where, $params) = local_hub_stats_get_confirmed_sql();
    $sql = 'SELECT r.* 
              FROM {hub_site_directory} r
             WHERE '.$where.' AND r.public IN (1, 2)
          ORDER BY r.users DESC
             LIMIT 10' ;
    return $DB->get_records_sql($sql, $params);
}

/**
 *
 * @global moodle_database $DB
 */
function local_hub_stats_top_10_sites_by_courses() {
    global $DB;
    list($where, $params) = local_hub_stats_get_confirmed_sql();
    $sql = 'SELECT r.*
              FROM {hub_site_directory} r
             WHERE '.$where.' AND r.public IN (1, 2)
          ORDER BY r.courses DESC
             LIMIT 10' ;
    return $DB->get_records_sql($sql, $params);
}

/**
 *
 * @global moodle_database $DB 
 */
function local_hub_stats_top_10_countries() {
    global $DB;
    list($where, $params) = local_hub_stats_get_confirmed_sql();
    $sql = 'SELECT r.countrycode, COUNT(DISTINCT r.id) countrycount
              FROM {hub_site_directory} r
             WHERE '.$where.'
          GROUP BY r.countrycode
          ORDER BY countrycount DESC
             LIMIT 10';
    return $DB->get_records_sql($sql, $params);
}

function local_hub_stats_get_confirmed_sql($prefix = 'r', $aliassuffix = '') {
    if (empty($prefix)) {
        $prefix = '';
    } else {
        $prefix = $prefix.'.';
    }
    // score > 3 allows for a number of rules (or major rules) to be matched at least. When score<1, we include all reached sites that are also not seen as moodle (see linkchecker rules).
    $sql = "{$prefix}timeregistered > 0 AND
            {$prefix}timeregistered < :thismonth{$aliassuffix} AND score > 3 AND
            ({$prefix}unreachable <= :maxunreachable{$aliassuffix} OR {$prefix}override BETWEEN 1 AND 3)";
    $params = array(
        'maxunreachable'.$aliassuffix => STATS_MAX_UNREACHABLE,
        'thismonth'.$aliassuffix => mktime(0, 0, 0, date('n'), 1, date('Y'))
    );
    return array($sql, $params);
}

/**
 * Update the hub's own count of users in the hub site directory.
 * @return int The count of users. Zero if this site is not registered with the hub.
 */
function local_hub_stats_update_moodle_users() {
    global $DB, $CFG;
    $siterecord = $DB->get_record('hub_site_directory', array('url' => $CFG->wwwroot), 'id, users');
    if ($siterecord) {
        // This site is registered in its own hub.
        $siterecord->users = $DB->count_records('user', array('deleted' => 0));
        $DB->set_field('hub_site_directory', 'users', $siterecord->users, array('url' => $CFG->wwwroot));
        return $siterecord->users;
    }
    return 0;
}
