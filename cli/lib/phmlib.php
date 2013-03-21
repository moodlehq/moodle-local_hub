<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/cohort/lib.php');

class local_moodleorg_phm_cohort_manager {
    /** @var object cohort object from cohort table */
    private $cohort;
    /** @var array of cohort members indexed by userid */
    private $existingusers;
    /** @var array of cohort members indexed by userid */
    private $currentusers;


    /**
     * Creates a cohort for identifier if it doesn't exist
     *
     * @param string $identifier identifier of cohort uniquely identifiying cohorts between dev plugin generated cohorts
     */
    public function __construct() {
        global $DB;

        $cohort = new stdClass;
        $cohort->idnumber = 'local_moodleorg:particularly-helpful-moodlers';
        $cohort->component = 'local_moodleorg';

        if ($existingcohort = $DB->get_record('cohort', (array) $cohort)) {
            $this->cohort = $existingcohort;
            // populate cohort members array based on existing members
            $this->existingusers = $DB->get_records('cohort_members', array('cohortid' => $this->cohort->id), 'userid', 'userid');
            $this->currentusers = array();
        } else {
            $cohort->contextid = context_system::instance()->id;
            $cohort->name = 'Particularly helpful moodlers';
            $cohort->description = 'Automatically generated cohort from particularly helpful moodler scripts.';
            $cohort->id = cohort_add_cohort($cohort);

            $this->cohort = $cohort;
            // no existing members as we've just created cohort
            $this->existingusers = array();
            $this->currentusers = array();
        }
    }

    /**
     * Add a member to the cohort keeps track of members who have been added.
     *
     * @param int $userid id from user table of user
     * @return bool true if member is a new member of cohort
     */
    public function add_member($userid) {
        if (!isset($this->existingusers[$userid])) {
            cohort_add_member($this->cohort->id, $userid);
        }
        $this->currentusers[$userid] = $userid;
    }

    /**
     * Returns the usersids who have not been to the cohort since this manager was created
     *
     * @param array array of removed users indexed by userid
     */
    public function old_users() {
        return array_diff_key($this->existingusers, $this->currentusers);
    }

    /**
     * Returns the cohort record
     *
     * @param stdClass cohort record
     */
    public function cohort() {
        return $this->cohort;
    }

    /**
     * Returns the current users of the cohort
     *
     * @param array array of removed users indexed by userid
     */
    public function current_users() {
        return $this->currentusers;
    }

    public function remove_old_users() {
        $userids = $this->old_users();

        foreach($userids as $userid => $value) {
            cohort_remove_member($this->cohort->id, $userid);
            unset($this->existingusers[$userid]);
        }

        return $userids;
    }
}

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

        $totalpostcount = $DB->count_records_select('forum_posts', 'userid = :userid AND created > :mintime', array('userid' => $record->userid, 'mintime' => $minposttime));

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
