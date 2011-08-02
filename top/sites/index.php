<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/sites/siteslib.php');
require_once($CFG->dirroot.'/local/moodleorg/stats/lib.php');
require_once($CFG->dirroot.'/local/moodleorg/stats/graphlib.php');
require_once($CFG->dirroot.'/local/moodleorg/stats/googlecharts.php');

define('STATS_DIR', 'sites');

$countries = get_string_manager()->get_list_of_countries();
$countryarray = get_combined_country_info();
$totalcountryinfo = $countryarray['TOTAL'];
unset($countryarray['TOTAL']);

$country = optional_param('country', '', PARAM_ALPHA);
$cool = optional_param('cool', 0, PARAM_INT);
$uncool = optional_param('uncool', 0, PARAM_INT);
$sitevoting = optional_param('voting', 0, PARAM_INT);
$edit = optional_param('edit', '', PARAM_ALPHA);

$PAGE->set_context(get_system_context());
$PAGE->set_url(new moodle_url('/sites/'));

$isadmin = ismoodlesiteadmin();
$USER->siteediting = false;
if ($isadmin && $edit == "on") {
    $USER->siteediting = true;
}

/// Try to get the country, from USER, IP or request
$usercountry = "";
if (!empty($USER->country)) {
    $usercountry = $USER->country;
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($countryinfo = $DB->get_record_sql("SELECT * FROM {countries} WHERE ipfrom <= inet_aton('$ip') AND inet_aton('$ip') <= ipto ")) {
        $usercountry = $countryinfo->code2;
    }
}

if (empty($country) && $usercountry) {
    $country = $usercountry;
}

$list = null;

if ($country!==null && array_key_exists($country, $countries)) {

    if (!isloggedin() || isguestuser()) {
        unset($USER->sitevoting);
    }

    if (!empty($cool)) {
        $votesiteid = $cool;
        $votemodifier = 1;
    } else if (!empty($uncool)) {
        $votesiteid = $uncool;
        $votemodifier = -1;
    }
    if (isset($votesiteid) && isloggedin() && confirm_sesskey()) {
        $message = vote_for_site($votesiteid, $votemodifier);
    }

    $sites = get_sites_for_country($country);

    $file = "flags/".strtolower($country).".png";
    if (file_exists($file)) {
        $flag = "<img align=bottom src=\"$file\" height=15 width=25 alt=\"\" />";
    } else {
        $flag = "";
    }

    if ($sitevoting) {
        if ($sitevoting == 1) {
            $USER->sitevoting = true;
        } else {
            $USER->sitevoting = false;
        }
    }

    $graph = new google_charts_map_graph();
    $graph->force_generation(true);
    $graph->set_chart_title($countries[$country]." map");
    $graph->set_default_colour('FFEAB3');
    $filename = 'country.map.'.$country.'.png';
    $graph->set_filename($filename);
    $graph->add_value($country, 100);

    $graph = html_writer::empty_tag('img', array('src'=>new moodle_url($graph), 'alt'=>$countries[$country]." map", 'style'=>'float:right;position:relative;top:20px;right:20px;'));

    $list = new stdClass;
    $list->printanchors = true;
    $list->width = '100%';
    if ($sites->privatesites>0) {
        $list->heading = $countries[$country]." $flag <span style='font-size:0.6em;'>$sites->totalsites sites total ($sites->privatesites are private and are not shown)</span>$graph";
    } else {
        $list->heading = $countries[$country]." $flag <span style='font-size:0.6em;'>$sites->totalsites sites total</span>$graph";
    }
    $list->data = Array();

    // Get old voting records
    if (!empty($USER->sitevoting)) {
        $oldvotes = $DB->get_records_menu('registry_votes', array('userid'=>$USER->id), '', 'siteid, vote');
        $countvotes = $DB->get_records_select_menu('registry_votes', 'siteid > 0 GROUP BY siteid', null, '', 'siteid, count(*) number');
    }

    $newtimestamp = time() - (60*60*24*14);
    if (!is_array($sites->sites)) $sites->sites = Array();
    foreach ($sites->sites as $site) {
        if (trim($site->sitename) == '') {
            $name = $site->url;
        } else {
            $name = $site->sitename;
        }

        if ($site->cool >= MAXVOTES) {
            $name = "<strong>$name</strong>";
        }

        $properties='';
        if ($site->timecreated>$newtimestamp) {
            $properties .= "&nbsp;<img src='/pix/i/new.gif' height='11' width='28' alt='(new)'>";
        }

        if (isloggedin() && !isguestuser() && ((int)$site->mailme === 1) && (!isset($SESSION->registrycontactmessagesent) || $SESSION->registrycontactmessagesent < 4)) {
            $properties .= '&nbsp;';
            $properties .= $OUTPUT->action_icon(new moodle_url('/sites/contact.php', array('siteid'=>$site->id, 'sesskey'=>sesskey())), new pix_icon('t/email', 'Send mail', 'moodle', array('style'=>'height:11px;width:11px;border:0;')));
        }

        if ($site->cool <= - MAXVOTES) {
            $properties .= '&nbsp;<img title="Uncool site!" src="/pix/s/sad.gif" height="15" width="15" alt="Uncool!" border=0>';
        } else if ($site->cool >= MAXVOTES) {
            $properties .= '&nbsp;<img title="Cool site!" src="/pix/s/cool.gif" height="15" width="15" alt="Cool!" border="0">';
        }

        if (!empty($USER->sitevoting) && $site->public) {
            $properties .= '&nbsp;&nbsp;&nbsp;';
            if (!isset($oldvotes[$site->id])) {
                $properties .= '<a title="I like this site!" href="index.php?cool='.$site->id.'&amp;country='.$site->country.'&amp;sesskey='.sesskey().'"><img src="/pix/s/yes.gif" height="17" width="14" alt="" border="0" /></a>';
                $properties .= '&nbsp;<a title="I don\'t like this site!" href="index.php?uncool='.$site->id.'&amp;country='.$site->country.'&amp;sesskey='.sesskey().'"><img src="/pix/s/no.gif" height="15" width="12" alt="" border="0" /></a>';
            } else if ($oldvotes[$site->id] >= 0) {
                $properties .= '<img title="Total score: '.$site->cool.'" src="/pix/s/yes.gif" height="17" width="14" alt="" border="0" />';
            } else {
                $properties .= '<img title="Total score: '.$site->cool.'" src="/pix/s/no.gif" height="15" width="12" alt="" border="0" />';
            }
            if ($isadmin && $USER->siteediting) {
                if (!empty($countvotes[$site->id])) {
                    if ($site->cool >= 0) {
                       $properties .= '&nbsp;(<span class="highlight">';
                    } else {
                       $properties .= '&nbsp;(<span class="highlightbad">';
                    }
                    
                    $url = new moodle_url('/sites/showvotes.php', array('id'=>$site->id));

                    $properties .= $OUTPUT->action_link($url, 'votes', new popup_action('click', $url, 'votes'));

                    //$properties .= link_to_popup_window('/sites/showvotes.php?id='.$site->id, 'votes', $countvotes[$site->id].'&raquo;'.$site->cool,400,500,'votes','',true);
                    $properties .= '</span>)';
                }
            }
        }

        if ($isadmin && $USER->siteediting) {
            $properties .= '&nbsp;&nbsp;&nbsp;';
            $properties .= $OUTPUT->action_icon(new moodle_url('/sites/edit.php', array('edit'=>$site->id, 'sesskey'=>sesskey())), new pix_icon('t/edit', 'edit', null, array('style'=>'height:11px;width:11px;border:0;')));
            $properties .= '&nbsp;';
            $properties .= $OUTPUT->action_icon(new moodle_url('/sites/edit.php', array('delete'=>$site->id, 'sesskey'=>sesskey())), new pix_icon('t/delete', 'delete', 'moodle', array('style'=>'height:11px;width:11px;border:0;')));

            //$properties .= '&nbsp;&nbsp;&nbsp;<a href="edit.php?edit='.$site->id.'&amp;sesskey='.sesskey().'"><img src="/pix/t/edit.gif" height="11" width="11" alt="edit" border="0"></a>';
            //$properties .= '&nbsp;<a href="edit.php?delete='.$site->id.'&amp;sesskey='.sesskey().'"><img src="/pix/t/delete.gif" height="11" width="11" alt="delete" border="0"></a>';
        }

        if ($site->public==2) {
            $list->data[] = "<a href='$site->url'>$name</a>$properties";
        } else if ($USER->siteediting===true) {
            $list->data[] = "<a class='dimmed' href='$site->url'>$name</a>$properties";
        } else {
            $list->data[] = $name.$properties;
        }
    }
}


$PAGE->navbar->add('Registered sites', new moodle_url('/sites/'));
$PAGE->set_title('Moodle.org: Registered sites');
$PAGE->set_heading('Registered moodle sites');
$PAGE->set_button(edit_button($isadmin, $country));

echo $OUTPUT->header();
if (isset($message) && $message!==false) {
    echo $message;
}
echo html_writer::start_tag('div', array('class'=>'boxwidthwide boxaligncenter', 'style'=>'padding:20px;'));
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::start_tag('p', array('class'=>'mdl-align'));
echo 'Some of the growing community of Moodle users are listed below.<br />';
echo 'To add or update your site, just use the "Registration" button on your Moodle admin page.<br />';
echo '<span style="font-size:0.9em;">(Note: we check these sites regularly and <a href="http://docs.moodle.org/en/Usage">remove unreachable or invalid sites</a>)</span>';
echo html_writer::end_tag('p');

echo html_writer::start_tag('p', array('class'=>'mdl-align'));
echo '<a href="moodle-registered-sites-20091103-large.jpg"><img src="'.$CFG->wwwroot.'/sites/moodle-registered-sites-20091103-small.jpg" width=540 height=270 /></a>';
echo html_writer::end_tag('p');

echo html_writer::start_tag('p', array('class'=>'mdl-align'));
echo "There are {$totalcountryinfo->total} currently active sites that have registered from {$totalcountryinfo->countries} countries.<br />";
echo "{$totalcountryinfo->private} of these have requested privacy and are not shown in the lists below.";
echo html_writer::end_tag('p');

echo prepare_country_tag_cloud($countryarray, ($isadmin && $USER->siteediting), 500);
echo html_writer::end_tag('div');

if (isset($list)) {
    echo html_writer::start_tag('div', array('class'=>'boxwidthwide boxaligncenter', 'style'=>'padding:20px;'));

    echo '<div style="margin-left:20%;margin-right:20%;text-align:center;font-size:0.9em;">';
    if (!isloggedin() || isguestuser()) {
        echo 'Sites can be marked "Cool" if three or more people vote for them.  Cool sites are promoted around moodle.org and other places. To vote on sites you need to be <a href="/login/index.php">logged in</a>.';
        echo "<br />";
    } else {
        $options = array();
        $options['country'] = $country;
        if ($isadmin && $USER->siteediting) {
            $options['edit']='on';
        }
        if (empty($USER->sitevoting)) {
            echo 'Sites can be marked "Cool" if three or more people vote for them.  Cool sites are promoted around moodle.org and other places. To see the voting controls, use this button:</p>';
            $options['voting'] = 1;
            $button = new single_button(new moodle_url('/sites/index.php', $options), 'Show voting buttons for these sites');
        } else {
            $options['voting'] = -1;
            $button = new single_button(new moodle_url('/sites/index.php', $options), 'Hide voting buttons for these sites');
        }
        $OUTPUT->render($button);
    }
    echo "<br /></div>";

    print_list($list);
    echo "<p align=\"right\" style='clear:both'><a href=\"#top\"><img src=\"http://moodle.org/pix/t/up.gif\" border=0 alt=\"Up to top\"></a></p>";
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();
