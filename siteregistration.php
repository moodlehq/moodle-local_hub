<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Page where the site admin is redirected to when he press Register button in the
 * site.
 * A confirmation registration form with recaptcha is displayed, avoiding automatic
 * registration.
 * After saving the site information in the hub database, the hub redirects the
 * admin on the final confirmation page on the site.
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot.'/local/hub/forms.php');
require_once($CFG->dirroot.'/webservice/lib.php');

if (!get_config('local_hub', 'hubenabled')) {
    throw new moodle_exception('hubnotenabled');
}

$PAGE->set_url('/local/hub/siteregistration.php');
$PAGE->set_title(get_string('siteregistration', 'local_hub'));
$PAGE->set_heading(get_string('siteregistration', 'local_hub'));

$name                       = optional_param('name', '', PARAM_TEXT);
$url                        = optional_param('url', '', PARAM_URL);
$token                      = optional_param('token', '', PARAM_ALPHANUM);
$description                = optional_param('description', '', PARAM_TEXT);
$contactname                = optional_param('contactname', '', PARAM_TEXT);
$contactemail               = optional_param('contactemail', '', PARAM_EMAIL);
$contactphone               = optional_param('contactphone', '', PARAM_TEXT);
$imageurl                   = optional_param('imageurl', '', PARAM_URL);
$privacy                    = optional_param('privacy', '', PARAM_ALPHA);
$language                   = optional_param('language', '', PARAM_ALPHANUMEXT);
$users                      = optional_param('users', '', PARAM_FLOAT);
$courses                    = optional_param('courses', '', PARAM_FLOAT);
$street                     = optional_param('street', '', PARAM_TEXT);
$regioncode                 = optional_param('regioncode', '', PARAM_ALPHANUMEXT);
$countrycode                = optional_param('countrycode', '', PARAM_ALPHANUMEXT);
$geolocation                = optional_param('geolocation', '', PARAM_RAW);
$contactable                = optional_param('contactable', '', PARAM_BOOL);
$emailalert                 = optional_param('emailalert', '', PARAM_BOOL);
$enrolments                 = optional_param('enrolments', '', PARAM_FLOAT);
$posts                      = optional_param('posts', '', PARAM_FLOAT);
$questions                  = optional_param('questions', '', PARAM_FLOAT);
$resources                  = optional_param('resources', '', PARAM_FLOAT);
$participantnumberaverage   = optional_param('participantnumberaverage', '', PARAM_FLOAT);
$modulenumberaverage        = optional_param('modulenumberaverage', '', PARAM_FLOAT);
$moodleversion              = optional_param('moodleversion', '', PARAM_INT);
$moodlerelease              = optional_param('moodlerelease', '', PARAM_TEXT);
$password                   = optional_param('password', '', PARAM_TEXT);

$siteheaders = get_headers($url);
if ( strpos($url, 'http://localhost') !== false or strpos($url, 'http://127.0.0.1') !== false or $siteheaders === false) {
    throw new moodle_exception('cannotregisternotavailablesite', 'local_hub', $url);
}

$hubpassword = get_config('local_hub', 'password');
if (!empty($hubpassword) and $hubpassword != $password) {
    throw new moodle_exception('wronghubpassword', 'local_hub', $url.'/admin/registration/hubselector.php');
}

//check if the site url is already registered
$hub = new local_hub();
$checkedsite = $hub->get_site_by_url($url);
if (!empty($checkedsite)) {
    redirect(new moodle_url($url."/admin/registration/confirmregistration.php",
            array('error' => 'urlalreadyexist', 'url' => $CFG->wwwroot, 'token' => $token,
                'hubname' => get_config('local_hub', 'name'))));
}

//fill the "recaptcha" Moodle form with hub values
$sitevalues = array('name' => $name,
        'url' => $url,
        'token' => $token,
        'description' => $description,
        'contactname' => $contactname,
        'contactemail' => $contactemail,
        'contactphone' => $contactphone,
        'imageurl' => $imageurl,
        'privacy' => $privacy,
        'language' => $language,
        'users' => $users,
        'courses' => $courses,
        'street' => $street,
        'regioncode' => $regioncode,
        'countrycode' => $countrycode,
        'geolocation' => $geolocation,
        'contactable' => $contactable,
        'emailalert' => $emailalert,
        'enrolments' => $enrolments,
        'posts' => $posts,
        'questions' => $questions,
        'resources' => $resources,
        'participantnumberaverage' => $participantnumberaverage,
        'modulenumberaverage' => $modulenumberaverage,
        'moodleversion' => $moodleversion,
        'moodlerelease' => $moodlerelease,
        'password' => $password);
$siteconfirmationform = new site_registration_confirmation_form('', $sitevalues);

$fromform = $siteconfirmationform->get_data();

if (!empty($fromform)) { //the recaptcha has been valided (get_data return NULL if the recaptcha is wrong)

    //check that the form has the required data
    //(to force people that don't call this page from a Moodle registration page to POST correct data.
    //Note that there is no good reason for people to do it)
    if (empty($fromform->token) or empty($fromform->url) or
            empty($fromform->name) or empty($fromform->contactname)
            or empty($fromform->contactemail) or empty($fromform->description)
            or empty($fromform->language)) {
        throw new moodle_exception('errorwrongdata', 'local_hub', new moodle_url('/index.php'));
    }

    $siteinfo = new stdClass();
    $siteinfo->token = $fromform->token;
    $siteinfo->url = $fromform->url;
    $siteinfo->description = $fromform->description;
    $siteinfo->name = $fromform->name;
    $siteinfo->contactname = $fromform->contactname;
    $siteinfo->contactemail = $fromform->contactemail;
    $siteinfo->contactphone = $fromform->contactphone;
    $siteinfo->imageurl = $fromform->imageurl;
    $siteinfo->privacy = $fromform->privacy;
    $siteinfo->language = $fromform->language;
    $siteinfo->users = $fromform->users;
    $siteinfo->courses = $fromform->courses;
    $siteinfo->street = $fromform->street;
    $siteinfo->regioncode = $fromform->regioncode;
    $siteinfo->countrycode = $fromform->countrycode;
    $siteinfo->geolocation = $fromform->geolocation;
    $siteinfo->contactable = $fromform->contactable;
    $siteinfo->emailalert = $fromform->emailalert;
    $siteinfo->enrolments = $fromform->enrolments;
    $siteinfo->posts = $fromform->posts;
    $siteinfo->questions = $fromform->questions;
    $siteinfo->resources = $fromform->resources;
    $siteinfo->participantnumberaverage = $fromform->participantnumberaverage;
    $siteinfo->modulenumberaverage = $fromform->modulenumberaverage;
    $siteinfo->moodleversion = $fromform->moodleversion;
    $siteinfo->moodlerelease = $fromform->moodlerelease;

    
    $newtoken = $hub->register_site($siteinfo);

    //Redirect to the site with the created token
    redirect(new moodle_url($url."/admin/registration/confirmregistration.php",
            array('newtoken' => $newtoken, 'url' => $CFG->wwwroot, 'token' => $fromform->token,
                'hubname' => get_config('local_hub', 'name'))));

}

echo $OUTPUT->header();
$siteconfirmationform->display();
echo $OUTPUT->footer();
