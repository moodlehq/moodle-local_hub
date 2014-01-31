<?php

/**
 * Redirect the /features top page according the current language
 */

require(__DIR__.'/../../../../config.php');
redirect(new moodle_url(get_string('url-features', 'local_moodleorg')));
