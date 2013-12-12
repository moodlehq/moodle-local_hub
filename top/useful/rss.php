<?php
require('../../../../config.php');
require($CFG->libdir.'/filelib.php');
require($CFG->dirroot.'/local/moodleorg/locallib.php');

$lang = optional_param('lang', 'en', PARAM_LANG);

if (!$mapping = local_moodleorg_get_mapping($lang)) {
    throw new moodle_exception('mapping not found..');
}

$useful = new frontpage_column_useful($mapping);
$content = $useful->get_rss();

send_file($content, 'rss.xml', 'default' , 0, true);
