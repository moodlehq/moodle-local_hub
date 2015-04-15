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

//NEVER change this value after installation, otherwise you will need to change all rating in the DB
define('HUB_COURSE_RATING_SCALE', 10);

/**
 * Maximum number of course per web service request
 */
define('HUB_MAXWSCOURSESRESULT', 25);

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
 * Course visibility: visible
 */
define('COURSEVISIBILITY_VISIBLE', '1');

/**
 * Course visibility: not visible
 */
define('COURSEVISIBILITY_NOTVISIBLE', '0');

class local_hub {
///////////////////////////
/// DB Facade functions  //
///////////////////////////

    /**
     * Add a new course feedback
     * @param object $feedback
     *      $feedback->courseid integer - mandatory
     *      $feedback->type string ('question', 'issue', 'improvement', 'appreciation')
     *      $feedback->text string
     *      $feedback->userid integer if not given, the current user is used
     */
    public function add_feedback($feedback) {
        global $DB, $USER;

        if (!isset($feedback->courseid)) {
            throw new moodle_exception('feedbackcourseidmissing');
        }

        if (!isset($feedback->userid)) {
            $feedback->userid = $USER->id;
        }

        $feedback->time = time();
        $DB->insert_record('hub_course_feedbacks', $feedback);
    }

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
        update_sendy_list($site);
    }

    /**
     * Add a site into the site directory
     * @param object $site
     * @return object site
     * @throws dml_exception if error
     */
    public function add_site($site, $usegivenregtime=false) {
        global $DB;
        if (!$usegivenregtime) {
            $site->timeregistered = time();
        }
        $site->timemodified = time();

        // Check if site already exists.
        $oldsite = $this->get_site_by_url($site->url, null);
        $site->deleted = 0;
        if (!empty($oldsite)) {
            $site->id = $oldsite->id;
            $this->update_site($site);
        } else {
            $site->id = $DB->insert_record('hub_site_directory', $site);
            update_sendy_list($site);
        }
        return $site;
    }

    /**
     * Delete all outcomes linked to a course id and add a new array of outcome
     * @param integer $courseid
     * @param array $outcomes
     * this array contain the outcome. One outcome is an array.
     *  outcome['fullname'] => the outcome fullname
     *  TODO outcome['description']
     */
    public function update_course_outcomes($courseid, $outcomes = array()) {
        global $DB;

        //delete previous outcomes
        $DB->delete_records('hub_course_outcomes', array('courseid' => $courseid));

        //add new outcomes
        if (!empty($outcomes)) {
            foreach ($outcomes as $outcome) {
                $newoutcome = new stdClass();
                $newoutcome->courseid = $courseid;
                $newoutcome->outcome = $outcome['fullname'];
                $DB->insert_record('hub_course_outcomes', $newoutcome);
            }
        }
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
     *  boolean 'onlyvisible' - set to true to return only visible course (not set => all course returned)
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
     *  string 'visibility' - if 'onlyvisible' is equal to true, then this option is not used.
     *                        This option should only be used for admin pages.
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
     * @param bool $countresult if set to true return only the course total
     *                          (then limitnum and limitfrom are ignored),
     *                          otherwise return the courses. It exist mainly for pagination, so we don't
     *                          need a second cout_courses() function almost similar to this one.
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
            $wheresql .= " ( " . $DB->sql_like('fullname', ':namesearch', false)
                    . " OR " . $DB->sql_like('description', ':descsearch', false)
                    . "  OR " . $DB->sql_like('coverage', ':coveragesearch', false) . " )";
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

        if ($countresult) {
            $courses = $DB->count_records_select('hub_course_directory', $wheresql, $sqlparams);
        } else {

            //retrieve rating
            $extracolumns = ' , r.ratingaverage, r.ratingcount ';
            $joinsql = ' LEFT JOIN (
                                    SELECT itemid, AVG(rating) AS ratingaverage, COUNT(id) AS ratingcount
                                    FROM {rating} GROUP BY itemid
                                  ) r ON r.itemid = c.id ';

            //sort result
            $ordersql = '';
            if (!empty($options['orderby'])) {
                $ordersql = ' ORDER BY ' . $options['orderby'];
            }

            $sql = 'SELECT c.* ' . $extracolumns . 'FROM {hub_course_directory} c ' . $joinsql . ' WHERE '
                    . $wheresql . $ordersql;

            $courses = $DB->get_records_sql($sql, $sqlparams, $limitfrom, $limitnum);
        }

        //retrieve the outcomes
        //TODO: improve this, incorporating it into the request above
        if (!$countresult) {
            if (!empty($courses)) {
                foreach ($courses as &$course) {
                    $courseoutcomes = $this->get_course_outcomes($course->id);
                    foreach ($courseoutcomes as $courseoutcome) {
                        $course->outcomes[] = $courseoutcome->outcome;
                    }
                }
            }
        }

        return $courses;
    }

    /**
     * Return sites found against some options.
     * By default it returns all visible and not deleted sites
     *
     * @param array $options array of options
     *              onlyvisible - boolean - return only visible sites, otherwise all sites
     *              search - string - search terms (on name and description)
     *              language - string - return sites of this language
     *              contactemail - string - returns sites associated with this email address
     *              urls - array of strings - return sites for these urls
     *              visible - boolean -  return visible or not visible sites
     *              trusted - boolean - return trusted or not trusted sites
     *              prioritise - boolean - return prioritised or not prioritised sites
     *              countrycode - string - return sites for this country code
     *              onlydeleted - boolean - return only deleted sites
     *              deleted - boolean - return deleted and not deleted sites
     * @return array of sites
     */
    public function get_sites($options = array(), $limitfrom=0, $limitnum=0, $countresult = false) {
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
            $wheresql .= " (" . $DB->sql_like('name', ':namesearch', false)
                    . " OR " . $DB->sql_like('description', ':descsearch', false) . " "
                    . " OR " . $DB->sql_like('url', ':urlsearch', false) . " )";
            $sqlparams['namesearch'] = '%' . $options['search'] . '%';
            $sqlparams['descsearch'] = '%' . $options['search'] . '%';
            $sqlparams['urlsearch'] = '%' . $options['search'] . '%';
        }

        if (key_exists('language', $options) and !empty($options['language'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " language = :language";
            $sqlparams['language'] = $options['language'];
        }

        if (key_exists('contactemail', $options) and !empty($options['contactemail'])) {
            if (!empty($wheresql)) {
                $wheresql .= " AND";
            }
            $wheresql .= " contactemail = :contactemail";
            $sqlparams['contactemail'] = $options['contactemail'];
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
            if (empty($options['deleted'])) { //do no return any deleted sites
                $wheresql .= " deleted = 0";
            }
        } else {
            $wheresql .= " deleted = 1";
        }

        if ($countresult) {
            $sites = $DB->count_records_select('hub_site_directory', $wheresql, $sqlparams);
        } else {
            $sites = $DB->get_records_select('hub_site_directory', $wheresql, $sqlparams, $ordersql,
                    '*', $limitfrom, $limitnum);
        }
        return $sites;
    }

    /**
     * Return a site for a given secret
     * @param string $secret
     * @return object site , false if null
     */
    public function get_site_by_secret($secret) {
        global $DB;
        return $DB->get_record('hub_site_directory', array('secret' => $secret, 'deleted' => 0));
    }

    /**
     * Return a site for a given url
     * @param string $url
     * @param int $deleted 0 for not deleted, 1 for deleted, null for either
     * @return object site , false if null
     */
    public function get_site_by_url($url, $deleted = 0) {
        global $DB;
        $params = array('url' => $url);
        if ($deleted !== null) {
            $params['deleted'] = $deleted;
        }
        return $DB->get_record('hub_site_directory', $params);
    }

    /**
     * Return number of visible registered sites
     * @return integer
     */
    public function get_sitesregister($fromid=0, $numrecs=50, $modifiedafter=0) {
        global $DB;
        return $DB->get_records_select('hub_site_directory', 'id > :id AND (timemodified > :timemod OR timelinkchecked > :timelinkcheck)', array('id' => $fromid, 'timemod' => $modifiedafter, 'timelinkcheck' => $modifiedafter), '', '*', 0, $numrecs);
    }

    /**
     * Return number of visible registered sites
     * @return integer
     */
    public function get_registered_sites_total() {
        global $DB;
        return $DB->count_records('hub_site_directory', array('deleted' => 0));
    }

    /**
     * Return number of visible registered courses
     * @return integer
     */
    public function get_registered_courses_total() {
        global $DB;
        return $DB->count_records('hub_course_directory', array('privacy' => 1, 'deleted' => 0));
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
     * the remote entity function (see the define values top of this file)
     * @param string $remoteentity the kind of the remote entity (registered site, hubdirectory, any site - see the define values)
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
        $communication->confirmed = 1;
        $this->update_communication($communication);
    }

    /**
     * update a communication object
     * @param object $communication
     */
    public function update_communication($communication) {
        global $DB;
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

    /**
     * Get all course outcome of a course
     * @param integer $courseid
     * @return array of course outcomes
     */
    public function get_course_outcomes($courseid) {
        global $DB;
        return $DB->get_records('hub_course_outcomes',
                array('courseid' => $courseid));
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

        //enrollable course total
        $options = array();
        $options['onlyvisible'] = true;
        $options['downloadable'] = false;
        $options['enrollable'] = true;
        $enrollablecourses = $this->get_courses($options, 0, 0, true);
        $hubinfo['enrollablecourses'] = $enrollablecourses;

        //downloadable course total
        $options['downloadable'] = true;
        $options['enrollable'] = false;
        $downloadablecourses = $this->get_courses($options, 0, 0, true);
        $hubinfo['downloadablecourses'] = $downloadablecourses;
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

            //delete outcomes
            $this->update_course_outcomes($courseid, null);

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

        //update outcomes

        if (!isset($course->outcomes)) {
            $course->outcomes = null;
        }
        $this->update_course_outcomes($courseid, $course->outcomes);


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
     * @param object $siteinfo Array
     * @param boolean $siteurltoupdate
     * @return string token
     */
    public function register_site($siteinfo, $siteurltoupdate='') {
        global $CFG;

        //$siteinfo must be an object
        if (is_array($siteinfo)) {
            $siteinfo = (object) $siteinfo;
        }

        //md5 the secret
        if (isset($siteinfo->secret) and !empty($siteinfo->secret)) {
            $siteinfo->secret = md5($siteinfo->secret);
        }

        //if we create or update a site, it can not be deleted
        $siteinfo->deleted = 0;

        // If update, check if the url changed, if yes it could be a potential hack attempt.
        // Make the site not visible and alert the hub administrator.
        if (!empty($siteurltoupdate)) {

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
                if ($siteinfo->url != $emailinfo->oldurl) {
                    $existingurlsite = $this->get_site_by_url($siteinfo->url);
                    if (!empty($existingurlsite)) {
                        throw new moodle_exception('urlalreadyexist', 'local_hub', $CFG->wwwroot);
                    }
                }

                //make the site not visible (hub admin need to reconfirm it)
                $siteinfo->visible = 0;

                // Alert the hub administrator.
                email_to_user(get_admin(), core_user::get_support_user(),
                        get_string('emailtitlesiteurlchanged', 'local_hub', $emailinfo->name),
                        get_string('emailmessagesiteurlchanged', 'local_hub', $emailinfo));
            }
        } else {
            //if creation mode, check that the secret doesn't exist already
            $checkedhub = $this->get_site_by_secret($siteinfo->secret);
            if (!empty($checkedhub)) { // No registration process failed but the secret still exists.
                throw new moodle_exception('sitesecretalreadyexist', 'local_hub');
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

        // Little hack: clean parameter from float to int
        // TODO: change database to accept FLOAT.
        $siteinfo->courses = (int) $siteinfo->courses;
        $siteinfo->users = (int) $siteinfo->users;
        $siteinfo->enrolments = (int) $siteinfo->enrolments;
        $siteinfo->posts = (int) $siteinfo->posts;
        $siteinfo->questions = (int) $siteinfo->questions;
        $siteinfo->participantnumberaverage = (int) $siteinfo->participantnumberaverage;
        $siteinfo->modulenumberaverage = (int) $siteinfo->modulenumberaverage;
        $siteinfo->resources = (int) $siteinfo->resources;

        //Add or update the site into the site directory (hub)
        if (!empty($siteurltoupdate)) {
            $this->update_site($siteinfo);

            //update the communication url if it changed
            if (!empty($currentsiteinfo) and $siteinfo->url != $currentsiteinfo->url) {
                $newcommunication = $this->get_communication(WSSERVER,
                                REGISTEREDSITE, $emailinfo->oldurl);
                $newcommunication->remoteurl = $siteinfo->url;
                $this->update_communication($newcommunication);
            }
        } else {
            $site = $this->add_site($siteinfo);
        }

        //we save the token into the communication table in order to have a reference to the hidden token
        $sitetohubcommunication = $this->get_communication(WSSERVER, REGISTEREDSITE, $siteinfo->url);
        if (empty($sitetohubcommunication)) {
            //create token for the hub
            $capabilities = array('local/hub:updateinfo', 'local/hub:registercourse',
                'local/hub:view', 'local/hub:unregistercourse', 'local/hub:viewsmallinfo');
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

        //log the operation
        if (!empty($siteurltoupdate)) {
            //we just log, do not send an email to admin for update
            //(an email was sent previously if the url or name changed)
            add_to_log(SITEID, 'local_hub', 'site update', '', $siteinfo->id);
        } else {
            // Send email to the hub administrator.

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

            email_to_user(get_admin(), core_user::get_support_user(),
                    get_string('emailtitlesiteadded', 'local_hub', $emailinfo->name),
                    get_string('emailmessagesiteadded', 'local_hub', $emailinfo));
            add_to_log(SITEID, 'local_hub', 'site registration', '', $site->id);
        }

        return $sitetohubcommunication->token;
    }

    /**
     * Check if a secret is valid (not marked as stolen)
     * @param string $secret
     * @return boolean true if the secret has not been marked as stolen
     */
    function check_secret_validity($secret) {
        global $DB;
        $stolensecret = $DB->get_record('hub_stolen_site_secrets', array('secret' => $secret));

        if (!empty($stolensecret)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Mark a site secret as stolen.
     * @param int $siteid the site whose the secret has been stolen
     */
    function marksecretstolen($siteid) {
        global $DB;

        $site = $this->get_site($siteid);

        if (!empty($site)) {
            $stolensecret = new stdClass();
            $stolensecret->secret = $site->secret;
            $stolensecret->siteurl = $site->url;
            $stolensecret->blockeddate = time();

            $DB->insert_record('hub_stolen_site_secrets', $stolensecret);
        }
    }

    function delete_site($id, $unregistercourses = false) {
        global $CFG;
        require_once($CFG->dirroot.'/local/hub/locallib.php');

        $sitetodelete = $this->get_site($id);

        //unregister the courses first
        if (!empty($unregistercourses)) {
            $this->delete_courses($sitetodelete->id);
        }

        $sitetohubcommunication = $this->get_communication(WSSERVER,
                REGISTEREDSITE, $sitetodelete->url);

        if (!empty($sitetohubcommunication)) {
            //delete the token for this site
            require_once($CFG->dirroot . '/webservice/lib.php');
            $webservice_manager = new webservice();
            $tokentodelete = $webservice_manager->get_user_ws_token($sitetohubcommunication->token);
            $webservice_manager->delete_user_ws_token($tokentodelete->id);

            //delete the communications to this hub
            $this->delete_communication($sitetohubcommunication);
        }

        // Send email to the site administrator.
        $contactuser = local_hub_create_contact_user($sitetodelete->contactemail ? $sitetodelete->contactemail : $CFG->noreplyaddress,
                                                     $sitetodelete->contactname ? $sitetodelete->contactname : get_string('noreplyname'));

        $emailinfo = new stdClass();
        $hubinfo = $this->get_info();
        $emailinfo->hubname = $hubinfo['name'];
        $emailinfo->huburl = $hubinfo['url'];
        $emailinfo->sitename = $sitetodelete->name;
        $emailinfo->siteurl = $sitetodelete->url;
        $emailinfo->unregisterpagelink = $sitetodelete->url .
                '/admin/registration/index.php?huburl=' .
                $hubinfo['url'] . '&force=1&unregistration=1';

        email_to_user($contactuser, get_admin(),
                get_string('emailtitlesitedeleted', 'local_hub', $emailinfo),
                get_string('emailmessagesitedeleted', 'local_hub', $emailinfo));

        $this->unregister_site($sitetodelete);
    }

    /**
     * Get all existing course languages
     */
    function get_courses_languages() {
        global $DB;
        return $DB->get_records('hub_course_directory',
                array('deleted' => 0), '', 'DISTINCT language');
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
            $user = new stdClass();
            $user->username = $username;
            $user->firstname = $username;
            $user->lastname = get_string('donotdeleteormodify', 'local_hub');
            $user->password = ''; //login no authorised with webservice authentication
            $user->auth = 'webservice';
            $user->confirmed = 1; //need to be confirmed otherwise got deleted
            $user->idnumber = $username;
            $user->mnethostid = 1;
            $user->description = get_string('hubwsuserdescription', 'local_hub');
            $user->timecreated = time();
            $user->timemodified = $user->timecreated;

            // Add extra fields to prevent a debug notice.
            $userfields = get_all_user_name_fields();
            foreach ($userfields as $key => $field) {
                if (!isset($user->$key)) {
                    $user->$key = null;
                }
            }

            // Insert the "site" user into the database.
            $user->id = $DB->insert_record('user', $user);
            \core\event\user_created::create_from_userid($user->id)->trigger();
            add_to_log(SITEID, 'user', get_string('create'), '/view.php?id='.$user->id,
                fullname($user));
        }

        //check and assign the role to user
        $context = context_system::instance();
        $existingroleassign = $DB->get_records('role_assignments', array('roleid'=>$roleid,
            'contextid'=>$context->id, 'userid'=>$user->id), 'id');
        if (empty($existingroleassign)) {
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
            //throw new moodle_exception('hiddentokenalreadyexist');
            // Just return the found token instead of throwing an error.
            $resulttoken = $token;
        }

        return $resulttoken;
    }

    /**
     * TODO: this is temporary till the way to send file by ws is defined
     * Add a screenshots to a course
     * @param array $file - $file['tmp_name'] should be the filename
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
     * Delete a screenshot
     * @param int $courseid
     * @param int $screenshotnumber
     */
    public function delete_screenshot($courseid, $screenshotnumber) {
        global $CFG;
        $level1 = floor($courseid / 1000) * 1000;
        $directory = "hub/$level1/$courseid";
        $filepath = $CFG->dataroot . '/' . $directory . '/screenshot_' . $courseid . "_" . $screenshotnumber;
        unlink($filepath);
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
     * Sanitize screenshot for a course
     * => rename screenshot files from 1 to number of screenshot files
     * => update database screenshots field (with the number of screenshot files)
     * @param int $courseid
     * @return $newscreenshotnumber int the new screenshot total
     */
    public function sanitize_screenshots($courseid) {
        global $CFG, $DB;
        $level1 = floor($courseid / 1000) * 1000;
        $directory = "hub/$level1/$courseid";

        $existingscreenshots = array();
        for ($screenshotnumber = 1; $screenshotnumber <= MAXSCREENSHOTSNUMBER; $screenshotnumber++) {
            if ($this->screenshot_exists($courseid, $screenshotnumber)) {
                $existingscreenshots[] = $screenshotnumber;
            }
        }

        //create tmp screenshot with the right number + delete old screenshot
        $newscreenshotnumber = 0;
        foreach ($existingscreenshots as $screenshotnumber) {
            $newscreenshotnumber++;
            $filepath = $CFG->dataroot . '/' . $directory . '/screenshot_' . $courseid . "_" . $screenshotnumber;
            copy($filepath,
                    $CFG->dataroot . '/' . $directory . '/tmp_screenshot_' . $courseid . "_" . $newscreenshotnumber);
            unlink($filepath);
        }

        //rename the tmp screenshot into real screenshot
        for ($screenshotnumber = 1; $screenshotnumber <= $newscreenshotnumber; $screenshotnumber++) {
            $filepath = $CFG->dataroot . '/' . $directory . '/tmp_screenshot_' . $courseid . "_" . $screenshotnumber;
            copy($filepath,
                    $CFG->dataroot . '/' . $directory . '/screenshot_' . $courseid . "_" . $screenshotnumber);
            unlink($filepath);
        }

        //update course screenshots field
        $course = new stdClass();
        $course->id = $courseid;
        $course->screenshots = $newscreenshotnumber;
        $DB->update_record('hub_course_directory', $course);

        return $newscreenshotnumber;
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
     * TODO: temporary function till file download design done  (course unique ref not used)
     * Return backup size
     * @param int $courseid
     * @return int
     */
    public function get_backup_size($courseid) {
        global $CFG;
        $level1 = floor($courseid / 1000) * 1000;
        $directory = "hub/$level1/$courseid";
        return filesize($CFG->dataroot . '/' . $directory . '/backup_' . $courseid . ".mbz");
    }

    /**
     * Check if the remote site is valid (not localhost and available by the hub)
     * Note: it doesn't matter if the site returns a 404 error.
     * The point here is to check if the site exists. It does not matter if the hub can not call the site,
     * as by security design, a hub should never call a site.
     * However an admin user registering his site should be able to access the site,
     * as people searching on the hub.
     * So we want:
     * a) to check that the url is not a local address
     * b) to check that the site return some not empty headers
     *    (it exists, at least the domain name is registered)
     * @param string $url the site url
     * @return boolean true if the site is valid
     */
    public function is_remote_site_valid($url) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        //Check if site is valid
        if ( strpos($url, 'http://localhost') !== false
                or strpos($url, 'http://127.0.0.1') !== false ) {
            return false;
        }

        $curl = new curl();
        $curl->setopt(array('CURLOPT_FOLLOWLOCATION' => true, 'CURLOPT_MAXREDIRS' => 3));
        $curl->head($url);
        $info = $curl->get_info();

        // Return true if return code is OK (200) or redirection (302).
        // Redirection occurs for many reasons including redirection to another site that handles single sign-on.
        if ($info['http_code'] === 200 || $info['http_code'] === 302) {
            return true;
        }

        // Some sites respond to head() with a 503.
        // As a fallback try get().
        // We don't just always do get() as it is much slower than head().
        $curl->get($url);
        $info = $curl->get_info();
        if ($info['http_code'] === 200 || $info['http_code'] === 302) {
            return true;
        }

        return false;
    }

    /**
     * This function display the hub homepage
     * It is called early when loading any Moodle page.
     * @return integer return true if Moodle index.php home page must continue normal display
     */
    public function display_homepage() {
        global $PAGE, $SITE, $OUTPUT, $CFG, $USER;

        //check if the front page search should not be displayed
        //=> hand over the home page to Moodle index.php
        //Two cases possible:
        //1- the hub is private and the users are not logged in
        //2- the hub is set with no search form on the login page
        $hubprivacy = get_config('local_hub', 'privacy');
        $searchfornologin = get_config('local_hub', 'searchfornologin');
        if (($hubprivacy == HUBPRIVATE or $searchfornologin === '0') and !isloggedin()) {
            return true;
        }

        require_once($CFG->dirroot . "/local/hub/forms.php");

        $PAGE->set_url('/');
        $PAGE->set_pagetype('site-index');
        $PAGE->set_docs_path('');
        $PAGE->set_pagelayout('frontpage');
        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading($SITE->fullname);

        //little trick to require login in order to rate or comment
        $mustbelogged = optional_param('mustbelogged', false, PARAM_BOOL);
        if ($mustbelogged) {
            require_login();
        }

        //log redirection to a course page
        $redirectcourseid = optional_param('redirectcourseid', false, PARAM_INT);
        if (!empty($redirectcourseid)) { //do not check sesskey because can be call by RSS feed
            $course = $this->get_course($redirectcourseid);
            if (!empty($course->courseurl)) {
                $courseurl = new moodle_url($course->courseurl);
            } else if (!empty($course->demourl)) {
                $courseurl = new moodle_url($course->demourl);
            } else {
                //we try to display a demo site but none has been set
                echo $OUTPUT->header();
                echo get_string('nodemo', 'local_hub');
                echo $OUTPUT->footer();
                die();
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
        $currentlanguage = explode('_', current_language()); 
        $fromformdata['language'] = optional_param('language', $currentlanguage[0], PARAM_ALPHANUMEXT);
        $fromformdata['educationallevel'] = optional_param('educationallevel', 'all', PARAM_ALPHANUMEXT);
        $fromformdata['downloadable'] = optional_param('downloadable', 1, PARAM_ALPHANUM);
        $fromformdata['orderby'] = optional_param('orderby', 'newest', PARAM_ALPHA);
        $fromformdata['search'] = $search;
        $coursesearchform = new course_search_form('', $fromformdata, 'get');
        $fromform = $coursesearchform->get_data();
        $coursesearchform->set_data($fromformdata);
        $fromform = (object) $fromformdata;

        //Retrieve courses by web service
        $options = array();
        //special shortcut if a course id is given in param, we search straight forward this id
        if ($courseid = optional_param('courseid', 0, PARAM_INTEGER)) {
            $options['onlyvisible'] = true;
            $options['ids'] = array($courseid);
            $options['downloadable'] = true;
            $options['enrollable'] = true;
            $courses = $this->get_courses($options);
            $coursetotal = 1;
            //Add the name of the course to the page title 
            //(useful because some sites as Facebook is going to read it to build a shared link name)
            foreach ($courses as $course) {
                $PAGE->set_title($course->fullname . ' - ' . $SITE->fullname);
            }
        } else {
            if (!empty($fromform) and optional_param('submitbutton', 0, PARAM_ALPHANUMEXT)) {
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
                    case 'ratingaverage':
                        $options['orderby'] = 'ratingaverage DESC';
                        break;
                    default:
                        break;
                }

                //get courses
                $options['search'] = $search;
                $options['onlyvisible'] = true;
                $options['downloadable'] = $downloadable;
                $options['enrollable'] = !$downloadable;
                $page = optional_param('page', 0, PARAM_INT);
                $courses = $this->get_courses($options, $page * HUB_COURSE_PER_PAGE, HUB_COURSE_PER_PAGE);
                $coursetotal = $this->get_courses($options, 0, 0, true);

                //reset the options
                $options['orderby'] = $fromform->orderby;
                unset($options['onlyvisible']);
            }
        }

        if (!empty($courses)) {

            //load javascript
            $courseids = array(); //all result courses
            $courseimagenumbers = array(); //number of screenshots of all courses (must be exact same order than $courseids)
            foreach ($courses as $course) {
                $courseids[] = $course->id;
                $courseimagenumbers[] = $course->screenshots;
            }
            $PAGE->requires->yui_module('moodle-block_community-imagegallery',
                    'M.blocks_community.init_imagegallery',
                    array(array('imageids' => $courseids,
                            'imagenumbers' => $courseimagenumbers,
                            'huburl' => $CFG->wwwroot)));

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
            require_once($CFG->dirroot . '/rating/lib.php');
            $ratingoptions = new stdclass();
            $ratingoptions->context = context_course::instance(SITEID); //front page course
            $ratingoptions->items = $courses;
            $ratingoptions->aggregate = RATING_AGGREGATE_COUNT; //the aggregation method
            $ratingoptions->scaleid = 0 - get_config('local_hub', 'courseratingscaleid'); //rating API is expecting "minus scaleid"
            $ratingoptions->userid = $USER->id;
            $ratingoptions->returnurl = $CFG->wwwroot . "/local/hub/index.php";
            $ratingoptions->component = 'local_hub';
            $ratingoptions->ratingarea = 'featured';

            $rm = new rating_manager();
            $courses = $rm->get_ratings($ratingoptions); //this function return $ratingoptions->items with information about the ratings

            foreach ($courses as $course) {
                $course->rating->settings->permissions->viewany = 1;
            }

            require_once($CFG->dirroot . '/comment/lib.php');
            foreach ($courses as $course) {
                $commentoptions = new stdClass();
                $commentoptions->context = context_course::instance(SITEID);
                $commentoptions->area = 'local_hub';
                $commentoptions->itemid = $course->id;
                $commentoptions->showcount = true;
                $commentoptions->component = 'local_hub';
                $course->comment = new comment($commentoptions);
                $course->comment->set_view_permission(true);
            }
        }

        //create rss feed link
        $enablerssfeeds = get_config('local_hub', 'enablerssfeeds');
        if (!empty($enablerssfeeds)) {
            require($CFG->libdir . '/rsslib.php');
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
            $orderby = key_exists('orderby', $options) ? $options['orderby'] : 'newest';
            $search = empty($search) ? 0 : urlencode($search);
            //retrieve guest user if user not logged in
            $userid = empty($USER->id) ? $CFG->siteguest : $USER->id;
            $ctx = context_course::instance(SITEID);
            //add the link tage to the header
            $rsslink = rss_get_url($ctx->id, $userid, 'local_hub',
                            $downloadable . '/' . $audience . '/' . $educationallevel
                            . '/' . $subject . '/' . $licence
                            . '/' . $language . '/' . $search . '/');
            $PAGE->add_alternate_version('RSS', $rsslink, 'application/rss+xml');
            //create the rss icon
            $rssicon = rss_get_link($ctx->id, $userid, 'local_hub',
                            $downloadable . '/' . $audience . '/' . $educationallevel
                            . '/' . $subject . '/' . $licence
                            . '/' . $language . '/' . $search . '/' . $orderby . '/',
                            get_string('rsstooltip', 'local_hub'));
        }

        /// OUTPUT
        echo $OUTPUT->header();

        //notification message sent to publisher
        if (optional_param('messagesent', 0, PARAM_INTEGER)) {
            echo $OUTPUT->notification(get_string('messagesentsuccess', 'local_hub'), 'notifysuccess');
        }

        //search form
        $coursesearchform->display();

        //Course listing
        $options['submitbutton'] = 1; //need to set up the submitbutton to 1 for the paging bar (simulate search)
        //and paramlink
        //set language value back to 'all'
        if (!key_exists('language', $options)) {
            $options['language'] = 'all';
        }

        if (isset($courses) and empty($courseid)) {
            if (empty($coursetotal)) {
                $coursetotalhtml = get_string('nocourse', 'local_hub');
            } else {
                $coursetotalhtml = get_string('coursetotal', 'local_hub', $coursetotal);
            }
            echo html_writer::tag('div', $coursetotalhtml, array('class' => 'hubcoursetotal'));
        }

        if (!empty($courses)) {
            //paging bar
            if ($coursetotal > HUB_COURSE_PER_PAGE) {
                $baseurl = new moodle_url('', $options);
                $pagingbarhtml = $OUTPUT->paging_bar($coursetotal, $page, HUB_COURSE_PER_PAGE, $baseurl);
                $pagingbarhtml = html_writer::tag('div', $pagingbarhtml, array('class' => 'pagingbar'));
                echo $pagingbarhtml;
            }

            echo highlight($search, $renderer->course_list($courses));

            if (!empty($pagingbarhtml)) {
                echo $pagingbarhtml;
            }
        }

        //rss icon
        if (!empty($enablerssfeeds)) {
            echo html_writer::tag('div', $rssicon, array('class' => 'hubrsslink'));
        }

        //permalink
        if (!empty($courses)) {
            $permalinkparams = array();
            //special case: course list is a unique course for a given ID
            if (!empty($courseid)) {
                $permalinkparams['courseid'] = $courseid;
            } else {
                $permalinkparams = $options;
            }

            $permalink = html_writer::tag('div',
                            html_writer::tag('a', get_string('permalink', 'local_hub'),
                                    array('href' => new moodle_url('', $permalinkparams))),
                            array('class' => 'hubcoursepermalink'));
            echo $permalink;
        }

        echo $OUTPUT->footer();
    }

}

/**
 * Callback function to check permission
 * used by Comment API
 * @return array
 */
function local_hub_comment_permissions($params) {
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

/**
 * Return rating related permissions
 *
 * @param string $options the context id
 * @return array an associative array of the user's rating permissions
 */
function local_hub_rating_permissions($contextid, $component, $ratingarea) {
    if ($component != 'local_hub' || $ratingarea != 'featured') {
        // We don't know about this component/ratingarea so just return null to get the
        // default restrictive permissions.
        return null;
    }
    if (is_siteadmin()) {
        $permissions = array('view' => true, 'viewany' => true, 'viewall' => true, 'rate' => true);
    } else {
        $permissions = array('view' => false, 'viewany' => false, 'viewall' => false, 'rate' => false);
    }
    return $permissions;
}

/**
 * Validates a submitted rating
 * @param array $params submitted data
 *            context => object the context in which the rated items exists [required]
 *            component => The component for this plugin - should always be local_hub [required]
 *            ratingarea => object the context in which the rated items exists [required]
 *            itemid => int the ID of the object being rated [required]
 *            scaleid => int the scale from which the user can select a rating. Used for bounds checking. [required]
 *            rating => int the submitted rating [required]
 *            rateduserid => int the id of the user whose items have been rated. NOT the user who submitted the ratings. 0 to update all. [required]
 *            aggregation => int the aggregation method to apply when calculating grades ie RATING_AGGREGATE_AVERAGE [required]
 * @return boolean true if the rating is valid. Will throw rating_exception if not
 */
function local_hub_rating_validate($params) {
    global $DB;

    // Check the component is local_hub
    if ($params['component'] != 'local_hub') {
        throw new rating_exception('invalidcomponent');
    }
    // validate rating area
    if ($params['ratingarea'] != 'featured') {
        throw new rating_exception('invalidratingarea');
    }
    //validate item id
    if (!$record = $DB->get_record('hub_course_directory', array('id'=>$params['itemid']))) {
        throw new rating_exception('invalidratingitemid');
    }
    //validate context id
    if (context_course::instance(SITEID)->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

    return true;
}

/**
 * Validate comments so they don't get hacked by users
 *
 * @param stdClass $comment_param {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean true if validate, otherwise throw an exception
 */
function local_hub_comment_validate($comment_param) {
    global $DB;

    // validate comment area
    if ($comment_param->commentarea != 'local_hub') {
        throw new comment_exception('invalidcommentarea');
    }
    //validate item id
    if (!$record = $DB->get_record('hub_course_directory', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    //validate context id
    if (context_course::instance(SITEID)->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
    return true;
}

/**
 * Optionally subscribe/unsubscribe the contactemail to/from a sendy mailing list.
 * Loads all sites associated with the supplied site contactemail and passes them to update_sendy_list_batch().
 * This is necessary because the emailalert property may be different on the various sites associated with a given email.
 * We can't subscribe or unsubscribe them based on a single site's value.
 *
 * @todo tobe in local_hub class or not? its a helper function for now for any functional expansion..
 *
 * @param $site a row from hub_site_directory
 */
function update_sendy_list($site) {
    $hub = new local_hub();
    $sites = $hub->get_sites(array('contactemail' => $site->contactemail));
    update_sendy_list_batch($sites);
}

/**
 * Send, en masse, updates to the sendy list via REST. Really, the list is managed at and by sendy.
 *
 * An email address will be subscribed if at least one site is associated with it with emailalert and contactable both set to 1.
 * If all sites for an email address have emailalert or contactable == 0 the email address will be unsubscribed.
 *
 * @todo tobe in local_hub class or not? its a helper function for now for any functional expansion..
 *
 * @param array $sites Sites to subscribe/unsubscribe to Sendy
 * @param int $chunksize
 */
function update_sendy_list_batch($sites, $chunksize=150) {
    global $CFG;
    require_once($CFG->dirroot.'/local/hub/curl.php');

    if (PHPUNIT_TEST) {
        // Don't update Sendy if we are running tests.
        return;
    }

    $sendyurl = get_config('local_hub', 'sendyurl');
    $sendylistid = get_config('local_hub', 'sendylistid');
    $sendyapikey = get_config('local_hub', 'sendyapikey');

    // Check for config.php overrides.
    if (isset($CFG->sendyurl)) {
         $sendyurl = $CFG->sendyurl;
    }
    if (isset($CFG->sendylistid)) {
         $sendylistid = $CFG->sendylistid;
    }
    if (isset($CFG->sendyapikey)) {
         $sendyapikey = $CFG->sendyapikey;
    }

    if (empty($sendyurl) || empty($sendylistid) || empty($sendyapikey)) {
        print_error('mailinglistnotconfigured', 'local_hub');
    }

    $subscribers = array();
    $unsubscribers = array();
    foreach ($sites as $site) {
        if (empty($site->contactemail)) {
            continue;
        }
        if ($site->emailalert == 1 && $site->contactable == 1) {
            $subscribers[$site->contactemail] = $site;
            unset($unsubscribers[$site->contactemail]);
        } else if ($site->emailalert == 0 || $site->contactable == 0) {
            if (!isset($subscribers[$site->contactemail])) {
                $unsubscribers[$site->contactemail] = $site;
            }
        }
    }

    // Loop through $subscribers.
    // For each subscriber, check their subscription status.
    // If the email address is not known to the list server, subscribe them.
    debugging('Subscribing '. count($subscribers). ' users in chunks of '. $chunksize, DEBUG_DEVELOPER);
    $chunks = array_chunk($subscribers, $chunksize);
    $resturl = '/subscribe';
    process_sendy_chunks($chunks, $sendyurl, $resturl, $sendylistid, $sendyapikey, array('1', 'true', 'Already subscribed.'));

    // Loop through $unsubscribers and unsubscribe them.
    // The state on list server doesn't matter, just unsubscribe.
    debugging('Unsubscribing '. count($unsubscribers). ' users in chunks of '. $chunksize, DEBUG_DEVELOPER);
    $chunks = array_chunk($unsubscribers, $chunksize);
    $resturl = '/unsubscribe';
    process_sendy_chunks($chunks, $sendyurl, $resturl, $sendylistid, $sendyapikey, array('1', 'true'));
}

function process_sendy_chunks($chunks, $sendyurl, $resturl, $sendylistid, $sendyapikey, $correctresults) {
    foreach ($chunks as $chunk) {
        echo '.';
        $curl = new curly;
        $requests = array();
        foreach ($chunk as $site) {
            if ($resturl == '/subscribe') {
                // Need to check the email address' status before subscribing.
                $emailstatus = get_sendy_status($sendyurl, $sendyapikey, $sendylistid, trim($site->contactemail));
                if ($emailstatus != 'Email does not exist in list') {
                    // They are either already subscribed or have been previously subscribed but unsubscribed so leave them alone.
                    debugging('Updating sendy @'.$sendyurl.$resturl.' for list '. $sendylistid .' skipped site id->'. $site->id .' email->'.$site->contactemail.' as the email status is:'.$emailstatus, DEBUG_DEVELOPER);
                    continue;
                }
            }

            $params = array ('name' => $site->contactname, 'email' => trim($site->contactemail), 'list' => $sendylistid, 'boolean' => 'true');
            $paramspost = $curl->format_postdata_for_curlcall($params);
            $request = array('url' => $sendyurl.$resturl, 'CURLOPT_POST' => 1, 'CURLOPT_POSTFIELDS' => $paramspost);
            $requests[] = $request;
            $chunkparams[] = $params;
        }

        // REST CALLS.
        $results = $curl->multi($requests);

        for ($i=0; $i<count($results);$i++) {
            if (!in_array($results[$i], $correctresults)) {
                debugging('Updating sendy @'.$sendyurl.$resturl.' for list '. $sendylistid .' had errors for site id->'. $chunk[$i]->id .' email->'.$chunk[$i]->contactemail.' :'. $results[$i], DEBUG_DEVELOPER);
            }
        }
    }
}

/**
 * Query a Sendy server for the status of an email address.
 * @return string See "Subscription status" at http://sendy.co/api for possible values
 */
function get_sendy_status($sendyurl, $sendyapikey, $sendylistid, $email) {
    global $CFG;
    require_once($CFG->dirroot.'/local/hub/curl.php');

    $params = array ('api_key' => $sendyapikey, 'list_id' => $sendylistid, 'email' => $email);
    $query = http_build_query($params);

    $resturl = '/api/subscribers/subscription-status.php';

    $curl = new curly;
    $result = $curl->post($sendyurl.$resturl, $params);

    return $result;
}
