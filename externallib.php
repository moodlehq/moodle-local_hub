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

require_once($CFG->libdir."/externallib.php");
require_once($CFG->dirroot."/local/hub/lib.php");

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
        require_capability('moodle/hub:viewinfo', $context);

        //not useful to validate no params, but following line just here to remind u ;)
        self::validate_parameters(self::get_info_parameters(), array());

        $hub = new local_hub();
        $hubinfo =  $hub->get_info();

        return $hubinfo;
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
                        'contactname' => new external_value(PARAM_TEXT, 'hub server administrator name'),
                        'contactemail' => new external_value(PARAM_EMAIL, 'hub server administrator email'),
                        'imageurl' => new external_value(PARAM_URL, 'hub logo url'),
                        'privacy' => new external_value(PARAM_ALPHA, 'hub privacy'),
                        'language' => new external_value(PARAM_ALPHANUMEXT, 'hub main language'),
                        'url' => new external_value(PARAM_URL, 'hub url'),
                        'sites' => new external_value(PARAM_NUMBER, 'number of registered sites on this hub'),
                        'courses' => new external_value(PARAM_NUMBER, 'number total of courses from all registered sites on this hub'),
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
                                'moodleversion' => new external_value(PARAM_INT, 'moodle version'),
                                'moodlerelease' => new external_value(PARAM_TEXT, 'moodle release'),
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
        require_capability('moodle/hub:updateinfo', $context);

        $params = self::validate_parameters(self::update_site_info_parameters(),
                array('siteinfo' => $siteinfo));

        //retieve site url
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $localhub = new local_hub();

        $siteurl = $localhub->get_communication(WSSERVER, REGISTEREDSITE, null, $token)->remoteurl;
        $localhub->register_site($params['siteinfo'], $siteurl); //'true' indicates registration update mode
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
    public static function register_courses_parameters() {
        return new external_function_parameters(
                array(
                        'courses' => new external_multiple_structure(
                        new external_single_structure(
                        array(
                                'fullname' => new external_value(PARAM_TEXT, 'course name'),
                                'shortname' => new external_value(PARAM_ALPHANUMEXT, 'course short name'),
                                'description' => new external_value(PARAM_TEXT, 'course description'),
                                'language' => new external_value(PARAM_ALPHANUMEXT, 'course language'),
                                'publishername' => new external_value(PARAM_TEXT, 'publisher name'),
                                'contributornames' => new external_value(PARAM_TEXT, 'contributor names'),
                                'coverage' => new external_value(PARAM_TEXT, 'coverage'),
                                'creatorname' => new external_value(PARAM_TEXT, 'creator name'),
                                'licenceshortname' => new external_value(PARAM_ALPHANUMEXT, 'licence short name'),
                                'subject' => new external_value(PARAM_INTEGER, 'subject'),
                                'audience' => new external_value(PARAM_ALPHA, 'audience'),
                                'educationallevel' => new external_value(PARAM_ALPHA, 'educational level'),
                                'creatornotes' => new external_value(PARAM_RAW, 'creator notes'),
                                'creatornotesformat' => new external_value(PARAM_INTEGER, 'notes format'),
                                'demourl' => new external_value(PARAM_URL, 'demo URL', VALUE_OPTIONAL),
                                'downloadable' => new external_value(PARAM_BOOL, 'is the course downloadable', VALUE_DEFAULT, 0),
                                'courseurl' => new external_value(PARAM_URL, 'course URL', VALUE_OPTIONAL),
                                'enrollable' => new external_value(PARAM_BOOL, 'is the course enrollable', VALUE_DEFAULT, 0),
                                'screenshotsids' => new external_value(PARAM_TEXT, 'screenshotsids', VALUE_OPTIONAL),
                        ), 'course info')
                        )
                )
        );
    }

    /**
     * Register courses
     * @return boolean 1 if registration was successfull
     */
    public static function register_courses($courses) {
        global $DB;
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('moodle/hub:registercourse', $context);

        $params = self::validate_parameters(self::register_courses_parameters(),
                array('courses' => $courses));

        $transaction = $DB->start_delegated_transaction();

        //retieve site url
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $hub = new local_hub();
        $siteurl = $hub->get_communication(WSSERVER, REGISTEREDSITE, null, $token)->remoteurl;

        foreach ($params['courses'] as $course) {
            $hub->register_course($course, $siteurl); //'true' indicates registration update mode
        }

        $transaction->allow_commit();
        return 1;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function register_courses_returns() {
        return new external_value(PARAM_BOOL, '1 if all went well');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
                array(
                        'search' => new external_value(PARAM_TEXT, 'string to search'),
                        'downloadable' => new external_value(PARAM_BOOL, 'is the course downloadable'),
                        'language' => new external_value(PARAM_ALPHANUMEXT, 'language', VALUE_DEFAULT, 'en')
                )
        );
    }

    /**
     * Get courses
     * @return array courses
     */
    public static function get_courses($search, $downloadable = 0, $language = 'en') {
        global $DB;

        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        require_capability('moodle/hub:view', $context);

        $params = self::validate_parameters(self::get_courses_parameters(),
                array('search' => $search, 'downloadable' => $downloadable, 'language' => $language));

        $hub = new local_hub();
        //TODO find out why validate params change 0 into '' !!! bug !!!

        $courses = $hub->get_courses($params['search'],
                $params['language'], true, $params['downloadable'], !$params['downloadable']);

        //create result
        $result = array();
        foreach ($courses as $course) {
            $courseinfo = array();
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname;
            $courseinfo['shortname'] = $course->shortname;
            $courseinfo['description'] = $course->description;
            $courseinfo['language'] = $course->language;
            $courseinfo['publishername'] = $course->publishername;
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
            if (!empty($course->demourl)) {
                $courseinfo['demourl'] = $course->demourl;
            }
            if (!empty($course->courseurl)) {
                $courseinfo['courseurl'] = $course->courseurl;
            }

            $result[] = $courseinfo;
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return boolean
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                array(
                        'id' => new external_value(PARAM_INTEGER, 'id'),
                        'fullname' => new external_value(PARAM_TEXT, 'course name'),
                        'shortname' => new external_value(PARAM_ALPHANUMEXT, 'course short name'),
                        'description' => new external_value(PARAM_TEXT, 'course description'),
                        'language' => new external_value(PARAM_ALPHANUMEXT, 'course language'),
                        'publishername' => new external_value(PARAM_TEXT, 'publisher name'),
                        'contributornames' => new external_value(PARAM_TEXT, 'contributor names', VALUE_OPTIONAL),
                        'coverage' => new external_value(PARAM_TEXT, 'coverage', VALUE_OPTIONAL),
                        'creatorname' => new external_value(PARAM_TEXT, 'creator name'),
                        'licenceshortname' => new external_value(PARAM_ALPHANUMEXT, 'licence short name'),
                        'subject' => new external_value(PARAM_INTEGER, 'subject'),
                        'audience' => new external_value(PARAM_ALPHA, 'audience'),
                        'educationallevel' => new external_value(PARAM_ALPHA, 'educational level'),
                        'creatornotes' => new external_value(PARAM_RAW, 'creator notes'),
                        'creatornotesformat' => new external_value(PARAM_INTEGER, 'notes format'),
                        'demourl' => new external_value(PARAM_URL, 'demo URL', VALUE_OPTIONAL),
                        'courseurl' => new external_value(PARAM_URL, 'course URL', VALUE_OPTIONAL),
                        'enrollable' => new external_value(PARAM_BOOL, 'is the course enrollable')
                ), 'course info')
        );
    }

}
