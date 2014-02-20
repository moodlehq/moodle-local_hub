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
 * External hub directory API
 *
 * @package    localhub
 * @copyright  2010 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/local/hub/lib.php");

class local_hub_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_info_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Return hub information
     * @return hub
     */
    public static function get_info() {
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);

        //viewinfo: hub directory
        //viewsmallinfo: registered site
        if (!has_capability('local/hub:viewinfo', $context)
                and !has_capability('local/hub:viewsmallinfo', $context)) {
            throw new moodle_exception('nocapabalitytogetinfo', 'local_hub');
        }

        //not useful to validate no params, but following line just here to remind u ;)
        self::validate_parameters(self::get_info_parameters(), array());

        $hub = new local_hub();
        $hubinfo = $hub->get_info();      
        $resultinfo = array();
        $resultinfo['name'] = $hubinfo['name'];
        $resultinfo['description'] = clean_param($hubinfo['description'], PARAM_TEXT);
        $resultinfo['hublogo'] = $hubinfo['hublogo'];
        $resultinfo['url'] = $hubinfo['url'];
        $resultinfo['language'] = $hubinfo['language'];
        $resultinfo['sites'] = $hubinfo['sites'];
        $resultinfo['courses'] = $hubinfo['courses'];
        $resultinfo['enrollablecourses'] = $hubinfo['enrollablecourses'];
        $resultinfo['downloadablecourses'] = $hubinfo['downloadablecourses'];
        if (has_capability('local/hub:viewinfo', $context)) {
             $resultinfo['contactname'] = $hubinfo['contactname'];
             $resultinfo['contactemail'] = $hubinfo['contactemail'];           
             $resultinfo['privacy'] = $hubinfo['privacy'];                       
        }

        return $resultinfo;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_info_returns() {
        return new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'hub name'),
                    'description' => new external_value(PARAM_TEXT, 'hub description'),
                    'contactname' => new external_value(PARAM_TEXT, 'hub server administrator name', VALUE_OPTIONAL),
                    'contactemail' => new external_value(PARAM_EMAIL, 'hub server administrator email', VALUE_OPTIONAL),
                    'hublogo' => new external_value(PARAM_INT, 'does a hub logo exist'),
                    'privacy' => new external_value(PARAM_ALPHA, 'hub privacy', VALUE_OPTIONAL),
                    'language' => new external_value(PARAM_ALPHANUMEXT, 'hub main language'),
                    'url' => new external_value(PARAM_URL, 'hub url'),
                    'sites' => new external_value(PARAM_NUMBER, 'number of registered sites on this hub', VALUE_OPTIONAL),
                    'courses' => new external_value(PARAM_NUMBER, 'number total of courses from all registered sites on this hub', VALUE_OPTIONAL),
                    'enrollablecourses' => new external_value(PARAM_INT, 'number total of visible enrollable courses on this hub', VALUE_OPTIONAL),
                    'downloadablecourses' => new external_value(PARAM_INT, 'number total of visible downloadable courses on this hub', VALUE_OPTIONAL),
                )
                , 'hub information');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_site_info_parameters() {
        return new external_function_parameters(
                array(
                    'siteinfo' => new external_single_structure(
                            array(
                                'name' => new external_value(PARAM_TEXT, 'site name'),
                                'description' => new external_value(PARAM_TEXT, 'site description'),
                                'contactname' => new external_value(PARAM_TEXT, 'site server administrator name'),
                                'contactemail' => new external_value(PARAM_EMAIL, 'site server administrator email'),
                                'contactphone' => new external_value(PARAM_TEXT, 'site server administrator phone'),
                                'imageurl' => new external_value(PARAM_URL, 'site logo url'),
                                'privacy' => new external_value(PARAM_ALPHA, 'site privacy'),
                                'language' => new external_value(PARAM_ALPHANUMEXT, 'site main language'),
                                'url' => new external_value(PARAM_URL, 'site url'),
                                'users' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise, number of users'),
                                'courses' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise, number of courses'),
                                'street' => new external_value(PARAM_TEXT, 'physical address'),
                                'regioncode' => new external_value(PARAM_ALPHANUMEXT, 'ISO 3166-2 region code'),
                                'countrycode' => new external_value(PARAM_ALPHANUMEXT, 'ISO 3166 country code'),
                                'geolocation' => new external_value(PARAM_RAW, 'geolocation'),
                                'contactable' => new external_value(PARAM_BOOL, '1 if the administrator can be contacted by Moodle form on the hub'),
                                'emailalert' => new external_value(PARAM_BOOL, '1 if the administrator receive email notification from the hub'),
                                'enrolments' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of enrolments'),
                                'posts' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of posts'),
                                'questions' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of questions'),
                                'resources' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of resources'),
                                'participantnumberaverage' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise average number of participants'),
                                'modulenumberaverage' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise verage number of course modules'),
                                'moodleversion' => new external_value(PARAM_FLOAT, 'moodle version'),
                                'moodlerelease' => new external_value(PARAM_TEXT, 'moodle release'),
                                'badges' => new external_value(PARAM_INT, '-1 if private info, otherwise number of badges.', VALUE_OPTIONAL),
                                'issuedbadges' => new external_value(PARAM_INT, '-1 if private info, otherwise number of issued badges.', VALUE_OPTIONAL)
                            ), 'site info')
                )
        );
    }

    /**
     * Update site registration
     * two security check:
     * 1- if the url changed, unactivate the site and alert the administrator
     * 2- call the site by web service and confirm it, if confirmation fail, the update is declare failed
     * @return boolean 1 if updated was successfull
     */
    public static function update_site_info($siteinfo) {
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:updateinfo', $context);

        $params = self::validate_parameters(self::update_site_info_parameters(),
                        array('siteinfo' => $siteinfo));

        //check that the hub can access the site
        $hubmanager = new local_hub();
        if (!$hubmanager->is_remote_site_valid($params['siteinfo']['url'])) {
            throw new moodle_exception('cannotregisternotavailablesite', 'local_hub', 
                    $params['siteinfo']['url']);
        }

        //add ip information
        $params['siteinfo']['ip'] = getremoteaddr();

        //retieve site url
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);

        $siteurl = $hubmanager->get_communication(WSSERVER, REGISTEREDSITE, null, $token)->remoteurl;

        //this following error should never happen
        //(communication record doesn't exist and webservice token exists)
        if (empty($siteurl)) {
            throw new moodle_exception('noexistingcommunication', 'local_hub');
        }

        $result = $hubmanager->register_site($params['siteinfo'], $siteurl);

        return 1;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function update_site_info_returns() {
        return new external_value(PARAM_BOOL, '1 if all went well');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function unregister_site_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Unregister site
     * @return bool 1 if unregistration was successfull
     */
    public static function unregister_site() {
        global $DB, $CFG;
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:updateinfo', $context);

        //clean params
        $params = self::validate_parameters(self::unregister_site_parameters(),
                        array());

        //retieve the site communication
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $hub = new local_hub();
        $communication = $hub->get_communication(WSSERVER, REGISTEREDSITE, null, $token);

        //retrieve the site
        $siteurl = $communication->remoteurl;
        $site = $hub->get_site_by_url($siteurl);

        //unregister the site
        if (!empty($site)) {
            $hub->unregister_site($site);
        }

        //delete the web service token
        require_once($CFG->dirroot . '/webservice/lib.php');
        $webservice_manager = new webservice();
        $tokentodelete = $webservice_manager->get_user_ws_token($communication->token);
        $webservice_manager->delete_user_ws_token($tokentodelete->id);

        //delete the site communication
        $hub->delete_communication($communication);

        return true;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function unregister_site_returns() {
        return new external_value(PARAM_INTEGER, '1 for successfull');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function unregister_courses_parameters() {
        return new external_function_parameters(
                array(
                    'courseids' => new external_multiple_structure(
                            new external_value(PARAM_INT, 'the id of the course to unregister')
                    )
                )
        );
    }

    /**
     * Unregister courses
     * @return array 1 if unregistration was successfull
     */
    public static function unregister_courses($courseids) {
        global $DB;
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:unregistercourse', $context);

        $params = self::validate_parameters(self::unregister_courses_parameters(),
                        array('courseids' => $courseids));

        $transaction = $DB->start_delegated_transaction();

        //retieve site url
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $hub = new local_hub();
        $siteurl = $hub->get_communication(WSSERVER, REGISTEREDSITE, null, $token)->remoteurl;

        foreach ($params['courseids'] as $courseid) {
            $hub->unregister_course($courseid, $siteurl); //'true' indicates registration update mode
        }

        $transaction->allow_commit();
        return true;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function unregister_courses_returns() {
        return new external_value(PARAM_INTEGER, '1 for successfull');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function register_courses_parameters() {
        return new external_function_parameters(
                array(
                    'courses' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'sitecourseid' => new external_value(PARAM_INT, 'the id of the course on the publishing site'),
                                        'fullname' => new external_value(PARAM_TEXT, 'course name'),
                                        'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                                        'description' => new external_value(PARAM_TEXT, 'course description'),
                                        'language' => new external_value(PARAM_ALPHANUMEXT, 'course language'),
                                        'publishername' => new external_value(PARAM_TEXT, 'publisher name'),
                                        'publisheremail' => new external_value(PARAM_EMAIL, 'publisher email'),
                                        'contributornames' => new external_value(PARAM_TEXT, 'contributor names'),
                                        'coverage' => new external_value(PARAM_TEXT, 'coverage'),
                                        'creatorname' => new external_value(PARAM_TEXT, 'creator name'),
                                        'licenceshortname' => new external_value(PARAM_ALPHANUMEXT, 'licence short name'),
                                        'subject' => new external_value(PARAM_ALPHANUM, 'subject'),
                                        'audience' => new external_value(PARAM_ALPHA, 'audience'),
                                        'educationallevel' => new external_value(PARAM_ALPHA, 'educational level'),
                                        'creatornotes' => new external_value(PARAM_RAW, 'creator notes'),
                                        'creatornotesformat' => new external_value(PARAM_INTEGER, 'notes format'),
                                        'demourl' => new external_value(PARAM_URL, 'demo URL', VALUE_OPTIONAL),
                                        'courseurl' => new external_value(PARAM_URL, 'course URL', VALUE_OPTIONAL),
                                        'enrollable' => new external_value(PARAM_BOOL, 'is the course enrollable', VALUE_DEFAULT, 0),
                                        'screenshots' => new external_value(PARAM_INT, 'the number of screenhots', VALUE_OPTIONAL),
                                        'deletescreenshots' => new external_value(PARAM_INT, 'ask to delete all the existing screenshot files (it does not reset the screenshot number)', VALUE_DEFAULT, 0),
                                        'contents' => new external_multiple_structure(new external_single_structure(
                                                        array(
                                                            'moduletype' => new external_value(PARAM_ALPHA, 'the type of module (activity/block)'),
                                                            'modulename' => new external_value(PARAM_TEXT, 'the name of the module (forum, resource etc)'),
                                                            'contentcount' => new external_value(PARAM_INT, 'how many time the module is used in the course'),
                                                )), 'contents', VALUE_OPTIONAL),
                                        'outcomes' => new external_multiple_structure(new external_single_structure(
                                                        array(
                                                            'fullname' => new external_value(PARAM_TEXT, 'the outcome fullname')
                                                )), 'outcomes', VALUE_OPTIONAL)
                                    )
                            )
                    )
                )
        );
    }

    /**
     * Register courses
     * @return array ids of created courses
     */
    public static function register_courses($courses) {
        global $DB;
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:registercourse', $context);

        $params = self::validate_parameters(self::register_courses_parameters(),
                        array('courses' => $courses));

        $hub = new local_hub();

        //retieve site url
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);

        $siteurl = $hub->get_communication(WSSERVER, REGISTEREDSITE, null, $token)->remoteurl;
        $site = $hub->get_site_by_url($siteurl);

        //check that the number of allowed publication is not reached
        if (isset($site->publicationmax)) {
            //site setting (overwrite the hub setting value)
            $maxpublication = $site->publicationmax;
        } else { //hub setting
            $maxpublication = get_config('local_hub', 'maxcoursesperday');
        }
        if ($maxpublication !== false) {

            //retrieve the number of publication for the last 24hours
            $options = array();
            $options['lastpublished'] = strtotime("-1 day");
            $options['siteid'] = $site->id;
            $options['enrollable'] = true;
            $options['downloadable'] = true;
            $lastpublishedcourses = $hub->get_courses($options);

            if (!empty($lastpublishedcourses)) {
                if (count($lastpublishedcourses) >= $maxpublication) {
                    if ($maxpublication > 0) {
                        //get the oldest publication
                        $nextpublicationtime = get_string('never', 'local_hub');
                        $oldestpublicationtime = time();
                        foreach ($lastpublishedcourses as $lastpublishedcourse) {
                            if ($lastpublishedcourse->timepublished < $oldestpublicationtime) {
                                $oldestpublicationtime = $lastpublishedcourse->timepublished;
                            }
                        }

                        $errorinfo = new stdClass();
                        //calculate the time when the site can publish again
                        $errorinfo->time = format_time((24 * 60 * 60) - (time() - $oldestpublicationtime));
                        $errorinfo->maxpublication = $maxpublication;
                        throw new moodle_exception('errormaxpublication', 'local_hub', '', $errorinfo);
                    } else {
                        throw new moodle_exception('errornopublication', 'local_hub');
                    }
                }
            }
        }


        $transaction = $DB->start_delegated_transaction();

        $courseids = array();
        foreach ($params['courses'] as $course) {
            $courseids[] = $hub->register_course($course, $siteurl); //'true' indicates registration update mode
        }

        $transaction->allow_commit();
        return $courseids;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function register_courses_returns() {
        return new external_multiple_structure(new external_value(PARAM_INTEGER, 'new id from the course directory table'));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
                array(
                    'search' => new external_value(PARAM_TEXT, 'string to search'),
                    'downloadable' => new external_value(PARAM_BOOL, 'course can be downloadable'),
                    'enrollable' => new external_value(PARAM_BOOL, 'course can be enrollable'),
                    'options' => new external_single_structure(
                            array(
                                'ids' => new external_multiple_structure(new external_value(PARAM_INTEGER, 'id of a course in the hub course directory'), 'ids of course', VALUE_OPTIONAL),
                                'sitecourseids' => new external_multiple_structure(new external_value(PARAM_INTEGER, 'id of a course in the site'), 'ids of course in the site', VALUE_OPTIONAL),
                                'coverage' => new external_value(PARAM_TEXT, 'coverage', VALUE_OPTIONAL),
                                'licenceshortname' => new external_value(PARAM_ALPHANUMEXT, 'licence short name', VALUE_OPTIONAL),
                                'subject' => new external_value(PARAM_ALPHANUM, 'subject', VALUE_OPTIONAL),
                                'audience' => new external_value(PARAM_ALPHA, 'audience', VALUE_OPTIONAL),
                                'educationallevel' => new external_value(PARAM_ALPHA, 'educational level', VALUE_OPTIONAL),
                                'language' => new external_value(PARAM_ALPHANUMEXT, 'language', VALUE_OPTIONAL),
                                'orderby' => new external_value(PARAM_ALPHA, 'orderby method: newest, eldest, publisher, fullname, ratingaverage', VALUE_OPTIONAL),
                                'givememore' => new external_value(PARAM_INT, 'next range of result - range size being set by the hub server ', VALUE_OPTIONAL),
                                'allsitecourses' => new external_value(PARAM_INTEGER,
                                        'if 1 return all not visible and visible courses whose siteid is the site
                                         matching token. Only courses of this site are returned.
                                         givememore parameter is ignored if this param = 1.
                                         In case of public token access, this param option is ignored', VALUE_DEFAULT, 0),
                            ), 'course info')
                )
        );
    }

    /**
     * Get courses
     * @return array courses
     */
    public static function get_courses($search, $downloadable, $enrollable, $options = array()) {
        global $DB, $CFG, $USER;

        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:view', $context);

        $params = self::validate_parameters(self::get_courses_parameters(),
                        array('search' => $search, 'downloadable' => $downloadable,
                            'enrollable' => $enrollable, 'options' => $options));

        //retieve siteid
        $onlyvisible = true;
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $localhub = new local_hub();
        $communication = $localhub->get_communication(WSSERVER, REGISTEREDSITE, null, $token);
        if (!empty($communication)) {
            $siteurl = $communication->remoteurl;
            if (!empty($siteurl)) {
                $site = $localhub->get_site_by_url($siteurl);
                if (!empty($site) and !empty($params['options']['allsitecourses'])) {
                    $params['options']['siteid'] = $site->id;
                    $onlyvisible = false;
                }
            }
        }

        $cleanedoptions = $params['options'];
        $cleanedoptions['onlyvisible'] = $onlyvisible;
        $cleanedoptions['search'] = $params['search'];
        $cleanedoptions['downloadable'] = $params['downloadable'];
        $cleanedoptions['enrollable'] = $params['enrollable'];

        //sort method
        if (!empty($params['options']['orderby'])) {
            switch ($params['options']['orderby']) {
                case 'newest':
                    $cleanedoptions['orderby'] = 'timemodified DESC';
                    break;
                case 'eldest':
                    $cleanedoptions['orderby'] = 'timemodified ASC';
                    break;
                case 'publisher':
                    $cleanedoptions['orderby'] = 'publishername ASC';
                    break;
                case 'fullname':
                    $cleanedoptions['orderby'] = 'fullname ASC';
                    break;
                case 'ratingaverage':
                    $cleanedoptions['orderby'] = 'ratingaverage DESC';
                    break;
                default:
                    unset($cleanedoptions['orderby']);
                    break;
            }
        }

        //retrieve the range of courses to return
        $maxcourses = get_config('local_hub', 'maxwscourseresult');
        if (empty($maxcourses)) {
            throw new moodle_exception('nocoursereturn', 'local_hub');
        }

        $hub = new local_hub();
        //the site is requesting all his own course
        if (!empty($params['options']['siteid'])) {
            $maxcourses = 0;
            $limitfrom = 0;
        } else {
            //the site is doing a normal courses request (not focussed on its own courses)
            //the hub server limit the return number of course
            $limitfrom = isset($params['options']['givememore'])?$params['options']['givememore']:0;
        }

        $courses = $hub->get_courses($cleanedoptions, $limitfrom, $maxcourses);
        $coursetotal = $hub->get_courses($cleanedoptions, 0, 0, true);


        //load ratings and comments
        if (!empty($courses)) {
            require_once($CFG->dirroot . '/comment/lib.php');
        }

        //create result
        $coursesresult = array();
        foreach ($courses as $course) {
            $courseinfo = array();
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname;
            $courseinfo['shortname'] = $course->shortname;
            $courseinfo['description'] = $course->description;
            $courseinfo['language'] = $course->language;
            $courseinfo['publishername'] = $course->publishername;
            //return publisher email, privacy and site course id
            // only if the request has been done by the site
            if (!empty($site) and $course->siteid == $site->id) {
                $courseinfo['publisheremail'] = $course->publisheremail;
                $courseinfo['privacy'] = $course->privacy;
                $courseinfo['sitecourseid'] = $course->sitecourseid;
            }
            $courseinfo['contributornames'] = $course->contributornames;
            $courseinfo['coverage'] = $course->coverage;
            $courseinfo['creatorname'] = $course->creatorname;
            $courseinfo['licenceshortname'] = $course->licenceshortname;
            $courseinfo['subject'] = $course->subject;
            $courseinfo['audience'] = $course->audience;
            $courseinfo['educationallevel'] = $course->educationallevel;
            $courseinfo['creatornotes'] = $course->creatornotes;
            $courseinfo['creatornotesformat'] = $course->creatornotesformat;
            $courseinfo['enrollable'] = $course->enrollable;
            $courseinfo['screenshots'] = $course->screenshots;
            $courseinfo['timemodified'] = $course->timemodified;
            if (!empty($course->courseurl)) {
                $courseinfo['courseurl'] = $course->courseurl;
            } else if (!empty($course->demourl)) { //courseurl is mandatory, demo url can be blank
                $courseinfo['demourl'] = $course->demourl;
            } else {
                 $courseurl = new moodle_url($CFG->wwwroot, array('sesskey' => sesskey(),
                                    'redirectcourseid' => $course->id));
                 $courseinfo['demourl'] = $courseurl->out(false);
            }

            //outcomes
            if (!empty($course->outcomes)) {
                foreach($course->outcomes as $outcome) {
                    $courseinfo['outcomes'][] = array('fullname' => $outcome);
                }
            }

            //get content
            $contents = $hub->get_course_contents($course->id);
            if (!empty($contents)) {
                foreach ($contents as $content) {
                    $tmpcontent = array();
                    $tmpcontent['moduletype'] = $content->moduletype;
                    $tmpcontent['modulename'] = $content->modulename;
                    $tmpcontent['contentcount'] = $content->contentcount;
                    $courseinfo['contents'][] = $tmpcontent;
                }
            }

            //set ratings
            if ($course->ratingcount) {
                //$courseinfo['rating']['aggregate'] = (float) $course->ratingaverage;
                //the ratings has been changed from scale 0 to 10 to a "Featured" award
                $courseinfo['rating']['aggregate'] = HUB_COURSE_RATING_SCALE;
            }
            $courseinfo['rating']['count'] = (int) $course->ratingcount;
            $courseinfo['rating']['scaleid'] = HUB_COURSE_RATING_SCALE;

            //get comments
            $commentoptions->context = get_context_instance(CONTEXT_COURSE, SITEID);
            $commentoptions->area = 'local_hub';
            $commentoptions->itemid = $course->id;
            $commentoptions->showcount = true;
            $commentoptions->component = 'local_hub';
            $course->comment = new comment($commentoptions);
            $comments = $course->comment->get_comments();
            foreach ($comments as $comment) {
                $coursecomment = array();
                $coursecomment['comment'] = clean_param($comment->content, PARAM_TEXT);
                $coursecomment['commentator'] = clean_param($comment->fullname, PARAM_TEXT);
                $coursecomment['date'] = $comment->timecreated;
                $courseinfo['comments'][] = $coursecomment;
            }

            //get backup size
            $returnthecourse = true;
            if (!$course->enrollable) {
                if ($hub->backup_exits($course->id)) {
                    $courseinfo['backupsize'] = $hub->get_backup_size($course->id);
                } else {
                    // We don't return the course when backup file is not found.
                    $returnthecourse = false;
                }
            }

            if ($returnthecourse) {
                $coursesresult[] = $courseinfo;
            }
        }

        return array('courses' => $coursesresult, 'coursetotal' => $coursetotal);
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function get_courses_returns() {
        return new external_single_structure(
            array('courses' => new external_multiple_structure(
                    new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INTEGER, 'id'),
                        'fullname' => new external_value(PARAM_TEXT, 'course name'),
                        'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                        'description' => new external_value(PARAM_TEXT, 'course description'),
                        'language' => new external_value(PARAM_ALPHANUMEXT, 'course language'),
                        'publishername' => new external_value(PARAM_TEXT, 'publisher name'),
                        'publisheremail' => new external_value(PARAM_EMAIL, 'publisher email', VALUE_OPTIONAL),
                        'privacy' => new external_value(PARAM_INT, 'privacy: published or not', VALUE_OPTIONAL),
                        'sitecourseid' => new external_value(PARAM_INT, 'course id on the site', VALUE_OPTIONAL),
                        'contributornames' => new external_value(PARAM_TEXT, 'contributor names', VALUE_OPTIONAL),
                        'coverage' => new external_value(PARAM_TEXT, 'coverage', VALUE_OPTIONAL),
                        'creatorname' => new external_value(PARAM_TEXT, 'creator name'),
                        'licenceshortname' => new external_value(PARAM_ALPHANUMEXT, 'licence short name'),
                        'subject' => new external_value(PARAM_ALPHANUM, 'subject'),
                        'audience' => new external_value(PARAM_ALPHA, 'audience'),
                        'educationallevel' => new external_value(PARAM_ALPHA, 'educational level'),
                        'creatornotes' => new external_value(PARAM_RAW, 'creator notes'),
                        'creatornotesformat' => new external_value(PARAM_INTEGER, 'notes format'),
                        'demourl' => new external_value(PARAM_URL, 'demo URL', VALUE_OPTIONAL),
                        'courseurl' => new external_value(PARAM_URL, 'course URL', VALUE_OPTIONAL),
                        'backupsize' => new external_value(PARAM_INT, 'course backup size in bytes', VALUE_OPTIONAL),
                        'enrollable' => new external_value(PARAM_BOOL, 'is the course enrollable'),
                        'screenshots' => new external_value(PARAM_INT, 'total number of screenshots'),
                        'timemodified' => new external_value(PARAM_INT, 'time of last modification - timestamp'),
                        'contents' => new external_multiple_structure(new external_single_structure(
                                        array(
                                            'moduletype' => new external_value(PARAM_ALPHA, 'the type of module (activity/block)'),
                                            'modulename' => new external_value(PARAM_TEXT, 'the name of the module (forum, resource etc)'),
                                            'contentcount' => new external_value(PARAM_INT, 'how many time the module is used in the course'),
                                )), 'contents', VALUE_OPTIONAL),
                        'rating' => new external_single_structure (
                                    array(
                                        'aggregate' =>  new external_value(PARAM_FLOAT, 'Rating average', VALUE_OPTIONAL),
                                        'scaleid' => new external_value(PARAM_INT, 'Rating scale'),
                                        'count' => new external_value(PARAM_INT, 'Rating count'),
                                ), 'rating', VALUE_OPTIONAL),


                        'comments' => new external_multiple_structure(new external_single_structure (
                                        array(
                                            'comment' => new external_value(PARAM_TEXT, 'the comment'),
                                            'commentator' => new external_value(PARAM_TEXT, 'the name of commentator'),
                                            'date' => new external_value(PARAM_INT, 'date of the comment'),
                                )), 'contents', VALUE_OPTIONAL),
                        'outcomes' => new external_multiple_structure(new external_single_structure(
                                                    array(
                                                        'fullname' => new external_value(PARAM_TEXT, 'the outcome fullname')
                                            )), 'outcomes', VALUE_OPTIONAL)
                    ), 'course info')),
                'coursetotal' => new external_value(PARAM_INTEGER, 'total number of courses')), 'courses result');
}

    /**
     * TODO: not in use yet, will be prompt to change
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_sites_parameters() {
        return new external_function_parameters(
                array(
                    'search' => new external_value(PARAM_TEXT, 'string to search'),
                    'options' => new external_single_structure(
                            array(
                                'urls' => new external_multiple_structure(new external_value(PARAM_INTEGER, 'url'), 'urls to look for', VALUE_OPTIONAL),
                            ), '')
                )
        );
    }

    /**
     * TODO: not in use yet, will be prompt to change
     * Get sites
     * @return array sites
     */
    public static function get_sites($search, $options = array()) {
        global $DB;

        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:view', $context); //TODO: will need to be change for hub:siteview
        //hub:view should concern only courses

        $params = self::validate_parameters(self::get_sites_parameters(),
                        array('search' => $search, 'options' => $options));

        $cleanedoptions = $params['options'];
        $cleanedoptions['search'] = $params['search'];
        $cleanedoptions['onlyvisible'] = true;
        $hub = new local_hub();
        $sites = $hub->get_sites($cleanedoptions);

        //create result
        $result = array();
        foreach ($sites as $site) {
            $siteinfo = array();
            $siteinfo['id'] = $site->id;
            $siteinfo['name'] = $site->name;
            $siteinfo['url'] = $site->url;
            $result[] = $siteinfo;
        }

        return $result;
    }

    /**
     * TODO: not in use yet, will be prompt to change
     * Returns description of method result value
     * @return boolean
     */
    public static function get_sites_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INTEGER, 'id'),
                            'name' => new external_value(PARAM_TEXT, 'name'),
                            'url' => new external_value(PARAM_URL, 'url'),
                        ), 'site info')
        );
    }

    /**
     * TODO: not in use yet, will be prompt to change
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_sitesregister_parameters() {
        return new external_function_parameters(
                array(
                    'fromid' => new external_value(PARAM_INT, 'data greater than this id.'),
                    'numrecs' => new external_value(PARAM_INT, 'number of records to fetch.'),
                    'modifiedafter' => new external_value(PARAM_INT, 'fetch records after this time.'),
                )
        );
    }

    /**
     * Get sites data for moodle.org
     * @return array sites
     */
    public static function get_sitesregister($fromid, $numrecs=50, $modifiedafter=0) {
        global $DB;

        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('local/hub:viewinfo', $context);
        $params = self::validate_parameters(self::get_sitesregister_parameters(),
                        array('fromid' => $fromid, 'numrecs' => $numrecs, 'modifiedafter' => $modifiedafter));
        $hub = new local_hub();
        $sites = $hub->get_sitesregister($params['fromid'], $params['numrecs'], $params['modifiedafter']);

        //create result. moodle.org (transformed for it.)
        $result = array();
        foreach ($sites as $site) {
            $siteinfo = array();
            $siteinfo['hubid'] = $site->id;
            $siteinfo['sitename'] = $site->name;
            $siteinfo['url'] = $site->url;
            $siteinfo['description'] = $site->description;
            $siteinfo['secret'] = $site->secret;
            $siteinfo['trusted'] = $site->trusted;
            $siteinfo['lang'] = $site->language;
            $siteinfo['timecreated'] = $site->timeregistered;
            $siteinfo['timeupdated'] = $site->timemodified;
            $siteinfo['adminname'] = $site->contactname;
            $siteinfo['adminemail'] = $site->contactemail;
            $siteinfo['adminphone'] = $site->contactphone;
            $siteinfo['imageurl'] = $site->imageurl;
            $siteinfo['prioritise'] = $site->prioritise;
            $siteinfo['country'] = $site->countrycode;
            $siteinfo['regioncode'] = $site->regioncode;
            $siteinfo['street'] = $site->street;
            $siteinfo['geolocation'] = $site->geolocation;
            $siteinfo['moodlerelease'] = $site->moodlerelease;
            $siteinfo['moodleversion'] = $site->moodleversion;
            $siteinfo['ipaddress'] = $site->ip;
            $siteinfo['courses'] = $site->courses;
            $siteinfo['users'] = $site->users;
            $siteinfo['enrolments'] = $site->enrolments;
            $siteinfo['resources'] = $site->resources;
            $siteinfo['questions'] = $site->questions;
            $siteinfo['modulenumberaverage'] = $site->modulenumberaverage;
            $siteinfo['posts'] = $site->posts;
            $siteinfo['participantnumberaverage'] = $site->participantnumberaverage;
            $siteinfo['deleted'] = $site->deleted;
            $siteinfo['publicationmax'] = $site->publicationmax;
            $siteinfo['badges'] = $site->badges;
            $siteinfo['issuedbadges'] = $site->issuedbadges;

            // other mappings that seem to apply
            // so going by what http://wiki.moodle.com/display/sysadmin/moodle.net+moodle.org+statistics+table+mapping+txt has lets try
            switch ($site->privacy) {
                case 'notdisplayed':
                    $siteinfo['public'] = 0;
                    break;
                case 'linked':
                    $siteinfo['public'] = 1;
                    break;
                case 'named':
                    $siteinfo['public'] = 2;
                    break;
                default:
                    $siteinfo['public'] = 0;
                    break;
            }

            $result[] = $siteinfo;
        }

        return $result;
    }

    /**
     * TODO: not in use yet, will be prompt to change
     * Returns description of method result value
     * @return boolean
     */
    public static function get_sitesregister_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'hubid' => new external_value(PARAM_INTEGER, 'id'),
                            'sitename' => new external_value(PARAM_TEXT, 'site name'),
                            'url' => new external_value(PARAM_URL, 'site url'),
                            'description' => new external_value(PARAM_TEXT, 'site description'), //allows for multilang in newer data.
                            'secret' => new external_value(PARAM_TEXT, 'secret'),
                            'trusted' => new external_value(PARAM_INT, 'trust flag'),
                            'lang' => new external_value(PARAM_ALPHANUMEXT, 'site main language'),
                            'timecreated' => new external_value(PARAM_INT, 'time registeration occured'),
                            'timeupdated' => new external_value(PARAM_INT, 'time modificatin occured'),
                            'adminname' => new external_value(PARAM_TEXT, 'site server administrator name'),
                            'adminemail' => new external_value(PARAM_EMAIL, 'site server administrator email'),
                            'adminphone' => new external_value(PARAM_TEXT, 'site server administrator phone'),
                            'imageurl' => new external_value(PARAM_URL, 'site logo url'),
                            'prioritise' => new external_value(PARAM_INT, 'prioritise field'),
                            'country' => new external_value(PARAM_ALPHANUMEXT, 'ISO 3166 country code'),
                            'regioncode' => new external_value(PARAM_ALPHANUMEXT, 'ISO 3166-2 region code'),
                            'street' => new external_value(PARAM_TEXT, 'physical address'),
                            'geolocation' => new external_value(PARAM_RAW, 'geolocation'),
                            'moodlerelease' => new external_value(PARAM_TEXT, 'moodle release'),
                            'moodleversion' => new external_value(PARAM_FLOAT, 'moodle version'),
                            'ipaddress' => new external_value(PARAM_TEXT, 'ip field'),
                            'courses' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise, number of courses'),
                            'users' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise, number of users'),
                            'enrolments' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of enrolments'),
                            'resources' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of resources'),
                            'questions' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of questions'),
                            'modulenumberaverage' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise verage number of course modules'),
                            'posts' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise number of posts'),
                            'participantnumberaverage' => new external_value(PARAM_FLOAT, '-1 if private info, otherwise average number of participants'),
                            'deleted' => new external_value(PARAM_INT, 'deleted field'),
                            'publicationmax' => new external_value(PARAM_INT, 'publicationmax field'),
                            'public' => new external_value(PARAM_INT, 'site privacy'),
                            'badges' => new external_value(PARAM_INT, '-1 if private info, otherwise number of badges.', VALUE_OPTIONAL),
                            'issuedbadges' => new external_value(PARAM_INT, '-1 if private info, otherwise number of issued badges.', VALUE_OPTIONAL)
                        ), 'site register info')
        );
    }
}