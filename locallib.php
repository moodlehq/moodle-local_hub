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
 * Gets mapping on local moodleorg for feeds.
 * @global type $SESSION
 * @global type $DB
 * @param type $forcelang
 * @param int $forcepop try to pop off a number of dependency langs (we won't pop off the first 'en')
 * @return type
 */
function local_moodleorg_get_mapping($forcelang = false, $forcepop = null) {
    global $SESSION, $DB;

    if ($forcelang) {
        // Language has been forced by params.
        $userlang = $forcelang;
    } else {
        // Get the users current lang.
        $userlang = isset($SESSION->lang) ? $SESSION->lang : 'en';
    }

    // We will to english, unless a mapping is found.
    $lang = null;

    // Get the depdencies of the users lang and see if a mapping exists
    // for the current language or its parents..
    $langdeps = get_string_manager()->get_language_dependencies($userlang);

    // pop off some , we're probably searching for a higher lang (for posts/content there).
    if ($forcepop) {
        for ($x=0; $x<$forcepop; $x++) {
            array_pop($langdeps);
        }
    }

    // Add to english to the start of the array as get_language_dependencies() goes
    // in least specific order first.
    array_unshift($langdeps, 'en');

    list($insql, $inparams) = $DB->get_in_or_equal($langdeps);
    $sql = "SELECT lang, courseid, scaleid FROM {moodleorg_useful_coursemap} WHERE lang $insql";
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

// put back Dan's frontpage classes here instead of being in theme as its breaking stuff.
// frontpagefeeds() in theme_moodleorgcleaned now takes care of avoiding the frontpageinclude hack.
/**
 * Frontpage column of information to end up as a ul .
 */
abstract class frontpage_column
{
    /** The number of items to show on the front page */
    const MAXITEMS = 4;
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
     * Generate the link to display more content
     *
     * @return string the more info link
     */
    abstract protected function more_link();

    /**
     * RSS URL to view the the content via RSS
     *
     * @return string the more info link
     */
    abstract protected function rss_url();

    /**
     * A name to use with css (or strings)
     *
     * @return string css class name
     */
    public function name() {
        return substr(get_class($this), 17);
    }

    /**
     * Returns the course object of this lang codes mapping
     *
     * @return stdClass course object from the database
     * @throws exception if mapping/course doesn't exist.
     */
    protected function get_course($mapping = null) {
        global $DB;

        if (is_null($mapping)) {
            $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $this->mapping->lang), '*', MUST_EXIST);
        }
        $course = $DB->get_record('course', array('id' => $mapping->courseid), '*', MUST_EXIST);
        return $course;
    }

    protected function get_cache() {
        return cache::make('local_moodleorg', 'frontpagecolumn'); //defined in local_moodleorg/db/caches.php
    }

    /**
     * Get the items for this column. Will get from cache or generate
     * and store from cache if it doesn't exist.
     *
     * @return array of li items to be displayed/cached.
     */
    protected function get() {
        $cache = $this->get_cache();
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

        $cache = $this->get_cache();
        $key = $this->cache_key();
        $cache->set($key, $content);
    }

    /**
     * Generates the HTML for display of the content
     *
     * @return string the html content
     */
    public function output() {
        global $OUTPUT;

        $objects = $this->get();

        $o = '';
        $o.= html_writer::start_tag('ul', array('class'=>'media-list'));
        foreach ($objects as $obj) {
            $o.= $OUTPUT->frontpage_li($obj, '');
        }
        $o.= html_writer::end_tag('ul');

        $o.= html_writer::empty_tag('hr');

//        if ($rsslink = $this->rss_url()) {
//            $o.= html_writer::link($rsslink, $OUTPUT->pix_icon('i/rss', 'subscribe by rss'));
//        }
        if ($morelink = $this->more_link()) {
            $o.= ' '.$morelink;
        }

        return $o;
    }
        public function output_row() {
        global $OUTPUT;

        $objects = $this->get();
        $span = 'span'. (int)12/self::MAXITEMS;
        $o = '';
        $o.= html_writer::start_tag('ul', array('class'=>'media-list'));

        $o .= html_writer::start_tag('li', array('class' => 'media heading '. $span));
        $o .= $OUTPUT->heading(get_string('feed_'. $this->name(), 'theme_moodleorgcleaned'), 2, 'feedheading');
        $o .= html_writer::div($this->rsslink(), 'rsslink');
        $o .= html_writer::start_div('detailoverviews');
        $o .= html_writer::div($this->morelink(), 'morelink');
        $o .= html_writer::end_div();
        $o .= html_writer::end_tag('li');

        foreach ($objects as $obj) {
            $o.= $OUTPUT->frontpage_li($obj, $span);
        }
        $o.= html_writer::end_tag('ul');

        return $o;
    }

    public function rsslink() {
        global $OUTPUT;
        if ($rsslink = $this->rss_url()) {
            return html_writer::link($rsslink, $OUTPUT->pix_icon('i/rss', 'subscribe by rss'));
        }
    }
    public function morelink() {
        global $OUTPUT;
        if ($morelink = $this->more_link()) {
            return $morelink;
        }
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
        foreach (get_all_user_name_fields() as $addname) {
            $postuser->$addname = $post->$addname;
        }

        $by = new stdClass();
        $by->name = fullname($postuser);
        $by->date = userdate($post->modified, get_string('strftimedaydate', 'core_langconfig'));

        $link = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
        $link->set_anchor('p'.$post->id);

        $item = new stdClass;
        $item->image = $OUTPUT->user_picture($postuser, array('courseid' => $course->id));
        $item->link = html_writer::link($link, s($post->subject));
        $item->smalltext = html_writer::span(get_string('bynameondate_by', 'local_moodleorg'), 'by').
                html_writer::span($by->name, 'name'). html_writer::span(get_string('bynameondate_dash', 'local_moodleorg'), 'dash').
                html_writer::span($by->date, 'date');
        return $item;
    }
}

class frontpage_column_news extends frontpage_column_forumposts
{
    const MAXITEMS=3;
    protected function cache_key() {
        return 'news_'. current_language();
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
            $item = $this->item_from_post($post, $SITE);
            $item->image = '';
            $items[] = $item;
        }
        return $items;
    }

    protected function more_link() {
        global $CFG, $SITE;

        //TODO: SHOULD BE CACHING THE FORUMID!
        require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return '';
        }

        $url = new moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        return html_writer::link($url, get_string('feed_news_more', 'theme_moodleorgcleaned'));
    }

    protected function rss_url() {
        global $CFG, $SITE;

        // Wow this is really ugly....
        require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this
        require_once($CFG->dirroot.'/lib/rsslib.php');

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return '';
        }

        // La la la.
        $modinfo = get_fast_modinfo($SITE);
        $cm = $modinfo->instances['forum'][$forum->id];
        $context = context_module::instance($cm->id);
        $user = guest_user();

        return rss_get_url($context->id, $user->id, 'mod_forum', $forum->id);
    }
}


class frontpage_column_events extends frontpage_column
{
    protected function cache_key() {
        return 'events_'.current_language();
    }

    protected function generate() {
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->dirroot.'/calendar/lib.php');

        $course = $this->get_course();

        // Preload course context dance..
        $select = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $join = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $sql = "SELECT c.* $select
            FROM {course} c
            $join
            WHERE EXISTS (SELECT 1 FROM {event} e WHERE e.courseid = c.id)
            AND c.id = :courseid";
        $courses = $DB->get_records_sql($sql, array('contextlevel' => CONTEXT_COURSE, 'courseid' => $course->id));
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

    protected function more_link() {
        $url = new moodle_url('calendar/view.php'); //, array('view' => 'month'));
        return html_writer::link($url, 'Calendar entries');
    }

    protected function rss_url() {
        global $CFG, $USER, $DB, $OUTPUT;
        // No RSS feed, provide iCal if possible.
        if (isloggedin()) {
            $authtoken = sha1($USER->id . $DB->get_field('user', 'password', array('id'=>$USER->id)) . $CFG->calendar_exportsalt);
            $link = new moodle_url('/calendar/export_execute.php', array('preset_what'=>'all', 'preset_time'=>'recentupcoming', 'userid' => $USER->id, 'authtoken'=>$authtoken));
            return $link;
        } else {
            $authtoken = sha1('1' . $DB->get_field('user', 'password', array('id'=>1)) . $CFG->calendar_exportsalt);
            $link = new moodle_url('/calendar/export_execute.php', array('preset_what'=>'all', 'preset_time'=>'recentupcoming', 'userid' => 1, 'authtoken'=>$authtoken));
            return $link;
        }
    }
    public function rsslink() {
        global $OUTPUT;
        if ($rsslink = $this->rss_url()) {
            return html_writer::link($rsslink, $OUTPUT->pix_icon('i/rss', 'download calendar iCal file'));
        }
    }
}

class frontpage_column_useful extends frontpage_column_forumposts
{
    protected function cache_key() {
        return 'useful_'.current_language();
    }

    protected function generate() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/rating/lib.php');

        $course = $this->get_course();

        // Set up the ratings information that will be the same for all posts.
        $ratingoptions = new stdClass();
        $ratingoptions->component = 'mod_forum';
        $ratingoptions->ratingarea = 'post';
        $ratingoptions->userid = $CFG->siteguest;
        $rm = new rating_manager();

        $rs = $this->getposts($course);

        $rsscontent = '';
        $fullcontents = '';
        $frontcontent = array();
        $frontpagecount = 0;

        $rsscontent.= $this->rss_header();

        if (!empty($rs)) {
            foreach ($rs as $post) {
                 //function prints also which we capture via buffer
                list($frontcontentbit, $rsscontentbit, $fullcontentbit) = $this->processprintpost($post, $course, $rm, $ratingoptions);
                $rsscontent .= $rsscontentbit;
                if ($frontpagecount < self::MAXITEMS) {
                    $frontcontent[] = $frontcontentbit;
                    $frontpagecount++;
                }
                $fullcontents .= $fullcontentbit;
            }
            $rs->close();
        }

        // check number of posts, get more if not enough from other mappings.
        // no loop,just one look towards 'parent' langs for now
        if ($frontpagecount < self::MAXITEMS && $this->mapping->lang !== 'en') {
            $moremapping = local_moodleorg_get_mapping(false, 1);
            $anothercourse = $this->get_course($moremapping);
            $rs = $this->getposts($anothercourse);

            if (!empty($rs)) {
                foreach ($rs as $post) {
                     //function prints also which we capture via buffer
                    list($frontcontentbit, $rsscontentbit, $fullcontentbit) = $this->processprintpost($post, $anothercourse, $rm, $ratingoptions);
                     $rsscontent .= $rsscontentbit; //lets keep the content same for sanity.
                    if ($frontpagecount < self::MAXITEMS) {
                        $frontcontent[] = $frontcontentbit;
                        $frontpagecount++;
                    }
                    $fullcontents .= $fullcontentbit;
                }
                $rs->close();
            }
        }

        $rsscontent.= $this->rss_footer();
        $cache = $this->get_cache();
        $cache->set('useful_full_'.$this->mapping->lang, $fullcontents);
        $cache->set('rss_'.$this->mapping->lang, $rsscontent);

        return $frontcontent;
    }

    protected function getposts($course) {
        global $DB;
        $ctxselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = cm.id AND ctx.contextlevel = :contextlevel)";
        $userselect = user_picture::fields('u', null, 'uid');

        $params = array();
        $params['courseid'] = $course->id;
        $params['since'] = time() - (DAYSECS * 30);
        $params['cmtype'] = 'forum';
        $params['contextlevel'] = CONTEXT_MODULE;

        $noscalesql = "SELECT fp.*, fd.forum $ctxselect, $userselect
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

        if (!empty($this->mapping->scaleid)) {
            // Check some forums with the scale exist..
            $negativescaleid = $this->mapping->scaleid * -1;
            $forumids = $DB->get_records('forum', array('course'=>$course->id, 'scale'=>$negativescaleid), '', 'id');
            if (empty($forumids)) {
                debugging("No forums found for {$this->mapping->lang} with scale {$this->mapping->scaleid}", DEBUG_DEVELOPER);
                // forum admin may have removed the scale, try without scale (latest posts) ..
                $sql = $noscalesql;
            } else {
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
            }
        } else {
            $sql = $noscalesql;
        }


        return $DB->get_recordset_sql($sql, $params, 0, 30);
    }

    protected function processprintpost($post, $course, $rm, $ratingoptions) {
        global $DB;

        $discussions = array();
        $forums = array();
        $cms = array();
        $rsscontent = '';
        context_helper::preload_from_record($post);

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
        $post->message = file_rewrite_pluginfile_urls($post->message, 'pluginfile.php', $cm->id, 'mod_forum', 'post', $post->id);
        $rsscontent.= html_writer::tag('description', 'by '.htmlspecialchars(fullname($post).' <br /><br />'.format_text($post->message, $post->messageformat)))."\n";
        $rsscontent.= html_writer::tag('guid', $postlink->out(false), array('isPermaLink'=>'true'))."\n";
        $rsscontent.= html_writer::end_tag('item')."\n";

        $frontcontentbit = $this->item_from_post($post, $course);

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

        ob_start();
        echo "<br /><br />";
        //add the ratings information to the post
        //Unfortunately seem to have do this individually as posts may be from different forums
        if ($forum->assessed != RATING_AGGREGATE_NONE) {
            $modcontext = context_module::instance($cm->id, MUST_EXIST);
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
        // the actual reason for buffer follows
        forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false, $fulllink);

        $fullcontentbit = ob_get_contents();
        ob_end_clean();
        return array($frontcontentbit, $rsscontent, $fullcontentbit);
    }

    public function get_rss() {
        $cache = $this->get_cache();
        $key = 'rss_'.$this->mapping->lang;
        if ($content = $cache->get($key)) {
            return $content;
        }

        $this->generate();
        if (!$content = $cache->get($key)) {
            throw new moodle_exception('cant get content');
        }

        return $content;
    }

    public function get_full_content() {
        $cache = $this->get_cache();
        $key = 'useful_full_'.$this->mapping->lang;
        if ($content = $cache->get($key)) {
            return $content;
        }

        $this->generate();
        if (!$content = $cache->get($key)) {
            throw new moodle_exception('cant get content');
        }

        return $content;
    }

    protected function more_link() {
        return html_writer::link(new moodle_url('/course/view.php', array('id' => $this->mapping->courseid)), 'More posts');
    }

    protected function rss_url() {
        return new moodle_url('/useful/rss.php', array('lang' => $this->mapping->lang));
    }

    private function rss_header() {
        $title = get_string('rsstitle', 'local_moodleorg');
        $description = get_string('rssdescription', 'local_moodleorg');
        $year = date("Y");

       return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>$title</title>
    <link>http://moodle.org/useful/</link>
    <description>$description</description>
    <generator>Moodle</generator>
    <copyright>&amp;#169; $year Moodle.org</copyright>
    <image>
      <url>http://moodle.org/pix/i/rsssitelogo.gif</url>
      <title>moodle</title>
      <link>http://moodle.org</link>
      <width>140</width>
      <height>35</height>
    </image>
EOF;
    }
    private function rss_footer() {
        return "</channel>\n</rss>";
    }
}

class frontpage_column_resources extends frontpage_column
{
    const FEEDURL = 'http://pipes.yahoo.com/pipes/pipe.run?_id=2a7f5e44ac0ae95e1fa10bc5ee09149e&_render=rss';
    protected function cache_key() {
        return 'resources_'. current_language();
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

    protected function more_link() {
        return null;
    }

    protected function rss_url() {
        return self::FEEDURL;
    }
}

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
        global $DB, $CFG;
        require_once($CFG->dirroot.'/cohort/lib.php');

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
 * Works out the particularly helpful moodlers across the whole site and returns
 * metadata about the phms.
 *
 * @param int $minposts the minimum number of posts to be counted as a PHM
 * @param int $minratings the minimum number of ratings to be coutned as a PHM
 * @param int $minraters the minimum number of raters
 * @param float $minratio the ratio of posts to 'useful' ratings to be coutned as phm.
 *
 * @return array of phms indexed by userid. Containing array('totalratings' => X 'postcount' => Y, 'raters' => Z)
 */
function local_moodleorg_get_phms($minposts = 14, $minratings = 14, $minraters = 8, $minratio = 0.02) {
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

    return $phms;
}
