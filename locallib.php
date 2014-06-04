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
 * Picks the most appropriate course mapping for feeds the current user
 *
 * @todo use MUC instead of fetching mappings from the database over and over again
 * @param string $forcelang force the given language code instead of the detected one
 * @param int $forcepop try to pop off a number of dependency langs (we won't pop off the first 'en')
 * @return stdClass|null the course mapping or null of not found
 */
function local_moodleorg_get_mapping($forcelang = false, $forcepop = null) {
    global $SESSION, $DB;

    if ($forcelang) {
        // Language has been forced by params.
        $userlang = $forcelang;
    } else {
        // Get the users current lang.
        $userlang = isset($SESSION->lang) ? $SESSION->lang : 'en';
        if ($userlang === 'es_mx') { //hardcode mapping lookups for es_mx to es.
            $userlang = 'es';
        }
    }

    // We will default to English, unless a mapping is found.
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

    // Prepend English to the start of the array as get_language_dependencies() goes
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

/**
 * Represents a frontpage block to display a feed of information (such as
 * site news, useful posts, events and resources).
 */
abstract class frontpage_column {

    /** The number of items to show on the front page */
    const MAXITEMS = 4;

    /** @var string the associated mapping record */
    protected $mapping = null;

    /**
     * Constructor.
     *
     * @param stdClass $mapping optional course mapping if needed
     */
    public function __construct($mapping = null) {
        $this->mapping = $mapping;
    }

    /**
     * Returns items for this column
     *
     * Uses cached items if they are available.
     *
     * @return array of items to be displayed
     */
    public function get() {

        if (debugging('', DEBUG_DEVELOPER)) {
            // Do not rely on cached structures in developer mode.
            $skipcache = true;
        }

        $cache = $this->get_cache();
        $key = $this->cache_key();

        // If we have a valid cache, use it.
        if (empty($skipcache) and ($content = $cache->get($key))) {
            $content->source = 'cache/'.$key;
            return $content;
        }

        // Otherwise re-generate the contents.
        $content = $this->generate();
        $cache->set($key, $content);

        $content->source = 'fresh/'.$key;
        return $content;
    }

    /**
     * Force the update of the content
     */
    public function update() {

        $content = $this->generate();
        $cache = $this->get_cache();
        $key = $this->cache_key();
        $cache->set($key, $content);
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
     * @return moodle_url|string|null URL to display more data or null if not available
     */
    abstract protected function more_url();

    /**
     * @return moodle_url|string|null URL to display info via RSS or null if not available
     */
    abstract protected function rss_url();

    /**
     * Returns the course object of this lang codes mapping
     *
     * @return stdClass course object from the database
     * @throws exception if mapping/course doesn't exist.
     */
    protected function get_mapped_course($mapping = null) {
        global $DB;

        if (is_null($mapping)) {
            $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $this->mapping->lang), '*', MUST_EXIST);
        }

        $course = $DB->get_record('course', array('id' => $mapping->courseid), '*', MUST_EXIST);

        return $course;
    }

    /**
     * @return cache frontpagecolumn cache defined in local_moodleorg/db/caches.php
     */
    protected function get_cache() {
        return cache::make('local_moodleorg', 'frontpagecolumn');
    }
}


/**
 * Site news (aka Announcements)
 */
class frontpage_column_news extends frontpage_column {

    protected function cache_key() {
        return 'news_'. current_language();
    }

    protected function generate() {
        global $CFG, $SITE;
        require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

        // Structure to be returned
        $data = (object)array(
            'timegenerated' => time(),
            'rssurl' => (string) $this->rss_url(),
            'moreurl' => (string) $this->more_url(),
            'items' => array(),
        );

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return $data;
        }

        $modinfo = get_fast_modinfo($SITE);
        if (empty($modinfo->instances['forum'][$forum->id])) {
            return $data;
        }
        $cm = $modinfo->instances['forum'][$forum->id];

        $posts = forum_get_discussions($cm, 'p.modified DESC', false, -1, self::MAXITEMS);

        $isfirstpost = true;
        foreach ($posts as $post) {
            //$url = new moodle_url('/mod/forum/discuss.php', array('d' => $post->discussion));
            $url = new moodle_url('/news/');
            if ($isfirstpost) {
                $isfirstpost = false;
            } else {
                $url->set_anchor('p'.$post->id);
            }
            $data->items[] = (object) array(
                'title' => s($post->subject),
                'date' => userdate($post->modified, get_string('strftimedaydate', 'core_langconfig')),
                'url' => $url->out(),
            );
        }

        return $data;
    }

    protected function more_url() {
        global $CFG, $SITE;
        require_once($CFG->dirroot.'/mod/forum/lib.php');

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return '';
        }

        #return new moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        return new moodle_url('/news/');
    }

    protected function rss_url() {
        global $CFG, $SITE;
        require_once($CFG->dirroot.'/mod/forum/lib.php');
        require_once($CFG->dirroot.'/lib/rsslib.php');

        if (!$forum = forum_get_course_forum($SITE->id, 'news')) {
            return '';
        }

        $modinfo = get_fast_modinfo($SITE);
        $cm = $modinfo->instances['forum'][$forum->id];
        $context = context_module::instance($cm->id);
        $user = guest_user();

        return rss_get_url($context->id, $user->id, 'mod_forum', $forum->id);
    }
}


/**
 * Events
 */
class frontpage_column_events extends frontpage_column {

    protected function cache_key() {
        return 'events_'.current_language();
    }

    protected function generate() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/calendar/lib.php');

        $course = $this->get_mapped_course();

        // Preload course contexts
        $sql = "SELECT c.*, ".context_helper::get_preload_record_columns_sql('ctx')."
                  FROM {course} c
             LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                 WHERE EXISTS (SELECT 1 FROM {event} e WHERE e.courseid = c.id)
                       AND c.id = :courseid";
        $courses = $DB->get_records_sql($sql, array('contextlevel' => CONTEXT_COURSE, 'courseid' => $course->id));
        foreach ($courses as $course) {
            context_helper::preload_from_record($course);
        }

        list($courses, $group, $user) = calendar_set_filters($courses);
        $events = calendar_get_upcoming($courses, $group, $user, 365, self::MAXITEMS);

        // Define the base url for calendar linking
        $baseurl = new moodle_url('/calendar/view.php', array('view' => 'day', 'course'=> $course->id));

        // Structure to be returned
        $data = (object)array(
            'timegenerated' => time(),
            'rssurl' => (string) $this->rss_url(),
            'moreurl' => (string) $this->more_url(),
            'items' => array(),
        );

        foreach ($events as $event) {
            $ed = usergetdate($event->timestart);
            $linkurl = calendar_get_link_href($baseurl, $ed['mday'], $ed['mon'], $ed['year']);
            $data->items[] = (object) array(
                'title' => s($event->name),
                'url'=> (string) $linkurl,
                'date' => userdate($event->timestart, get_string('strftimedaydate', 'core_langconfig')),
            );
        }

        return $data;
    }

    protected function more_url() {
        return new moodle_url('calendar/view.php');
    }

    /**
     * We have no RSS feed here, provide iCal if possible
     */
    protected function rss_url() {
        global $CFG, $USER, $DB;

        if (isloggedin()) {
            $authtoken = sha1($USER->id . $DB->get_field('user', 'password', array('id'=>$USER->id)) . $CFG->calendar_exportsalt);
            $link = new moodle_url('/calendar/export_execute.php', array(
                'preset_what' => 'all',
                'preset_time' => 'recentupcoming',
                'userid' => $USER->id,
                'authtoken'=>$authtoken,
            ));
            return $link;

        } else {
            $authtoken = sha1('1' . $DB->get_field('user', 'password', array('id'=>1)) . $CFG->calendar_exportsalt);
            $link = new moodle_url('/calendar/export_execute.php', array(
                'preset_what' => 'all',
                'preset_time' => 'recentupcoming',
                'userid' => 1,
                'authtoken' => $authtoken,
            ));
            return $link;
        }
    }
}


/**
 * Useful posts
 */
class frontpage_column_useful extends frontpage_column {

    protected function cache_key() {
        return 'useful_'.current_language();
    }

    protected function generate() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/rating/lib.php');

        $course = $this->get_mapped_course();

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
            $anothercourse = $this->get_mapped_course($moremapping);
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

        return (object)array(
            'timegenerated' => time(),
            'rssurl' => (string) $this->rss_url(),
            'moreurl' => (string) $this->more_url(),
            'items' => $frontcontent,
        );
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
                debugging("No forums found for {$this->mapping->lang} with scale {$this->mapping->scaleid} (or not enough posts in the forums)", DEBUG_DEVELOPER);
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

        $postuser = new stdClass();
        $postuser->id        = $post->userid;
        $postuser->firstname = $post->firstname;
        $postuser->lastname  = $post->lastname;
        $postuser->imagealt  = $post->imagealt;
        $postuser->picture   = $post->picture;
        $postuser->email     = $post->email;
        foreach (get_all_user_name_fields() as $addname) {
            $postuser->$addname = $post->$addname;
        }

        $link = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
        $link->set_anchor('p'.$post->id);

        $frontcontentbit = new stdClass();
        $frontcontentbit->courseid = $course->id;
        $frontcontentbit->user = $postuser;
        $frontcontentbit->url = (string)$link;
        $frontcontentbit->title = s($post->subject);
        $frontcontentbit->date = userdate($post->modified, get_string('strftimedaydate', 'core_langconfig'));

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

    protected function more_url() {
        return new moodle_url('/course/view.php', array('id' => $this->mapping->courseid));
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

/**
 * Resources
 */
class frontpage_column_resources extends frontpage_column {

    const FEEDURL = 'http://pipes.yahoo.com/pipes/pipe.run?_id=2a7f5e44ac0ae95e1fa10bc5ee09149e&_render=rss';

    protected function cache_key() {
        return 'resources_'. current_language();
    }

    protected function generate() {
        global $CFG;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        $feed = new moodle_simplepie(self::FEEDURL);
        $feeditems = $feed->get_items(0,self::MAXITEMS);

        // Structure to be returned
        $data = (object)array(
            'timegenerated' => time(),
            'rssurl' => (string) $this->rss_url(),
            'moreurl' => (string) $this->more_url(),
            'items' => array(),
        );

        foreach ($feeditems as $item) {
            $title = s($item->get_title());
            if (preg_match('/^Plugins: /', $title)) {
                $imagename = 'icon';
                $imagecomponent = 'mod_lti';
                $imagealt = 'Plugins';

            } else if (preg_match('/^Jobs: /', $title)) {
                $imagename = 'icon';
                $imagecomponent = 'mod_feedback';
                $imagealt = 'Jobs';

            } else if (preg_match('/^Courses: /', $title)) {
                $imagename = 'icon';
                $imagecomponent = 'mod_imscp';
                $imagealt = 'Courses';

            } else {
                $imagename = 'icon';
                $imagecomponent = 'mod_label';
                $imagealt = 'Buzz';
            }

            $data->items[] = (object) array(
                'title' => $title,
                'url' => (string) new moodle_url($item->get_link()),
                'date' => userdate($item->get_date('U'), get_string('strftimedaydate', 'core_langconfig')),
                'image' =>  (object) array(
                    'name' => $imagename,
                    'component' => $imagecomponent,
                    'alt' => $imagealt,
                )
            );
        }

        return $data;
    }

    protected function more_url() {
        return null;
    }

    protected function rss_url() {
        return self::FEEDURL;
    }
}


/**
 * Helper class to update the list of thr PHM cohort members
 *
 * 1. Make an instance of this manager
 * 2. Call add_member() for every user to add/confirm
 * 3. Call remove_old_users() to prune existing members not added in 2.
 */
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
        if (!isset($this->existingusers[$userid]) and !isset($this->currentusers[$userid])) {
            cohort_add_member($this->cohort->id, $userid);
        }

        if (isset($this->existingusers[$userid])) {
            $isnewmember = false;
        } else {
            $isnewmember = true;
        }

        $this->currentusers[$userid] = $userid;

        return $isnewmember;
    }

    /**
     * Returns the userids who have been added to the cohort since the manager was created
     *
     * @return array array of new members indexed by userid
     */
    public function new_users() {
        return array_diff_key($this->currentusers, $this->existingusers);
    }

    /**
     * Returns the usersids who have not been added to the cohort since this manager was created
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
 * metadata about the PHMs.
 *
 * Supported options:
 *
 * bool verbose - produce debugging information via {@link mtrace()}
 * int minratings - the minimum number of ratings to be counted as a PHM
 * int minraters - the minimum number of raters
 * float minratio - the ratio of posts to 'useful' ratings to be coutned as phm.
 * int recentposttime - phm must have posted something somewhere after this timestamp
 *
 * The returned array is the list of PHMs candidates, indexed by userid. For
 * each PHM, array with following keys is returned:
 *
 * userid, firstname, lastname, totalratings, postcount, raters, ratio
 *
 * @param array $options parameters for criteria for granting the PHM status
 * @return array of phms indexed by userid
 */
function local_moodleorg_get_phms(array $options = array()) {
    global $DB;

    $verbose = isset($options['verbose']) ? $options['verbose'] : false;
    $minratings = isset($options['minratings']) ? $options['minratings'] : 14;
    $minraters = isset($options['minraters']) ? $options['minraters'] : 8;
    $minratio = isset($options['minratio']) ? $options['minratio'] : 0.020;
    $recentposttime = isset($options['recentposttime']) ? $options['recentposttime'] : time() - 60 * DAYSECS;

    $forummodid = $DB->get_field('modules', 'id', array('name' => 'forum'));

    $innersql = " FROM {forum_posts} fp
                  JOIN {forum_discussions} fd ON fp.discussion = fd.id
                  JOIN {course_modules} cm ON cm.instance = fd.forum
                  JOIN {context} ctx ON ctx.instanceid = cm.id
                  JOIN {rating} r ON r.contextid = ctx.id
                  JOIN {moodleorg_useful_coursemap} m ON -r.scaleid = m.scaleid
                  JOIN {user} u ON fp.userid = u.id
                  WHERE cm.module = :forummodid
                  AND ctx.contextlevel = :contextlevel AND r.component = :component
                  AND r.ratingarea = :ratingarea AND r.itemid = fp.id
                  AND u.deleted = 0
                  ";

    $params = array('forummodid'    => $forummodid,
                     'contextlevel' => CONTEXT_MODULE,
                     'component'    => 'mod_forum',
                     'ratingarea'   => 'post',
                    );


    $raterssql = "SELECT fp.userid, u.firstname, u.lastname, COUNT(r.id) AS ratingscount
                    $innersql
                  GROUP BY fp.userid, u.firstname, u.lastname";

    $phms = array();
    $rs = $DB->get_recordset_sql($raterssql, $params);
    foreach($rs as $record) {

        $verbose and mtrace(sprintf('Processing user %d %s %s', $record->userid, $record->firstname, $record->lastname));

        if ($record->ratingscount < $minratings) {
            $verbose and mtrace(' not enough ratings ('.$record->ratingscount.' / '.$minratings.')');
            continue;
        }

        $countsql = "SELECT COUNT(DISTINCT(r.userid)) $innersql AND fp.userid = :userid";
        $countparms = array_merge($params, array('userid' => $record->userid));
        $raterscount = $DB->count_records_sql($countsql, $countparms);

        if ($raterscount < $minraters) {
            $verbose and mtrace(' not enough raters ('.$raterscount.' / '.$minraters.')');
            continue;
        }

        $totalpostcount = $DB->count_records_select('forum_posts', 'userid = :userid', array('userid' => $record->userid));

        $ratio = round($record->ratingscount / $totalpostcount, 3);

        if ($ratio < $minratio) {
            $verbose and mtrace(' not enough ratio ('.$ratio.' / '.$minratio.')');
            continue;
        }

        $recentpostcount = $DB->count_records_select('forum_posts', "userid = :userid AND created > :recentposttime",
            array('userid' => $record->userid, 'recentposttime' => $recentposttime));

        if ($recentpostcount < 1) {
            $verbose and mtrace(' no post in last 60 days');
            continue;
        }

        $phms[$record->userid] = array(
            'userid' => $record->userid,
            'lastname' => $record->lastname,
            'firstname' => $record->firstname,
            'totalpostcount' => $totalpostcount,
            'recentpostcount' => $recentpostcount,
            'ratingscount' => $record->ratingscount,
            'raterscount' => $raterscount,
            'ratio' => $ratio,
        );

        $verbose and mtrace(' looking good');
    }
    $rs->close();

    return $phms;
}

/**
 * Send e-mail notification about the PHM cohort update
 *
 * At the moment, this sends the e-mail to a list of hard-coded people only.
 * In the future, this may be improved so that we take recipients from our own
 * mapping table and e-mail them info about the PHMs who are also enrolled in
 * some of their course.
 *
 * @param array $phms as returned by {@link local_moodleorg_get_phms()}
 * @param array $newmembers indexed by userid
 * @param array $removedmembers indexed by userid
 */
function local_moodleorg_notify_phm_cohort_status(array $phms, array $newmembers, array $removedmembers) {
    global $CFG, $DB;

    if (empty($phms)) {
        // This is weird and should not happen. Consider raising an alarm here.
        return;
    }

    if (empty($newmembers) and empty($removedmembers)) {
        // Nothing has changed in the cohort, no need to report anything.
        return;
    }

    $message = "The PHM cohort at moodle.org has been updated:\n";
    $message .= sprintf(" %d member(s) added\n", count($newmembers));
    $message .= sprintf(" %d member(s) removed\n", count($removedmembers));

    if (!empty($newmembers)) {
        $message .= "\nNewly added PHM cohort members:\n";
        foreach ($newmembers as $newmemberid => $unused) {
            $message .= sprintf("* %s %s (https://moodle.org/user/profile.php?id=%d)\n",
                $phms[$newmemberid]['firstname'],
                $phms[$newmemberid]['lastname'],
                $phms[$newmemberid]['userid']);
        }
    }

    if (!empty($removedmembers)) {
        list($subsql, $params) = $DB->get_in_or_equal(array_keys($removedmembers));
        $sql = "SELECT id,firstname,lastname
                  FROM {user}
                 WHERE id $subsql";
        $names = $DB->get_records_sql($sql, $params);
        $message .= "\nRemoved cohort members:\n";
        foreach ($removedmembers as $removedmemberid => $unused) {
            $message .= sprintf("* %s %s (https://moodle.org/user/profile.php?id=%d)\n",
                $names[$removedmemberid]->firstname,
                $names[$removedmemberid]->lastname,
                $names[$removedmemberid]->id);
        }
    }

    $message .= "\nSee the attached file for more details.\n";
    $vars = array_keys(reset($phms));

    // $report will hold CSV formatted per RFC 4180
    $report = implode(';', $vars);
    $report .= ";status\r\n";

    foreach ($phms as $phm) {
        $line = array();
        foreach ($vars as $var) {
            $line[] = '"'.$phm[$var].'"';
        }
        if (isset($newmembers[$phm['userid']])) {
            $line[] = '"NEW"';
        } else {
            $line[] = '""';
        }
        $line = implode(';', $line);
        $report .= $line."\r\n";
    }

    $attachment = tempnam($CFG->dataroot, 'tmp_phm_report_');
    file_put_contents($attachment, $report);

    $subject = '[moodle.org] Particularly helpful Moodlers';

    $helen = $DB->get_record('user', array('id' => 24152), '*', MUST_EXIST);
    email_to_user($helen, 'moodle.org', $subject, $message, '', basename($attachment), 'phm_report.csv', false);

    $david = $DB->get_record('user', array('id' => 1601), '*', MUST_EXIST);
    email_to_user($david, 'moodle.org', $subject, $message, '', basename($attachment), 'phm_report.csv', false);

    unlink($attachment);
}

/**
 * Gets statistics from moodle.net via webservice and inserts into registry table.
 * @return $sites data for insertion into registry table.
 */
function local_moodleorg_get_moodlenet_stats($token, $moodleneturl, $fromid=0, $modifiedafter=0, $numrecs=50) {
    global $CFG;

    $functionname = 'hub_get_sitesregister';
    $restformat = 'json';
    $params = array ('fromid' => $fromid, 'numrecs' => $numrecs, 'modifiedafter' => $modifiedafter);

    /// REST CALL
    $serverurl = $moodleneturl . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
    $curl = new curl;
    //if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
    $restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
    $resp = $curl->post($serverurl . $restformat, $params);
    $sites = json_decode($resp);
    return $sites;
}

/**
 * Gets statistics from moodle.net via webservice and inserts into registry table.
 * @return $sites data for insertion into registry table.
 */
function local_moodleorg_send_moodlenet_stats_19_sites($token, $moodleneturl, $mintimelastsynced, $newdatasince) {
    global $CFG;

    $functionname = 'hub_sync_into_sitesregister';
    $restformat = 'json';
    $params = array ('prevsynctime' => $mintimelastsynced, 'newdatasince' => $newdatasince);

    /// REST CALL
    $serverurl = $moodleneturl . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
    $curl = new curl;
    //if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
    $restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
    $resp = $curl->post($serverurl . $restformat, $params);
    $newsynctime = json_decode($resp);
    return $newsynctime;
}