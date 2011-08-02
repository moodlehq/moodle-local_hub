<?php 

/// WARNING!   REALLY HACKY CODE AHEAD!


include('config.php');
include('mod/forum/lib.php');


$coursecontextid = 53;   // Using Moodle course
$courseid = 5;           // Using Moodle course
$days = 90;
$minposts = 1;
$minratings = 13;
$minraters = 8;
$minscore = 5;   // 1 - 7


exit;   // disabled now it's done

print_header('Fix ratings', 'Fix ratings', 'Fix ratings');

$forums = get_records_select('forum', "course = $courseid");

mtrace(count($forums). ' forums...');

foreach ($forums as $forum) {
    mtrace($forum->name);

    if ($forum->scale == -1) {      // 3 point connected

        $sql = "SELECT p.id, p.id FROM forum_posts p, forum_discussions d 
                WHERE d.forum = '$forum->id' AND p.discussion = d.id";

        if ($posts = get_records_sql($sql)) {
            mtrace(count($posts). ' posts with 3 points scale...');
            foreach ($posts as $post) {
                execute_sql("DELETE FROM forum_ratings WHERE (post = $post->id) AND (rating <= 1)");
                execute_sql("UPDATE forum_ratings SET rating = 1 WHERE post = $post->id");
            }
    
            set_field('forum', 'scale', -88, 'id', $forum->id);
        }

    } else if ($forum->scale == -2) {  // 7 point helpful

        $sql = "SELECT p.id, p.id FROM forum_posts p, forum_discussions d 
                WHERE d.forum = '$forum->id' AND p.discussion = d.id";

        if ($posts = get_records_sql($sql)) {
            mtrace(count($posts). ' posts with 7 points scale...');
            foreach ($posts as $post) {
                execute_sql("DELETE FROM forum_ratings WHERE (post = $post->id) AND (rating <= 3)");
                execute_sql("UPDATE forum_ratings SET rating = 1 WHERE post = $post->id");
            }
    
            set_field('forum', 'scale', -88, 'id', $forum->id);
        }
    }
}

?>
