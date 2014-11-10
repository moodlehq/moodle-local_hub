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
 * Forms for the hub plugin
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');

/**
 * Registration confirmation form - a captcha is required to be filled by the registered hub
 * => if any spam occurs, it will be manual spam
 */
class site_registration_confirmation_form extends moodleform {

    public function definition() {
        global $CFG, $SITE;

        $strrequired = get_string('required');
        $mform = & $this->_form;
        $mform->addElement('header', 'moodle', get_string('siteregistration', 'local_hub'));

        $mform->addElement('static', 'comment', '',
                get_string('siteregconfcomment', 'local_hub', $SITE->fullname));
        $mform->addElement('static', 'sitenamestring',
                get_string('sitename', 'local_hub'), $this->_customdata['name']);
        $mform->addElement('hidden', 'name', $this->_customdata['name']);
        $mform->addElement('hidden', 'url', $this->_customdata['url']);
        $mform->addElement('hidden', 'token', $this->_customdata['token']);
        $mform->addElement('hidden', 'description', $this->_customdata['description']);
        $mform->addElement('hidden', 'contactname', $this->_customdata['contactname']);
        $mform->addElement('hidden', 'contactemail', $this->_customdata['contactemail']);
        $mform->addElement('hidden', 'contactphone', $this->_customdata['contactphone']);
        $mform->addElement('hidden', 'imageurl', $this->_customdata['imageurl']);
        $mform->addElement('hidden', 'privacy', $this->_customdata['privacy']);
        $mform->addElement('hidden', 'language', $this->_customdata['language']);
        $mform->addElement('hidden', 'users', $this->_customdata['users']);
        $mform->addElement('hidden', 'courses', $this->_customdata['courses']);
        $mform->addElement('hidden', 'street', $this->_customdata['street']);
        $mform->addElement('hidden', 'regioncode', $this->_customdata['regioncode']);
        $mform->addElement('hidden', 'countrycode', $this->_customdata['countrycode']);
        $mform->addElement('hidden', 'geolocation', $this->_customdata['geolocation']);
        $mform->addElement('hidden', 'contactable', $this->_customdata['contactable']);
        $mform->addElement('hidden', 'emailalert', $this->_customdata['emailalert']);
        $mform->addElement('hidden', 'enrolments', $this->_customdata['enrolments']);
        $mform->addElement('hidden', 'posts', $this->_customdata['posts']);
        $mform->addElement('hidden', 'questions', $this->_customdata['questions']);
        $mform->addElement('hidden', 'resources', $this->_customdata['resources']);
        if (!empty($this->_customdata['password'])) {
            $mform->addElement('hidden', 'password', $this->_customdata['password']);
        }
        $mform->addElement('hidden', 'participantnumberaverage',
                $this->_customdata['participantnumberaverage']);
        $mform->addElement('hidden', 'modulenumberaverage', $this->_customdata['modulenumberaverage']);
        $mform->addElement('hidden', 'moodleversion', $this->_customdata['moodleversion']);
        $mform->addElement('hidden', 'moodlerelease', $this->_customdata['moodlerelease']);
        $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'),
                array('https' => $CFG->loginhttps));
        $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');

        $this->add_action_buttons(false, get_string('confirmregistration', 'local_hub'));
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $recaptcha_element = $this->_form->getElement('recaptcha_element');
        if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
            $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
            $response_field = $this->_form->_submitValues['recaptcha_response_field'];
            if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                $errors['recaptcha'] = $result;
            }
        } else {
            $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
        }

        return $errors;
    }

}

class course_search_form extends moodleform {

    public function definition() {
        global $CFG;
        $strrequired = get_string('required');
        $mform = & $this->_form;

        //set default value
        $search = $this->_customdata['search'];
        if (isset($this->_customdata['coverage'])) {
            $coverage = $this->_customdata['coverage'];
        } else {
            $coverage = 'all';
        }
        if (isset($this->_customdata['licence'])) {
            $licence = $this->_customdata['licence'];
        } else {
            $licence = 'all';
        }
        if (isset($this->_customdata['subject'])) {
            $subject = $this->_customdata['subject'];
        } else {
            $subject = 'all';
        }
        if (isset($this->_customdata['audience'])) {
            $audience = $this->_customdata['audience'];
        } else {
            $audience = 'all';
        }
        if (isset($this->_customdata['language'])) {
            $language = $this->_customdata['language'];
        } else {
            $language = current_language();
        }
        if (isset($this->_customdata['educationallevel'])) {
            $educationallevel = $this->_customdata['educationallevel'];
        } else {
            $educationallevel = 'all';
        }
        if (isset($this->_customdata['visibility'])) {
            $visibility = $this->_customdata['visibility'];
        } else {
            $visibility = COURSEVISIBILITY_NOTVISIBLE;
        }
        if (isset($this->_customdata['downloadable'])) {
            $downloadable = $this->_customdata['downloadable'];
        } else {
            $downloadable = 1;
        }
        if (isset($this->_customdata['siteid'])) {
            $siteid = $this->_customdata['siteid'];
        } else {
            $siteid = 'all';
        }
        if (isset($this->_customdata['lastmodified'])) {
            $lastmodified = $this->_customdata['lastmodified'];
        } else {
            $lastmodified = HUB_LASTMODIFIED_WEEK;
        }
        if (isset($this->_customdata['orderby'])) {
            $orderby = $this->_customdata['orderby'];
        } else {
            $orderby = 'newest';
        }

        $mform->addElement('header', 'site', get_string('search', 'local_hub'));

        $options = array(0 => get_string('enrollable', 'local_hub'),
            1 => get_string('downloadable', 'local_hub'));
        if (key_exists('adminform', $this->_customdata)) {
            $options = array('all' => get_string('any')) + $options;
        }
        $mform->addElement('select', 'downloadable', get_string('enroldownload', 'local_hub'), $options);
        $mform->addHelpButton('downloadable', 'enroldownload', 'local_hub');
        $mform->setDefault('downloadable', $downloadable);

        //visible field
        //Note: doesn't matter if form html is hacked, index script does not return any invisible courses
        if (key_exists('adminform', $this->_customdata)) {
            $options = array();
            $options[COURSEVISIBILITY_ALL] = get_string('visibilityall', 'local_hub');
            $options[COURSEVISIBILITY_VISIBLE] = get_string('visibilityyes', 'local_hub');
            $options[COURSEVISIBILITY_NOTVISIBLE] = get_string('visibilityno', 'local_hub');
            $mform->addElement('select', 'visibility', get_string('visibility', 'local_hub'), $options);
            $mform->setDefault('visibility', $visibility);
            unset($options);
            $mform->addHelpButton('visibility', 'visibility', 'local_hub');
        }

        $options = array();
        $options['all'] = get_string('any');
        $options[HUB_AUDIENCE_EDUCATORS] = get_string('audienceeducators', 'hub');
        $options[HUB_AUDIENCE_STUDENTS] = get_string('audiencestudents', 'hub');
        $options[HUB_AUDIENCE_ADMINS] = get_string('audienceadmins', 'hub');
        $mform->addElement('select', 'audience', get_string('audience', 'local_hub'), $options);
        $mform->setDefault('audience', $audience);
        unset($options);
        $mform->addHelpButton('audience', 'audience', 'local_hub');

        if (key_exists('adminform', $this->_customdata)) {
            $options = array();
            $options['all'] = '-';
            $options[HUB_LASTMODIFIED_WEEK] = get_string('periodweek', 'local_hub');
            $options[HUB_LASTMODIFIED_FORTEENNIGHT] = get_string('periodforteennight', 'local_hub');
            $options[HUB_LASTMODIFIED_MONTH] = get_string('periodmonth', 'local_hub');
            $mform->addElement('select', 'lastmodified', get_string('lastmodified', 'local_hub'), $options);
            $mform->setDefault('lastmodified', $lastmodified);
            unset($options);
            $mform->addHelpButton('lastmodified', 'lastmodified', 'local_hub');
        }

        $options = array();
        $options['all'] = get_string('any');
        $options[HUB_EDULEVEL_PRIMARY] = get_string('edulevelprimary', 'hub');
        $options[HUB_EDULEVEL_SECONDARY] = get_string('edulevelsecondary', 'hub');
        $options[HUB_EDULEVEL_TERTIARY] = get_string('eduleveltertiary', 'hub');
        $options[HUB_EDULEVEL_GOVERNMENT] = get_string('edulevelgovernment', 'hub');
        $options[HUB_EDULEVEL_ASSOCIATION] = get_string('edulevelassociation', 'hub');
        $options[HUB_EDULEVEL_CORPORATE] = get_string('edulevelcorporate', 'hub');
        $options[HUB_EDULEVEL_OTHER] = get_string('edulevelother', 'hub');
        $mform->addElement('select', 'educationallevel', get_string('educationallevel', 'local_hub'), $options);
        $mform->setDefault('educationallevel', $educationallevel);
        unset($options);
        $mform->addHelpButton('educationallevel', 'educationallevel', 'local_hub');

        require_once($CFG->dirroot . "/course/publish/lib.php");
        $publicationmanager = new course_publish_manager();
        $options = $publicationmanager->get_sorted_subjects();
        foreach ($options as $key => &$option) {
            $keylength = strlen($key);
            if ($keylength == 10) {
                $option = "&nbsp;&nbsp;" . $option;
            } else if ($keylength == 12) {
                $option = "&nbsp;&nbsp;&nbsp;&nbsp;" . $option;
            }
        }
        $options = array_merge(array('all' => get_string('any')), $options);
        $mform->addElement('select', 'subject', get_string('subject', 'hub'), $options);
        $mform->setDefault('subject', $subject);
        unset($options);
        $mform->addHelpButton('subject', 'subject', 'local_hub');
        $this->init_javascript_enhancement('subject', 'smartselect',
                array('selectablecategories' => true, 'mode' => 'compact'));

        require_once($CFG->libdir . "/licenselib.php");
        $licensemanager = new license_manager();
        $licences = $licensemanager->get_licenses();
        $options = array();
        $options['all'] = get_string('any');
        foreach ($licences as $license) {
            $options[$license->shortname] = get_string($license->shortname, 'license');
        }
        $mform->addElement('select', 'licence', get_string('license'), $options);
        unset($options);
        $mform->addHelpButton('licence', 'licence', 'local_hub');
        $mform->setDefault('licence', $licence);

        //search for only language that exist in the course DB
        require_once($CFG->dirroot . "/local/hub/lib.php");
        $hub = new local_hub();
        $courseslanguages = $hub->get_courses_languages();
        $alllanguages = get_string_manager()->get_list_of_languages();
        $languages = array();
        foreach ($courseslanguages as $languagecode => $lang) {
            $languages[$languagecode] = $alllanguages[$languagecode];
        }
        asort($languages, SORT_LOCALE_STRING);
        $languages = array_merge(array('all' => get_string('any')), $languages);
        $mform->addElement('select', 'language', get_string('language'), $languages);
        $mform->setDefault('language', $language);
        $mform->addHelpButton('language', 'language', 'local_hub');

        $mform->addElement('select', 'orderby', get_string('orderby', 'local_hub'),
                array('newest' => get_string('orderbynewest', 'local_hub'),
                    'eldest' => get_string('orderbyeldest', 'local_hub'),
                    'fullname' => get_string('orderbyname', 'local_hub'),
                    'publisher' => get_string('orderbypublisher', 'local_hub'),
                    'ratingaverage' => get_string('orderbyratingaverage', 'local_hub')));
        $mform->setDefault('orderby', $orderby);
        $mform->setType('orderby', PARAM_ALPHA);
        $mform->addHelpButton('orderby', 'orderby', 'local_hub');
        $mform->setAdvanced('audience');
        $mform->setAdvanced('educationallevel');
        $mform->setAdvanced('subject');
        $mform->setAdvanced('licence');
        $mform->setAdvanced('language');
        $mform->setAdvanced('orderby');

        if (key_exists('adminform', $this->_customdata)) {
            if ($hub->get_sites(array(), 0, 0, true) < 100) { // field not humanly usable over 100 sites.
                $sites = $hub->get_sites();
                $siteids = array();
                foreach ($sites as $site) {

                    $siteids[$site->id] = $site->name;
                }
                asort($siteids, SORT_LOCALE_STRING);
                $siteids = array('all' => get_string('any')) + $siteids;
                $mform->addElement('select', 'siteid', get_string('site', 'local_hub'), $siteids);
                $mform->setDefault('siteid', $siteid);
                $mform->addHelpButton('siteid', 'site', 'local_hub');
            }
        }

        $mform->addElement('text', 'search', get_string('keywords', 'local_hub'));
        $mform->setType('search', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('search', 'keywords', 'local_hub');
        $mform->setDefault('search', $search);

        $mform->addElement('submit', 'submitbutton', get_string('search', 'local_hub'));
    }

}

class site_search_form extends moodleform {

    public function definition() {
        $strrequired = get_string('required');
        $mform = & $this->_form;

        //set default value
        $search = $this->_customdata['search'];
        if (isset($this->_customdata['trusted'])) {
            $trusted = $this->_customdata['trusted'];
        } else {
            $trusted = 'all';
        }

        if (isset($this->_customdata['countrycode'])) {
            $country = $this->_customdata['countrycode'];
        } else {
            $country = 'all';
        }
        if (isset($this->_customdata['language'])) {
            $language = $this->_customdata['language'];
        } else {
            $language = 'all';
        }

        $mform->addElement('header', 'site', get_string('sitesearch', 'local_hub'));

        $options = array();
        $options['all'] = get_string('any');
        $options[1] = get_string('trustedyes', 'local_hub');
        $options[0] = get_string('trustedno', 'local_hub');
        $mform->addElement('select', 'trusted', get_string('trusted', 'local_hub'), $options);
        $mform->setDefault('trusted', $trusted);
        unset($options);
        $mform->addHelpButton('trusted', 'trusted', 'local_hub');

        $options = array();
        $options['all'] = get_string('any');
        $options = array_merge($options, get_string_manager()->get_list_of_countries());
        $mform->addElement('select', 'countrycode', get_string('country', 'local_hub'), $options);
        $mform->setDefault('countrycode', $country);
        unset($options);
        $mform->addHelpButton('countrycode', 'country', 'local_hub');

        $languages = get_string_manager()->get_list_of_languages();
        asort($languages, SORT_LOCALE_STRING);
        $languages = array_merge(array('all' => get_string('any')), $languages);
        $mform->addElement('select', 'language', get_string('sitelang', 'local_hub'), $languages);
        $mform->setDefault('language', $language);
        $mform->addHelpButton('language', 'sitelang', 'local_hub');

        $mform->addElement('text', 'search', get_string('keywords', 'local_hub'));
        $mform->setType('search', PARAM_RAW);
        $mform->addHelpButton('search', 'sitekeywords', 'local_hub');
        $mform->setDefault('search', $search);

        $this->add_action_buttons(false, get_string('sitesearch', 'local_hub'));
    }

}

class send_message_form extends moodleform {

    public function definition() {
        $strrequired = get_string('required');
        $mform = & $this->_form;

        $coursename = $this->_customdata['coursename'];

        $mform->addElement('header', '', get_string('msgtopublisher', 'local_hub', $coursename));

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->addElement('hidden', 'admin', $this->_customdata['admin']);

        $sentoptions = array('hub' =>  get_string('senttohub', 'local_hub', get_config('local_hub', 'name')));
        if (!empty($this->_customdata['publishername'])) {
            $sentoptions['publisher'] = get_string('senttopublisher', 'local_hub', $this->_customdata['publishername']);
        }
        $mform->addElement('select', 'sentto', get_string('sendto', 'local_hub'), $sentoptions);

        $options = array('question' => get_string('msgtypequestion', 'local_hub'),
            'improvement' => get_string('msgtypeimprovement', 'local_hub'),
            'issue' => get_string('msgtypeissue', 'local_hub'),
            'appreciation' => get_string('msgtypeappreciation', 'local_hub'));
        $mform->addElement('select', 'type', get_string('msgtype', 'local_hub'), $options);
        $mform->setType('type', PARAM_ALPHA);
        $mform->addHelpButton('type', 'msgtype', 'local_hub');
        $mform->addRule('type', $strrequired, 'required', null, 'client');

        $mform->addElement('textarea', 'message', get_string('emailmessage', 'local_hub'),
                array('rows' => 10, 'cols' => 60));
        $mform->setType('message', PARAM_TEXT);
        $mform->addHelpButton('message', 'emailmessage', 'local_hub');
        $mform->addRule('message', $strrequired, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('sendmessage', 'local_hub'));
    }

}
