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

    $coursecontext = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);
    $coursecontextid = $coursecontext->id;


    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    $course_name = $course->fullname;

    $timenow = time();
    $timeago = $timenow - ($days * 24 * 3600);

    $totalcount = 0;

    list($ctxselect, $ctxjoin) = context_instance_preload_sql('cm.id', CONTEXT_MODULE, 'ctx');

    $negativescaleid = $scaleid * -1;
    $params = array('courseid' => $courseid, 'scaleid' => $negativescaleid);

    $sql = "SELECT f.* $ctxselect
            FROM {forum} f
            LEFT JOIN {course_modules} cm ON cm.instance = f.id
            LEFT JOIN {modules} m ON m.id = cm.module
            $ctxjoin
            WHERE cm.course = :courseid AND scale = :scaleid AND m.name = 'forum'";
    $forums = $DB->get_records_sql($sql, $params);
    if (empty($forums)) {
        mtrace("\t No forums found in courseid $courseid with scaleid $scaleid");
        return false;
    }
    $forumcontexts = array();
    foreach ($forums as &$forum) {
        $forumcontexts[] = $forum->ctxid;
        context_instance_preload($forum);
    }

    $userinfo = array();
    $usersort = array();

    list($esql, $params) = get_enrolled_sql($coursecontext);
    $params['timeago'] = $timeago;
    $sql = "SELECT u.*, fp.postcount totalposts, tfp.postcount totalpostsalltime
            FROM {user} u
            JOIN ($esql) je ON je.id = u.id
            LEFT JOIN (
                SELECT fp.userid, COUNT(fp.id) postcount
                FROM {forum_posts} fp
                LEFT JOIN {forum_discussions} fd ON fd.id = fp.discussion
                WHERE fd.course = $courseid
                AND fp.created > :timeago
                GROUP BY fp.userid
            ) fp ON fp.userid = u.id
            LEFT JOIN (
                SELECT fp.userid, COUNT(fp.id) postcount
                FROM {forum_posts} fp
                LEFT JOIN {forum_discussions} fd ON fd.id = fp.discussion
                WHERE fd.course = $courseid
                GROUP BY fp.userid
            ) tfp ON tfp.userid = u.id
            WHERE u.deleted = 0
            AND fp.postcount > 0
            ORDER BY u.lastname ASC, u.firstname ASC";

    $enrolments = $DB->get_recordset_sql($sql, $params);
    list($forumidsin, $forumidsparams) = $DB->get_in_or_equal(array_keys($forums), SQL_PARAMS_NAMED);

    foreach ($enrolments as $user) {

        $totalpostcount = $user->totalpostsalltime;

        if ($totalpostcount < $minposts) {
            continue;
        }

        $count = $user->totalposts;
        if ($count < $minposts) {
            continue;
        }

        $ratingcount = phm_get_users_rater_count($user->id, $forumcontexts);
        $ratercount = phm_get_users_rater_count($user->id, $forumcontexts, true);
        $ratio = $ratingcount/$totalpostcount;

        if ($ratingcount < $minratings || $ratio < $minratio) {
            continue;
        }

        if ($ratercount < $minraters) {
            continue;
        }

        $userinfo[$user->id]['name'] = fullname($user);
        $userinfo[$user->id]['count'] = $count;
        $userinfo[$user->id]['ratingcount'] = $ratingcount;
        $userinfo[$user->id]['totalpostcount'] = $totalpostcount;
        $userinfo[$user->id]['ratercount'] = $ratercount;
        $userinfo[$user->id]['ratio'] = $ratio;//(float)$ratercount/(float)$totalpostcount;

        $usersort[$user->id] = $userinfo[$user->id]['ratio'];

        $totalcount++;

    }

    $enrolments->close();

    $existingmembers = groups_get_members($groupid);


    arsort($usersort);

    $table = new html_table();
    $table->attributes = array('class'=>'generaltable');
    $table->head = array('Name', 'Total Posts', 'Recent Posts', 'Total Ratings', 'Total Raters', 'Ratio of ratings/posts', 'Current PHM?');
    $table->data = array();

    foreach ($usersort as $id => $score) {

        $row = array(
            html_writer::link(new moodle_url('/user/view.php', array('id'=>$id, 'course'=>$courseid)), $userinfo[$id]['name']),
            html_writer::link(new moodle_url('/mod/forum/user.php', array('id'=>$id, 'course'=>$courseid)), $userinfo[$id]['totalpostcount']),
            html_writer::link(new moodle_url('/mod/forum/user.php', array('id'=>$id, 'course'=>$courseid)), $userinfo[$id]['count']),
            $userinfo[$id]['ratingcount'],
            $userinfo[$id]['ratercount'],
            format_float((float)$userinfo[$id]['ratio'], 3)
        );

        if (groups_is_member($groupid, $id)) {
            $row[] = "PHM";
            unset($existingmembers[$id]);
        } else {
            $row[] = '<img src="http://moodle.org/pix/s/smiley.gif" title="New!"/>';
            if ($savechanges) {
                groups_add_member($groupid, $id);
            }
        }

        $table->data[] = $row;
    }

    $s.= html_writer::tag('h1', "Using Moodle participants with: >= $minposts posts in the last $days days, >=  $minratings ratings total by more than $minraters raters, ratings/posts ratio >= $minratio");
    $s.= html_writer::tag('h2', "$totalcount users");
    $s.= html_writer::table($table);

    $s.= html_writer::tag('h1', "PHM Members being removed");
    $s.= html_writer::tag('h2', count($existingmembers)." users");

    $table = new html_table();
    $table->attributes = array('class'=>'generaltable');
    $table->head = array('Name', 'Total Posts', 'Recent Posts', 'Total Ratings', 'Total Raters', 'Ration of ratings/posts', 'Current PHM?');
    $table->data = array();

    foreach ($existingmembers as $user) {
        if ($savechanges) {
            groups_remove_member($groupid, $user->id);
        }

        $sql = "SELECT COUNT(*)
                FROM {forum_posts} fp
                LEFT JOIN {forum_discussions} fd ON fd.id = fp.discussion
                WHERE fd.course = :courseid
                AND fp.userid = :userid";
        $totalpostcount = $DB->count_records_sql($sql, array('courseid'=>$courseid, 'timeago'=>$timeago, 'userid'=>$user->id));

        $sql = "SELECT COUNT(*)
                FROM {forum_posts} fp
                LEFT JOIN {forum_discussions} fd ON fd.id = fp.discussion
                WHERE fd.course = :courseid
                AND fp.created > :timeago
                AND fp.userid = :userid";
        $count = $DB->count_records_sql($sql, array('courseid'=>$courseid, 'timeago'=>$timeago, 'userid'=>$user->id));

        $ratingcount = phm_get_users_rater_count($user->id, $forumcontexts);
        $ratercount = phm_get_users_rater_count($user->id, $forumcontexts, true);

        $row = array(
            html_writer::link(new moodle_url('/user/view.php', array('id'=>$user->id, 'course'=>$courseid)), fullname($user)),
            html_writer::link(new moodle_url('/mod/forum/user.php', array('id'=>$user->id, 'course'=>$courseid)), $totalpostcount),
            html_writer::link(new moodle_url('/mod/forum/user.php', array('id'=>$user->id, 'course'=>$courseid)), $count),
            $ratingcount,
            $ratercount,
            format_float(((float)$ratingcount) / ((float)$totalpostcount), 3),
            'PHM'
        );

        $table->data[] = $row;

    }
    $s.= html_writer::table($table);


    // begin email sending..
    $subject =  "Particularly Helpful Moodlers (in $course_name)";
    foreach($emailusers as $emailuser) {
        // hacky - force sending html to users who don't want it.
        // ps. I hate html email - danp
        $emailuser->mailformat = 1;
        email_to_user($emailuser, 'moodle.org', $subject, 'HTML email only, sorry', $s);
    }

    return true;
}

function phm_get_users_rater_count($userid, array $forumcontextids, $distinct=false) {
    global $DB;
    list($ratingsin, $params) = $DB->get_in_or_equal($forumcontextids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    if ($distinct) {
        $sql = "SELECT COUNT(DISTINCT r.userid) ";
    } else {
        $sql = "SELECT COUNT(*) ";
    }
    $sql.= "FROM {rating} r
            LEFT JOIN {context} ctx ON ctx.id = r.contextid
            LEFT JOIN {course_modules} cm ON cm.id = ctx.instanceid
            LEFT JOIN {forum_discussions} fd ON fd.forum = cm.instance
            LEFT JOIN {forum_posts} fp ON fp.discussion = fd.id
            WHERE r.contextid $ratingsin AND fp.userid = :userid AND r.itemid = fp.id";
    return $DB->count_records_sql($sql, $params);
}
