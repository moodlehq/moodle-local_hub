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
 * Administrator can manage registered course on this page
 * Delete, Hide,...
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot. "/local/hub/forms.php");

admin_externalpage_setup('managecourses');
$hub = new local_hub();

/// Check if the page has been called with delete argument
$delete  = optional_param('delete', -1, PARAM_INTEGER);
$confirm  = optional_param('confirm', false, PARAM_INTEGER);
if ($delete != -1 and $confirm and confirm_sesskey()) {
    $hub->delete_course($delete);
}


/// Check if the page has been called by visible action
$visible  = optional_param('visible', -1, PARAM_INTEGER);
if ($visible != -1 and confirm_sesskey()) {
    $id  = optional_param('id', '', PARAM_INTEGER);
    $course = $hub->get_course($id);
    if (!empty($course)) {
        $course->privacy = $visible;
        $hub->update_course($course);
    }
}


$search  = optional_param('search', '', PARAM_TEXT);
$renderer = $PAGE->get_renderer('local_hub');
$contenthtml = "";
if ($delete != -1 and !$confirm) { //we want to display delete confirmation page
    $course = $hub->get_course($delete);
    $contenthtml = $renderer->delete_course_confirmation($course);
} else { //all other cases we go back to site list page (no need confirmation)

    //forms
    $coursesearchform = new course_search_form('', array('search' => $search));
    $fromform = $coursesearchform->get_data();

    //Retrieve courses by web service
    $courses = array();
    if (!empty($fromform)) {
        $downloadable  = optional_param('downloadable', false, PARAM_INTEGER);

        $options = array();
        if (!empty($fromform->coverage)) {
            $options['coverage'] = $fromform->coverage;
        }
        if ($fromform->licence != 'all') {
            $options['licenceshortname'] = $fromform->licence;
        }
        if ($fromform->subject != 'all') {
            $options['subject'] = $fromform->subject;
        }
        if ($fromform->audience != 'all') {
            $options['audience'] = $fromform->audience;
        }
        if ($fromform->educationallevel != 'all') {
            $options['educationallevel'] = $fromform->educationallevel;
        }
        if ($fromform->language != 'all') {
            $options['language'] = $fromform->language;
        }

        //get courses
        $courses = $hub->get_courses($search, $options, true, $downloadable, !$downloadable);
    }

   

    //(search, none language, no onlyvisible)
    $contenthtml = $renderer->course_list($courses, true);
}




echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecourses', 'local_hub'), 3, 'main');
if (!($delete != -1 and !$confirm)) {
    $coursesearchform->display();
}
echo $contenthtml;
echo $OUTPUT->footer();