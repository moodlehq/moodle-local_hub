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
 * Administrator can manage sites on this page.
 * Trust, Prioritise, Delete, Hide...
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/forms.php");

admin_externalpage_setup('managesites');

//check that the PHP xmlrpc extension is enabled
if (!extension_loaded('xmlrpc')) {
    echo $OUTPUT->header();
    $xmlrpcnotification = $OUTPUT->doc_link('admin/environment/php_extension/xmlrpc', '');
    $xmlrpcnotification .= get_string('xmlrpcdisabled', 'local_hub');
    echo $OUTPUT->notification($xmlrpcnotification);
    echo $OUTPUT->footer();
    die();
}

$hub = new local_hub();

/// Check if the page has been called with trust argument
$delete = optional_param('delete', -1, PARAM_INTEGER);
$confirm = optional_param('confirm', false, PARAM_INTEGER);
if ($delete != -1 and $confirm and confirm_sesskey()) {
    $unregistercourses = optional_param('unregistercourses', false, PARAM_BOOL);
    $hub->delete_site($delete, $unregistercourses);
}


/// Check if the page has been called with trust argument
$trust = optional_param('trust', -1, PARAM_INTEGER);
if ($trust != -1 and confirm_sesskey()) {
    $id = required_param('id', PARAM_INTEGER);
    $site = $hub->get_site($id);
    if (!empty($site)) {
        $site->trusted = $trust;
        $hub->update_site($site);
    }
}

$search = optional_param('search', '', PARAM_TEXT);
$renderer = $PAGE->get_renderer('local_hub');
$contenthtml = "";
if ($delete != -1 and !$confirm) { //we want to display delete confirmation page
    $site = $hub->get_site($delete);
    $contenthtml = $renderer->delete_confirmation($site);
} else { //all other cases we go back to site list page (no need confirmation)
    //forms
    $sitesearchform = new site_search_form('', array('search' => $search, 'adminform' => 1));
    $fromform = $sitesearchform->get_data();

    //if the page result from any action from the renderer, set data to the previous search in order to
    //display the same result
    if ((!empty($search) or $trust != -1 or $delete != -1 /*or $visible != -1 or $prioritise != -1*/)
            and confirm_sesskey()) {
        $fromformdata['trusted'] = optional_param('trusted', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['countrycode'] = optional_param('countrycode', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['language'] = optional_param('language', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['search'] = $search;
        $sitesearchform->set_data($fromformdata);
        $fromform = (object) $fromformdata;
    }

    //Retrieve courses by web service
    $sites = null;
    $options = array();
    if (!empty($fromform)) {

        if ($fromform->trusted != 'all') {
            $options['trusted'] = $fromform->trusted;
        }

        if ($fromform->countrycode != 'all') {
            $options['countrycode'] = $fromform->countrycode;
        }
        if ($fromform->language != 'all') {
            $options['language'] = $fromform->language;
        }

        //get courses
        $options['search'] = $search;
        $sites = $hub->get_sites($options);
    }

    //(search, none language, no onlyvisible)
    $contenthtml = $renderer->site_list($sites, true);
}

echo $OUTPUT->header();

//display a message if we come back from site settings page
$updatesite = optional_param('sitesettings', '', PARAM_TEXT);
if (!empty($updatesite) and confirm_sesskey()) {
     echo $OUTPUT->notification(get_string('sitesettingsupdated', 'local_hub', $updatesite), 'notifysuccess');
}

if (!($delete != -1 and !$confirm)) {
    echo $OUTPUT->heading(get_string('managesites', 'local_hub'), 3, 'main');
    $sitesearchform->display();
} else {
    echo $OUTPUT->heading(get_string('deletesite', 'local_hub', $site->name), 3, 'main');
}
echo $contenthtml;
echo $OUTPUT->footer();