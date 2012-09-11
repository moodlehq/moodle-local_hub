<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require($CFG->dirroot.'/mod/forum/lib.php');
require($CFG->dirroot.'/rating/lib.php');

$USER = guest_user();
load_all_capabilities();

$mappings =  $DB->get_records('moodleorg_useful_coursemap');
foreach ($mappings as $map) {
    mtrace("Generating feed for {$map->lang} [course: {$map->courseid}, scale: {$map->scaleid}]...");
    $USER->lang = $map->lang;
    generate_useful_items($map->lang, $map->courseid, $map->scaleid);
    mtrace("Done.");
}
die;

function generate_useful_items($langcode, $courseid, $scaleid) {
    global $DB, $USER, $CFG;
    //Set up the ratings information that will be the same for all posts
    $ratingoptions = new stdClass();
    $ratingoptions->component = 'mod_forum';
    $ratingoptions->ratingarea = 'post';
    $ratingoptions->userid = $USER->id;
    $rm = new rating_manager();

    ob_start();   // capture all output

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    // TODO: is this necessary??
    $negativescaleid = $scaleid * -1;
    $forumids = $DB->get_records('forum', array('course'=>$courseid, 'scale'=>$negativescaleid), '', 'id');
    if (empty($forumids)) {
        mtrace("No forums found for $langcode");
        return;
    }

    //TODO: this is in the existing code, but it seems to do nothing!
    foreach ($forumids as $forum) {
        $forumlist[] = $forum->id;
    }
    $forumids = implode(',', $forumlist);

    list($ctxselect, $ctxjoin) = context_instance_preload_sql('cm.id', CONTEXT_MODULE, 'ctx');
    $userselect = user_picture::fields('u', null, 'uid');

    $params = array();
    $params['courseid'] = $courseid;
    $params['since'] = time() - (3600*24*7);   // 7 days
    $params['cmtype'] = 'forum';
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

    $rs = $DB->get_recordset_sql($sql, $params);

    $cachedir = make_cache_directory('moodleorg/useful');
    $rsspath = $cachedir.'/rss-'.$langcode.'.xml';
    $htmlpath = $cachedir.'/content-'.$langcode.'.html';

    $rssfile = fopen($rsspath, 'w+');
    fwrite($rssfile, file_get_contents($CFG->dirroot.'/local/moodleorg/top/useful/rss-head.txt'));

    $discussions = array();
    $forums = array();
    $cms = array();

    foreach ($rs as $post) {

        context_instance_preload($post);

        if (!array_key_exists($post->discussion, $discussions)) {
            $discussions[$post->discussion] = $DB->get_record('forum_discussions', array('id'=>$post->discussion));
            if (!array_key_exists($post->forum, $forums)) {
                $forums[$post->forum] = $DB->get_record('forum', array('id'=>$post->forum));
                $cms[$post->forum] = get_coursemodule_from_instance('forum', $post->forum, $courseid);
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
        fwrite($rssfile, html_writer::start_tag('item')."\n");
        fwrite($rssfile, html_writer::tag('title', s($post->subject))."\n");
        fwrite($rssfile, html_writer::tag('link', $postlink->out(false))."\n");
        fwrite($rssfile, html_writer::tag('pubDate', gmdate('D, d M Y H:i:s',$post->modified).' GMT')."\n");
        fwrite($rssfile, html_writer::tag('description', 'by '.htmlspecialchars(fullname($post).' <br /><br />'.format_text($post->message, $post->messageformat)))."\n");
        fwrite($rssfile, html_writer::tag('guid', $postlink->out(false), array('isPermaLink'=>'true'))."\n");
        fwrite($rssfile, html_writer::end_tag('item')."\n");



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

    fwrite($rssfile, file_get_contents($CFG->dirroot.'/local/moodleorg/top/useful/rss-foot.txt'));
    fclose($rssfile);

    /// Write collected output (only if successful) to the content file
    $htmlfile = fopen($htmlpath, 'w+');
    fwrite($htmlfile, ob_get_contents());
    fclose($htmlfile);

    ob_end_clean();
}
