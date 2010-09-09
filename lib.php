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
define('HUB_COURSE_PER_PAGE', 10);
define('HUB_COURSE_RATING_SCALE', 10);

/**
 * Maximum number of course per day default
 */
define('HUB_MAXCOURSESPERSITEPERDAY', 20);


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
     * @param integer $strictness
     * @return object site, false if null
     */
    public function get_site($id, $strictness = IGNORE_MISSING) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('id' => $id), '*', $strictness);
    }

    /**
     * Unregister a site from the directory
     * If the site doesn't exist do nothing - no error thrown
     * If the site id doesn't match the site url, throw an error
     * @param string $siteurl
     */
    public function unregister_site($site) {
        global $CFG;

        //check that the site exist (otherwise unregister)
        if (!empty($site)) {
            //reset the following settings that could have been changed by admin
            $site->deleted = 1;
            $site->trusted = 0;
            $site->visible = 0;
            $site->prioritise = 0;
            $this->update_site($site);

            add_to_log(SITEID, 'local_hub', 'site unregistration', '', $site->id);
        }
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

        //check if a deleted site exist
        $deletedsite = $this->get_site_by_url($site->url, true);
        $site->deleted = 0;
        if (!empty($deletedsite)) {
            $site->id = $deletedsite->id;
            $this->update_site($site);
        } else {
            $site->id = $DB->insert_record('hub_site_directory', $site);
        }
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
        $course->timepublished = time();
        $course->timemodified = time();
        $id = $DB->insert_record('hub_course_directory', $course);
        return $id;
    }

    /**
     * Return a course for a given id
     * @param integer $id
     * @param integer $strictness
     * @return object course, false if null
     */
    public function get_course($id, $strictness = IGNORE_MISSING) {
        global $DB;
        return $DB->get_record('hub_course_directory', array('id' => $id), '*', $strictness);
    }

    /**
     * Return a enrollable course for a given site id and course id
     * @param integer $id
     * @return object course, false if null
     */
    public function get_enrollable_course_by_site($siteid, $sitecourseid) {
        global $DB;
        return $DB->get_record('hub_course_directory',
                array('siteid' => $siteid, 'sitecourseid' => $sitecourseid, 'enrollable' => 1));
    }

    /**
     * Mark a course as deleted (we never really delete a course)
     * @param integer $id - id of the course to remove from the directory
     * @return boolean true
     * @throws dml_exception if error
     */
    public function delete_course($id) {
        global $DB;
        $course = $DB->get_record('hub_course_directory',
                        array('id' => $id));
        $course->deleted = 1;
        $course->timemodified = time();
        return $DB->update_record('hub_course_directory', $course);
    }

    /**
     * Mark all courses of a site as deleted (we never really delete a course)
     * @param integer $siteid - site id of the courses to remove from the directory
     * @return boolean true
     * @throws dml_exception if error
     */
    public function delete_courses($siteid) {
        global $DB;
        $DB->set_field('hub_course_directory', 'deleted', 1, array('siteid' => $siteid));
        $DB->set_field('hub_course_directory', 'timemodified', time(), array('siteid' => $siteid));
        return true;
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
     *  boolean 'onlydeleted' - set to true to return deleted course only,
     *                          otherwise only undeleted
     *  string 'subject' - all course with this subject and subsubject
     *                     (TODO: need a subjectonly options)
     *  string 'language' - all course with this language
     *  string 'audience' - all course with this audience
     *  string 'licenceshortname' - all course with this licence
     *  string 'educationallevel' - select
     *  string 'visibility' - this option is overrided by the onlyvisible parameter
     *                        (only use for admin pages)
     *  array 'ids' - return all the course for these given id on the hub
     *  array 'sitecourseids' - return all the course for these given id on the site
     *                          (most of the time you will give a siteid)
     *  integer 'siteid' - return course for this site id only
     *  integer 'lastmodified' - return all the courses that have been modified/created
     *                           after this lastmodified time.
     *  integer 'lastpublished' - return all the courses that have been first published
     *                            after this lastpublished time.
     *  string 'orderby' - let you override the default order by
     * @param int $limitfrom
     * @param int $limitnum
     * @param bool $countresult if set to true return the count, otherwise result the raw
     * @return array of courses/int
     */
    public function get_courses($options = array(), $limitfrom=0, $limitnum=0, $countresult = false) {
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
            $wheresql .= " (fullname " . $DB->sql_ilike() . " :namesearch OR description "
                    . $DB->sql_ilike() . " :descsearch OR coverage "
                    . $DB->sql_ilike() . " :coveragesearch)";
            $sqlparams['namesearch'] = '%' . $options['search'] . '%';
            $sqlparams['descsearch'] = '%' . $options['search'] . '%';
            $sqlparams['coveragesearch'] = '%' . $options['search'] . '%';
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
            foreach ($edufields as $key => $value) {
                if (strpos($key, $options['subject']) !== false) {
                    if ($topsubject) {
                        $wheresql .= " (";
                        $topsubject = false;
                    } else {
                        $wheresql .= " OR";
                    }
                    $wheresql .= " subject = :" . $key;
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
            foreach ($options['ids'] as $id) {
                if ($idlist == '(') {
                    $idlist .= $id;
                } else {
                    $idlist .= ',' . $id;
                }
            }
            $idlist .= ')';
            $wheresql .= " id IN " . $idlist;
        }

        if (!empty($options['sitecourseids'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $idlist = '(';
            foreach ($options['sitecourseids'] as $sitecourseid) {
                if ($idlist == '(') {
                    $idlist .= $sitecourseid;
                } else {
                    $idlist .= ',' . $sitecourseid;
                }
            }
            $idlist .= ')';
            $wheresql .= " sitecourseid IN " . $idlist;
        }

        //check that one of the downloadable/enrollable option is false (otherwise display both kind of course)
        if (!((key_exists('downloadable', $options) and $options['downloadable'])
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

        if (!empty($options['siteid'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " siteid = :siteid";
            $sqlparams['siteid'] = $options['siteid'];
        }

        if (!empty($options['lastmodified'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " timemodified > :lastmodified";
            $sqlparams['lastmodified'] = $options['lastmodified'];
        }

        if (!empty($options['lastpublished'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " timepublished > :lastpublished";
            $sqlparams['lastpublished'] = $options['lastpublished'];
        }

        if (!empty($wheresql)) {
            $wheresql .= " AND";
        }
        if (!key_exists('onlydeleted', $options) or !$options['onlydeleted']) {
            $wheresql .= " deleted = 0";
        } else {
            $wheresql .= " deleted = 1";
        }

        if (!empty($options['orderby'])) {
            $ordersql = $options['orderby'];
        }

        if ($countresult) {
            $courses = $DB->count_records_select('hub_course_directory', $wheresql, $sqlparams);
        } else {
            $courses = $DB->get_records_select('hub_course_directory', $wheresql, $sqlparams,
                            $ordersql, '*', $limitfrom, $limitnum);
        }
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
            $wheresql .= " (name " . $DB->sql_ilike() . " :namesearch OR description "
                    . $DB->sql_ilike() . " :descsearch)";
            $sqlparams['namesearch'] = '%' . $options['search'] . '%';
            $sqlparams['descsearch'] = '%' . $options['search'] . '%';
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
            foreach ($options['urls'] as $url) {
                if ($urllist == '(\'') {
                    $urllist .= $url;
                } else {
                    $urllist .= '\',\'' . $url;
                }
            }
            $urllist .= '\')';
            $wheresql .= " url IN " . $urllist;
        }

        //this option should be only be called by admin script
        //note that this option is overrided by the onlyvisible parameter
        if (key_exists('visible', $options)) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " visible = :visibility";
            $sqlparams['visibility'] = $options['visible'];
        }

        if (key_exists('trusted', $options)) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " trusted = :trusted";
            $sqlparams['trusted'] = $options['trusted'];
        }

        if (key_exists('prioritise', $options)) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " prioritise = :prioritise";
            $sqlparams['prioritise'] = $options['prioritise'];
        }

        if (key_exists('countrycode', $options)) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " countrycode = :countrycode";
            $sqlparams['countrycode'] = $options['countrycode'];
        }

        if (!empty($wheresql)) {
            $wheresql .= " AND";
        }
        if (!key_exists('onlydeleted', $options) or !$options['onlydeleted']) {
            $wheresql .= " deleted = 0";
        } else {
            $wheresql .= " deleted = 1";
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
        return $DB->get_record('hub_site_directory', array('token' => $token, 'deleted' => 0));
    }

    /**
     * Return a site for a given url
     * @param string $url
     * @param int $deleted
     * @return object site , false if null
     */
    public function get_site_by_url($url, $deleted = 0) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('url' => $url, 'deleted' => $deleted));
    }

    /**
     * Return number of visible registered sites
     * @return integer
     */
    public function get_registered_sites_total() {
        global $DB;
        return $DB->count_records('hub_site_directory', array('visible' => 1, 'deleted' => 0));
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
     * Record a communication information between the hub and another entity
     * (hub directory, site, public site)
     * Mostly use to remember the token given to us by the remote entity,
     * or the token that we gave to the remote entity
     * A communication is composed by:
     *     type of the communication, hub point of view: SERVER / CLIENT
     *     remoteentity name
     *     remoteentity url
     *     token used during this communication
     * If a communication was previously existing, just overrride token, remotename and confirmed fields
     * @param object $communication
     * @return int id of the create communication into the DB
     */
    public function add_communication($communication) {
        global $DB;

        //look for previously deleted communication
        $deletedcommunication = $this->get_communication($communication->type,
                        $communication->remoteentity, $communication->remoteurl, null, 1);

        $communication->deleted = 0;
        if (!empty($deletedcommunication)) {
            $communication->id = $deletedcommunication->id;
            $DB->update_record('hub_communications', $communication);
            $id = $communication->id;
        } else {
            $id = $DB->insert_record('hub_communications', $communication);
        }
        return $id;
    }

    /**
     * Get communication information between the hub and another entity (hub directory, site, public site)
     * Mostly use to remember the token given to us by the remote entity,
     * or the token that we gave to the remote entity
     * @param string $type can be SERVER or CLIENT. SERVER mean that the hub
     * is the server into the communication, so the token
     * refered, is used by the remote entity to call the a hub function.
     * CLIENT mean that the hub use the token to call
     * the remote entity function
     * @param string $remoteentity the name of the remote entity
     * @param string $remoteurl the token of the remote entity
     * @param string $token the token used by this communication
     * @param int $deleted set to 1, return only deleted communication
     * @return object return the communication
     */
    public function get_communication($type, $remoteentity, $remoteurl = null, $token = null, $deleted = 0) {
        global $DB;

        $params = array('type' => $type,
            'remoteentity' => $remoteentity);

        if (!empty($remoteurl)) {
            $params['remoteurl'] = $remoteurl;
        }

        if (!empty($token)) {
            $params['token'] = $token;
        }

        $params['deleted'] = $deleted;

        $token = $DB->get_record('hub_communications', $params);
        return $token;
    }

    /**
     * Delete communication
     * @param integer $communicationid
     */
    public function delete_communication($communication) {
        global $DB;
        $communication->deleted = 1;
        $DB->update_record('hub_communications', $communication);
    }

    /**
     * Confirm a communication
     * When the hub try to register on the hub directory, it first creates
     * a token for the hub directory, send it,
     * and wait for the hub directory to confirm the communication has been established.
     * Then the hub call this function to confirm
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
        return $DB->get_records('hub_course_contents',
                array('courseid' => $courseid), 'contentcount DESC');
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

            add_to_log(SITEID, 'local_hub', 'course unregistration', '', $course->id);
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
            $course->format = 'mbz';
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
        $existingenrollablecourse = $this->get_enrollable_course_by_site($course->siteid, $course->sitecourseid);
        if (!empty($existingenrollablecourse) and $course->enrollable) {
            $course->id = $existingenrollablecourse->id;
            $courseid = $existingenrollablecourse->id;
            $this->update_course($course);
            add_to_log(SITEID, 'local_hub', 'course update', '', $courseid);

            //delete previous course content
            $this->delete_course_contents($courseid);
        } else {
            $courseid = $this->add_course($course);
            add_to_log(SITEID, 'local_hub', 'course registration', '', $courseid);
        }

        //update course tag
        $tags = array();
        if (!empty($course->coverage)) {
            $tags = split(',', $course->coverage);
        }
        require_once($CFG->dirroot . '/tag/lib.php');
        tag_set('hub_course_directory', $courseid, $tags);

        //add new course contents
        if (!empty($course->contents)) {
            foreach ($course->contents as $content) {
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
                    unlink($directory . '/screenshot_' . $courseid . "_" . $screenshotnumber);
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

        //if we create or update a site, it can not be deleted
        $siteinfo->deleted = 0;

        //if update, check if the url changed, if yes it could be a potential hack attempt
        //=> make the hub not visible and alert the administrator
        if (!empty($siteurltoupdate)) {
            $sameurl = 1;

            //retrieve current hub info
            $currentsiteinfo = $this->get_site_by_url($siteurltoupdate);
            $siteinfo->id = $currentsiteinfo->id; //needed for hub update

            $emailinfo = new stdClass();
            $emailinfo->name = $siteinfo->name;
            $emailinfo->oldname = $currentsiteinfo->name; // needed for the email params
            $emailinfo->url = $siteinfo->url;
            $emailinfo->oldurl = $currentsiteinfo->url; //needed for url testing
            $emailinfo->contactname = $siteinfo->contactname;
            $emailinfo->contactemail = $siteinfo->contactemail;
            $emailinfo->huburl = $CFG->wwwroot;
            $emailinfo->managesiteurl = $CFG->wwwroot . '/local/hub/admin/managesites.php';
            $languages = get_string_manager()->get_list_of_languages();
            $emailinfo->language = $languages[$siteinfo->language];

            //check if the url or name changed
            if ($siteinfo->url != $emailinfo->oldurl or
                    $siteinfo->name != $emailinfo->oldname) {

                //check if the site url already exist
                $existingurlsite = $this->get_site_by_url($siteinfo->url);
                if (!empty($existingurlsite)) {
                    throw new moodle_exception('urlalreadyexist', 'local_hub', $CFG->wwwroot);
                }

                //make the site not visible (hub admin need to reconfirm it)
                $siteinfo->visible = 0;

                $sameurl = 0;

                //alert the administrator
                $contactuser = new object;
                $contactuser->email = $siteinfo->contactemail ? $siteinfo->contactemail : $CFG->noreplyaddress;
                $contactuser->firstname = $siteinfo->contactname ? $siteinfo->contactname : get_string('noreplyname');
                $contactuser->lastname = '';
                $contactuser->maildisplay = true;
                email_to_user(get_admin(), $contactuser,
                        get_string('emailtitlesiteurlchanged', 'local_hub', $emailinfo->name),
                        get_string('emailmessagesiteurlchanged', 'local_hub', $emailinfo));
            }
        } else {
            //if creation mode, check that the token don't exist already
            $checkedhub = $this->get_site_by_token($siteinfo->token);
            if (!empty($checkedhub)) { //no registration process failed but the token still exist
                //probably token already attributed, should never happen
                throw new moodle_exception('sitetokenalreadyexist');
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
            //getimagesize is a GD function
            list($imagewidth, $imageheight, $imagetype, $imageattr) = getimagesize($siteinfo->imageurl);
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
            $site = $this->add_site($siteinfo);
        }

        //we save the token into the communication table in order to have a reference to the hidden token
        $sitetohubcommunication = $this->get_communication(WSSERVER, REGISTEREDSITE, $siteinfo->url);
        if (empty($sitetohubcommunication)) {
            //create token for the hub
            $capabilities = array('local/hub:updateinfo', 'local/hub:registercourse',
                'local/hub:view', 'local/hub:unregistercourse');
            $tokenusedbysite = $this->create_hub_token('Registered Hub User', 'Registered site',
                            $siteinfo->url . '_registered_site_user',
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
        if (empty($emailinfo)) {
            $emailinfo = new stdClass();
            $emailinfo->name = $siteinfo->name;
            $emailinfo->url = $siteinfo->url;
            $emailinfo->contactname = $siteinfo->contactname;
            $emailinfo->contactemail = $siteinfo->contactemail;
            $emailinfo->huburl = $CFG->wwwroot;
            $emailinfo->managesiteurl = $CFG->wwwroot . '/local/hub/admin/managesites.php';
            $languages = get_string_manager()->get_list_of_languages();
            $emailinfo->language = $languages[$siteinfo->language];
        }
        if (!empty($siteurltoupdate)) {
            //we just log, do not send an email to admin for update
            //(an email was sent previously if the url or name changed)
            add_to_log(SITEID, 'local_hub', 'site update', '', $siteinfo->id);
        } else {
            email_to_user(get_admin(), $contactuser,
                    get_string('emailtitlesiteadded', 'local_hub', $emailinfo->name),
                    get_string('emailmessagesiteadded', 'local_hub', $emailinfo));
            add_to_log(SITEID, 'local_hub', 'site registration', '', $site->id);
        }

        return $sitetohubcommunication->token;
    }

    /**
     * Create a user, role and token. Return the created token id.
     * @param string $rolename the role to create/use - will be assign to the user
     * @param string $servicename service to link to the new token
     * @param string $username user to link to the new token
     * @param array $capabilities list of capabilities to add to the role
     * @return object created token
     */
    function create_hub_token($rolename, $servicename, $username, $capabilities) {
        global $CFG, $DB;

        //requires libraries
        require_once($CFG->dirroot . '/user/lib.php');

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
            $roleid = create_role($rolename, clean_param($rolename, PARAM_ALPHAEXT),
                            get_string('hubwsroledescription', 'local_hub'), '', true);
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
            role_assign($roleid, $user->id, $context->id);
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
                foreach ($rolecapabilities as $rolecapability) {
                    if ($rolecapability->capability == $capability) {
                        $capabilityassigned = true;
                        break;
                    }
                }

                if (!$capabilityassigned) {
                    assign_capability($capability, CAP_ALLOW, $roleid, $context->id);
                }
            }
        }

        //enable the hidden service and assign it to the user
        foreach ($services as $service) { //there should be only one service into the array!!!
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
            $resulttoken->token = md5(uniqid(rand(), 1));
            $tokenid = $DB->insert_record('external_tokens', $resulttoken);
            $resulttoken->id = $tokenid;
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

        // Generate a two-level path for the userid. First level groups them by slices of 1000 users,
        // second level is userid
        $level1 = floor($courseid / 1000) * 1000;

        $userdir = "hub/$level1/$courseid";

        $directory = make_upload_directory($userdir);

        //get the extension of this image in order to check that it is an image
        $imageext = image_type_to_extension(exif_imagetype($file['tmp_name']));

        if (!empty($imageext) and $screenshotnumber < MAXSCREENSHOTSNUMBER) {

            //delete previously existing screenshot
            if ($this->screenshot_exists($courseid, $screenshotnumber)) {
                unlink($directory . '/screenshot_' . $courseid . "_" . $screenshotnumber);
            }

            move_uploaded_file($file['tmp_name'],
                    $directory . '/screenshot_' . $courseid . "_" . $screenshotnumber);
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
        return file_exists($CFG->dataroot . '/' . $directory . '/screenshot_' . $courseid . "_" . $screenshotnumber);
    }

    /**
     * TODO: this is temporary till the way to send file by ws is defined
     * Add a backup to a course
     * @param array $file
     * @param integer $courseid
     */
    public function add_backup($file, $courseid) {

        // Generate a two-level path for the userid. First level groups them by slices of 1000 users,
        //  second level is userid
        $level1 = floor($courseid / 1000) * 1000;

        $userdir = "hub/$level1/$courseid";

        $directory = make_upload_directory($userdir);

        move_uploaded_file($file['tmp_name'], $directory . '/backup_' . $courseid . ".mbz");
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
        return file_exists($CFG->dataroot . '/' . $directory . '/backup_' . $courseid . ".mbz");
    }

    /**
     * TODO: it is a bit a hacky way...
     * This function display the hub homepage
     * It is called early when loading any Moodle page.
     */
    public function display_homepage() {
        global $PAGE, $SITE, $OUTPUT, $CFG, $USER;

        require_once($CFG->dirroot . "/local/hub/forms.php");

        $PAGE->set_url('/');
        $PAGE->set_pagetype('site-index');
        $PAGE->set_docs_path('');
        $PAGE->set_pagelayout('frontpage');
        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading($SITE->fullname);

        //log redirection to a course page
        $redirectcourseid = optional_param('redirectcourseid', false, PARAM_INT);
        if (!empty($redirectcourseid)) { //do not check sesskey because can be call by RSS feed
            $course = $this->get_course($redirectcourseid);
            if (!empty($course->courseurl)) {
                $courseurl = new moodle_url($course->courseurl);
            } else {
                $courseurl = new moodle_url($course->demourl);
            }
            $rss = optional_param('rss', false, PARAM_BOOL);
            $rss = empty($rss) ? '' : 'rss';
            add_to_log(SITEID, 'local_hub', 'course redirection ' . $rss, '', $redirectcourseid);
            redirect(new moodle_url($courseurl));
        }

        $search = optional_param('search', null, PARAM_TEXT);

        $renderer = $PAGE->get_renderer('local_hub');

        //forms
        //Warning: because we want to support GET and we want people to be able to give the url,
        // we need to bypass the sesskey form checking
        $_GET['sesskey'] = sesskey();

        $fromformdata['coverage'] = optional_param('coverage', 'all', PARAM_TEXT);
        $fromformdata['licence'] = optional_param('licence', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['subject'] = optional_param('subject', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['siteid'] = optional_param('siteid', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['lastmodified'] = optional_param('lastmodified', HUB_LASTMODIFIED_WEEK, PARAM_ALPHANUMEXT);
        $fromformdata['audience'] = optional_param('audience', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['language'] = optional_param('language', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['educationallevel'] = optional_param('educationallevel', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['downloadable'] = optional_param('downloadable', 0, PARAM_ALPHANUM);
        $fromformdata['search'] = $search;
        $coursesearchform = new course_search_form('', $fromformdata, 'get');
        $fromform = $coursesearchform->get_data();
        $coursesearchform->set_data($fromformdata);
        $fromform = (object) $fromformdata;

        //Retrieve courses by web service
        $options = array();
        if (!empty($fromform)) {
            $downloadable = optional_param('downloadable', false, PARAM_INTEGER);

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
            $page = optional_param('page', 0, PARAM_INT);
            $courses = $this->get_courses($options, $page * HUB_COURSE_PER_PAGE, HUB_COURSE_PER_PAGE);

            $coursetotal = $this->get_courses($options, 0, 0, true);

            //get courses content
            foreach ($courses as $course) {

                $contents = $this->get_course_contents($course->id);
                if (!empty($contents)) {
                    foreach ($contents as $content) {
                        $course->contents[] = $content;
                    }
                }
            }

            //load ratings and comments
            if (!empty($courses)) {
                require_once($CFG->dirroot . '/rating/lib.php');
                $ratingoptions = new stdclass();
                $ratingoptions->context = get_context_instance(CONTEXT_COURSE, SITEID); //front page course
                $ratingoptions->items = $courses;
                $ratingoptions->aggregate = RATING_AGGREGATE_AVERAGE; //the aggregation method
                $ratingoptions->scaleid = 10;
                $ratingoptions->userid = $USER->id;
                $ratingoptions->returnurl = "$CFG->wwwroot/local/hub/index.php";

                $rm = new rating_manager();
                $courses = $rm->get_ratings($ratingoptions);

                foreach ($courses as $course) {
                    $course->rating->settings->permissions->viewany = 1;
                }

                require_once($CFG->dirroot . '/comment/lib.php');
                foreach ($courses as $course) {
                    $commentoptions->context = get_context_instance(CONTEXT_COURSE, SITEID);
                    $commentoptions->area = 'local_hub';
                    $commentoptions->itemid = $course->id;
                    $commentoptions->showcount = true;
                    $commentoptions->component = 'local_hub';
                    $course->comment = new comment($commentoptions);
                    $course->comment->set_view_permission(true);
                }
            }
        }

        echo $OUTPUT->header();
        $coursesearchform->display();
        //set to course to null if you didn't do any search (so the render doesn't display 'no search result')
        if (!optional_param('submitbutton', 0, PARAM_ALPHANUMEXT)) {
            $courses = null;
        }
        $options['submitbutton'] = 1; //need to set up the submitbutton to 1 for the paging bar (simulate search)
        echo highlight($search, $renderer->course_list($courses));
        //paging bar
        if ($coursetotal > HUB_COURSE_PER_PAGE) {
            $baseurl = new moodle_url('', $options);
            $pagingbarhtml = $OUTPUT->paging_bar($coursetotal, $page, HUB_COURSE_PER_PAGE, $baseurl);
            echo html_writer::tag('div', $pagingbarhtml, array('class' => 'pagingbar'));
        }


        //create rss feed link
        $enablerssfeeds = get_config('local_hub', 'enablerssfeeds');
        if (!empty($enablerssfeeds)) {
            $audience = key_exists('audience', $options) ? $options['audience'] : 'all';
            $educationallevel = key_exists('educationallevel', $options) ? $options['educationallevel'] : 'all';
            if (key_exists('downloadable', $options)) {
                $downloadable = empty($options['downloadable']) ? 0 : 1;
            } else {
                $downloadable = 'all';
            }
            $subject = key_exists('subject', $options) ? $options['subject'] : 'all';
            $licence = key_exists('licence', $options) ? $options['licence'] : 'all';
            $language = key_exists('language', $options) ? $options['language'] : 'all';
            $audience = key_exists('audience', $options) ? $options['audience'] : 'all';
            $search = empty($search) ? 0 : urlencode($search);
            //retrieve guest user if user not logged in
            $userid = empty($USER->id) ? $CFG->siteguest : $USER->id;
            rss_print_link(get_context_instance(CONTEXT_COURSE, SITEID)->id, $userid, 'local_hub',
                    $downloadable . '/' . $audience . '/' . $educationallevel
                    . '/' . $subject . '/' . $licence
                    . '/' . $language . '/' . $search . '/');
        }
        echo $OUTPUT->footer();
    }

}

/**
 * Callback function to check permission
 * used by Comment API
 * @return array
 */
function hub_comment_permissions($params) {
    global $DB, $USER;

    $post = false;

    //check post permission only if the user didn't post previously  comment
    if (isset($USER->id)) {
        $comments = $DB->get_records('comments',
                        array('userid' => $USER->id, 'itemid' => $params->itemid,
                            'contextid' => $params->context->id, 'commentarea' => 'local_hub'));
        if (empty($comments)) {
            $post = has_capability('moodle/comment:post', $params->context);
        }
    }

    return array('view' => true,
        'post' => $post);
}