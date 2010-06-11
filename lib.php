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


/**
 * maximum number of screenshot for a course
 */
define('MAXSCREENSHOTSNUMBER', 10);



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



//// Course visibility ////

/**
 * Course visibility: all
 */
define('COURSEVISIBILITY_ALL', '2');

/**
 * Course visibility: all
 */
define('COURSEVISIBILITY_VISIBLE', '1');

/**
 * Course visibility: all
 */
define('COURSEVISIBILITY_NOTVISIBLE', '0');



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
     * @return integer course id
     * @throws dml_exception if error
     */
    public function add_course($course) {
        global $DB;
        $course->timemodified = time();
        $id = $DB->insert_record('hub_course_directory', $course);
        return $id;
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
     * Return a enrollable course for a given site id and course id
     * @param integer $id
     * @return object course, false if null
     */
    public function get_enrollable_course_by_site($siteid, $sitecourseid) {
        global $DB;
        return $DB->get_record('hub_course_directory',
                array('siteid'=>$siteid, 'sitecourseid' => $sitecourseid, 'enrollable' => 1));
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
     * Return course found against some options, by default it returns all visible courses
     * @param array $options all different search options
     *  string  'search' string that will be compared to course name and site description
     *  string  'options' language, license, audience, subject...
     *  boolean 'onlyvisible' - set to false to return full list
     *  boolean 'downloadable' - set to true to return downloadable course
     *  boolean 'enrollable' - set to true to return enrollable course
     *  boolean 'onlydeleted' - set to true to return deleted course only, otherwise only undeleted
     * @return array of courses
     */
    public function get_courses($options = array()) {
        global $DB;

        $sqlparams = array();
        $wheresql = '';

        if (!empty($options['onlyvisible'])) {
            $wheresql .= " privacy = :visible";
            $sqlparams['visible'] = 1;
            $ordersql = 'fullname';
        } else {
            $ordersql = 'privacy DESC, fullname';
        }

        if (!empty($options['search'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " (fullname ".$DB->sql_ilike()." :namesearch OR description ".$DB->sql_ilike()." :descsearch)";
            $sqlparams['namesearch'] = '%'.$options['search'].'%';
            $sqlparams['descsearch'] = '%'.$options['search'].'%';
        }

        if (!empty($options['language'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " language = :language";
            $sqlparams['language'] = $options['language'];
        }

        if (!empty($options['audience'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " audience = :audience";
            $sqlparams['audience'] = $options['audience'];
        }

        if (!empty($options['licenceshortname'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " licenceshortname = :licenceshortname";
            $sqlparams['licenceshortname'] = $options['licenceshortname'];
        }

        if (!empty($options['subject'])) {
            if (!empty($wheresql)) {
                        $wheresql .= " AND";
            }
            //search subject and all sub-subjects
            $edufields = get_string_manager()->load_component_strings('edufields', 'en');
            $topsubject = true;
            foreach($edufields as $key => $value) {
                if (strpos($key, $options['subject']) !==false) {
                    if ($topsubject) {
                        $wheresql .= " (";
                        $topsubject = false;
                    } else {
                        $wheresql .= " OR";
                    }
                    $wheresql .= " subject = :".$key;
                    $sqlparams[$key] = $key;
                }
            }
            $wheresql .= ")";      
        }

        if (!empty($options['educationallevel'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " educationallevel = :educationallevel";
            $sqlparams['educationallevel'] = $options['educationallevel'];
        }

        //this option should be only be called by admin script
        //note that this option is overrided by the onlyvisible parameter
        if (key_exists('visibility', $options) and $options['visibility'] != COURSEVISIBILITY_ALL) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            if ($options['visibility'] === COURSEVISIBILITY_VISIBLE) {
                $privacy = 1;
            } else {
                $privacy = 0;
            }
            $wheresql .= " privacy = :visibility";
            $sqlparams['visibility'] = $privacy;
        }

        if (!empty($options['ids'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $idlist = '(';
            foreach($options['ids'] as $id) {
                if ($idlist == '(') {
                    $idlist .= $id;
                } else {
                    $idlist .= ','.$id;
                }
            }
            $idlist .= ')';
            $wheresql .= " id IN ". $idlist;
        }

        if (!empty($options['sitecourseids'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $idlist = '(';
            foreach($options['sitecourseids'] as $sitecourseid) {
                if ($idlist == '(') {
                    $idlist .= $sitecourseid;
                } else {
                    $idlist .= ','.$sitecourseid;
                }
            }
            $idlist .= ')';
            $wheresql .= " sitecourseid IN ". $idlist;
        }

        //check that one of the downloadable/enrollable option is false (otherwise display both kind of course)
        if (! ((key_exists('downloadable', $options) and $options['downloadable'])
                and
              (key_exists('enrollable', $options) and $options['enrollable']))) {

            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }

            if (key_exists('downloadable', $options) and $options['downloadable']) {
                $wheresql .= " enrollable = 0";
            } else if (key_exists('enrollable', $options) and $options['enrollable']) {
                $wheresql .= " enrollable = 1";
            } else { //this case means that we are searching course as downloadable == 0 and enrollable == 0
                $wheresql .= " enrollable = 4"; //=> return nothing,
                //it should never be ask to return a course not enrollable AND not downloadable
            }
        }

        if (!empty($options['allsitecourses'])) {
            $siteid = $options['siteid'];
             if (!empty($siteid)) {
                if (!empty($wheresql)) {
                    $wheresql .= " AND";
                }
                $wheresql .= " siteid = :siteid";
                $sqlparams['siteid'] = $siteid;
             }
        }

        if (!empty($wheresql)) {
                $wheresql .= " AND";
        }
        if (!key_exists('onlydeleted', $options) or !$options['onlydeleted']) {          
            $wheresql .= " deleted = 0";
        } else {            
            $wheresql .= " deleted = 1";
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
    public function get_sites($options = array()) {
        global $DB;

        $sqlparams = array();
        $wheresql = '';

        if (key_exists('onlyvisible', $options) and !empty($options['onlyvisible'])) {
            $wheresql .= " visible = :visible";
            $sqlparams['visible'] = 1;
            $ordersql = 'prioritise DESC, trusted DESC, name';
        } else {
            $ordersql = 'prioritise DESC, trusted DESC, visible DESC, name';
        }

        if (key_exists('search', $options) and !empty($options['search'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " (name ".$DB->sql_ilike()." :namesearch OR description ".$DB->sql_ilike()." :descsearch)";
            $sqlparams['namesearch'] = '%'.$options['search'].'%';
            $sqlparams['descsearch'] = '%'.$options['search'].'%';
        }

        if (key_exists('language', $options) and !empty($options['language'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " language = :language";
            $sqlparams['language'] = $options['language'];
        }

        if (key_exists('urls', $options) and !empty($options['urls'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $urllist = '(\'';
            foreach($options['urls'] as $url) {
                if ($urllist == '(\'') {
                    $urllist .= $url;
                } else {
                    $urllist .= '\',\''.$url;
                }
            }
            $urllist .= '\')';
            $wheresql .= " url IN ". $urllist;
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
     * Return number of visible registered courses
     * @return integer
     */
    public function get_registered_courses_total() {
        global $DB;
        return $DB->count_records('hub_course_directory', array('privacy' => 1));
    }

    /**
     * Record a communication information between the hub and another entity (hub directory, site, public site)
     * Mostly use to remember the token given to us by the remote entity, or the token that we gave to the remote entity
     * A communication is composed by:
     *     type of the communication, hub point of view: SERVER / CLIENT
     *     remoteentity name
     *     remoteentity url
     *     token used during this communication
     * @param object $communication
     * @return int id of the create communication into the DB
     */
    public function add_communication($communication) {
        global $DB;
        $id = $DB->insert_record('hub_communications', $communication);
        return $id;
    }

    /**
     * Get communication information between the hub and another entity (hub directory, site, public site)
     * Mostly use to remember the token given to us by the remote entity, or the token that we gave to the remote entity
     * @param string $type can be SERVER or CLIENT. SERVER mean that the hub is the server into the communication, so the token
     * refered, is used by the remote entity to call the a hub function. CLIENT mean that the hub used the token to call
     * the remote entity function
     * @param string $remoteentity the name of the remote entity
     * @param string $remoteurl the token of the remote entity
     * @param string $token the token used by this communication
     * @return object return the communication
     */
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

    /**
     * Delete communication
     * @param integer $communicationid
     */
    public function delete_communication($communicationid) {
        global $DB;
        $DB->delete_records('hub_communications', array('id' => $communicationid));
    }

    /**
     * Confirm a communication
     * When the hub try to register on the hub directory, it first creates a token for the hub directory, send it,
     * and wait for the hub directory to confirm the communication has been established. Then the hub call this function to confirm
     * the communication.
     * @param object $communication
     */
    public function confirm_communication($communication) {
        global $DB;
        $communication->confirmed = 1;
        $DB->update_record('hub_communications', $communication);
    }

    /**
     * Add a course content
     * @param object $content
     */
    public function add_course_content($content) {
        global $DB;
        $DB->insert_record('hub_course_contents', $content);
    }

    /**
     * Delete all course contents of a course
     * @param int $courseid
     */
    public function delete_course_contents($courseid) {
        global $DB;
        $DB->delete_records('hub_course_contents', array('courseid' => $courseid));
    }

    /**
     * Get all course contents of a course
     * @param integer $courseid
     * @return array of course contents
     */
    public function get_course_contents($courseid) {
        global $DB;
        return $DB->get_records('hub_course_contents', array('courseid' => $courseid), 'contentcount DESC');
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
        $hubinfo['hublogo'] = get_config('local_hub', 'hublogo');
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

    /**
     * Unregister a course from the directory
     * If the course doesn't exist do nothing - no error thrown
     * If the course site id doesn't match the site url, throw an error
     * @param int $courseid
     * @param string $siteurl
     */
    public function unregister_course($courseid, $siteurl) {
        global $CFG;
        
        $site = $this->get_site_by_url($siteurl);
        $course = $this->get_course($courseid);

        //check that the course exist (otherwise unregister)
        if (!empty($course)) {
            //check that the course match the site
            if (empty($site) or ($course->siteid != $site->id)) {
                throw new moodle_exception('triedtounregisteracourseforwrongsite');
            }
            $course->deleted = 1;
            $this->update_course($course);
        }
    }

    /**
     * Register a course for a specific site
     * @param object $course
     * @param string $siteurl
     * @return int course id
     */
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

        if ($site->trusted) {
            $course->privacy = 1;
        } else {
            $course->privacy = 0;
        }
        $course->deleted = 0;

        //if the course is enrollable and is already registered, update it
        $existingenrollablecourse= $this->get_enrollable_course_by_site($course->siteid, $course->sitecourseid);
        if (!empty($existingenrollablecourse)) {
            $course->id = $existingenrollablecourse->id;
            $courseid = $existingenrollablecourse->id;
            $this->update_course($course);

            //delete previous course content
            $this->delete_course_contents($courseid);

        } else {
            $courseid = $this->add_course($course);
        }

        //add new course contents
        if (!empty($course->contents)) {
            foreach ( $course->contents as $content) {
                $content['courseid'] = $courseid;
                $this->add_course_content($content);
            }
        }

        //delete all screenshots if required
        if (!empty($course->deletescreenshots)) {

            $level1 = floor($courseid / 1000) * 1000;

            $userdir = "hub/$level1/$courseid";

            $directory = make_upload_directory($userdir);

            for ($screenshotnumber = 1; $screenshotnumber <= MAXSCREENSHOTSNUMBER; $screenshotnumber = $screenshotnumber + 1) {

               //delete all existing screenshot
                if ($this->screenshot_exists($courseid, $screenshotnumber)) {
                    unlink($directory.'/screenshot_'.$courseid."_".$screenshotnumber);
                }
            }
        }

        return $courseid;

    }


    /**
     * Register the site (creation / update)
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

                //check if the site url already exist
                $existingurlsite = $this->get_site_by_url($siteinfo->url);
                if (!empty($existingurlsite)) {
                    throw new moodle_exception('urlalreadyexist', 'local_hub', $CFG->wwwroot);
                }

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

                require_once($CFG->dirroot.'/admin/registration/lib.php'); //get_site_privacy_string()
                $registrationmanager = new registration_manager();
                $siteinfo->oldprivacystring = $registrationmanager->get_site_privacy_string($siteinfo->oldprivacy);
                $siteinfo->privacystring = $registrationmanager->get_site_privacy_string($siteinfo->privacy);

                //alert the administrator
                $contactuser = new object;
                $contactuser->email = $siteinfo->contactemail ? $siteinfo->contactemail : $CFG->noreplyaddress;
                $contactuser->firstname = $siteinfo->contactname ? $siteinfo->contactname : get_string('noreplyname');
                $contactuser->lastname = '';
                $contactuser->maildisplay = true;
                email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteurlchanged', 'local_hub', $siteinfo->name),
                        get_string('emailmessagesiteurlchanged', 'local_hub', $siteinfo));
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
            throw new moodle_exception('errorlangnotrecognized', 'hub', new moodle_url('/index.php'));
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
                new moodle_url('/index.php'), $sizestrings);
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
            $capabilities = array('moodle/hub:updateinfo', 'moodle/hub:registercourse', 'moodle/hub:view', 'moodle/hub:unregistercourse');
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
        $emailinfo = $siteinfo;
        $emailinfo->huburl = $CFG->wwwroot;
        $emailinfo->managesiteurl = $CFG->wwwroot.'/local/hub/admin/managesites.php';
        $languages = get_string_manager()->get_list_of_languages();
        $emailinfo->language = $languages[$siteinfo->language];
        if (!empty($siteurltoupdate)) {
            email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteupdated', 'local_hub', $emailinfo->name),
                    get_string('emailmessagesiteupdated', 'local_hub', $emailinfo));
        } else {
            email_to_user(get_admin(), $contactuser, get_string('emailtitlesiteadded', 'local_hub', $emailinfo->name),
                    get_string('emailmessagesiteadded', 'local_hub', $emailinfo));
        }

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
     * TODO: this is temporary till the way to send file by ws is defined
     * Add a screenshots to a course
     * @param array $file
     * @param integer $courseid
     */
    public function add_screenshot($file, $courseid, $screenshotnumber) {

        // Generate a two-level path for the userid. First level groups them by slices of 1000 users, second level is userid
        $level1 = floor($courseid / 1000) * 1000;

        $userdir = "hub/$level1/$courseid";

        $directory = make_upload_directory($userdir);

        //get the extension of this image in order to check that it is an image
        $imageext = image_type_to_extension(exif_imagetype($file['tmp_name']));

        if (!empty($imageext) and $screenshotnumber < MAXSCREENSHOTSNUMBER) {

            //delete previously existing screenshot
            if ($this->screenshot_exists($courseid, $screenshotnumber)) {
                unlink($directory.'/screenshot_'.$courseid."_".$screenshotnumber);
            }

            move_uploaded_file($file['tmp_name'], $directory.'/screenshot_'.$courseid."_".$screenshotnumber);
        }

    }

    /**
     * TODO: temporary,  add_screenshot() function
     * Check if a screenshot exists
     * @param int $courseid
     * @param int $screenshotnumber
     * @return bool
     */
    public function screenshot_exists($courseid, $screenshotnumber) {
        global $CFG;
        $level1 = floor($courseid / 1000) * 1000;

        $directory = "hub/$level1/$courseid";
        return file_exists($CFG->dataroot. '/' . $directory.'/screenshot_'.$courseid."_".$screenshotnumber);
    }

    /**
     * TODO: this is temporary till the way to send file by ws is defined
     * Add a backup to a course
     * @param array $file
     * @param integer $courseid
     */
    public function add_backup($file, $courseid) {

        // Generate a two-level path for the userid. First level groups them by slices of 1000 users, second level is userid
        $level1 = floor($courseid / 1000) * 1000;

        $userdir = "hub/$level1/$courseid";

        $directory = make_upload_directory($userdir);

        move_uploaded_file($file['tmp_name'], $directory.'/backup_'.$courseid.".zip");
    }

    /**
     * TODO: temporary function till file download design done  (course unique ref not used)
     * Check a backup exists
     * @param int $courseid
     */
    public function backup_exits($courseid) {
        global $CFG;
        $level1 = floor($courseid / 1000) * 1000;

        $directory = "hub/$level1/$courseid";
        return file_exists($CFG->dataroot. '/' . $directory.'/backup_'.$courseid.".zip");

        
    }

    /**
     * TODO: it is a bit a hacky way...
     * This function display the hub homepage
     * It is called early when loading any Moodle page.
     */
    public function display_homepage() {
        global $PAGE, $SITE, $OUTPUT, $CFG;

        require_once($CFG->dirroot. "/local/hub/forms.php");

        $PAGE->set_url('/');
        $PAGE->set_pagetype('site-index');
        $PAGE->set_docs_path('');
        $PAGE->set_pagelayout('frontpage');
        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading($SITE->fullname);

        $search  = optional_param('search', null, PARAM_TEXT);

        $renderer = $PAGE->get_renderer('local_hub');

        //forms
        $coursesearchform = new course_search_form('', array('search' => $search));
        $fromform = $coursesearchform->get_data();
        $courses = null;
        //Retrieve courses by web service
        $options = array();
        if (!empty($fromform)) {
            $downloadable  = optional_param('downloadable', false, PARAM_INTEGER);
           
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
            $options['search'] = $search;
            $options['onlyvisible'] = true;
            $options['downloadable'] = $downloadable;
            $options['enrollable'] = !$downloadable;
            $courses = $this->get_courses($options);

            //get courses content
            foreach($courses as $course) {
              
                $contents = $this->get_course_contents($course->id);
                 if (!empty($contents)) {
                     foreach($contents as $content) {
                        $course->contents[] = $content;
                     }
                 }
            }
        }

        echo $OUTPUT->header();
        $coursesearchform->display();
        echo $renderer->course_list($courses);
        echo $OUTPUT->footer();
    }

}