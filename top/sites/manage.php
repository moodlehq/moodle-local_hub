<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/top/sites/siteslib.php');
require_once($CFG->dirroot.'/local/moodleorg/top/register/update_list_subscription.php');

$siteid = optional_param('id', 0, PARAM_INT);
$frame  = optional_param('frame', '', PARAM_ALPHA);
//$delete = optional_param('delete', '');
//$confirm = optional_param('confirm', '');
//$cool = optional_param('cool', '');
$isadmin = ismoodlesiteadmin();

require_login();

if (!ismoodlesiteadmin()) {
    print_error('erroradminonly', 'local_moodleorg');
}

$adminaccount = get_admin();

$countries = get_string_manager()->get_list_of_countries();

/***************************
What the hell is this doing?
  $wwwroot = $CFG->wwwroot;
  $CFG->wwwroot = '';
***************************/

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/sites/manage.php');
$PAGE->set_title('moodle.org: Manage sites');

if (isset($delete) and confirm_sesskey()) {
    $PAGE->set_pagelayout('embedded');
    echo $OUTPUT->header();
    echo $OUTPUT->spacer(array('height'=>50, 'width'=>1, 'br'=>$br));
    echo html_writer::start_tag('div', array('class'=>'boxaligncenter'));

    $site = $DB->get_record("registry", array('id' => $siteid, "confirmed", "0"), '*', MUST_EXIST);

    $to->email = $site->adminemail;
    $to->firstname = $site->adminname;
    $to->lastname = "";
    $message = "Thanks for registering details of your Moodle site with Moodle.org!\n\n".
               "    $site->url\n\n".
               "Unfortunately we could NOT accept it for the listing (http://moodle.org/sites).\n\n";
    if (!empty($reason)) {
        $message .= "Reason: \n   ".s($reason)."\n\n";
    } else {
        $message .= "Reasons for this usually include:\n".
                    "  - the site was on an intranet and not accessible from outside\n".
                    "  - the site was protected by a password or another method\n".
                    "  - the site didn't have any content yet\n".
                    "  - the site was obviously only a temporary testing site\n\n";
    }
    $message .= "If this situation changes in future, please try registering again!\n\n";
    email_to_user($to, $USER, "Your Moodle site was not accepted by Moodle.org: $site->sitename", $message);
    // email_to_user($adminaccount, $USER, "Your Moodle site was not accepted by Moodle.org: $site->sitename", $message);
    // email_to_user($USER, $USER, "Your Moodle site was not accepted by Moodle.org: $site->sitename", $message);

    $DB->delete_records("registry", array("id" => $siteid));
    echo $OUTPUT->notification("$site->adminname ($site->adminemail) has been emailed", 'notifysuccess');
    echo $OUTPUT->notification("Site '$site->sitename' has been removed from the registry", 'notifysuccess');

    echo '<center><a href="manage.php?frame=index" target="index">Continue</a></center>';

    echo html_writer::end_tag('div');
    echo $OUTPUT->footer();
    exit;
}

if ((isset($confirm) or isset($cool)) and confirm_sesskey()) {
    $PAGE->set_pagelayout('embedded');
    echo $OUTPUT->header();
    echo $OUTPUT->spacer(array('height'=>50, 'width'=>1, 'br'=>$br));
    echo html_writer::start_tag('div', array('class'=>'boxaligncenter'));

    $site = $DB->get_record("registry", array('id' => $siteid, "confirmed", "0"), '*', MUST_EXIST);

    $site->url = strip_tags($_POST['url']);
    $site->sitename = strip_tags($_POST['sitename']);
    $site->adminname = strip_tags($_POST['adminname']);
    $site->adminemail = strip_tags($_POST['adminemail']);
    $site->country = $_POST['country'];
    $site->public = $_POST['public'];
    $site->mailme = $_POST['mailme'];

    if ($oldsite = $DB->get_record("registry", array("url" => $site->url, "confirmed" => "1"))) {
        $newsite = clone($site);
        $newsite->confirmed = 1;
        $newsite->id = $oldsite->id;
        $newsite->timecreated = $oldsite->timecreated;
        if (isset($cool)) {
            $newsite->cool = MAXVOTES;
            $newsite->cooldate = time();
        }

        $DB->update_record("registry", $newsite);
        $DB->delete_records("registry", array("id"=> $site->id));

        update_list_subscription($oldsite->adminemail, $oldsite->mailme, $newsite->adminemail, $newsite->mailme);  // subscribe them to list
        $site = $newsite;

    } else {
        $DB->set_field("registry", "confirmed", 1, array("id" => $site->id));

        update_list_subscription('', 0, $site->adminemail, $site->mailme);  // subscribe them to list
        if (isset($cool)) {
             $DB->set_field("registry", "cool", MAXVOTES, array("id" => $site->id));
             $DB->set_field("registry", "cooldate", time(), array("id" => $site->id));
        }
    }


    $to->email = $site->adminemail;
    $to->firstname = $site->adminname;
    $to->lastname = "";
    $message = "Thank you for registering your Moodle site!\n\n".
               "    $site->url\n\n".
               "It has been confirmed and added to the list:\n\n".
               "    http://moodle.org/sites/\n\n";
    if (empty($site->public)) {
        $message .= "(Because you wanted privacy your site will not be shown)\n\n";
    }
    $message .= "For Moodle support please see http://moodle.org/\n";
    if (!empty($reason)) {
        $message .= "\n--------------------\n\nPersonal note: ".s($reason)."\n";
    }
    email_to_user($to, $USER, "Registry confirmed: $site->sitename", $message);
    //email_to_user($adminaccount, $USER, "Registry confirmed: $site->sitename", $message);

    echo $OUTPUT->notification("$site->sitename (<a href=\"$site->url\">$site->url</a>) confirmed.", 'notifysuccess');
    echo $OUTPUT->notification("$site->adminname ($site->adminemail) has been emailed", 'notifysuccess');

    echo '<center><a href="manage.php?frame=index" target="index">Continue</a></center>';

    echo html_writer::end_tag('div');
    echo $OUTPUT->footer();
    exit;
}


/// Frameset

if (empty($frame)) {
    ?>
    <html>
     <head><title>New Moodle Sites</title></head>
     <frameset cols="300,*" border="5">
       <frame src="manage.php?frame=index" name="index"
              scrolling="yes"  marginwidth="3" marginheight="3">
       <frame src="manage.php?frame=info" name="main"
              scrolling="yes"  marginwidth="0" marginheight="0">
     </frameset>
     <noframes>Sorry, but support for Frames is required</noframes>
    </html>
    <?php
    exit;
}



if ($frame == 'info') {
    echo "<head><body>";

    echo '<object data="http://docs.moodle.org/en/index.php?title=Development:Site_registration&printable=yes" type="text/html" width="100%" height="90%">';
    echo '<a href="http://docs.moodle.org/en/index.php?title=Development:Site_registration&printable=yes">Read this page for more info</a>';
    echo '</object>';

    /**
    echo "<h3>Guidelines for acceptance</h3>";
    echo "<ul>";
    echo "<ul>";
    echo "<li>The site must be accessible and active.";
            echo "<li>The URL must work and be plausibly ongoing (ie not http://145.34.43.1/~students/cf673474/moodle).";
    echo "<li>They must have at least one or two active courses on their site";
    echo "<li>They must look serious about continuing the site (look at graphics, text, custom domain name etc)";
    echo "</ul>";
    echo "<p>Note that all the sites here will be smaller or borderline cases.  All the big obvious sites
             are accepted automatically by the registration system and never appear here.</p>";
    echo "<p>If you can't accept the site, then reject it and shorten the queue.  Moodle sites can always re-register.</p>";
    echo "<p><-- Select sites from the left.  In Firefox/Mozilla try middle-clicking  - it will bring them up in individual tabs, which works very well</p>";
    echo "</ul>";
    **/

    echo '<p><a href="http://docs.moodle.org/en/index.php?title=Development:Site_registration&action=edit">Edit this text in the docs wiki</a></p>';

    echo "</body></head>";
    die;
}



/// Display the whole list of sites

if ($frame == 'index') {

    $filter = optional_param('filter_sitename', 0,PARAM_TEXT);

    $PAGE->set_pagelayout('embedded');

    echo $OUTPUT->header();
    echo $OUTPUT->single_button(new moodle_url('manage.php', array('frame' => 'index')), 'Refresh');

    echo "<form action=\"manage.php\" method=\"get\"><div><input type=\"hidden\" name=\"frame\" value=\"index\" />";
    echo "Sitename: <input type=text name=filter_sitename size=10 value='".s($filter)."'><input type=submit value=Search><br />";
    echo "<font size=1>Enrolments: 0:italic, 1-10:normal, >10 bold</font><br /></br />";
    $weekago = time() - (60*60*24*7*1);

    if (!empty($filter)) {
        $like = $DB->sql_like('sitename', '?', false);
        $sql = "SELECT * FROM {registry} WHERE confirmed = 0 AND timecreated <= ? AND $like ORDER BY timecreated ASC";
        $params = array($weekago, '%'.$filter.'%');
    }else {
        $sql = "SELECT * FROM registry WHERE confirmed = 0 AND timecreated <= $weekago ORDER BY timecreated ASC";
        $params = array();
    }
    if (!$sites = $DB->get_records_sql($sql, $params)) {
        echo $OUTPUT->notification("No sites to check :-)", 'notifysuccess');
        echo $OUTPUT->footer();
        exit;
    }
    echo "<font size=1>\n<ol>";
    foreach ($sites as $site) {
        $site->url = clean_text($site->url);
        $site->sitename = trim(clean_text($site->sitename));
        if (empty($site->sitename)) {
            $site->sitename = "?????";
        }
        echo '<li><a target=main title="'.$site->url.'" href="manage.php?frame=site&id='.$site->id.'">';
        if ($site->enrolments == 0) {
            echo '<i>'.$site->sitename.'</i>';
        } else if ($site->enrolments >= 10) {
            echo '<b>'.$site->sitename.'</b>';
        } else {
            echo $site->sitename;
        }
        echo '</a></li>';
    }
    echo "</ol></font>";

    echo $OUTPUT->footer();
    exit;
}

if ($frame == 'site') {

    $site = $DB->get_record("registry", array("id" => $siteid, "confirmed" => "0"), '*', MUST_EXIST);

    ?>
    <html>
     <head><title><?php p($site->sitename) ?></title></head>
     <frameset rows="250,*" border="5">
       <frame src="manage.php?frame=siteedit&id=<?php echo $site->id ?>" name="siteedit"
              scrolling="yes"  marginwidth="0" marginheight="0">
       <frame src="<?php echo $site->url ?>" name="realsite"
              scrolling="yes"  marginwidth="0" marginheight="0">
     </frameset>
     <noframes>Sorry, but support for Frames is required</noframes>
    </html>
    <?php
    exit;
}


if ($frame == 'siteedit') {

    $site = $DB->get_record("registry", array("id" => $siteid, "confirmed" => "0"), '*', MUST_EXIST);
    $PAGE->set_pagelayout('embedded');
    $PAGE->set_heading(s($site->sitename));
    echo $OUTPUT->header();

    $PUBLIC[0] = "Not to be listed";
    $PUBLIC[1] = "Listed, but not linked";
    $PUBLIC[2] = "Listed and linked";

    $MAILME[0] = "No, do not mail me notifications";
    $MAILME[1] = "Yes, mail me security notifications";

    echo "<table><tr><td valign=top>";
    echo '<font face="sans-serif" size="1">';
    echo 'Checklist:<ol>';
    echo '<li>Does the URL work?</li>';
    echo '<li>Does the URL look permanent?</li>';
    echo '<li>Is it a non-test site?</li>';
    echo '<li>Activity when they registered:';
    if ($site->moodleversion >= 2005031100) {
        echo "<ul><li>Courses: $site->courses</li><li>Users: $site->users</li><li>Enrolments: $site->enrolments</li><li>Teachers: $site->teachers</li><li>Posts: $site->posts</li><li>Resources: $site->resources</li><li>Questions: $site->questions</li></ul>";
    }
    echo "</li></ol>";
    echo "</td><td>";

    echo html_writer::start_tag('div', array('class'=>'boxaligncenter', 'style'=>'padding:20px;'));

    echo "<form name=\"form$site->id\" method=\"post\" action=\"manage.php\">";
    echo "<input type=hidden name=id value=\"$site->id\">";
    echo "<input size=40 type=text name=url value=\"".s($site->url)."\">";
    echo " (".format_time(time() - $site->timecreated)." ago)<br />";
    echo "<input size=40 type=text name=sitename value=\"".s($site->sitename)."\">";
    if (!empty($site->errormsg)) {
      echo " [-] ".$site->errormsg;
    }
    echo "<br /><input size=40 type=text name=adminname value=\"".s($site->adminname)."\"><br />";
    echo "<input size=40 type=text name=adminemail value=\"".s($site->adminemail)."\"><br />";
    echo html_writer::select($countries, 'country', $site->country); echo '<br />';
    echo html_writer::select($PUBLIC, 'public', $site->public); echo '<br />';
    echo html_writer::select($MAILME, 'mailme', $site->mailme); echo '<br />';

    echo '<input type=submit name=confirm value="Confirm!">';
    echo '<input type=submit name=cool value="Confirm as cool">';
    echo '<input type=submit name=delete value="Reject"><br/>';
    echo '<input type=hidden name=sesskey value="'.sesskey().'"><br/>';
    echo 'Include note: <input size=40 type=text name=reason value=""> (optional)';
    echo "</form>";

    if ($oldsite = $DB->get_record("registry", array("url" => $site->url, "confirmed" => 1))) {
        echo html_writer::start_tag('div', array('class'=>'boxaligncenter', 'style'=>'padding:20px;'));
        echo "<h4>Information being replaced:</h4>";
        echo "<UL>";
        echo "<LI><a href=\"$oldsite->url\">$oldsite->url</a>";
        echo "<LI>$oldsite->sitename";
        echo "<LI>$oldsite->moodlerelease ($oldsite->moodleversion)";
        echo "<LI>$oldsite->host";
        echo "<LI>$oldsite->lang";
        echo "<LI>$oldsite->secret";
        echo "<LI>".$countries[$oldsite->country];
        echo "<LI>Admin: $oldsite->adminname ($oldsite->adminemail)";
        echo "<LI>Public: $oldsite->public (".$PUBLIC[$oldsite->public].")";
        echo "<LI>Mailme: $oldsite->mailme (".$MAILME[$oldsite->mailme].")";
        echo "</UL>";
        echo html_writer::end_tag('div');
    }

    echo html_writer::end_tag('div');
    echo '</td></tr></table>';

    echo $OUTPUT->footer();

    die;
}