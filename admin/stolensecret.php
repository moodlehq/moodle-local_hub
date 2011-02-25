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
 * Administrator can manage stolen secret on this page
 * @package   localhub
 * @copyright 2011 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/admin/forms.php");

admin_externalpage_setup('hubstolensecret');

$hub = new local_hub();
$renderer = $PAGE->get_renderer('local_hub');

$stolen = optional_param('stolen', 0, PARAM_INT);
if ($stolen and sesskey()) {
    $confirm = optional_param('confirm', 0, PARAM_INT);   
    if ($confirm) {
        //mark the token as stolen
        $hub->marksecretstolen($stolen);

        //delete site and web service token
        $hub->delete_site($stolen);

        $confirmmsg = $OUTPUT->notification(
                        get_string('secretblocked', 'local_hub'), 'notifysuccess');
    } else {
        $hackedsite = $hub->get_site($stolen);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('stolensecret', 'local_hub'));
        echo $renderer->stolensecret_confirmation($hackedsite);
        echo $OUTPUT->footer();
        die();
    }
}

$mform = new hub_search_stolen_secret();
if ($data = $mform->get_data()) {
    $sites = array();

    //search site
    if (!empty($data->secret)) { //by token
        $site = $hub->get_site_by_secret($data->secret);
        if (!empty($site)) {
            $search = $data->secret;
            $sites[] = $site;
        }
    } else if (!empty($data->sitename)) { //by site name
        $sites = $hub->get_sites(array('search' => $data->sitename));
        $search = $data->sitename;
    }
}

/// OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('stolensecret', 'local_hub'));
if (!empty($confirmmsg)) {
    echo $confirmmsg;
}
$mform->display();

if (isset($sites)) {
    if (empty($sites)) {
        echo get_string('nosite', 'local_hub');
    } else {
        echo highlight($search, $renderer->site_list($sites, true, true));
    }
}
echo $OUTPUT->footer();