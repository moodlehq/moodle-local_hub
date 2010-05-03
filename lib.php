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
 * Hub library
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



//// HUB IMAGE SIZE

/**
 * maximum width that a logo image can have (used to display site logo
 * in community hub)
 */
define('HUBLOGOIMAGEWIDTH', 150);

/**
 * maximum height that a logo image can have (used to display site logo
 * in community hub)
 */
define('HUBLOGOIMAGEHEIGHT', 150);



//// HUB PRIVACY

/**
 * Hub privacy: private
 */
define('HUBPRIVATE', 'private');

/**
 * Hub privacy: public
 */
define('HUBALLOWPUBLICSEARCH', 'public');

/**
 * Hub privacy: public and global
 */
define('HUBALLOWGLOBALSEARCH', 'search');



//// Communication /////

/**
 * Hub server
 */
define('HUB', 'hub');

/**
 * Registered site
 */
define('REGISTEREDSITE', 'site');

/**
 * Public site
 */
define('PUBLICSITE', 'publicsite');

/**
 * Hub directory
 */
define('HUBDIRECTORY', 'hubdirectory');

/**
 * WS server
 */
define('WSSERVER', 'server');

/**
 * WS client
 */
define('WSCLIENT', 'client');

require_once($CFG->dirroot.'/lib/hublib.php'); //get_site_privacy_string()



class local_hub {

///////////////////////////
/// DB Facade functions  //
///////////////////////////


    /**
     * Return a site for a given id
     * @param integer $id
     * @return object site, false if null
     */
    public function get_site($id) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('id'=>$id));
    }

    /**
     * Remove a site from the directory (delete the row from DB)
     * @param integer $id - id of the site to remove from the directory
     * @return boolean true
     * @throws dml_exception if error
     */
    public function delete_site($id) {
        global $DB;
        return $DB->delete_records('hub_site_directory', array('id'=>$id));
    }

    /**
     * Update a site
     * @param object $site
     * @throws dml_exception if error
     */
    public function update_site($site) {
        global $DB;
        $site->timemodified = time();
        $DB->update_record('hub_site_directory', $site);
    }

    /**
     * Add a site into the site directory
     * @param object $site
     * @return object site
     * @throws dml_exception if error
     */
    public function add_site($site) {
        global $DB;
        $site->timeregistered = time();
        $site->timemodified = time();
        $site->id = $DB->insert_record('hub_site_directory', $site);
        return $site;
    }

    /**
     * Add a course into the course directory
     * @param object $
     * @return object course
     * @throws dml_exception if error
     */
    public function add_course($course) {
        global $DB;
        $course->timemodified = time();
        $course->id = $DB->insert_record('hub_course_directory', $course);
        return $course;
    }

    /**
     * Return a course for a given id
     * @param integer $id
     * @return object course, false if null
     */
    public function get_course($id) {
        global $DB;
        return $DB->get_record('hub_course_directory', array('id'=>$id));
    }

    /**
     * Remove a course from the directory (delete the row from DB)
     * @param integer $id - id of the course to remove from the directory
     * @return boolean true
     * @throws dml_exception if error
     */
    public function delete_course($id) {
        global $DB;
        return $DB->delete_records('hub_course_directory', array('id'=>$id));
    }

    /**
     * Update a course
     * @param object $course
     * @throws dml_exception if error
     */
    public function update_course($course) {
        global $DB;
        $course->timemodified = time();
        $DB->update_record('hub_course_directory', $course);
    }

    /**
     * Return course found against some parameters, by default it returns all visible courses
     * @param string $search String that will be compared to course name and site description
     * @param string $language language code to compare (has to be exact)
     * @param boolean $onlyvisible - set to false to return full list
     * @return array of courses
     */
    public function get_courses($search =null, $language =null, $onlyvisible = true, $downloadable = true, $enrollable = true) {
        global $DB;

        $sqlparams = array();
        $wheresql = '';

        if (!empty($onlyvisible)) {
            $wheresql .= " privacy = :visible";
            $sqlparams['visible'] = 1;
            $ordersql = 'trusted DESC, fullname';
        } else {
            $ordersql = 'trusted DESC, privacy DESC, fullname';
        }

        if (!empty($search)) {
            if (!empty($onlyvisible)) {
                $wheresql .= " AND";
            }
            $wheresql .= " (fullname ".$DB->sql_ilike()." :namesearch OR description ".$DB->sql_ilike()." :descsearch)";
            $sqlparams['namesearch'] = '%'.$search.'%';
            $sqlparams['descsearch'] = '%'.$search.'%';
        }

        if (!empty($language)) {
            if (!empty($onlyvisible) || !empty($search) ) {
                $wheresql .= " AND";
            }
            $wheresql .= " language = :language";
            $sqlparams['language'] = $language;
        }

        if (!($downloadable and $enrollable)) {
            if (!empty($onlyvisible) || !empty($search) || !empty($language)) {
                $wheresql .= " AND";
            }
            if ($downloadable) {
                $wheresql .= " enrollable = 0";
            } else {
                $wheresql .= " enrollable = 1";
            }
        }

        $courses = $DB->get_records_select('hub_course_directory', $wheresql, $sqlparams, $ordersql);
        return $courses;
    }


    /**
     * Return sites found against some parameters, by default it returns all visible sites
     * @param string $search String that will be compared to site name and site description
     * @param string $language language code to compare (has to be exact)
     * @param boolean $onlyvisible - set to false to return full list
     * @return array of sites
     */
    public function get_sites($search =null, $language =null, $onlyvisible = true) {
        global $DB;

        $sqlparams = array();
        $wheresql = '';

        if (!empty($onlyvisible)) {
            $wheresql .= " visible = :visible";
            $sqlparams['visible'] = 1;
            $ordersql = 'prioritise DESC, trusted DESC, name';
        } else {
            $ordersql = 'prioritise DESC, trusted DESC, visible DESC, name';
        }

        if (!empty($search)) {
            if (!empty($onlyvisible)) {
                $wheresql .= " AND";
            }
            $wheresql .= " (name ".$DB->sql_ilike()." :namesearch OR description ".$DB->sql_ilike()." :descsearch)";
            $sqlparams['namesearch'] = '%'.$search.'%';
            $sqlparams['descsearch'] = '%'.$search.'%';
        }

        if (!empty($language)) {
            if (!empty($onlyvisible) || !empty($search)) {
                $wheresql .= " AND";
            }
            $wheresql .= " language = :language";
            $sqlparams['language'] = $language;
        }

        $sites = $DB->get_records_select('hub_site_directory', $wheresql, $sqlparams, $ordersql);
        return $sites;
    }

    /**
     * Return a site for a given token
     * @param string $token
     * @return object site , false if null
     */
    public function get_site_by_token($token) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('token' => $token));
    }

    /**
     * Return a site for a given url
     * @param string $url
     * @return object site , false if null
     */
    public function get_site_by_url($url) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('url' => $url));
    }

    /**
     * Return number of visible registered sites
     * @return integer
     */
    public function get_registered_sites_total() {
        global $DB;
        return $DB->count_records('hub_site_directory', array('visible' => 1));
    }

    /**
     * TODO: Return number of visible registered courses
     * @return integer
     */
    public function get_registered_courses_total() {
        global $DB;
        return 0;
        //return $DB->count_records('hub_courses_directory', array('visible' => 1));
    }


    public function add_communication($communication) {
        global $DB;
        $id = $DB->insert_record('hub_communications', $communication);
        return $id;
    }

    public function get_communication($type, $remoteentity, $remoteurl = null, $token = null) {
        global $DB;

        $params = array('type' => $type,
                'remoteentity' => $remoteentity);
        if (!empty($remoteurl)) {
            $params['remoteurl'] = $remoteurl;
        }
        if (!empty($token)) {
            $params['token'] = $token;
        }
        $token = $DB->get_record('hub_communications',$params);
        return $token;
    }

    public function confirm_communication($communication) {
        global $DB;
        $communication->confirmed = 1;
        $DB->update_record('hub_communications', $communication);
    }


///////////////////////////
/// Library functions   ///
///////////////////////////


    /**
     * Return hub server information
     * @return array
     */
    public function get_info() {
        global $CFG;
        $hubinfo = array();
        $hubinfo['name'] = get_config('local_hub', 'name');
        $hubinfo['description'] = get_config('local_hub', 'description');
        $hubinfo['contactname'] = get_config('local_hub', 'contactname');
        $hubinfo['contactemail'] = get_config('local_hub', 'contactemail');
        $hubinfo['imageurl'] = get_config('local_hub', 'imageurl');
        $hubinfo['privacy'] = get_config('local_hub', 'privacy');
        $hubinfo['language'] = get_config('local_hub', 'language');
        $hubinfo['url'] = $CFG->wwwroot;
        $hubinfo['sites'] = $this->get_registered_sites_total();
        $hubinfo['courses'] = $this->get_registered_courses_total();
        return $hubinfo;
    }


    /**
     * Retrieve the privacy string matching the define value
     * @param string $privacy must match the define into moodlelib.php
     * @return string
     */
    public function get_privacy_string($privacy) {
        switch ($privacy) {
            case HUBPRIVATE:
                $privacystring = get_string('nosearch', 'local_hub');
                break;
            case HUBALLOWPUBLICSEARCH:
                $privacystring = get_string('allowpublicsearch', 'local_hub');
                break;
            case HUBALLOWGLOBALSEARCH:
                $privacystring = get_string('allowglobalsearch', 'local_hub');
                break;
        }
        if (empty($privacy)) {
            throw new moodle_exception('unknownprivacy');
        }
        return $privacystring;
    }



    public function register_course($course, $siteurl) {
        global $CFG;

        //$siteinfo must be an object
        if (is_array($course)) {
            $course = (object) $course;
        }

        //check if the language exist
        //Note: it should have been tested on client side
        $languages = get_string_manager()->get_list_of_languages();
        if (!key_exists($course->language, $languages)) {
            throw new moodle_exception('errorlangnotrecognized', 'hub');
        }

        $site = $this->get_site_by_url($siteurl);
        $course->siteid = $site->id;

        if (!empty($course->share)) {
            $course->format = 'zip';
        } else {
            $course->format = 'url';
        }

        $course->privacy = 0;
        $course->trusted = 0;

        $this->add_course($course);

    }


    /**
     * Register the site (creation / update)
     * 1- check token doesn't already existing / check if url different
     * 2- check lang and image size
     * 3- create new token for the site to call the hub server
     * 4- confirm the registration to the site (web service)
     * 5- add / update site
     * 6- send email to the hub administrator
     * @param object $siteinfo
     * @param boolean $siteurltoupdate
     */
    public function register_site($siteinfo, $siteurltoupdate='') {
        global $CFG;

        //$siteinfo must be an object
        if (is_array($siteinfo)) {
            $siteinfo = (object) $siteinfo;
        }

        //if update, check if the url changed, if yes it could be a potential hack attempt
        //=> make the hub not visible and alert the administrator
        if (!empty($siteurltoupdate)) {
            $sameurl = 1;

            //retrieve current hub info
            $currentsiteinfo = $this->get_site_by_url($siteurltoupdate);
            $siteinfo->id = $currentsiteinfo->id; //needed for hub update
            $siteinfo->oldurl = $currentsiteinfo->url; //needed for url testing

            if ($siteinfo->url != $siteinfo->oldurl) {
                //make the site not visible (hub admin need to reconfirm it)
                $siteinfo->visible = 0;

                $siteinfo->oldname = $currentsiteinfo->name;
                $siteinfo->olddescription = $currentsiteinfo->description;
                $siteinfo->oldcontactname = $currentsiteinfo->contactname;
                $siteinfo->oldcontactemail = $currentsiteinfo->contactemail;
                $siteinfo->oldcontactphone = $currentsiteinfo->contactphone;
                $siteinfo->oldimageurl = $currentsiteinfo->imageurl;
                $siteinfo->oldprivacy = $currentsiteinfo->privacy;
                $siteinfo->oldlanguage = $currentsiteinfo->language;
                $siteinfo->oldusers = $currentsiteinfo->users;
                $siteinfo->oldcourses = $currentsiteinfo->courses;
                $siteinfo->oldstreet = $currentsiteinfo->street;
                $siteinfo->oldregioncode = $currentsiteinfo->regioncode;
                $siteinfo->oldcountrycode = $currentsiteinfo->countrycode;
                $siteinfo->oldgeolocation = $currentsiteinfo->geolocation;
                $siteinfo->oldcontactable = $currentsiteinfo->contactable;
                $siteinfo->oldemailalert = $currentsiteinfo->emailalert;
                $siteinfo->oldenrolments = $currentsiteinfo->enrolments;
                $siteinfo->oldposts = $currentsiteinfo->posts;
                $siteinfo->oldquestions = $currentsiteinfo->questions;
                $siteinfo->oldresources = $currentsiteinfo->resources;
                $siteinfo->oldmodulenumberaverage = $currentsiteinfo->modulenumberaverage;
                $siteinfo->oldparticipantnumberaverage = $currentsiteinfo->participantnumberaverage;
                $siteinfo->oldmoodleversion = $currentsiteinfo->moodleversion;
                $siteinfo->oldmoodlerelease = $currentsiteinfo->moodlerelease;

                $sameurl = 0;

                $hub = new hub();
                $siteinfo->oldprivacystring = $hub->get_site_privacy_string($siteinfo->oldprivacy);
                $siteinfo->privacystring = $hub->get_site_privacy_string($siteinfo->privacy);

                //alert the administrator
                $contactuser = new object;
                $contactuser->email = $siteinfo->contactemail ? $siteinfo->contactemail : $CFG->noreplyaddress;
                $contactuser->firstname = $siteinfo->contactname ? $siteinfo->contactname : get_string('noreplyname');
                $contactuser->lastname = '';
                $contactuser->maildisplay = true;
//                email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteurlchanged', 'local_hub', $siteinfo->name),
//                        get_string('emailmessagesiteurlchanged', 'local_hub', $siteinfo));
            }


        } else {
            //if creation mode, check that the token don't exist already
            $checkedhub = $this->get_site_by_token($siteinfo->token);
            if (!empty($checkedhub)) { //no registration process failed but the token still exist
                throw new moodle_exception('sitetokenalreadyexist'); //probably token already attributed, should never happen
            }
        }

        //check if the language exist
        //Note: it should have been tested on client side
        $languages = get_string_manager()->get_list_of_languages();
        if (!key_exists($siteinfo->language, $languages)) {
            throw new moodle_exception('errorlangnotrecognized', 'hub', new moodle_url('/local/hub/index.php'));
        }

        //check if the image (imageurl) has a correct size
        //Note: it should have been tested on client side
        if (!empty($siteinfo->imageurl)) {
            list($imagewidth, $imageheight, $imagetype, $imageattr)  = getimagesize($siteinfo->imageurl); //getimagesize is a GD function
            if ($imagewidth > HUBLOGOIMAGEWIDTH or $imageheight > HUBLOGOIMAGEHEIGHT) {
                $sizestrings = new stdClass();
                $sizestrings->width = HUBLOGOIMAGEWIDTH;
                $sizestrings->height = HUBLOGOIMAGEHEIGHT;
                throw new moodle_exception('errorbadimageheightwidth', 'local_hub',
                new moodle_url('/local/hub/index.php'), $sizestrings);
            }

            //TODO we do not record image yet, it could be a security issue
            $siteinfo->imageurl = '';

        }

        //Add or update the site into the site directory (hub)
        if (!empty($siteurltoupdate)) {
            $this->update_site($siteinfo);
        } else {
            $this->add_site($siteinfo);
        }

        //we save the token into the communication table in order to have a reference to the hidden token
        $sitetohubcommunication = $this->get_communication(WSSERVER, REGISTEREDSITE, $siteinfo->url);
        if (empty($sitetohubcommunication)) {
            //create token for the hub
            $capabilities = array('moodle/hub:updateinfo', 'moodle/hub:registercourse');
            $tokenusedbysite = $this->create_hub_token('Registered Hub User', 'Registered site', $siteinfo->url.'_registered_site_user',
                    $capabilities);

            $sitetohubcommunication = new stdClass();
            $sitetohubcommunication->token = $tokenusedbysite->token;
            $sitetohubcommunication->type = WSSERVER;
            $sitetohubcommunication->remoteentity = REGISTEREDSITE;
            $sitetohubcommunication->remotename = $siteinfo->name;
            $sitetohubcommunication->remoteurl = $siteinfo->url;
            $sitetohubcommunication->confirmed = 1;
            $sitetohubcommunication->id = $this->add_communication($sitetohubcommunication);
        }

        //send email to the Moodle administrator
        $contactuser = new object;
        $contactuser->email = $siteinfo->contactemail ? $siteinfo->contactemail : $CFG->noreplyaddress;
        $contactuser->firstname = $siteinfo->contactname ? $siteinfo->contactname : get_string('noreplyname');
        $contactuser->lastname = '';
        $contactuser->maildisplay = true;
//        if (!empty($siteurltoupdate)) {
//            email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteupdated', 'local_hub', $siteinfo->name),
//                    get_string('emailmessagesiteupdated', 'local_hub', $siteinfo));
//        } else {
//            email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteadded', 'local_hub', $siteinfo->name),
//                    get_string('emailmessagesiteadded', 'local_hub', $siteinfo));
//        }

        return $sitetohubcommunication->token;
    }


    /**
     * Create a user, role and token. Return the created token id.
     * @global <type> $CFG
     * @global <type> $DB
     * @param <type> $name
     * @param <type> $servicename
     * @param <type> $capabilities
     * @return <type>
     */
    function create_hub_token($rolename, $servicename, $username, $capabilities) {
        global $CFG, $DB;

        //requires libraries
        require_once($CFG->dirroot.'/user/lib.php');

        //check the hidden service
        //because we cannot know the id of the service, we consider that hidden services have unique name!
        $services = $DB->get_records('external_services', array('name' => $servicename));
        //if ever we have two hidden service with the same name, it's due to a programmation error
        if (count($services) > 1) {
            throw new moodle_exception('hiddenservicewithsamename');
        }
        if (count($services) < 1) {
            throw new moodle_exception('unknownservicename');
        }

        $role = $DB->get_record('role', array('name' => $rolename));
        if (empty($role)) {
            $roleid = create_role($rolename, clean_param($rolename, PARAM_ALPHAEXT), get_string('hubwsroledescription', 'local_hub'), '', true);
        } else {
            $roleid = $role->id;
        }

        //check and create a user
        $user = $DB->get_record('user', array('username' => $username,
                'idnumber' => $username));
        if (empty($user)) {
            $user->username = $username;
            $user->firstname = $username;
            $user->lastname = get_string('donotdeleteormodify', 'local_hub');
            $user->password = ''; //login no authorised with webservice authentication
            $user->auth = 'webservice';
            $user->confirmed = 1; //need to be confirmed otherwise got deleted
            $user->idnumber = $username;
            $user->mnethostid = 1;
            $user->description = get_string('hubwsuserdescription', 'local_hub');
            $user->id = user_create_user($user);
        }

        //check and assign the role to user
        $context = get_context_instance(CONTEXT_SYSTEM);
        $roleusers = get_role_users($roleid, $context);
        foreach ($roleusers as $roleuser) {
            if ($roleuser->username == $username) {
                $userfound = true;
            }
        }
        if (empty($userfound)) {
            role_assign($roleid, $user->id, null, $context->id);
        }


        //check and assign capabilities to role
        $capabilities[] = 'webservice/xmlrpc:use';
        if (empty($role)) {
            $role = new stdClass();
            $role->id = $roleid;
        }
        $rolecapabilities = get_capabilities_from_role_on_context($role, $context);
        if (!empty($capabilities)) {
            foreach ($capabilities as $capability) {
                $capabilityassigned = false;
                foreach($rolecapabilities as $rolecapability) {
                    if ($rolecapability->capability == $capability) {
                        $capabilityassigned = true;
                        break;
                    }
                }

                if(!$capabilityassigned) {
                    assign_capability($capability, CAP_ALLOW, $roleid, $context->id);
                }
            }
        }

        //enable the hidden service and assign it to the user
        foreach($services as $service) { //there should be only one service into the array!!!
            //checked at beginning of the function
            $serviceid = $service->id;
            //if no hidden token was created for this service, we need to enable it
            if (!$service->enabled) {
                $service->enabled = 1;
                $DB->update_record('external_services', $service);
            }

            $serviceuser = $DB->get_record('external_services_users',
                    array('externalserviceid' => $serviceid, 'userid' => $user->id));
            if (empty($serviceuser)) {
                $serviceuser = new stdClass();
                $serviceuser->externalserviceid = $serviceid;
                $serviceuser->userid = $user->id;
                $serviceuser->timecreated = time();
                $DB->insert_record('external_services_users', $serviceuser);
            }

        }

        //check and create a token
        $resulttoken = new stdClass();
        $resulttoken->userid = $user->id;
        $resulttoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
        $resulttoken->externalserviceid = $serviceid;
        $resulttoken->contextid = $context->id;
        $resulttoken->creatorid = $user->id;
        $token = $DB->get_record('external_tokens', (array) $resulttoken);
        if (empty($token)) {
            $resulttoken->timecreated = time();
            $resulttoken->token = md5(uniqid(rand(),1));
            $tokenid = $DB->insert_record('external_tokens', $resulttoken);
            $resulttoken->id =$tokenid;
        } else {
            throw new moodle_exception('hiddentokenalreadyexist');
        }

        return $resulttoken;
    }

    /**
     * TODO: temporary function till file upload design done (course unique ref not used)
     * Add a screenshots to a course
     * @param <type> $file
     * @param <type> $filename
     * @param <type> $courseshortname
     * @param <type> $siteurl
     */
    public function add_screenshot($file, $filename, $course) {

    }

    /**
     * TODO: temporary function till file upload design done  (course unique ref not used)
     * Add a backup to a course
     * @param <type> $file
     * @param <type> $filename
     * @param <type> $course
     */
    public function add_backup($file, $filename, $course) {
        $context = get_system_context();
        $record->contextid = $context->id;
        $record->filearea = 'hub_backup';
        $record->itemid = $course->id;
        $record->filename = $filename;
        $record->filepath = '/';
        $fs = get_file_storage();
        $fs->create_file_from_pathname($record, $file['tmp_name']);

    }

    /**
     * TODO: temporary function till file download design done  (course unique ref not used)
     * Get a backup
     * @param <type> $filename
     * @param <type> $course
     */
    public function get_backup($filename, $courseid) {
        $context = get_system_context();
        $browser = get_file_browser();
        $fileinfo = $browser->get_file_info($context, 'hub_backup',
                $courseid, '/', $filename);
        return $fileinfo;
    }

}