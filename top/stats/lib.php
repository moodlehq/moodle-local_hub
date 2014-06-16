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
function local_moodleorg_stats_get_registry_stats() {
    global $DB;
    list($where, $params) = local_moodleorg_stats_get_confirmed_sql();
    $sql = 'SELECT
                COUNT(DISTINCT r.id) registrycount,
                SUM(r.courses) courses,
                SUM(r.users) users,
                SUM(r.enrolments) enrolments,
                SUM(r.teachers) teachers,
                SUM(r.posts) posts,
                SUM(r.resources) resources,
                SUM(r.questions) questions,
                COUNT(DISTINCT r.countrycode) countrycount
            FROM {registry} r
            WHERE '.$where;
    $stats = $DB->get_record_sql($sql, $params);

    return $stats;
}

/**
 *
 * @global moodle_database $DB
 */
function local_moodleorg_stats_top_10_sites_by_users() {
    global $DB;
    list($where, $params) = local_moodleorg_stats_get_confirmed_sql();
    $sql = 'SELECT r.* 
              FROM {registry} r
             WHERE '.$where.' AND r.public IN (1, 2)
          ORDER BY r.users DESC
             LIMIT 10' ;
    return $DB->get_records_sql($sql, $params);
}

/**
 *
 * @global moodle_database $DB
 */
function local_moodleorg_stats_top_10_sites_by_courses() {
    global $DB;
    list($where, $params) = local_moodleorg_stats_get_confirmed_sql();
    $sql = 'SELECT r.*
              FROM {registry} r
             WHERE '.$where.' AND r.public IN (1, 2)
          ORDER BY r.courses DESC
             LIMIT 10' ;
    return $DB->get_records_sql($sql, $params);
}

/**
 *
 * @global moodle_database $DB 
 */
function local_moodleorg_stats_top_10_countries() {
    global $DB;
    list($where, $params) = local_moodleorg_stats_get_confirmed_sql();
    $sql = 'SELECT r.countrycode, COUNT(DISTINCT r.id) countrycount
              FROM {registry} r
             WHERE '.$where.'
          GROUP BY r.countrycode
          ORDER BY countrycount DESC
             LIMIT 10';
    return $DB->get_records_sql($sql, $params);
}

function local_moodleorg_stats_get_confirmed_sql($prefix = 'r', $aliassuffix = '') {
    if (empty($prefix)) {
        $prefix = '';
    } else {
        $prefix = $prefix.'.';
    }
    $sql = "{$prefix}timeregistered > 0 AND
            {$prefix}timeregistered < :thismonth{$aliassuffix} AND
            {$prefix}confirmed = 1 AND
            ({$prefix}unreachable <= :maxunreachable{$aliassuffix} OR {$prefix}override BETWEEN 1 AND 3)";
    $params = array(
        'maxunreachable'.$aliassuffix => STATS_MAX_UNREACHABLE,
        'thismonth'.$aliassuffix => mktime(0, 0, 0, date('n'), 1, date('Y'))
    );
    return array($sql, $params);
}

function local_moodleorg_stats_update_moodle_users() {
    global $DB;
    $moodle = $DB->get_record('registry', array('host' => 'moodle.org'), 'id, users', MUST_EXIST);
    $moodle->users = $DB->count_records('user', array('deleted' => 0));
    $DB->update_record('registry',$moodle);
    return $moodle;
}