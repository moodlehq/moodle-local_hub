<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/hub/publicstats/top/sites/siteslib.php');

$country = optional_param('country', '', PARAM_ALPHA);
$cool = optional_param('cool', 0, PARAM_INT);
$uncool = optional_param('uncool', 0, PARAM_INT);
$sitevoting = optional_param('voting', 0, PARAM_INT);
$edit = optional_param('edit', '', PARAM_ALPHA);

$isadmin = ismoodlesiteadmin();

$USER->siteediting = ($isadmin && $edit == "on");

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

if (empty($country) and $usercountry) {
    $country = $usercountry;
}

$hide_all_links = false;
$mostrecent = 0;
$strresources = get_string("modulenameplural", "resource");

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/sites/index_norecordset.php'));
$PAGE->set_title('Moodle: Moodle sites');
$PAGE->set_heading('Moodle sites');
$PAGE->set_button(edit_button($isadmin, $country));
$PAGE->navbar->add($strresources.' '.$PAGE->heading, new moodle_url('/mod/resource/index.php', array('id'=>SITEID)));

echo $OUTPUT->header();

/// Process cool / uncool
if ($cool or $uncool) {
    if (isloggedin() and confirm_sesskey()) {
        if ($site = $DB->get_record('registry', array('id'=>$cool+$uncool))) {  // site exists
            $country = $site->country;
            if ($DB->record_exists('hub_site_directory_votes', array('userid'=>$USER->id, 'siteid'=>$site->id))) {
                echo $OUTPUT->notification(get_string('erroralreadyvoted', 'local_hub', s($site->sitename)));
            } else {
                if ($cool) {
                    $site->cool = $site->cool + 1;
                } else if ($uncool) {
                    $site->cool = $site->cool - 1;
                }
                $coolsite = new stdClass;
                $coolsite->id = $site->id;
                $coolsite->cool = $site->cool;
                $coolsite->cooldate = time();
                $DB->update_record('registry', $coolsite);

                $vote = new stdClass;
                $vote->userid = $USER->id;
                $vote->siteid = $site->id;
                $vote->vote = $cool ? 1 : -1;
                $vote->timevoted = time();
                $DB->insert_record('registry_votes', $vote);

                if ($cool) {
                    echo $OUTPUT->notification('Your positive feeling for "'.s($site->sitename).'" has been recorded', 'notifysuccess');
                } else {
                    echo $OUTPUT->notification('Your negative feeling against "'.s($site->sitename).'" has been recorded', 'notifysuccess');
                }
            }
        }
    }
}

echo "<a name=\"top\"></a>";
echo html_writer::start_tag('div', array('class'=>'boxaligncenter boxwidthwide', 'style'=>'padding:20px;'));

echo "<h2 align=center>Moodle Sites</h2>\n";

echo "<p align=center>Some of the growing community of Moodle users are listed below.</p>";
echo "<p align=center>To add or update your site, just use the \"Registration\" button on your Moodle admin page.</p>";
echo "<p align=center>(Note: sites that are unreachable or obviously just for testing are not accepted)</p>";

echo "<p align=center><a href=\"moodle-registered-sites-20061001-large-sm.png\"><img src=\"world.png\" width=540 height=270 /></a></p>";


/// Sort the sites

$countries = get_string_manager()->get_list_of_countries();
foreach ($countries as $code => $fullname) {
    $list[$code]->name = $fullname;
    $list[$code]->count = 0;
}

$counthidden = 0;

$usedcountry = array();

$sites = $DB->get_records_select('hub_site_directory', '', null, 'sitename', 'id, country, sitename, public, url, timecreated, timeupdated, lang, cool');
foreach ($sites as $key => $site) {
    if (empty($list[$site->country]->name)) {    /// Unknown country
        $list[$site->country]->name = $site->country;
    }
    if (!isset($list[$site->country]->count)) {
        $list[$site->country]->count = 0;
    }
    $list[$site->country]->count++;
    $usedcountry[$site->country] = true;
    if ($site->public > 0 or !empty($USER->siteediting)) {
        $list[$site->country]->sites[$site->url] = $site;
    }
    if ($site->timeupdated > $mostrecent) {
        $mostrecent = $site->timeupdated;
    }
    if (!$site->public) {
        $counthidden++;
    }
}

echo "<p align=center>Currently there are ".$DB->count_records("hub_site_directory")." sites from ".count($usedcountry)." countries who have registered.<br />";

echo "$counthidden of these have requested privacy and are not shown in the lists below.</p>";

if ($hide_all_links) {
    echo "<p align=center>(Links have been temporarily switched off)</p>";
}

echo "<p align=center><font size=1>";
$startlist = true;
foreach ($list as $code => $acountry) {
    if ($acountry->count) {
        if ($startlist) {
            $startlist = false;
        } else {
            echo " | ";
        }
        if ($code == $usercountry) {
            echo "<b><a title=\"We have detected that this is where you are located\" href=\"index.php?country=$code\">$acountry->name</a></b> ";
        } else {
            echo "<a href=\"index.php?country=$code\">$acountry->name</a> ";
        }
    }
}
echo "| <a href=\"index.php?country=all\">SHOW ALL</a>";
echo "</font></p>";

echo html_writer::end_tag('div');
echo "<br />";



if ($sitevoting) {
    if ($sitevoting == 1) {
        $USER->sitevoting = true;
    } else {
        $USER->sitevoting = false;
    }
}

echo '<center>';
if (!isloggedin() || isguestuser()) {
    echo '<div style="margin-left:20%;margin-right:20%;text-align:center;font-size:0.9em;">Sites can be marked "Cool" if three or more people vote for them.  Cool sites are promoted around moodle.org and other places. To vote on sites you need to be <a href="/login/index.php">logged in</a>.</div>';
    echo "<br />";
    if (isguestuser()) {
        unset($USER->sitevoting);
    }
} else {
    if (empty($USER->sitevoting)) {
        echo '<div style="margin-left:20%;margin-right:20%;text-align:center;font-size:0.9em;">Sites can be marked "Cool" if three or more people vote for them.  Cool sites are promoted around moodle.org and other places. To see the voting controls, use this button:</div>';
        $options = array('voting'=>1);
        $button = new single_button(new moodle_url('/sites/index.php', $options), 'Show voting buttons for these sites');
    } else {
        $options = array('voting'=>-1);
        $button = new single_button(new moodle_url('/sites/index.php', $options), 'Hide voting buttons for these sites');
    }
    echo $OUTPUT->single_button($button);
    echo '<br />';
}
echo '</center>';

$timenow = time();

if (!empty($country)) {

    if (($country != 'all' and isset($list[$country]))) {
        $newlist[$country] = $list[$country];
        $list = $newlist;

        // Get old voting records
        if (!empty($USER->sitevoting)) {
            $oldvotes = $DB->get_records_menu('registry_votes', array('userid'=>$USER->id), '', 'siteid, vote');
            $countvotes = $DB->get_records_select_menu('registry_votes', 'siteid > 0 GROUP BY siteid', null, '', 'siteid, COUNT(*) number');
        }
    }

    foreach ($list as $code => $country) {
        if ($country->count) {
            if ($country->count == 1) {
                $strsite = "$country->count site";
            } else {
                $strsite = "$country->count sites";
            }

            if (!empty($country->sites)) {
                $countsites = count($country->sites);
            } else {
                $countsites = 0;
            }
            if ($countsites < $country->count) {
                $countsites = $country->count - $countsites;
                $strsite .= " ($countsites not shown here)";
            }

            $file = "flags/".strtolower($code).".png";
            if (file_exists($file)) {
                $flag = "<img align=bottom src=\"$file\" height=15 width=25 alt=\"\">";
            } else {
                $flag = "";
            }

            echo "<a name=\"$code\"></a><table width=80% align=center cellspacing=0 cellpadding=5 class=generalbox><tr>";
            echo "<th align=left class=header>$country->name&nbsp;&nbsp;&nbsp;$flag</th>";
            echo "<th align=right class=header><font size=1>$strsite</font></th>";
            echo "</tr><tr><td colspan=2 class=\"generalboxcontent\">\n";
            if (!empty($country->sites)) {
                echo "<ul>\n";
#uksort($country->sites, 'strnatcasecmp');
                foreach ($country->sites as $site) {
                    if (empty($site->lang)) {
                        echo '<li>';
                    } else {
                        $site->lang = str_replace('_', '-', $site->lang);
                        echo '<li lang="'.$site->lang.'">';
                    }
                    if (trim($site->sitename) == '') {
                        $site->sitename = $site->url;
                    }
                    if ($site->cool >= MAXVOTES) {
                        echo '<b>';
                    }
                    if ($USER->siteediting) {
                        if ($site->public ==2) {
                            $class = '';
                        } else {
                            $class = 'class=dimmed';
                        }
                        echo '<a '.$class.' href="'.$site->url.'/">'.$site->sitename.'</a>';
                    } else if ($site->public == 1 or ! $site->url or $hide_all_links ) {
                        echo $site->sitename;
                    } else if ($site->public == 2) {
                        echo '<a href="'.$site->url.'/">'.$site->sitename.'</a>';
                    }
                    if ($site->timecreated) {
                        if ($timenow - $site->timecreated < 609600) {  // two weeks
                            echo '&nbsp;<img src="/pix/i/new.gif" height="11" width="28" alt="(new)">';
                        }
                    }
                    if ($site->cool <= - MAXVOTES) {
                        echo '</b>';
                        echo '&nbsp;<img title="Uncool site!" src="/pix/s/sad.gif" height="15" width="15" alt="Uncool!" border=0>';
                    } else if ($site->cool >= MAXVOTES) {
                        echo '</b>';
                        echo '&nbsp;<img title="Cool site!" src="/pix/s/cool.gif" height="15" width="15" alt="Cool!" border="0">';
                    }

                    if ($isadmin and $USER->siteediting) {
                        echo '&nbsp;&nbsp;&nbsp;<a href="edit.php?edit='.$site->id.'&amp;sesskey='.sesskey().'"><img src="/pix/t/edit.gif" height="11" width="11" alt="edit" border="0"></a>';
                        echo '&nbsp;<a href="edit.php?delete='.$site->id.'&amp;sesskey='.sesskey().'"><img src="/pix/t/delete.gif" height="11" width="11" alt="delete" border="0"></a>';
                    }

                    if (!empty($USER->sitevoting) && $site->public) {
                        echo '&nbsp;&nbsp;&nbsp;';
                        if (!isset($oldvotes[$site->id])) {
                            echo '<a title="I like this site!" href="index.php?cool='.$site->id.'&amp;sesskey='.sesskey().'"><img src="/pix/s/yes.gif" height="17" width="14" alt="" border="0"></a>';
                            echo '&nbsp;<a title="I don\'t like this site!" href="index.php?uncool='.$site->id.'&sesskey='.sesskey().'"><img src="/pix/s/no.gif" height="15" width="12" alt="" border="0"></a>';
                        } else if ($oldvotes[$site->id] >= 0) {
                            echo '<img title="Total score: '.$site->cool.'" src="/pix/s/yes.gif" height="17" width="14" alt="" border="0">';

                        } else {
                            echo '<img title="Total score: '.$site->cool.'" src="/pix/s/no.gif" height="15" width="12" alt="" border="0">';
                        }
                        if ($isadmin and $USER->siteediting) {
                            if (!empty($countvotes[$site->id])) {
                                if ($site->cool >= 0) {
                                   $class = 'highlight';
                                } else {
                                   $class = 'highlightbad';
                                }
                                echo '&nbsp;(<span class="'.$class.'">';
                                link_to_popup_window('/sites/showvotes.php?id='.$site->id, 'votes', $countvotes[$site->id].'&raquo;'.$site->cool);
                                echo '</span>)';
                            }
                        }
                    }
                    echo "</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<ul><li>None of the registered sites have been made public</li></ul>";
            }
            echo "<p align=\"right\"><font size=1><a href=\"#top\"><img src=\"http://moodle.org/pix/s/yes.gif\" border=0 alt=\"Up to top\"></a></font></p>";
            echo "</td></tr></table><br />\n";
        }
    }
}

echo "<br />";

echo "<p align=center><font size=-1>Page last updated: ";
echo userdate($mostrecent);
echo "</font></p>";

echo $OUTPUT->footer();