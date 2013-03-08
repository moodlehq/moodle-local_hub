<?php

defined('MOODLE_INTERNAL') || die;

include_once($CFG->dirroot.'/mod/forum/lib.php');
include_once($CFG->dirroot.'/group/lib.php');

/**
 *
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @param array $emailusers
 * @param <type> $courseid
 * @param <type> $groupid
 * @param int $scaleid
 * @param <type> $days
 * @param <type> $minposts
 * @param <type> $minratings
 * @param <type> $minraters
 * @param <type> $minratio
 * @param <type> $savechanges
 */
function phm_calculate_users($emailusers, $courseid, $groupid, $scaleid, $days = 60, $minposts = 1, $minratings = 14, $minraters = 8, $minratio = 0.02, $savechanges = true) {
    global $DB, $OUTPUT;

    $s = '';

    $forummodid = $DB->get_field('modules', 'id', array('name' => 'forum'));

    $innersql = " FROM {forum_posts} fp
                  JOIN {forum_discussions} fd ON fp.discussion = fd.id
                  JOIN {course_modules} cm ON cm.instance = fd.forum
                  JOIN {context} ctx ON ctx.instanceid = cm.id
                  JOIN {rating} r ON r.contextid = ctx.id
                  WHERE cm.module = :forummodid
                  AND ctx.contextlevel = :contextlevel AND r.component = :component
                  AND r.ratingarea = :ratingarea AND r.itemid = fp.id";

    $params = array('forummodid'         => $forummodid,
                        'contextlevel'    => CONTEXT_MODULE,
                        'component'       => 'mod_forum',
                        'ratingarea'      => 'post'
                    );


    $raterssql = "SELECT fp.userid, COUNT(r.id) AS ratingscount
                    $innersql
                  GROUP BY fp.userid";

    $phms = array();
    $rs = $DB->get_recordset_sql($raterssql, $params);
    foreach($rs as $record) {
        if ($record->ratingscount < 14) {
            // Need at least 14 ratings.
            continue;
        }

        $countsql = "SELECT COUNT(DISTINCT(r.userid)) $innersql AND fp.userid = :userid";
        $coutnparms = array_merge($params, array('userid' => $record->userid));
        $raterscount = $DB->count_records_sql($sql, $coutnparms);

        if ($raterscount < 8) {
            // Need at least 8 different ratings.
            continue;
        }

        $totalposts = $DB->count_records('forum_posts', array('userid' => $record->userid));

        $ratio = $record->ratingscount / $totalpostcount;

        if ($ratio < 0.12) {
            // Need a post ratio this good.
            continue;
        }

        $phms[$record->userid] = array('totalratings' => $record->raterscount, 'postcount' =>  $totalposts, 'raters' => $raterscount);
    }
    $rs->close();

    print_object($phms);

    return true;
}
