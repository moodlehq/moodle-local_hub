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
    
    $downloadable  = optional_param('downloadable', false, PARAM_INTEGER);

    //forms
    $coursesearchform = new course_search_form('', array('search' => $search, 'adminform' => 1));
    $fromform = $coursesearchform->get_data();

    //if the page result from any action from the renderer, set data to the previous search in order to
    //display the same result
    if (($visible != -1 or ($delete != -1  and $confirm)) and confirm_sesskey()) {
        $fromformdata['coverage']  = optional_param('coverage', 'all', PARAM_TEXT);
        $fromformdata['licence']  = optional_param('licence', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['subject'] = optional_param('subject', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['audience']  = optional_param('audience', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['language']  = optional_param('language', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['educationallevel']  = optional_param('educationallevel', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['visibility']  = optional_param('visibility', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['downloadable']  = $downloadable;
        $fromformdata['search'] = $search;
        $coursesearchform->set_data($fromformdata);
        $fromform = (object)$fromformdata;
    }

    
    //Retrieve courses by web service
    $courses = null;
    $options = array();
    if (!empty($fromform)) {

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

        $options['visibility'] = $fromform->visibility;

        //get courses
        $options['search'] = $search;
        $options['onlyvisible'] = false;
        $options['downloadable'] = $downloadable;
        $options['enrollable'] = !$downloadable;
        $courses = $hub->get_courses($options);

        //get courses content
        foreach($courses as $course) {

            $contents = $hub->get_course_contents($course->id);
             if (!empty($contents)) {
                 foreach($contents as $content) {
                    $course->contents[] = $content;
                 }
             }
        }
    }

   
/// (search, none language, no onlyvisible)
    $options['search'] = $search;
    $options['downloadable'] = $downloadable;
    $contenthtml = $renderer->course_list($courses, true, $options);
}




echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecourses', 'local_hub'), 3, 'main');
if (!($delete != -1 and !$confirm)) {
    $coursesearchform->display();
}
echo $contenthtml;
echo $OUTPUT->footer();