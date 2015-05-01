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
 * Administrator can check the status of an email address in Sendy
 * @package   local_hub
 * @copyright 2015 Andrew Davis <andrew@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__.'/../../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/admin/forms.php");

admin_externalpage_setup('checkemailsendystatus');

$hub = new local_hub();
$mform = new local_hub_checkemailsendystatus_form();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('checkemailsendystatus', 'local_hub'));

if ($data = $mform->get_data()) {
    $sendyurl = get_config('local_hub', 'sendyurl');
    $sendylistid = get_config('local_hub', 'sendylistid');
    $sendyapikey = get_config('local_hub', 'sendyapikey');

    // Check for config.php overrides.
    if (isset($CFG->sendyurl)) {
         $sendyurl = $CFG->sendyurl;
    }
    if (isset($CFG->sendylistid)) {
         $sendylistid = $CFG->sendylistid;
    }
    if (isset($CFG->sendyapikey)) {
         $sendyapikey = $CFG->sendyapikey;
    }

    if (empty($sendyurl) || empty($sendylistid) || empty($sendyapikey)) {
        print_error('mailinglistnotconfigured', 'local_hub');
    }

    $a = new stdClass();
    $a->email = $data->email;
    $a->status = get_sendy_status($sendyurl, $sendyapikey, $sendylistid, $data->email);
    echo $OUTPUT->notification(get_string('emailsendystatus', 'local_hub', $a), 'notifysuccess');
}

$mform->display();

echo $OUTPUT->footer();
