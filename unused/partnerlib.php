<?php 

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

function partner_calculate_users($courseid, $groupid, $savechanges = true) {

    ///global $CFG,$db;
    ///$db->debug = 1;
    ///$CFG->debugdisplay=1;
    ///$CFG->debug=38911;

    require_login();

    if (!$coursecontext = get_context_instance(CONTEXT_COURSE, $courseid)) {
        error("Wrong course");
    } else {
        $coursecontextid = $coursecontext->id;
    }

    if (!has_capability('moodle/course:managegroups', $coursecontext)) {
        error("Sorry, you don't have permissions to use this");
    }

    $course_name = get_field('course', 'fullname', 'id', $courseid);

    $navigation = build_navigation(array(array('name' => "Moodle Partners (in $course_name)", 'link' => null, 'type' => 'title')));

    print_header("Moodle Partners (in $course_name)", "Moodle Partners (in $course_name)", $navigation);

    $timenow = time();

    $timeago = $timenow - ($days * 24 * 3600);

    $enrolments = get_records_select('role_assignments', "contextid = $coursecontextid", '', 'userid, roleid');

    $totalcount = 0;

    foreach ($enrolments as $enrolment) {
        $user->id = $enrolment->userid;

    /// Look if user is partner in Using Moodle Partners group
        if (!record_exists_sql("SELECT id
                                  FROM {$CFG->prefix}groups_members
                                 WHERE groupid = 179 
                                   AND userid = $user->id")) {
            continue;
        }

        $user = get_record('user', 'id', $user->id);

        $userinfo[$user->id]['name'] = "$user->firstname $user->lastname";

        $usersort[$user->id] = $user->lastname . $user->firstname;

        $totalcount++;

    }

    $existingmembers = groups_get_members($groupid);

    print_heading("$totalcount users");

    asort($usersort);

    echo '<table border="1" cellpadding="3" align="center">';
    echo "<tr>";
    echo "<th>Name</th>";
    echo "<th>Current Developer?</th>";
    echo "</tr>";
    foreach ($usersort as $id => $score) {
        echo "<tr>";
        echo "<td><a href=\"http://moodle.org/user/view.php?id=$id&course=$courseid\">".$userinfo[$id]['name']."</a></td>";
        echo "<td>";
        if (groups_is_member($groupid, $id)) {
            echo "Developer";
            unset($existingmembers[$id]);
        } else {
            echo '<img src="http://moodle.org/pix/s/smiley.gif" title="New!"/>';
             if ($savechanges) {
                 groups_add_member($groupid, $id);
             }
        }
        echo "</td></tr>";
    }
    echo "</table>";

    print_heading("Developer Members being removed");

    echo '<table border="1" cellpadding="3" align="center">';
    echo "<tr>";
    echo "<th>Name</th>";
    echo "<th>Current Developer?</th>";
    echo "</tr>";
    foreach ($existingmembers as $user) {
        $id = $user->id;
        if ($savechanges) {
            groups_remove_member($groupid, $id);
        }
     
        echo "<tr>";
        echo "<td><a href=\"http://moodle.org/user/view.php?id=$id&course=$courseid\">$user->firstname $user->lastname</a></td>";
        echo "Developer";
        echo "</td></tr>";

    }
    echo "</table>";

    print_footer();
}

?>
