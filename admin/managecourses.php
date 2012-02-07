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

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . "/local/hub/forms.php");

admin_externalpage_setup('managecourses');

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
$renderer = $PAGE->get_renderer('local_hub');

/// BULK OPERATIONS
$bulkoperation = optional_param('bulkselect', false, PARAM_ALPHANUM);
$confirm = optional_param('confirm', false, PARAM_INTEGER);
if (!empty($bulkoperation) and confirm_sesskey()) {
    //retrieve all ids
    for ($i = 1; $i <= HUB_COURSE_PER_PAGE; $i = $i + 1) {
        $selectedcourseid = optional_param('bulk-' . $i, false, PARAM_INTEGER);
        if (!empty($selectedcourseid)) {
            $bulkcourses[] = $hub->get_course($selectedcourseid);
        }
    }
    if (!$confirm) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('managecourses', 'local_hub'), 3, 'main');
        echo $renderer->course_bulk_operation_confirmation($bulkcourses, $bulkoperation);
        echo $OUTPUT->footer();
        die();
    } else if ($bulkoperation == 'bulkdelete') {
        foreach ($bulkcourses as $bulkcourse) {
            $hub->delete_course($bulkcourse->id);
        }
    } else {
        foreach ($bulkcourses as $bulkcourse) {
            if ($bulkoperation == 'bulkvisible') {
                $bulkcourse->privacy = COURSEVISIBILITY_VISIBLE;
            } else if ($bulkoperation == 'bulknotvisible') {
                $bulkcourse->privacy = COURSEVISIBILITY_NOTVISIBLE;
            }
            $hub->update_course($bulkcourse);
        }
    }
}


/// VISIBLE OPERATION
$courseid = optional_param('courseid', null, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INTEGER);
if ($visible != -1 and confirm_sesskey()) {
    $course = $hub->get_course($courseid);
    if (!empty($course)) {
        $course->privacy = $visible;
        $hub->update_course($course);
        if (!empty($course->privacy)) {
            $notificationstring = get_string('coursevisible', 'local_hub', $course->fullname);
        } else {
            $notificationstring = get_string('coursenotvisible', 'local_hub', $course->fullname);
        }
        $notification = $OUTPUT->notification($notificationstring, 'notifysuccess');
    }
}

$search = optional_param('search', '', PARAM_TEXT);
$options = array();
$courses = null;

/// FORM DATA
$fromformdata['coverage'] = optional_param('coverage', 'all', PARAM_TEXT);
$fromformdata['licence'] = optional_param('licence', 'all', PARAM_ALPHANUMEXT);
$fromformdata['subject'] = optional_param('subject', 'all', PARAM_ALPHANUMEXT);
$fromformdata['siteid'] = optional_param('siteid', 'all', PARAM_ALPHANUMEXT);
$fromformdata['lastmodified'] = optional_param('lastmodified', HUB_LASTMODIFIED_WEEK, PARAM_ALPHANUMEXT);
$fromformdata['audience'] = optional_param('audience', 'all', PARAM_ALPHANUMEXT);
$fromformdata['language'] = optional_param('language', 'all', PARAM_ALPHANUMEXT);
$fromformdata['educationallevel'] = optional_param('educationallevel', 'all', PARAM_ALPHANUMEXT);
$fromformdata['visibility'] = optional_param('visibility', COURSEVISIBILITY_NOTVISIBLE, PARAM_ALPHANUMEXT);
$fromformdata['downloadable'] = optional_param('downloadable', 'all', PARAM_ALPHANUM);
$fromformdata['orderby'] = optional_param('orderby', 'newest', PARAM_ALPHA);
$fromformdata['adminform'] = 1;
$fromformdata['search'] = $search;

$coursesearchform = new course_search_form('', $fromformdata);
$fromform = $coursesearchform->get_data();

$coursesearchform->set_data($fromformdata);
$fromform = (object) $fromformdata;


/// COURSE DATA
if (!empty($courseid)) {
    $options['ids'] = array($courseid);
    $options['downloadable'] = true;
    $options['enrollable'] = true;
    $courses = $hub->get_courses($options);
    unset($options['ids']);
    unset($options['downloadable']);
    unset($options['enrollable']);
    $coursetotal = 1;
} else {
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
    if ($fromform->siteid != 'all') {
        $options['siteid'] = $fromform->siteid;
    }
    if ($fromform->lastmodified != 'all') {
        switch ($fromform->lastmodified) {
            case HUB_LASTMODIFIED_WEEK:
                $lastmodified = strtotime("-7 day");
                break;
            case HUB_LASTMODIFIED_FORTEENNIGHT:
                $lastmodified = strtotime("-14 day");
                break;
            case HUB_LASTMODIFIED_MONTH:
                $lastmodified = strtotime("-30 day");
                break;
        }

        $options['lastmodified'] = $lastmodified;
    }

    $options['visibility'] = $fromform->visibility;

    //sort method
    switch ($fromform->orderby) {
        case 'newest':
            $options['orderby'] = 'timemodified DESC';
            break;
        case 'eldest':
            $options['orderby'] = 'timemodified ASC';
            break;
        case 'publisher':
            $options['orderby'] = 'publishername ASC';
            break;
        case 'fullname':
            $options['orderby'] = 'fullname ASC';
            break;
        default:
            break;
    }

    //get courses
    $options['search'] = $search;
    $options['onlyvisible'] = false;
    if ($fromform->downloadable != 'all') {
        $options['downloadable'] = $fromform->downloadable;
        $options['enrollable'] = !$fromform->downloadable;
    } else {
        $options['downloadable'] = 1;
        $options['enrollable'] = 1;
    }

    $page = optional_param('page', 0, PARAM_INT);

    $courses = $hub->get_courses($options,
                    $page * HUB_COURSE_PER_PAGE, HUB_COURSE_PER_PAGE);
   
    $coursetotal = $hub->get_courses($options, 0, 0, true);

    //$options will be reused to call the same url with same GET param,
    //need to reinitialize
    $options['downloadable'] = $fromform->downloadable;
    $options['lastmodified'] = $fromform->lastmodified;
    $options['orderby'] = $fromform->orderby;
}


//complete course data with site name and course content
$sites = $hub->get_sites(array('deleted' => 1));
$courseids = array(); //all result courses
$courseimagenumbers = array(); //number of screenshots of all courses (must be exact same order than $courseids)
foreach ($courses as &$course) {
    //get site name for each courses
    $course->site = $sites[$course->siteid];

    //get course content for each course
    $contents = $hub->get_course_contents($course->id);
    if (!empty($contents)) {
        foreach ($contents as $content) {
            $course->contents[] = $content;
        }
    }

    //some information for the YUI imagegallery javascript
    $courseids[] = $course->id;
    $courseimagenumbers[] = $course->screenshots;
}

//add rating information to the course
require_once($CFG->dirroot . '/rating/lib.php');
$ratingoptions = new stdclass();
$ratingoptions->context = get_context_instance(CONTEXT_COURSE, SITEID); //front page course
$ratingoptions->items = $courses;
$ratingoptions->aggregate = RATING_AGGREGATE_COUNT; //the aggregation method
$ratingoptions->scaleid = 0 - get_config('local_hub', 'courseratingscaleid'); //rating API is expecting "minus scaleid"
$ratingoptions->userid = $USER->id;
$ratingoptions->returnurl = $CFG->wwwroot . "/local/hub/admin/managecourses.php";
$ratingoptions->component = 'local_hub';
$ratingoptions->ratingarea = 'featured';
$rm = new rating_manager();
$courses = $rm->get_ratings($ratingoptions); //this function return $ratingoptions->items with information about the ratings


/// OUTPUT

//load javascript for YUI imagegallery javascript screenshot
$PAGE->requires->yui_module('moodle-block_community-imagegallery',
        'M.blocks_community.init_imagegallery',
        array(array('imageids' => $courseids,
                'imagenumbers' => $courseimagenumbers,
                'huburl' => $CFG->wwwroot)));

//display header
echo $OUTPUT->header();

//display a message if we come back from site settings page
$updatecourse = optional_param('coursesettings', '', PARAM_TEXT);
if (!empty($updatecourse) and confirm_sesskey()) {
    echo $OUTPUT->notification(get_string('coursesettingsupdated', 'local_hub', $updatecourse),
            'notifysuccess');
}

//display some notification
if (!empty($notification)) {
    echo $notification;
}

//display heading title
echo $OUTPUT->heading(get_string('managecourses', 'local_hub'), 3, 'main');

//display course search form
$coursesearchform->display();

if (isset($courses) and empty($courseid)) {
    if (empty($coursetotal)) {
        $coursetotalhtml = get_string('nocourse', 'local_hub');
    } else {
        $coursetotalhtml = get_string('coursetotal', 'local_hub', $coursetotal);
    }
    echo html_writer::tag('div', $coursetotalhtml, array('class' => 'hubcoursetotal'));
}

//display the top paging bar
if ($coursetotal > HUB_COURSE_PER_PAGE) {
    $baseurl = new moodle_url('', $options);
    $pagingbarhtml = $OUTPUT->paging_bar($coursetotal, $page, HUB_COURSE_PER_PAGE, $baseurl);
    $pagingbarhtml = html_writer::tag('div', $pagingbarhtml, array('class' => 'pagingbar'));
    echo $pagingbarhtml;
}

//display course result list
echo $renderer->course_list($courses, true, $options);

//display the bottom paging bar
if (!empty($pagingbarhtml)) {
    echo $pagingbarhtml;
}

$PAGE->requires->string_for_js('nocourseselected', 'local_hub');
$PAGE->requires->yui_module('moodle-local_hub-courses', 'M.local_hub.init_hubcourses',
        array(array('scriptname' => 'local/hub/admin/managecourses.php')));

//display footer
echo $OUTPUT->footer();