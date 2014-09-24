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
 * Administrator can see if server can
 * @package   local_hub
 * @copyright 2014 Dan Poltawski <dan@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__.'/../../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/admin/forms.php");

admin_externalpage_setup('checksiteconnectivity');


$hub = new local_hub();
$mform = new local_hub_siteconnectivity_form();


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('checksiteconnectivity', 'local_hub'));

if ($data = $mform->get_data()) {
    if ($hub->is_remote_site_valid($data->url)) {
        echo $OUTPUT->notification(get_string('urlaccessible', 'local_hub', $data->url), 'notifysuccess');
    } else {
        echo $OUTPUT->notification(get_string('urlnotaccessible', 'local_hub', $data->url), 'notifyproblem');
    }
}

$mform->display();

echo $OUTPUT->footer();