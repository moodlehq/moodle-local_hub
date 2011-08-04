<?php

// Martin Dougiamas  MDLSITE-550

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');

require($CFG->dirroot.'/mod/forum/lib.php');

$USER = guest_user();
load_all_capabilities();

ob_start();   // capture all output

$courseid = 5;
$scaleid = -88;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$forumids = $DB->get_records('forum', array('course'=>$courseid, 'scale'=>$scaleid), '', 'id');
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
$sql = "SELECT fp.*, fd.forum $ctxselect, $userselect
        FROM {forum_posts} fp
        JOIN {modules} m
        LEFT JOIN {user} u ON u.id = fp.userid
        LEFT JOIN {forum_discussions} fd ON fd.id = fp.discussion
        LEFT JOIN {course_modules} cm ON (cm.course = fd.course AND cm.instance = fd.forum AND cm.module = m.id)
        $ctxjoin
        LEFT JOIN {rating} r ON r.contextid = ctx.id AND fp.id = r.itemid
        WHERE fd.course = :courseid
        AND m.name = :cmtype
        AND r.timecreated > :since
        GROUP BY fp.id
        ORDER BY r.timecreated DESC";

$rs = $DB->get_recordset_sql($sql, $params);

$rssfile = fopen('rss.xml', 'w+');
fwrite($rssfile, file_get_contents('rss-head.txt'));

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
    //$fulllink = html_writer::link($postlink, get_string("postincontext", "forum"));

    echo "<br /><br />";
    forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false);

}
$rs->close();

fwrite($rssfile, file_get_contents('rss-foot.txt'));
fclose($rssfile);

/// Write collected output (only if successful) to the content file
$htmlfile = fopen('content.html', 'w+');
fwrite($htmlfile, ob_get_contents());
fclose($htmlfile);

ob_end_clean();
