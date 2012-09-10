<?php

defined('MOODLE_INTERNAL') || die();

if (has_capability('moodle/site:config', get_system_context())) {
    $ADMIN->add('localplugins', new admin_externalpage('local_moodleorg_coursemapping', 'Moodle.org Course Mapping', '/local/moodleorg/admin/coursemapping.php', 'moodle/site:config'));
}
