<?php

defined('MOODLE_INTERNAL') || die;

/**
 *
 * @global moodle_database $DB
 */
function stats_get_registry_stats() {
    global $DB;
    
    $sql = 'SELECT
                COUNT(DISTINCT r.id) registrycount,
                SUM(r.courses) courses,
                SUM(r.users) users,
                SUM(r.enrolments) enrolments,
                SUM(r.teachers) teachers,
                SUM(r.posts) posts,
                SUM(r.resources) resources,
                SUM(r.questions) questions,
                COUNT(DISTINCT r.country) countrycount
            FROM {registry} r
            WHERE '.stats_get_criteria_sql();
    $stats = $DB->get_record_sql($sql);

    return $stats;
}

/**
 *
 * @global moodle_database $DB
 */
function stats_top_10_sites_by_users() {
    global $DB;
    $sql = 'SELECT r.* FROM {registry} r WHERE '.stats_get_criteria_sql().' AND r.public IN (1, 2) ORDER BY r.users DESC LIMIT 10' ;
    return $DB->get_records_sql($sql);
}

/**
 *
 * @global moodle_database $DB
 */
function stats_top_10_sites_by_courses() {
    global $DB;
    $sql = 'SELECT r.* FROM {registry} r WHERE '.stats_get_criteria_sql().' AND r.public IN (1, 2) ORDER BY r.courses DESC LIMIT 10' ;
    return $DB->get_records_sql($sql);
}

/**
 *
 * @global moodle_database $DB 
 */
function stats_top_10_countries() {
    global $DB;
    $sql = 'SELECT r.country, COUNT(DISTINCT r.id) countrycount FROM {registry} r WHERE '.stats_get_criteria_sql().' GROUP BY r.country ORDER BY countrycount DESC LIMIT 10';
    return $DB->get_records_sql($sql);
}

function stats_get_criteria_sql($prefix='r') {
    return '('.$prefix.'.unreachable < 2 OR '.$prefix.'.override IN (1, 2, 3)) AND '.$prefix.'.confirmed=1';
}
