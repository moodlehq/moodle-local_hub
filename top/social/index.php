<?php

/**
 * Provides the content of the moodle.org/social/ page
 */

require(__DIR__.'/../../../../config.php');

$PAGE->set_url(new moodle_url('/social/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('socialtitle', 'local_moodleorg'));
$PAGE->set_heading($PAGE->title);
$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::tag('p', get_string('socialinfo', 'local_moodleorg'));

echo html_writer::start_tag('dl', array('class' => 'dl-horizontal', 'id' => 'social-media-list'));
echo local_moodleorg_top_social_media('googleplus', 'moodle');
echo local_moodleorg_top_social_media('googleplus', 'community');
echo local_moodleorg_top_social_media('twitter', 'moodle');
echo local_moodleorg_top_social_media('twitter', 'moodledev');
echo local_moodleorg_top_social_media('twitter', 'moodlesites');
echo local_moodleorg_top_social_media('twitter', 'moodlesecurity');
echo local_moodleorg_top_social_media('twitter', 'moodleresearch');
echo local_moodleorg_top_social_media('twitter', 'moodlenet');
echo local_moodleorg_top_social_media('twitter', 'moodlethemes');
echo local_moodleorg_top_social_media('twitter', 'moodleplugins');
echo local_moodleorg_top_social_media('twitter', 'moodlejobs');
echo local_moodleorg_top_social_media('twitter', 'moodlehq');
echo local_moodleorg_top_social_media('facebook', 'moodle', 'https://www.facebook.com/moodle.lms');
echo local_moodleorg_top_social_media('linkedin', 'moodle', 'https://www.linkedin.com/company/moodle-community');
echo local_moodleorg_top_social_media('youtube', 'moodle', 'http://www.youtube.com/user/moodlehq');
echo html_writer::end_tag('dl');

echo $OUTPUT->footer();
die();

/**
 * Helper function to render the list of media channels
 *
 * @param string $type media type googleplus|twitter|facebook|linkedin
 * @param string $name media identifier withing the given type
 * @param string $url explicit URL of the media channel, try to guess otherwise
 * @return string HTML
 */
function local_moodleorg_top_social_media($type, $name, $url=null) {

    // Populate the media channel name. We do not translate these.
    if ($type === 'twitter') {
        $title = '@'.$name;

    } else if ($type === 'googleplus' and $name === 'community') {
        $title = 'Moodle community';

    } else {
        $title = 'Moodle';
    }

    // Get the localised description
    $info = s(get_string('social-'.$type.'-'.$name, 'local_moodleorg'));

    // Populate the media URL
    $link = null;
    if (!is_null($url)) {
        $link = $url;

    } else if ($type === 'twitter') {
        $link = 'https://twitter.com/'.$name;

    } else if ($type === 'googleplus') {
        $link = 'https://plus.google.com/+'.$name;

    }

    $item = html_writer::start_tag('dt', array('class' => $type, 'id' => 'social-media-'.$type.'-'.$name));
    if (!is_null($link)) {
        $item .= html_writer::link($link, $title);
    } else {
        $item .= $title;
    }
    $item .= html_writer::end_tag('dt');
    $item .= html_writer::tag('dd', s($info));

    return $item;
}
