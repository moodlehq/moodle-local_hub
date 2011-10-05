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

$PAGE->set_context(get_system_context());
$PAGE->set_url('/local/hub/siteregistration.php');
$PAGE->set_title(get_string('siteregistration', 'local_hub'));
$PAGE->set_heading(get_string('siteregistration', 'local_hub'));

$name                       = optional_param('name', '', PARAM_TEXT);
$url                        = optional_param('url', '', PARAM_URL);
$token                      = optional_param('token', '', PARAM_TEXT); //this is the site secret
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
$moodleversion              = optional_param('moodleversion', '', PARAM_FLOAT);
$moodlerelease              = optional_param('moodlerelease', '', PARAM_TEXT);
$password                   = optional_param('password', '', PARAM_TEXT);

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

$hub = new local_hub();

//check the secret is not stolen
if (!$hub->check_secret_validity(md5($token))) {
    echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('local_hub');
    echo $renderer->secretisstolen($sitevalues);
    echo $OUTPUT->footer();
    exit();
}


//fresh moodle install on same url / Moved Moodle install on a url where a Moodle site was previously registered
//the user comes to this page from an email link with a special token valid one time.
$freshmoodletoken = optional_param('freshmoodletoken', '', PARAM_ALPHANUMEXT);
$freshmoodleid = optional_param('id', 0, PARAM_INT);
$freshmoodletokenconf = optional_param('freshmoodletokenconf', 0, PARAM_INT);
if (!empty($freshmoodletoken) and !empty($freshmoodleid)) {
    $freshregistration = get_config('local_hub_unregistration', $freshmoodleid);
    if (!empty($freshregistration)) {
        $freshregistration = unserialize($freshregistration);
        if ($freshregistration['freshmoodletoken'] == $freshmoodletoken) {
            $renderer = $PAGE->get_renderer('local_hub');
            if ($freshmoodletokenconf) {
                //update the registration with new value
                $freshregistration['oldsite'] = (array)  $freshregistration['oldsite'];
                $freshregistration['newsite']['id'] = $freshregistration['oldsite']['id'];
                $newtoken = $hub->register_site($freshregistration['newsite'],
                        $freshregistration['oldsite']['url']);

                //delete the token, no unregistration possible anymore
                set_config($freshmoodleid, null, 'local_hub_unregistration');

                //log the fresh install
                add_to_log(SITEID, 'local_hub', 'fresh/moved site on previously registered', '',
                        $freshregistration['newsite']['id'] . ', ' . $freshregistration['newsite']['url']
                        .',' . $freshregistration['oldsite']['url']);

                //redirect to the new Moodle site to confirm the registration.
                //This could fail if the administrator do not log into the Moodle site.
                //However the administrator will be able to register a new time without
                //previously installed error. So it's not that bad.
                redirect(new moodle_url($freshregistration['newsite']['url']
                        ."/admin/registration/confirmregistration.php",
                    array('newtoken' => $newtoken, 'url' => $CFG->wwwroot,
                        'token' => $freshregistration['newsite']['secret'],
                        'hubname' => get_config('local_hub', 'name'))));
            } else {
                $htmlcontent = $renderer->confirmfreshmoodlereg($freshregistration, $freshmoodletoken, $freshmoodleid);
            }
        } else {
            $tokenerror = true;
        }
    } else {
       $tokenerror = true;
    }

    if (!empty($tokenerror)) {
        throw new moodle_exception(get_string('freshtokenerror', 'local_hub'));
    }

    echo $OUTPUT->header();
    echo $htmlcontent;
    echo $OUTPUT->footer();
    exit();
}

//check if the remote site is available
if (!$hub->is_remote_site_valid($url)) {
    throw new moodle_exception('cannotregisternotavailablesite', 'local_hub', $url);
}

//check if the registration password is correct
$hubpassword = get_config('local_hub', 'password');
if (!empty($hubpassword) and $hubpassword != $password) {
    throw new moodle_exception('wronghubpassword', 'local_hub', $url.'/admin/registration/hubselector.php');
}

//check if the site url is already registered
$sitewithsameurl = $hub->get_site_by_url($url);
if (!empty($sitewithsameurl)) {
    $urlexists = true;
} else {
    $urlexists = false;
}

//check if the secret already exists
$sitewithsamesecret = $hub->get_site_by_secret(md5($token));
if (!empty($sitewithsamesecret)) {
    $secretexists = true;
} else {
    $secretexists = false;
}

if ($secretexists and !$urlexists) { //the site has been moved or the site has been copied
    $action = optional_param('action', '', PARAM_ALPHA);

    switch ($action) {
        case 'moved':

            //update the registration
            $newsitevalues = (object) $sitevalues;
            $newsitevalues->id = $sitewithsamesecret->id;
            unset($newsitevalues->password);
            $newtoken = $hub->register_site($newsitevalues, $sitewithsamesecret->url);

            //log the moved site
            add_to_log(SITEID, 'local_hub', 'site moved', '',
                    'id:' . $sitewithsamesecret->id . ', new url: ' . $newsitevalues->url
                    . ', old url: ' . $sitewithsamesecret->url);

            //redirect to the Moodle site confirming the registration.
            redirect(new moodle_url($url."/admin/registration/confirmregistration.php",
                array('newtoken' => $newtoken, 'url' => $CFG->wwwroot, 'token' => $token,
                    'hubname' => get_config('local_hub', 'name'))));

            break;
        case 'copied':

            //request the Moodle site to generate a new secret (=> new registration)
            redirect(new moodle_url($url."/admin/registration/renewregistration.php",
            array('url' => $CFG->wwwroot, 'token' => $token,
                'hubname' => get_config('local_hub', 'name'))));

        default:
            echo $OUTPUT->header();
            $renderer = $PAGE->get_renderer('local_hub');
            echo $renderer->movedorcopiedsiteform($sitevalues, $sitewithsamesecret);
            echo $OUTPUT->footer();
            exit();
            break;
    }

} else if ((!$secretexists and $urlexists) //New moodle site on a previously registered url
             //Already registered moodle site on another previously registered url
             or ($secretexists and $urlexists and ($sitewithsamesecret->url != $sitewithsameurl->url))){

    //check if a site already attempt a registration
    $registrationattempt = get_config('local_hub_unregistration', $sitewithsameurl->id);
    if (!empty($registrationattempt)) {
        throw new moodle_exception('freshmoodleregistrationerror2');
    }

    //create a temporary registration token to identify the previously registered site administrator
    //The previously registered site admin will receive an email with a link
    //when clicking on the link he will be able to update the registration.
    $freshmoodletoken = md5(uniqid(rand(),1));
    $sitevalues['secret'] = $sitevalues['token'];
    unset($sitevalues['password']);
    unset($sitevalues['token']);
    set_config($sitewithsameurl->id, serialize(
            array('newsite' => $sitevalues, 'oldsite' => $sitewithsameurl,
                'freshmoodletoken' => $freshmoodletoken)),
            'local_hub_unregistration');

    //alert existing "secret" site administrator
    $contactuser = new stdClass();
    $contactuser->email = $sitewithsameurl->contactemail;
    $contactuser->firstname = $sitewithsameurl->contactname;
    $contactuser->lastname = '';
    $contactuser->maildisplay = true;
    $emailinfo = new stdClass();
    $emailinfo->existingsite = $sitewithsameurl->name;
    $emailinfo->hubname = get_config('local_hub', 'name');
    $freshregistrationurl = new moodle_url('/local/hub/siteregistration.php',
            array('freshmoodletoken' => $freshmoodletoken, 'id' => $sitewithsameurl->id));
    $emailinfo->deletesiteregistration = $freshregistrationurl->out(false);
    $emailinfo->url = $sitewithsameurl->url;
    email_to_user($contactuser, get_admin(),
            get_string('emailtitleurlalreadyexists', 'local_hub', $emailinfo),
            get_string('emailmessageurlalreadyexists', 'local_hub', $emailinfo));

    throw new moodle_exception('freshmoodleregistrationerror', 'local_hub',
            new moodle_url($url), $sitewithsameurl);

}



//fill the "recaptcha" Moodle form with hub values
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
    $siteinfo->secret = $fromform->token;
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

    if (!$secretexists and !$urlexists) {
        $newtoken = $hub->register_site($siteinfo);
        //log the new site
        add_to_log(SITEID, 'local_hub', 'new site registration', '', $siteinfo->url);
    } else if ($secretexists and $urlexists and
            ($sitewithsamesecret->url == $sitewithsameurl->url)) {
        //the site is already registered
        //It happens when new fresh site has been installed and the email link
        //wasn't followed till the end of the registration replacement process.
        $newtoken = $hub->register_site($siteinfo, $siteinfo->url);

        //log the overwritting site registration
        add_to_log(SITEID, 'local_hub', 'site registered a new time', '', $siteinfo->url);
    } else {
        //log the code logic error (it should never happen)
        add_to_log(SITEID, 'local_hub', 'registration code logic error', '', $siteinfo->url);
        throw new moodle_exception('codelogicerror', 'local_hub');
    }

    //Redirect to the site with the created token
    redirect(new moodle_url($url."/admin/registration/confirmregistration.php",
            array('newtoken' => $newtoken, 'url' => $CFG->wwwroot, 'token' => $fromform->token,
                'hubname' => get_config('local_hub', 'name'))));

} 

echo $OUTPUT->header();
$siteconfirmationform->display();
echo $OUTPUT->footer();
