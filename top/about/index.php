<?php

/**
 * Redirect the /about top page according the current language
 */

require(__DIR__.'/../../../../config.php');
redirect(new moodle_url(get_string('url-about', 'local_moodleorg')));
