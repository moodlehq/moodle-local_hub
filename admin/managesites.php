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

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/hub/lib.php');

admin_externalpage_setup('managesites');
$hub = new local_hub();

/// Check if the page has been called with trust argument
$delete  = optional_param('delete', -1, PARAM_INTEGER);
$confirm  = optional_param('confirm', false, PARAM_INTEGER);
if ($delete != -1 and $confirm and confirm_sesskey()) {
    $hub->delete_site($delete);
}


/// Check if the page has been called with trust argument
$trust  = optional_param('trust', -1, PARAM_INTEGER);
if ($trust != -1 and confirm_sesskey()) {
    $id  = optional_param('id', '', PARAM_INTEGER);
    $site = $hub->get_site($id);
    if (!empty($site)) {
        $site->trusted = $trust;
        $hub->update_site($site);
    }
}

/// Check if the page has been called by visible action
$visible  = optional_param('visible', -1, PARAM_INTEGER);
if ($visible != -1 and confirm_sesskey()) {
    $id  = optional_param('id', '', PARAM_INTEGER);
    $site = $hub->get_site($id);
    if (!empty($site)) {
        $site->visible = $visible;
        $hub->update_site($site);
    }
}

/// Check if the page has been called by prioritise action
$prioritise  = optional_param('prioritise', -1, PARAM_INTEGER);
if ($prioritise != -1 and confirm_sesskey()) {
    $id  = optional_param('id', '', PARAM_INTEGER);
    $site = $hub->get_site($id);
    if (!empty($site)) {
        $site->prioritise = $prioritise;
        if ($prioritise) {
            $site->trusted = true;
        }
        $hub->update_site($site);
    }
}

$search  = optional_param('search', '', PARAM_TEXT);
$renderer = $PAGE->get_renderer('local_hub');
$contenthtml = "";
if ($delete != -1 and !$confirm) { //we want to display delete confirmation page
    $site = $hub->get_site($delete);
    $contenthtml = $renderer->delete_confirmation($site);
} else { //all other cases we go back to site list page (no need confirmation)
    $sites = $hub->get_sites($search, null, false); //return list of all sites
    //(search, none language, no onlyvisible)
    $contenthtml = $renderer->searchable_site_list($sites, $search, true);
}



echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managesites', 'local_hub'), 3, 'main');
echo $contenthtml;
echo $OUTPUT->footer();