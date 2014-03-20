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
 * @package     local_moodleorg
 * @copyright   2013 Dan Poltawski <dan@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../../config.php');
require($CFG->libdir.'/filelib.php');
require($CFG->dirroot.'/local/moodleorg/locallib.php');

$lang = optional_param('lang', 'en', PARAM_LANG);

if (!$mapping = local_moodleorg_get_mapping($lang)) {
    throw new moodle_exception('mapping_not_found', 'local_moodleorg');
}

$useful = new frontpage_column_useful($mapping);
$content = $useful->get_rss();

send_file($content, 'rss.xml', 'default' , 0, true);
