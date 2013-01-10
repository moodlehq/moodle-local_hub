<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library for moodle.org.
 *
 * @package local_moodleorg
 * @copyright 2013 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Frontpage column of information to end up as a ul .
 */
abstract class frontpage_column
{
    /** The number of items to show on the front page */
    const MAXITEMS = 6;
    /** @var string The mapping record */
    protected $mapping = null;

    /**
     * Constructor.
     */
    public function __construct($mapping) {
        $this->mapping = $mapping;
    }

    /**
     * Define the key to be used for storing this infromation in
     * the cache.
     * @return string the key
     */
    abstract protected function cache_key();

    /**
     * Generate the content to be displayed in this column.
     *
     * @return array of li items to be displayed/cached.
     */
    abstract protected function generate();

    /**
     * Returns the course object of this lang codes mapping
     *
     * @return stdClass course object from the database
     * @throws exception if mapping/course doesn't exist.
     */
    protected function get_course() {
        global $DB;

        $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $this->mapping->lang), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $mapping->courseid), '*', MUST_EXIST);
        return $course;
    }

    /**
     * Get the items for this column. Will get from cache or generate
     * and store from cache if it doesn't exist.
     *
     * @return array of li items to be displayed/cached.
     */
    protected function get() {
        $cache = cache::make('local_moodleorg', 'frontpagecolumn');
        $key = $this->cache_key();
        if ($content = $cache->get($key)) {
            return $content;
        }

        $content = $this->generate();
        $cache->set($key, $content);
        return $content;
    }

    /**
     * Force the update of the content.
     * @return void
     */
    public function update() {
        $content = $this->generate();
        $cache->set($key, $content);
    }

    /**
     * Generates the HTML for display of the content
     *
     * @return string the html content
     */
    public function output() {
        $objects = $this->get();

        $o = '';
        $o.= html_writer::start_tag('ul', array('style'=>'list-style-type: none; padding:0; margin:0;'));
        foreach ($objects as $obj) {
            $o.= local_moodleorg_frontpage_li($obj);
        }
        $o.= html_writer::end_tag('ul');
        return $o;
    }
}

/**
 * Specialised from page column for displaying forum content
 */
abstract class frontpage_column_forumposts extends frontpage_column
{
    /**
     * Creates an object from a li for the forum
     *
     * @param stdClass $post record frm the database
     * @param stdClass $course record frm the database
     * @return stdClass li object
     */
    protected function item_from_post($post, $course) {
        global $OUTPUT;

        $postuser = new stdClass;
        $postuser->id        = $post->userid;
        $postuser->firstname = $post->firstname;
        $postuser->lastname  = $post->lastname;
        $postuser->imagealt  = $post->imagealt;
        $postuser->picture   = $post->picture;
        $postuser->email     = $post->email;

        $by = new stdClass();
        $by->name = fullname($postuser);
        $by->date = userdate($post->modified, get_string('strftimedaydate', 'core_langconfig'));

        $link = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
        $link->set_anchor('p'.$post->id);

        $item = new stdClass;
        $item->image = $OUTPUT->user_picture($postuser, array('courseid' => $course->id));
        $item->link = html_writer::link($link, s($post->subject));
        $item->smalltext = get_string('bynameondate', 'forum', $by);
        return $item;
    }
}

class frontpage_column_news extends frontpage_column_forumposts
{
    protected function cache_key() {
        return 'news';
    }

    protected function generate() {
        global $CFG, $SITE, $OUTPUT;

        require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return array();
        }

        $modinfo = get_fast_modinfo($SITE);
        if (empty($modinfo->instances['forum'][$forum->id])) {
            return array();
        }
        $cm = $modinfo->instances['forum'][$forum->id];

        $posts = forum_get_discussions($cm, 'p.modified DESC', false, -1, self::MAXITEMS);

        $items = array();
        foreach ($posts as $post) {
            $items[] = $this->item_from_post($post, $SITE);
        }
        return $items;
    }
}


class frontpage_column_events extends frontpage_column
{
    protected function cache_key() {
        return 'events_'.$this->mapping->lang;
    }

    protected function generate() {
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->dirroot.'/calendar/lib.php');

        $course = $this->get_course();

        // Preload course context dance..
        list ($select, $join) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
        $sql = "SELECT c.* $select
            FROM {course} c
            $join
            WHERE EXISTS (SELECT 1 FROM {event} e WHERE e.courseid = c.id)
            AND c.id = ?";
        $courses = $DB->get_records_sql($sql, array($course->id));
        foreach ($courses as $course) {
            context_helper::preload_from_record($course);
        }

        list($courses, $group, $user) = calendar_set_filters($courses);
        $events = calendar_get_upcoming($courses, $group, $user, 365, self::MAXITEMS);


        // Define the base url for clendar linking..
        $baseurl = new moodle_url('/calendar/view.php', array('view' => 'day', 'course'=> $course->id));

        $items = array();
        foreach ($events as $event) {
            $ed = usergetdate($event->timestart);
            $linkurl = calendar_get_link_href($baseurl, $ed['mday'], $ed['mon'], $ed['year']);
            $linkurl->set_anchor('event_'.$event->id);

            $obj = new stdClass;
            $obj->image = $OUTPUT->pix_icon('i/siteevent', get_string('globalevent', 'calendar'), 'moodle', array('style'=>'width:35px; height: 35px;'));
            $obj->link = html_writer::link($linkurl, $event->name);
            $obj->smalltext = userdate($event->timestart, get_string('strftimedaydate', 'core_langconfig'));
            $items[] = $obj;
        }
        return $items;
    }
}

class frontpage_column_useful extends frontpage_column_forumposts
{
    protected function cache_key() {
        return 'useful_'.$this->mapping->lang;
    }

    protected function generate() {
        global $DB, $CFG;

        $course = $this->get_course();

        // Set up the ratings information that will be the same for all posts.
        $ratingoptions = new stdClass();
        $ratingoptions->component = 'mod_forum';
        $ratingoptions->ratingarea = 'post';
        $ratingoptions->userid = $CFG->siteguest;
        $rm = new rating_manager();


        list($ctxselect, $ctxjoin) = context_instance_preload_sql('cm.id', CONTEXT_MODULE, 'ctx');
        $userselect = user_picture::fields('u', null, 'uid');

        $params = array();
        $params['courseid'] = $course->id;
        $params['since'] = time() - (DAYSECS * 30);
        $params['cmtype'] = 'forum';

        if (!empty($this->mapping->scaleid)) {
            // Check some forums with the scale exist..
            $negativescaleid = $this->mapping->scaleid * -1;
            $forumids = $DB->get_records('forum', array('course'=>$course->id, 'scale'=>$negativescaleid), '', 'id');
            if (empty($forumids)) {
                debugging("No forums found for {$this->mapping->langcode} with scale {$this->mapping->scaleid}", DEBUG_DEVELOPER);
                return array();
            }

            $params['scaleid'] = $negativescaleid;
            $sql = "SELECT fp.*, fd.forum $ctxselect, $userselect
                FROM {forum_posts} fp
                JOIN {user} u ON u.id = fp.userid
                JOIN {forum_discussions} fd ON fd.id = fp.discussion
                JOIN {course_modules} cm ON (cm.course = fd.course AND cm.instance = fd.forum)
                JOIN {modules} m ON (cm.module = m.id)
                $ctxjoin
                JOIN {rating} r ON (r.contextid = ctx.id AND fp.id = r.itemid AND r.scaleid = :scaleid)
                WHERE fd.course = :courseid
                AND m.name = :cmtype
                AND r.timecreated > :since
                GROUP BY fp.id, fd.forum, ctx.id, u.id
                ORDER BY MAX(r.timecreated) DESC";
        } else {
            $sql = "SELECT fp.*, fd.forum $ctxselect, $userselect
                FROM {forum_posts} fp
                JOIN {user} u ON u.id = fp.userid
                JOIN {forum_discussions} fd ON fd.id = fp.discussion
                JOIN {course_modules} cm ON (cm.course = fd.course AND cm.instance = fd.forum)
                JOIN {modules} m ON (cm.module = m.id)
                $ctxjoin
                WHERE fd.course = :courseid
                AND m.name = :cmtype
                AND fp.created > :since
                ORDER BY fp.created DESC";
        }


        $rs = $DB->get_recordset_sql($sql, $params, 0, 30);

        $discussions = array();
        $forums = array();
        $cms = array();
        $frontpagecount = 0;
        $rsscontent = '';
        $frontcontent = array();


        $rsscontent.= file_get_contents($CFG->dirroot.'/local/moodleorg/top/useful/rss-head.txt');

        // Start capturing output for /useful/ (hack)
        ob_start();
        foreach ($rs as $post) {

            context_instance_preload($post);

            if (!array_key_exists($post->discussion, $discussions)) {
                $discussions[$post->discussion] = $DB->get_record('forum_discussions', array('id'=>$post->discussion));
                if (!array_key_exists($post->forum, $forums)) {
                    $forums[$post->forum] = $DB->get_record('forum', array('id'=>$post->forum));
                    $cms[$post->forum] = get_coursemodule_from_instance('forum', $post->forum, $course->id);
                }
            }

            $discussion = $discussions[$post->discussion];
            $forum = $forums[$post->forum];
            $cm = $cms[$post->forum];

            $forumlink = new moodle_url('/mod/forum/view.php', array('f'=>$post->forum));
            $discussionlink = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
            $postlink = clone $discussionlink;
            $postlink->set_anchor('p'.$post->id);

            // First do the rss file
            $rsscontent.= html_writer::start_tag('item')."\n";
            $rsscontent.= html_writer::tag('title', s($post->subject))."\n";
            $rsscontent.= html_writer::tag('link', $postlink->out(false))."\n";
            $rsscontent.= html_writer::tag('pubDate', gmdate('D, d M Y H:i:s',$post->modified).' GMT')."\n";
            $rsscontent.= html_writer::tag('description', 'by '.htmlspecialchars(fullname($post).' <br /><br />'.format_text($post->message, $post->messageformat)))."\n";
            $rsscontent.= html_writer::tag('guid', $postlink->out(false), array('isPermaLink'=>'true'))."\n";
            $rsscontent.= html_writer::end_tag('item')."\n";


            if ($frontpagecount < self::MAXITEMS) {
                $frontcontent[] = $this->item_from_post($post, $course);
                $frontpagecount++;
            }

            // Output normal posts
            $fullsubject = html_writer::link($forumlink, format_string($forum->name,true));
            if ($forum->type != 'single') {
                $fullsubject .= ' -> '.html_writer::link($discussionlink->out(false), format_string($post->subject,true));
                if ($post->parent != 0) {
                    $fullsubject .= ' -> '.html_writer::link($postlink->out(false), format_string($post->subject,true));
                }
            }
            $post->subject = $fullsubject;
            $fulllink = html_writer::link($postlink, get_string("postincontext", "forum"));

            echo "<br /><br />";
            //add the ratings information to the post
            //Unfortunately seem to have do this individually as posts may be from different forums
            if ($forum->assessed != RATING_AGGREGATE_NONE) {
                $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
                $ratingoptions->context = $modcontext;
                $ratingoptions->items = array($post);
                $ratingoptions->aggregate = $forum->assessed;//the aggregation method
                $ratingoptions->scaleid = $forum->scale;
                $ratingoptions->assesstimestart = $forum->assesstimestart;
                $ratingoptions->assesstimefinish = $forum->assesstimefinish;
                $postswithratings = $rm->get_ratings($ratingoptions);

                if ($postswithratings && count($postswithratings)==1) {
                    $post = $postswithratings[0];
                }
            }
            forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false, $fulllink);

        }
        $rs->close();

        $rsscontent.= file_get_contents($CFG->dirroot.'/local/moodleorg/top/useful/rss-foot.txt');

        $cache = cache::make('local_moodleorg', 'usefulposts');
        $cache->set('useful_'.$this->mapping->langcode, ob_get_contents());
        ob_end_clean();
        $cache->set('rss_'.$this->mapping->langcode, $rsscontent);

        return $frontcontent;
    }
}

class frontpage_column_resources extends frontpage_column
{
    const FEEDURL = 'http://pipes.yahoo.com/pipes/pipe.run?_id=2a7f5e44ac0ae95e1fa10bc5ee09149e&_render=rss';
    protected function cache_key() {
        return 'resources';
    }

    protected function generate() {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        $feed = new moodle_simplepie(self::FEEDURL);
        $feeditems = $feed->get_items(0,self::MAXITEMS);

        $items = array();
        foreach ($feeditems as $item) {
            $title = $item->get_title();
            if (preg_match('/^Plugins: /', $title)) {
                $image = $OUTPUT->pix_icon('icon', 'Plugins', 'mod_lti', array('style'=>'width:35px; height: 35px'));
            } else if (preg_match('/^Jobs: /', $title)) {
                $image = $OUTPUT->pix_icon('icon', 'Jobs', 'mod_feedback', array('style'=>'width:35px; height: 35px'));
            } else if (preg_match('/^Courses: /', $title)) {
                $image = $OUTPUT->pix_icon('icon', 'Jobs', 'mod_imscp', array('style'=>'width:35px; height: 35px'));
            } else {
                $image = $OUTPUT->pix_icon('icon', 'Buzz', 'mod_label', array('style'=>'width:35px; height: 35px'));
            }

            $obj = new stdClass;
            $obj->image = $image;
            $obj->link = html_writer::link(new moodle_url($item->get_link()), $item->get_title());
            $obj->smalltext = userdate($item->get_date('U'), get_string('strftimedaydate', 'core_langconfig'));
            $items[] = $obj;
        }

        return $items;
    }
}

function local_moodle_get_mapping() {
    global $SESSION, $DB;

    // Get the users current lang.
    $userlang = isset($SESSION->lang) ? $SESSION->lang : 'en';

    // We will to english, unless a mapping is found.
    $lang = null;

    // Get the depdencies of the users lang and see if a mapping exists
    // for the current language or its parents..
    $langdeps = get_string_manager()->get_language_dependencies($userlang);

    // Add to english to the start of the array as get_language_dependencies() goes
    // in least specific order first.
    array_unshift($langdeps, 'en');

    list($insql, $inparams) = $DB->get_in_or_equal($langdeps);
    $sql = "SELECT lang, courseid FROM {moodleorg_useful_coursemap} WHERE lang $insql";
    $mappings = $DB->get_records_sql($sql, $inparams);

    $mapping = null;
    while (!empty($langdeps) and empty($mapping)) {
        $thislang = array_pop($langdeps);

        if (isset($mappings[$thislang])) {
            $mapping = $mappings[$thislang];
        }
    }
    return $mapping;
}

function local_moodleorg_frontpage_li($obj) {
    $o = '';
    $o.= html_writer::start_tag('li')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'float: left; margin: 3px;'))."\n";
    $o.= $obj->image."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= html_writer::start_tag('div', array('style'=>'display:block;'))."\n";
    $o.= $obj->link . "<br />\n";
    $o.= html_writer::start_tag('span', array('style'=>'font-size:0.8em; color: grey;'));
    $o.= $obj->smalltext;
    $o.= html_writer::end_tag('span')."\n";
    $o.= html_writer::end_tag('div')."\n";
    $o.= '<br />';
    $o.= html_writer::end_tag('li')."\n";
    return $o;
}
