<?php

defined('MOODLE_INTERNAL') || die;

define('MAXVOTES', 3);

require_once($CFG->dirroot.'/local/moodleorg/top/stats/lib.php');

function get_combined_country_info() {
    global $CFG, $DB;

    list($confirmedwhere, $confirmedparams) = local_moodleorg_stats_get_confirmed_sql('r', 'pub');

    $countries = get_string_manager()->get_list_of_countries();
    $sql = "SELECT r.countrycode as country, COUNT('x') AS totalcount, SUM(privacy = 'named' or privacy = 'linked') AS publiccount
              FROM {registry} r
             WHERE $confirmedwhere
          GROUP BY r.countrycode
            HAVING (COUNT('x')) > 0
          ORDER BY (COUNT('x')) DESC";
    $resultingcountries = $DB->get_records_sql($sql, $confirmedparams);
    $countryarray = Array();
    $countryarray['00'] = new stdClass;
    $countryarray['00']->countrycode = '00';
    $countryarray['00']->country = 'Unknown';
    $countryarray['00']->totalcount = 0;
    $countryarray['00']->publiccount = 0;
    $countryarray['00']->privatecount = 0;
    $totalpublic = 0;
    $totalprivate = 0;
    foreach ($resultingcountries as $country) {
        if (array_key_exists($country->country, $countries)) {
            $countryarray[$country->country] = new stdClass;
            $countryarray[$country->country]->countrycode = $country->country;
            $countryarray[$country->country]->country = $countries[$country->country];
            $countryarray[$country->country]->totalcount = $country->totalcount;
            $countryarray[$country->country]->publiccount = $country->publiccount;
            $countryarray[$country->country]->privatecount = $country->totalcount - $country->publiccount;
        } else {
            $countryarray['00']->totalcount += $country->totalcount;
            $countryarray['00']->publiccount += $country->publiccount;
            $countryarray['00']->privatecount += $country->totalcount - $country->publiccount;
        }
        $totalpublic += $country->publiccount;
        $totalprivate += $country->totalcount - $country->publiccount;
    }
    //$countryarray[] = array_shift($countryarray);
    array_shift($countryarray);
    $countryarray['TOTAL'] = new stdClass;
    $countryarray['TOTAL']->public = $totalpublic;
    $countryarray['TOTAL']->private = $totalprivate;
    $countryarray['TOTAL']->total = $totalpublic+$totalprivate;
    $countryarray['TOTAL']->countries = count($resultingcountries);
    return $countryarray;
}

/**
 *
 * @global moodle_database $DB
 * @param string $countrycode
 * @return stdClass
 */
function get_sites_for_country($countrycode) {
    global $DB;
    list($where, $params) = local_moodleorg_stats_get_confirmed_sql(null);
    $params['countrycode'] = $countrycode;

    $country = new stdClass;
    $country->totalsites =   $DB->count_records_select('registry', "countrycode LIKE :countrycode AND $where", $params);
    $country->privatesites = $DB->count_records_select('registry', "countrycode LIKE :countrycode AND privacy = 'notlinked' AND $where", $params);
    $country->publicsites =  $country->totalsites - $country->privatesites;
    $country->sites =        $DB->get_records_select('registry', "countrycode LIKE :countrycode AND (privacy = 'named' or privacy = 'linked') AND $where", $params, 'name, url', 'id,name,url,countrycode,privacy,timeregistered,cool,mailme');
    return $country;
}

define('SITES_SORT_NAME_DESC',0);
define('SITES_SORT_TOTAL_DESC',1);
function sort_combined_countries(&$countries, $method=SITES_SORT_TOTAL_DESC) {
    switch ($method) {
        case SITES_SORT_TOTAL_DESC: uasort($countries, "country_total_compare");break;
        case SITES_SORT_NAME_DESC: uasort($countries, "country_name_compare");break;
        default:return false;
    }
    return true;
}

function country_name_compare($a, $b) {
    if ($a->country==$b->country) {
        return 0;
    } else {
        return ($a->country>$b->country)?1:-1;
    }
}

function country_total_compare($a, $b) {
    if ($a->totalcount==$b->totalcount) {
        return 0;
    } else {
        return ($a->totalcount>$b->totalcount)?-1:1;
    }
}

function prepare_country_tag_cloud(&$countries, $canedit=false, $taglimit=50, $maxsize=1.5, $minsize=0.6) {
    sort_combined_countries($countries);
    if ($taglimit>count($countries)) $taglimit=count($countries);
    $tempcountries = array_slice($countries, 0, $taglimit);
    $maxtotal = get_top_total($tempcountries, 5);
    sort_combined_countries($tempcountries, SITES_SORT_NAME_DESC);
    
    $html = "<p class='countrytagcloud' style='text-align:center;'>";
    $countryhtml = Array();

    foreach ($tempcountries as $country) {
        $fontsize = round((($maxsize-$minsize)*($country->totalcount/$maxtotal))+$minsize,2);
        if ($fontsize>$maxsize) {
            $fontsize = $maxsize;
        } else if ($fontsize<$minsize) {
            $fontsize = $minsize;
        }
        if ($canedit) {
            $editlink = '&amp;edit=on';
        } else {
            $editlink = '';
        }
        $countryhtml[] = "<a href='/sites/index.php?country=".$country->countrycode.$editlink."' style='font-size:{$fontsize}em'>".$country->country."</a>";
    }
    $html .= join('&nbsp; &nbsp;', $countryhtml)."</p>";
    return $html;
}

function get_top_total(&$countries, $ignore=5) {
    $max = 0;
    $ignore = (count($countries)/100)*$ignore;
    $ignored = 0;
    foreach ($countries as $country) {
        if ($ignored<$ignore) {
            $ignored++;
        } else {
            if ($country->totalcount>$max) $max = $country->totalcount;
        }
    }
    return $max;
}

function edit_button($isadmin, $country="") {
    global $CFG, $USER;

    if ($isadmin) {
        if (!empty($USER->siteediting)) {
            $string = get_string("turneditingoff");
            $edit = "off";
        } else {
            $string = get_string("turneditingon");
            $edit = "on";
        }
        return "<table><tr><td><form target=\"_parent\" method=\"get\" action=\"$CFG->wwwroot/sites/index.php\">".
               "<input type=\"hidden\" name=\"edit\" value=\"$edit\" />".
               "<input type=\"hidden\" name=\"country\" value=\"$country\" />".
               "<input type=\"submit\" value=\"$string\" /></form></td>".
               "<td><form target=\"_parent\" method=\"get\" action=\"$CFG->wwwroot/sites/manage.php\">".
               "<input type=\"submit\" value=\"Check new registrations\" /></form></tr></table>";
    }
    return "";
}

function ismoodlesiteadmin() {
    global $CFG, $USER;

    //TODO: hehe, this should definitely use new capability from local_moodleorg plugin! (skodak)

    if (isset($USER->id)) {
        return (is_siteadmin()              // Normal admins
               or $USER->id == 1074    // Sean Keogh - s.keogh@pteppic.net
               or $USER->id == 1519    // Sergio Alfaro - sergio@alfaro.cl
               or $USER->id == 1323    // Bernard Boucher - bernard.boucher@cjonquiere.qc.ca
               or $USER->id == 3923    // Koen Roggemans - koen@roggemans.net
               or $USER->id == 5382    // Ralf Hilgenstock - rh@dialoge.net
               or $USER->id == 5514    // Jon Bolton - jon.bolton@gmail.com
               or $USER->id == 13679   // Toshihiro Kita - moodle@t-kita.net
               or $USER->id == 15677   // Mark Stevens - mstevens@aus.edu
               or $USER->id == 17978   // Clausia Antoneli - clamaan@gmail.com
               or $USER->id == 23713   // Samuli Karevaara - samuli.karevaara@lamk.fi
               or $USER->id == 24152   // Helen Foster - helen@moodle.org
               or $USER->id == 39680   // Julian Ridden - julian@moodle.com.au
               or $USER->id == 40774   // Don Schwartz - schwartzie@gmail.com
               or $USER->id == 51473   // Anthony Borrow - aborrow@jesuitcp.org
               or $USER->id == 53644   // Philippe Verdenal - pverdenal@online.fr
               or $USER->id == 104159  // Dan Poltowski - d.poltawski@lancaster.ac.uk
               or $USER->id == 140206  // Ken Wilson - kaw@wilberforce.ac.uk
               or $USER->id == 156867  // Phillip Lynch - Lists@Eapop.com.au
               or $USER->id == 6292    //Peter Sereinigg - psinfo@act2win.com
               or $USER->id == 865279
       or $USER->id == 2942);  // Jeff Watkins - jwatkins@classroomrevolution.com
    } else {
        return is_siteadmin();
    }
}

/**
 * Can be used to print a list into the page
 *
 * <b>Creating the print list object</b>
 * 
 * In order to create a list object you simply need to instatiate a stdClass object
 * and set the required properties as detailed below.
 *
 * <b>$list->style</b> <i>optional</i> Sets the style of the list this defaults to <b>ul</b> but can
 * be set to <b>ol</b> if you want an ordered list instead
 *
 * <b>$list->heading</b> <i>optional</i> Sets a heading before the list and should be a string
 *
 * <b>$list->data</b> Is an array of strings to display in the list, nested lists
 * are supported please read below for information on how to print nested lists.
 *
 * <b>$list->printanchors</b> <i>optional</i> If set to true an alphabetical anchor line is printed
 * at the top of the list allowing the user to <i>jump</i> to a particular letter.
 * 
 * <b>$list->printanchorsbothends</b> <i>optional</i> If set to true and printanchors set to true then
 * this prints the alphabetical anchor line at the bottom of the list as well
 *
 * <code>
 * $list = new stdClass;
 * $list->style = ''
 * $list->heading = 'Learn to count';
 * $list->data = Array('one','two','three','four','five');
 * print_list($list);
 * </code>
 *
 * <b>Creating nested lists</b>
 *
 * If you want to creata a nested list you simply need to create the second list
 * object and add it to the data array in the appropriate place.
 * 
 * The following option is also available and allows you to print a string before
 * the nested list.
 * 
 * <b>$list->title</b> <i>optional</i> Is the string to print before the nested list
 * <code>
 * $list = new stdClass;
 * $list->style = ''
 * $list->heading = 'Learn to count';
 * $list->data = Array();
 * $list->data[] = 'one';
 * $list->data[] = 'two';
 * $sublist = new stdClass;
 * $sublist->title = 'three';
 * $sublist->data = Array('a','b','c','d','e');
 * $list->data[] = $sublist;
 * $list->data[] = 'four';
 * $list->data[] = 'five';
 * print_list($list);
 * </code>
 *
 * @param stdClass $list
 * @param bool $return If set to true HTML is returned rather than printed
 * @param bool $disableanchors Only used for recursion don't bother setting
 */
function print_list($list, $return=false, $disableanchors=false) {
    $html = '';
    $style = 'ul';
    if (isset($list->style) && $list->style!='ul') {
        $style = 'ol';
    }
    if (isset($list->heading) && !empty($list->heading)) {
        $html .= sprintf("<h3 class='headingblock header'>%s</h3>\n", $list->heading);
    }
    if (isset($list->printanchors) && $list->printanchors===true && $disableanchors===false) {
        $html .= "<p>#PRINTANCHORS#</p>";
        $anchors = Array('#'=>0,'A'=>0,'B'=>0,'C'=>0,'D'=>0,'E'=>0,'F'=>0,'G'=>0,'H'=>0,'I'=>0,'J'=>0,'K'=>0,'L'=>0,'M'=>0,'N'=>0,'O'=>0,'P'=>0,'Q'=>0,'R'=>0,'S'=>0,'T'=>0,'U'=>0,'V'=>0,'W'=>0,'X'=>0,'Y'=>0,'Z'=>0);
    } else {
        $list->printanchors = false;
    }
    $html .= sprintf("<%s style='list-style:none;list-spacing:10px;'>\n", $style);
    foreach ($list->data as $line) {
        if (is_object($line) && get_class($line)=='stdClass') {
            if (isset($line->title)&&!empty($line->title)) {
                $line = $line->title."<br />".print_list($line, true, true);
            } else {
                $line = print_list($line, true, true);
            }
        } else if ($list->printanchors===true) {
            if (preg_match('/^(\s*<[^>]+>\s*)*([a-zA-Z])/',$line, $m)) {
                $firstletter = strtoupper($m[2]);
            } else {
                $firstletter = '#';
            }
            if ($anchors[$firstletter]===0) {
                $line = "<a nohref='nohref' name='$firstletter'></a>".$line;
            }
            $anchors[$firstletter]++;
        }
        $html .= sprintf("<li>%s</li>\n", $line);
    }
    $html .= sprintf("</%s>\n", $style);

    if ($list->printanchors) {
        if (isset($list->printanchorsbothends) && $list->printanchorsbothends===true) {
            $html .= "<p>#PRINTANCHORS#</p>";
        }
        $anchorlinks = Array();
        foreach ($anchors as $letter=>$count) {
            if ($count>0) {
                $anchorlinks[] = "<a href='#$letter' title='$count'>$letter</a>";
            } else {
                $anchorlinks[] = "$letter";
            }
        }
        $html = str_replace('#PRINTANCHORS#',join(' ', $anchorlinks), $html);
    }
    if ($return===true) {
        return $html;
    } else {
        echo $html;
        return true;
    }
}

function linkcheck($url) {
    $return = false;

    $urlArray = parse_url($url);
    if (empty($urlArray['port'])) $urlArray['port'] = "80";
    if (empty($urlArray['path'])) $urlArray['path'] = "/";

    $sock = @fsockopen($urlArray[host], $urlArray[port], $errnum, $errstr, 10);

    if (!$sock) {
        $return = false;

    } else {
        $dump = "HEAD ".$urlArray['path']." HTTP/1.1\r\n";
        $dump .= "User-Agent: Moodle.org Link Checker (http://moodle.org/sites/)\r\n";
        $dump .= "Host: $urlArray[host]\r\nConnection: close\r\n";
        $dump .= "Connection: close\r\n\r\n";
        fputs($sock, $dump);
        while ($str = fgets($sock, 1024)) {
            $return = true;
        }
        fclose($sock);
    }

    return $return;
}

function vote_for_site($siteid, $votemodifier) {
    die('NOT CONVERTED YET!');
    global $USER;
    $message = false;
    if ($site = get_record('registry', 'id', $siteid)) {  // site exists
        $country = $site->country;
        if (record_exists('registry_votes', 'userid', $USER->id, 'siteid', $site->id)) {
            $message = notify('You have already voted for "'.s($site->name).'"', 'notifyproblem', 'center', true);
        } else {
            $coolsite = new Object;
            $coolsite->id = $site->id;
            $coolsite->cool = $site->cool + $votemodifier;
            $coolsite->cooldate = time();
            if (update_record('registry', $coolsite)) {
                $vote = new Object;
                $vote->userid = $USER->id;
                $vote->siteid = $site->id;
                $vote->vote = $votemodifier;
                $vote->timevoted = time();
                if (insert_record('registry_votes', $vote)) {
                    if ($votemodifier==1) {
                        $message = notify('Your positive feeling for "'.s($site->name).'" has been recorded', 'notifysuccess', 'center', true);
                    } else {
                        $message = notify('Your negative feeling against "'.s($site->name).'" has been recorded', 'notifyproblem', 'center', true);
                    }
                }
            }
        }
    }
    return $message;
}
