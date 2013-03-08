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
function phm_calculate_users($minposts = 14, $minratings = 14, $minraters = 8, $minratio = 0.02) {
    global $DB, $OUTPUT;

    $s = '';
    $minposttime = time() - YEARSECS;

    $forummodid = $DB->get_field('modules', 'id', array('name' => 'forum'));

    $innersql = " FROM {forum_posts} fp
                  JOIN {forum_discussions} fd ON fp.discussion = fd.id
                  JOIN {course_modules} cm ON cm.instance = fd.forum
                  JOIN {context} ctx ON ctx.instanceid = cm.id
                  JOIN {rating} r ON r.contextid = ctx.id
                  WHERE cm.module = :forummodid
                  AND ctx.contextlevel = :contextlevel AND r.component = :component
                  AND r.ratingarea = :ratingarea AND r.itemid = fp.id
                  AND fp.created > :minposttime
                  ";

    $params = array('forummodid'    => $forummodid,
                     'contextlevel' => CONTEXT_MODULE,
                     'component'    => 'mod_forum',
                     'ratingarea'   => 'post',
                     'minposttime'  => $minposttime
                    );


    $raterssql = "SELECT fp.userid, COUNT(r.id) AS ratingscount
                    $innersql
                  GROUP BY fp.userid";

    $phms = array();
    $rs = $DB->get_recordset_sql($raterssql, $params);
    foreach($rs as $record) {
        if ($record->ratingscount < $minratings) {
            // Need at least 14 ratings.
            continue;
        }

        $totalpostcount = $DB->count_records('forum_posts', 'userid = :userid AND timecreated > :mintime', array('userid' => $record->userid, 'mintime' => $minposttime));

        if ($totalpostcount < $minposts) {
            // Need a minimum of X posts
            continue;
        }

        $countsql = "SELECT COUNT(DISTINCT(r.userid)) $innersql AND fp.userid = :userid";
        $countparms = array_merge($params, array('userid' => $record->userid));
        $raterscount = $DB->count_records_sql($countsql, $countparms);

        if ($raterscount < $minraters) {
            // Need at least 8 different ratings.
            continue;
        }

        $ratio = $record->ratingscount / $totalpostcount;

        if ($ratio < $minratio) {
            // Need a post ratio this good.
            continue;
        }

        $phms[$record->userid] = array('totalratings' => $record->ratingscount, 'postcount' =>  $totalpostcount, 'raters' => $raterscount);
    }
    $rs->close();

    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($phms), SQL_PARAMS_QM, 'param', false);

    // Users we'd be removing:
    $sql = "SELECT u.id, u.firstname, u.lastname FROM user u
            JOIN groups_members gm ON u.id = gm.userid AND gm.groupid = 1
            WHERE gm.userid $insql";

    $users = $DB->get_records_sql($sql, $inparams);
    echo "Would remove ".count($users)." users\n";
    foreach ($users as $u) {
        echo "{$u->firstname} {$u->lastname} \n";
    }

    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($phms));
    $sql = "SELECT u.id, u.firstname, u.lastname FROM user u
            LEFT JOIN groups_members gm ON u.id = gm.userid AND gm.groupid = 1
            WHERE u.id $insql AND gm.userid IS NULL";

    $users = $DB->get_records_sql($sql, $inparams);
    echo "Would add ".count($users)." users\n";
    foreach ($users as $u) {
        echo "{$u->firstname} {$u->lastname} \n";
    }

    print_object(count($phms));

    return true;
}
