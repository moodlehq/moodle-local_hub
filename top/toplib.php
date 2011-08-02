<?php // Hack by Martin Dougiamas, November 2008

defined('MOODLE_INTERNAL') || die;

/// Library of useful functions and constants used in Moodle top pages only

function print_moodle_page($page, $content="", $topcourseid=0, $navlinks=array()) {
    print_moodle_page_top($page, $content, $navlinks);
    print_moodle_page_bottom($page, $topcourseid);
}

/* Prints the header and main content */
function print_moodle_page_top($page, $content="", $navlinks=array()) {

    global $CFG, $SESSION, $USER, $SITE, $ME, $PAGE, $OUTPUT, $DB;

    $PAGE->set_context(get_system_context());
    $PAGE->set_url('/'.$page);
    $PAGE->set_docs_path($page);
    $PAGE->set_pagelayout('standard');

    $CFG->pagetheme = 'moodleofficial';

    $topicons = array('about', 'support', 'community', 'development', 'downloads');

    foreach ($navlinks as $navlink) {
        if (empty($navlink['name'])) {
            continue;
        }
        if (empty($navlink['link'])) {
            $navlink['link'] = null;
        }
        $PAGE->navbar->add($navlink['name'], $navlink['link']);
    }

    if ($page) {
        $titlename = $pagename = get_string("name$page", 'moodle.org');
        $PAGE->navbar->add($pagename);
    } else {
        $navigation = 'home';
        $titlename = $pagename = 'open-source community-based learning tools';
    }

    $PAGE->set_title('Moodle.org: '.$titlename);
    $PAGE->set_heading($pagename);
    echo $OUTPUT->header();

    //print_header('Moodle.org: '.$titlename,   // title
                 //$pagename,                                          // heading
                 //$navigation,                                        // navigation
                 //'', '', true, '&nbsp;', user_login_string($SITE), false, '', false);


/// Print the actual main content first

    if (in_array($page, $topicons)) {
        echo '<img class="frontpagesectionimage" src="'.$CFG->wwwroot.'/theme/moodleofficial/pix/'.$page.'.gif" alt="" />';
    }

    /// Main page
    print_moodle_content($content);


    if ($page == 'news' || $page == 'security') {   // add main forum
        require_once($CFG->dirroot .'/mod/forum/lib.php');


        if ($page == 'news') {
            if (! $mainforum = forum_get_course_forum($SITE->id, 'news')) {
                print_error(get_string('errornomainnews', 'moodle.org'));
            }
            //$numarticles = $SITE->newsitems;
            $rss = 'http://moodle.org/rss/file.php/51/1b51bf7f3cab9689af042af1ff4a07f0/mod_forum/1/rss.xml';
            $numarticles = 10;
            $CFG->forum_longpost = 320000;
            echo $OUTPUT->heading($OUTPUT->pix_icon('news', 'news', 'theme').'<br />'.$mainforum->name);;
        } else {
            $mainforum = $DB->get_record('forum', array('id' => 996), '*', MUST_EXIST);
            $rss = 'http://moodle.org/rss/file.php/154821/1b51bf7f3cab9689af042af1ff4a07f0/mod_forum/996/rss.xml';
            $numarticles = 10;
            $CFG->forum_longpost = 320000;
            echo $OUTPUT->heading($mainforum->name);
        }

        if (!empty($USER->id)) {
            forum_set_return();
            if (forum_is_subscribed($USER->id, $mainforum)) {
                $subtext = get_string('unsubscribe', 'forum');
            } else {
                $subtext = get_string('subscribe', 'forum');
            }
            echo '<div class="subscribelink"><a href="'.$CFG->wwwroot.'/mod/forum/subscribe.php?id='.$mainforum->id.'">'.$subtext.'</a></div>';
        }
        echo '<div class="rsslink">'.
            '<a href="'.$rss.'">'.
            '<img alt="RSS" title="RSS" src="http://moodle.org/pix/i/rss.gif"/></a>'.
            '</div>';

        forum_print_latest_discussions($SITE, $mainforum, $numarticles, 'plain', 'p.modified DESC');
    }
    // Layout table remains unclosed!
}

/* Prints the blocks and footer */
function print_moodle_page_bottom($page, $topcourseid=0) {

    global $CFG, $SESSION, $USER, $SITE, $ME, $DB, $OUTPUT, $PAGE;

/// Set up the page
    if (!$topcourseid) {
        $topcourseid = 1003;  // default blocks
    }

    $CFG->pagepath = '';
    echo $OUTPUT->footer();
}


function print_moodle_content($content) {
    if (is_array($content)) {
        foreach ($content as $string) {
            if (substr($string, 0, 5) == 'http:') {
                $link = explode('|', $string);
                echo '<div class="moodletop link"><span class="arrow sep">&#x25BA;</span> <a href="'.$link[0].'">'.get_string($link[1], 'moodle.org').'</a></div>';
            } else if (substr($string, -5, 5) == 'intro') {
                echo "<div class=\"moodletop intro $string\">".get_string($string, 'moodle.org')."</div>\n";
            } else if (substr($string, -5, 5) == 'title') {
                echo "<h3 class=\"moodletop $string\">".get_string($string, 'moodle.org')."</h3>\n";
            } else if (substr($string, 0, 1) == '<') {
                echo $string."\n";
            } else {
                echo "<div class=\"moodletop $string\">".get_string($string, 'moodle.org')."</div>\n";
            }
        }
    } else {
        echo $content;
    }
}

