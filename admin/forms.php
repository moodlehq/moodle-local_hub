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
 * Administration forms of the hub
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . '/course/publish/lib.php');

/**
 * Form to search a site matching a token
 */
class hub_search_stolen_secret extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = & $this->_form;
        $mform->addElement('header', 'moodle', get_string('searchsite', 'local_hub'));

        $mform->addElement('text', 'secret', get_string('secret', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->addElement('static', '', get_string('or', 'local_hub'));
        $mform->addElement('text', 'sitename', get_string('sitename', 'local_hub')
                , array('class' => 'admintextfield'));

        $this->add_action_buttons(false, get_string('search'));
    }

    /**
     * Validate fields
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $sitename = $this->_form->_submitValues['sitename'];
        $secret = $this->_form->_submitValues['secret'];
        if (empty($sitename) and empty($secret)) {
            $errors['secret'] = get_string('errosecretandnameempty', 'local_hub');
        }

        return $errors;
    }

}

/**
 * This form display registration form to Moodle.org
 * TODO: this form had some none hidden inputs originally, it has been changed since this time
 *       delete this Moodle form for a renderer with a single button
 */
class hub_registration_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $SITE;
        $hub = new local_hub();

        $mform = & $this->_form;
        $mform->addElement('header', 'moodle',
                get_string('hubdetails', 'local_hub'));
        $mform->addElement('static', 'comment', '',
                get_string('hubregistrationcomment', 'local_hub'));
        $mform->addElement('hidden', 'url', $CFG->wwwroot);

        $languages = get_string_manager()->get_list_of_languages();

        $hubname = get_config('local_hub', 'name');
        $hubdescription = get_config('local_hub', 'description');
        $contactname = get_config('local_hub', 'contactname');
        $contactemail = get_config('local_hub', 'contactemail');
        $hublogo = get_config('local_hub', 'hublogo');
        $privacy = get_config('local_hub', 'privacy');
        $hublanguage = get_config('local_hub', 'language');

        $mform->addElement('static', 'hubnamestring',
                get_string('name', 'local_hub'), $hubname);
        $mform->addElement('hidden', 'name', $hubname);
        $mform->addElement('static', 'hubprivacystring',
                get_string('privacy', 'local_hub'),
                $hub->get_privacy_string($privacy));
        $mform->addElement('hidden', 'privacy', $privacy);
        $mform->addElement('static', 'languagestring',
                get_string('language'), $languages[$hublanguage]);
        $mform->addElement('hidden', 'language', $hublanguage);
        $mform->addElement('static', 'hubdescriptionstring',
                get_string('description', 'local_hub'), format_text($hubdescription));
        $mform->addElement('hidden', 'description', $hubdescription);
        $mform->addElement('static', 'contactnamestring',
                get_string('contactname', 'local_hub'), $contactname);
        $mform->addElement('hidden', 'contactname', $contactname);
        $mform->addElement('static', 'contactemailstring',
                get_string('contactemail', 'local_hub'), $contactemail);
        $mform->addElement('hidden', 'contactemail', $contactemail);
        if (!empty($hublogo)) {
            $params = array('filetype' => HUB_HUBSCREENSHOT_FILE_TYPE, 'time' => time());
            $imageurl = new moodle_url($CFG->wwwroot .
                            "/local/hub/webservice/download.php", $params);
            $imagetag = html_writer::empty_tag('img',
                            array('src' => $imageurl, 'alt' => $hubname));
            $mform->addElement('static', 'logourlstring',
                    get_string('image', 'local_hub'), $imagetag);
        } else {
            $hublogo = 0;
        }
        $mform->addElement('hidden', 'hublogo', $hublogo);

        $mform->addElement('static', 'urlstring',
                get_string('url', 'local_hub'), $CFG->wwwroot);

        $hubinfo = $hub->get_info();
        $mform->addElement('static', 'sitesstring',
                get_string('registeredsites', 'local_hub'), $hubinfo['sites']);
        $mform->addElement('hidden', 'sites', $hubinfo['sites']);
        $mform->addElement('static', 'coursesstring',
                get_string('registeredcourses', 'local_hub'), $hubinfo['courses']);
        $mform->addElement('hidden', 'courses', $hubinfo['courses']);
        $mform->addElement('static', 'enrollablecoursesstring',
                get_string('enrollablecourses', 'local_hub'), $hubinfo['enrollablecourses']);
        $mform->addElement('hidden', 'enrollablecourses', $hubinfo['enrollablecourses']);
        $mform->addElement('static', 'downloadablecoursesstring',
                get_string('downloadablecourses', 'local_hub'), $hubinfo['downloadablecourses']);
        $mform->addElement('hidden', 'downloadablecourses', $hubinfo['downloadablecourses']);


        //if the hub is private do not display the register button
        if ($privacy != HUBPRIVATE and empty($this->_customdata['alreadyregistered'])) {
            $buttonlabel = get_string('hubregister', 'local_hub');
            $this->add_action_buttons(false, $buttonlabel);
        }
    }

}

/**
 * This form display hub settings
 */
class hub_settings_form extends moodleform {

    public function definition() {
        global $CFG, $SITE, $USER;

        //name (default) value
        $hubname = get_config('local_hub', 'name');
        if ($hubname === false) {
            $hubname = $SITE->fullname;
        }

        //description (default) value
        $hubdescription = get_config('local_hub', 'description');
        if ($hubdescription === false) {
            $hubdescription = $SITE->summary;
        }

        //contactname (default) value
        $contactname = get_config('local_hub', 'contactname');
        if ($contactname === false) {
            $contactname = $USER->firstname . " " . $USER->lastname;
        }

        //contactemail (default) value
        $contactemail = get_config('local_hub', 'contactemail');
        if ($contactemail === false) {
            $contactemail = $USER->email;
        }

        //imageurl (default) value
        $imageurl = get_config('local_hub', 'imageurl');
        if ($imageurl === false) {
            $imageurl = '';
        }

        //$availability (default) value
        $privacy = get_config('local_hub', 'privacy');
        if ($privacy === false) {
            $privacy = HUBPRIVATE;
        }

        //language (default) value
        $hublanguage = get_config('local_hub', 'language');
        if (empty($hublanguage)) {
            $hublanguage = current_language();
        }

        //max course publication per site per day (default) value
        $hubmaxpublication = get_config('local_hub', 'maxcoursesperday');
        if ($hubmaxpublication === false) {
            $hubmaxpublication = HUB_MAXCOURSESPERSITEPERDAY;
        }

        //front page search form is displayed
        $searchfornologin = get_config('local_hub', 'searchfornologin');
        if ($searchfornologin === false) {
            $searchfornologin = 1;
        }

        //max course publication per site per day (default) value
        $hubmaxwscourseresult = get_config('local_hub', 'maxwscourseresult');
        if ($hubmaxwscourseresult === false) {
            $hubmaxwscourseresult = HUB_MAXWSCOURSESRESULT;
        }

        //enable rss feed
        $enablerssfeeds = get_config('local_hub', 'enablerssfeeds');

        //password (default) value
        if ($privacy == HUBPRIVATE) {
            $password = get_config('local_hub', 'password');
        } else {
            $password = '';
        }

        //rss secret - secret to display invisible course only
        $rsssecret = get_config('local_hub', 'rsssecret');
        if ($rsssecret === false) {
            $rsssecret = '';
        }

        $enabled = get_config('local_hub', 'hubenabled');

        $recaptcha = get_config('local_hub', 'hubrecaptcha');

        $languages = get_string_manager()->get_list_of_languages();

        $strrequired = get_string('required');
        $mform = & $this->_form;
        $mform->addElement('header', 'moodle', get_string('settings', 'local_hub'));

        //get hub version
        require_once($CFG->dirroot . '/local/hub/version.php');
        $year = substr($plugin->version, 0, 4);
        $month = substr($plugin->version, 4, 2);
        $day = substr($plugin->version, 6, 2);
        $versiondate = mktime(0, 0 ,0 , $day, $month, $year);
        $mform->addElement('static', 'version', get_string('hubversion', 'local_hub'), 
                userdate($versiondate, get_string('strftimedaydate', 'langconfig')) 
                . ' (' . $plugin->version . ')');

        $mform->addElement('text', 'name', get_string('name', 'local_hub'),
                array('class' => 'admintextfield'));
        $mform->setDefault('name', $hubname);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addHelpButton('name', 'name', 'local_hub');

        $privacyoptions = array(HUBPRIVATE => get_string('nosearch', 'local_hub'),
            HUBALLOWPUBLICSEARCH => get_string('allowpublicsearch', 'local_hub'),
            HUBALLOWGLOBALSEARCH => get_string('allowglobalsearch', 'local_hub'));
        $mform->addElement('checkbox', 'enabled',
                get_string('enabled', 'local_hub'), '');
        $mform->setDefault('enabled', $enabled);
        $mform->addHelpButton('enabled', 'enabled', 'local_hub');

        $mform->addElement('checkbox', 'recaptchaenabled',
                get_string('recaptchaenabled', 'local_hub'), '');
        $mform->setDefault('recaptchaenabled', $recaptcha);
        $mform->addHelpButton('recaptchaenabled', 'recaptchaenabled', 'local_hub');

        $mform->addElement('select', 'privacy',
                get_string('privacy', 'local_hub'), $privacyoptions);
        $mform->setDefault('privacy', $privacy);
        $mform->addHelpButton('privacy', 'privacy', 'local_hub');

        $mform->addElement('select', 'lang',
                get_string('language', 'local_hub'), $languages);
        $mform->setDefault('lang', $hublanguage);
        $mform->addHelpButton('lang', 'hublang', 'local_hub');

        $mform->addElement('textarea', 'desc',
                get_string('description', 'local_hub'),
                array('rows' => 10, 'cols' => 15, 'class' => 'adminhubdescription'));
        $mform->addRule('desc', get_string('required'), 'required');
        $mform->setDefault('desc', $hubdescription);
        $mform->setType('desc', PARAM_TEXT);
        $mform->addHelpButton('desc', 'description', 'local_hub');

        $mform->addElement('text', 'contactname',
                get_string('contactname', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('contactname', $contactname);
        $mform->addRule('contactname', get_string('required'), 'required');
        $mform->addHelpButton('contactname', 'contactname', 'local_hub');

        $mform->addElement('text', 'contactemail',
                get_string('contactemail', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('contactemail', $contactemail);
        $mform->addRule('contactemail', get_string('required'), 'required');
        $mform->addHelpButton('contactemail', 'contactemail', 'local_hub');

        $this->update_hublogo();

        $mform->addElement('filepicker', 'hubimage',
                get_string('hubimage', 'local_hub'), null,
                array('subdirs' => 0,
                    'maxfiles' => 1
        ));
        $mform->addHelpButton('hubimage', 'hubimage', 'local_hub');


        $mform->addElement('text', 'password', get_string('password', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('password', $password);
        $mform->disabledIf('password', 'privacy', 'eq', HUBALLOWPUBLICSEARCH);
        $mform->disabledIf('password', 'privacy', 'eq', HUBALLOWGLOBALSEARCH);
        $mform->addHelpButton('password', 'hubpassword', 'local_hub');

        $mform->addElement('text', 'maxwscourseresult',
                get_string('maxwscourseresult', 'local_hub'));
        $mform->addHelpButton('maxwscourseresult',
                'maxwscourseresult', 'local_hub');
        $mform->setAdvanced('maxwscourseresult');
        $mform->setDefault('maxwscourseresult', $hubmaxwscourseresult);

        $mform->addElement('text', 'maxcoursesperday',
                get_string('maxcoursesperday', 'local_hub'));
        $mform->addHelpButton('maxcoursesperday',
                'maxcoursesperday', 'local_hub');
        $mform->setAdvanced('maxcoursesperday');
        $mform->setDefault('maxcoursesperday', $hubmaxpublication);

        $mform->addElement('checkbox', 'enablerssfeeds',
                get_string('enablerssfeeds', 'local_hub'));
        $mform->addHelpButton('enablerssfeeds', 'enablerssfeeds', 'local_hub');
        $mform->setAdvanced('enablerssfeeds');
        $mform->setDefault('enablerssfeeds', $enablerssfeeds);

        $mform->addElement('checkbox', 'searchfornologin',
                get_string('searchfornologin', 'local_hub'), '');
        $mform->setDefault('searchfornologin', $searchfornologin);
        $mform->addHelpButton('searchfornologin', 'searchfornologin', 'local_hub');
        $mform->setAdvanced('searchfornologin');

        $mform->addElement('text', 'rsssecret',
                get_string('rsssecret', 'local_hub'));
        $mform->addHelpButton('rsssecret',
                'rsssecret', 'local_hub');
        $mform->setAdvanced('rsssecret');
        $mform->setDefault('rsssecret', $rsssecret);

        $this->add_action_buttons(false, get_string('update'));
    }

    /**
     * Validate fields
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $name = $this->_form->_submitValues['name'];
        if (!empty($name)) {
            if (strcmp(clean_param($name, PARAM_TEXT), $name) != 0) {
                $errors['name'] = get_string('mustbetext', 'local_hub');
            }
        }

        $maxwscourseresult = $this->_form->_submitValues['maxwscourseresult'];
        if (empty($maxwscourseresult) or $maxwscourseresult < 1) {
           $errors['maxwscourseresult'] = get_string('maxwscourseresultempty', 'local_hub');
        }

        $desc = $this->_form->_submitValues['desc'];
        if (!empty($desc)) {
            if (strcmp(clean_param($desc, PARAM_TEXT), $desc) != 0) {
                $errors['desc'] = get_string('mustbetext', 'local_hub');
            }
        }

        $contactemail = $this->_form->_submitValues['contactemail'];
        if (!empty($contactemail)) {
            if (strcmp(clean_param($contactemail, PARAM_EMAIL), $contactemail) != 0) {
                $errors['contactemail'] = get_string('mustbeemail', 'local_hub');
            }
        }

        $maxcoursesperday = $this->_form->_submitValues['maxcoursesperday'];
        if (!empty($maxcoursesperday)) {
            if (strcmp(clean_param($maxcoursesperday, PARAM_INT), $maxcoursesperday) != 0) {
                $errors['maxcoursesperday'] = get_string('mustbeinteger', 'local_hub');
            }
        }

        return $errors;
    }

    /**
     * Add/remove the hub logo form element
     */
    public function update_hublogo() {
        $mform = & $this->_form;
        $logocheckbox = '';
        if ($mform->elementExists('keepcurrentimage')) {
            $logocheckbox = $mform->getElement('keepcurrentimage');
        }
        $hublogo = get_config('local_hub', 'hublogo');
        if (!empty($hublogo)) {

            $params = array('filetype' => HUB_HUBSCREENSHOT_FILE_TYPE, 'time' => time());
            $imageurl = new moodle_url("/local/hub/webservice/download.php", $params);

            $hubname = get_config('local_hub', 'name');
            if ($hubname === false) {
                $hubname = $SITE->fullname;
            }

            $imagetag = html_writer::empty_tag('img', array('src' => $imageurl, 'alt' => $hubname,
                        'class' => 'admincurrentimage'));
            if (!empty($logocheckbox)) {
                $logocheckbox->setText($imagetag);
            } else {
                $mform->addElement('checkbox', 'keepcurrentimage',
                    get_string('keepcurrentimage', 'local_hub'), ' ' . $imagetag);
                $mform->addHelpButton('keepcurrentimage', 'keepcurrentimage', 'local_hub');
                $mform->setDefault('keepcurrentimage', true);
                //need to move the element to the right position
                //    =>fix the issue where saving settings add the element at the bottom of the form
                $this->move_element_back_after('keepcurrentimage', 'contactemail');
            }

        } else { //if no logo, remove the hub logo element
            if (!empty($logocheckbox)) {
                $mform->removeElement('keepcurrentimage');
            }
        }
    }

    /**
     * Move a mform element right after another element
     * @param string $elementname
     * @param string $previouselementname
     */
    protected function move_element_back_after($elementname, $previouselementname) {
        $previouselementindex = $this->_form->_elementIndex[$previouselementname];
        $elementindex = $this->_form->_elementIndex[$elementname];

        //only move the $element if it is after the $previouselement
        if ($previouselementindex + 1 < $elementindex) {

            //backup elements currently between the previous element and the element to move
            for ($i = $previouselementindex + 1;
            ($i <= (count($this->_form->_elements) - 1)) and ($i != $elementindex); $i = $i +1) {
                    $followingelements[$i + 1] = $this->_form->_elements[$i];
            }

            //move the element
            $this->_form->_elementIndex[$elementname] = $previouselementindex + 1;
            $this->_form->_elements[$previouselementindex + 1] = $this->_form->_elements[$elementindex];

            //move the betweener elements after the moved element
            foreach ($followingelements as $newindex => $element) {
                $this->_form->_elementIndex[$element->_attributes['name']] = $newindex;
                $this->_form->_elements[$newindex] = $element;
            }
        }
    }

}

/**
 * This form displays site settings
 */
class hub_site_settings_form extends moodleform {

    public function definition() {

        //retrieve the publication
        $hub = new local_hub();
        $site = $hub->get_site($this->_customdata['id']);

        $mform = & $this->_form;
        $mform->addElement('header', 'moodle', get_string('sitesettingsform', 'local_hub', $site->name));

        $mform->addElement('hidden', 'id', $site->id);

        $mform->addElement('text', 'name',
                get_string('sitename', 'local_hub'));
        $mform->addHelpButton('name',
                'sitename', 'local_hub');
        $mform->setDefault('name', $site->name);

        $mform->addElement('text', 'url',
                get_string('siteurl', 'local_hub'));
        $mform->addHelpButton('url',
                'siteurl', 'local_hub');
        $mform->setDefault('url', $site->url);

        $mform->addElement('textarea', 'description',
                get_string('sitedesc', 'local_hub'));
        $mform->addHelpButton('description',
                'sitedesc', 'local_hub');
        $mform->setDefault('description', $site->description);

        $mform->addElement('text', 'contactname',
                get_string('siteadmin', 'local_hub'));
        $mform->addHelpButton('contactname',
                'siteadmin', 'local_hub');
        $mform->setDefault('contactname', $site->contactname);

        $mform->addElement('text', 'contactemail',
                get_string('siteadminemail', 'local_hub'));
        $mform->addHelpButton('contactemail',
                'siteadminemail', 'local_hub');
        $mform->setDefault('contactemail', $site->contactemail);

        $languages = get_string_manager()->get_list_of_languages();
        asort($languages, SORT_LOCALE_STRING);
        $mform->addElement('select', 'language', get_string('sitelanguage', 'local_hub'), $languages);
        $mform->setDefault('language', $site->language);
        $mform->addHelpButton('language', 'sitelanguage', 'local_hub');

        $countries = get_string_manager()->get_list_of_countries();
        $mform->addElement('select', 'countrycode', get_string('sitecountry', 'local_hub'), $countries);
        $mform->setDefault('countrycode', $site->countrycode);
        $mform->addHelpButton('countrycode', 'sitecountry', 'local_hub');

        $mform->addElement('text', 'publicationmax',
                get_string('publicationmax', 'local_hub'));
        $mform->addHelpButton('publicationmax',
                'publicationmax', 'local_hub');
        $mform->setDefault('publicationmax', $site->publicationmax);

        $this->add_action_buttons(false, get_string('update'));
    }

    /**
     * Set password to empty if hub not private
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $publicationmax = $this->_form->_submitValues['publicationmax'];
        if (!empty($publicationmax)) {
            if (strcmp(clean_param($publicationmax, PARAM_INT), $publicationmax) != 0) {
                $errors['publicationmax'] = get_string('mustbeinteger', 'local_hub');
            }
        }

        return $errors;
    }

}

/**
 * This form displays course settings
 */
class hub_course_settings_form extends moodleform {

    public function definition() {
        global $CFG;

        $strrequired = get_string('required');

        //retrieve the publication
        $hub = new local_hub();
        $course = $hub->get_course($this->_customdata['id']);

        $mform = & $this->_form;
        $mform->addElement('header', 'moodle',
                get_string('coursesettingsform', 'local_hub', $course->fullname));

        $mform->addElement('hidden', 'id', $course->id);

        $mform->addElement('checkbox', 'visible', get_string('coursevisibility', 'local_hub'));
        $mform->addHelpButton('visible', 'coursevisibility', 'local_hub');
        $mform->setDefault('visible', $course->privacy);

        $mform->addElement('text', 'fullname',
                get_string('coursename', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->addHelpButton('fullname',
                'coursename', 'local_hub');
        $mform->setDefault('fullname', $course->fullname);
        $mform->addRule('fullname', $strrequired, 'required', null, 'client');

        if (!empty($course->courseurl)) {
            $mform->addElement('text', 'courseurl',
                    get_string('courseurl', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
            $mform->addHelpButton('courseurl',
                    'courseurl', 'local_hub');
            $mform->setDefault('courseurl', $course->courseurl);
            $mform->addRule('courseurl', $strrequired, 'required', null, 'client');
        } else {
            $mform->addElement('text', 'demourl',
                    get_string('demourl', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
            $mform->addHelpButton('demourl',
                    'demourl', 'local_hub');
            $mform->setDefault('demourl', $course->demourl);
        }

        $mform->addElement('textarea', 'description',
                get_string('coursedesc', 'local_hub'),
                array('rows' => 10, 'cols' => 56, 'class' => 'coursesettingstextarea'));
        $mform->addHelpButton('description',
                'coursedesc', 'local_hub');
        $mform->addRule('description', $strrequired, 'required', null, 'client');
        $mform->setDefault('description', $course->description);

        $languages = get_string_manager()->get_list_of_languages();
        asort($languages, SORT_LOCALE_STRING);
        $mform->addElement('select', 'language', get_string('courselang', 'local_hub'), $languages);
        $mform->setDefault('language', $course->language);
        $mform->addHelpButton('language', 'courselang', 'local_hub');

        $mform->addElement('text', 'publishername', get_string('publishername', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->setDefault('publishername', $course->publishername);
        $mform->addRule('publishername', $strrequired, 'required', null, 'client');
        $mform->addHelpButton('publishername', 'publishername', 'local_hub');

        $mform->addElement('text', 'publisheremail', get_string('publisheremail', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->setDefault('publisheremail', $course->publisheremail);
        $mform->addRule('publisheremail', $strrequired, 'required', null, 'client');
        $mform->addHelpButton('publisheremail', 'publisheremail', 'local_hub');

        $mform->addElement('text', 'creatorname', get_string('creatorname', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->addRule('creatorname', $strrequired, 'required', null, 'client');
        $mform->setType('creatorname', PARAM_TEXT);
        $mform->setDefault('creatorname', $course->creatorname);
        $mform->addHelpButton('creatorname', 'creatorname', 'local_hub');

        $mform->addElement('text', 'contributornames', get_string('contributornames', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->setDefault('contributornames', $course->contributornames);
        $mform->addHelpButton('contributornames', 'contributornames', 'local_hub');

        $mform->addElement('text', 'coverage', get_string('tags', 'local_hub'),
                array('class' => 'coursesettingstextfield'));
        $mform->setType('coverage', PARAM_TEXT);
        $mform->setDefault('coverage', $course->coverage);
        $mform->addHelpButton('coverage', 'tags', 'local_hub');

        require_once($CFG->libdir . "/licenselib.php");
        $licensemanager = new license_manager();
        $licences = $licensemanager->get_licenses();
        $options = array();
        foreach ($licences as $license) {
            $options[$license->shortname] = get_string($license->shortname, 'license');
        }
        $mform->addElement('select', 'licence', get_string('licence', 'local_hub'), $options);
        $mform->setDefault('licence', $course->licenceshortname);
        unset($options);
        $mform->addHelpButton('licence', 'licence', 'local_hub');
        require_once($CFG->dirroot . "/course/publish/lib.php");
        $publicationmanager = new course_publish_manager();
        $options = $publicationmanager->get_sorted_subjects();

        //prepare data for the smartselect
        foreach ($options as $key => &$option) {
            $keylength = strlen($key);
            if ($keylength == 10) {
                $option = "&nbsp;&nbsp;" . $option;
            } else if ($keylength == 12) {
                $option = "&nbsp;&nbsp;&nbsp;&nbsp;" . $option;
            }
        }

        $options = array('none' => get_string('none', 'local_hub')) + $options;
        $mform->addElement('select', 'subject', get_string('subject', 'local_hub'), $options);
        unset($options);
        $mform->addHelpButton('subject', 'subject', 'local_hub');
        $mform->setDefault('subject', $course->subject);
        $mform->addRule('subject', $strrequired, 'required', null, 'client');
        $this->init_javascript_enhancement('subject', 'smartselect',
                array('selectablecategories' => false, 'mode' => 'compact'));

        $options = array();
        $options[HUB_AUDIENCE_EDUCATORS] = get_string('audienceeducators', 'local_hub');
        $options[HUB_AUDIENCE_STUDENTS] = get_string('audiencestudents', 'local_hub');
        $options[HUB_AUDIENCE_ADMINS] = get_string('audienceadmins', 'local_hub');
        $mform->addElement('select', 'audience', get_string('audience', 'local_hub'), $options);
        $mform->setDefault('audience', $course->audience);
        unset($options);
        $mform->addHelpButton('audience', 'audience', 'local_hub');

        $options = array();
        $options[HUB_EDULEVEL_PRIMARY] = get_string('edulevelprimary', 'local_hub');
        $options[HUB_EDULEVEL_SECONDARY] = get_string('edulevelsecondary', 'local_hub');
        $options[HUB_EDULEVEL_TERTIARY] = get_string('eduleveltertiary', 'local_hub');
        $options[HUB_EDULEVEL_GOVERNMENT] = get_string('edulevelgovernment', 'local_hub');
        $options[HUB_EDULEVEL_ASSOCIATION] = get_string('edulevelassociation', 'local_hub');
        $options[HUB_EDULEVEL_CORPORATE] = get_string('edulevelcorporate', 'local_hub');
        $options[HUB_EDULEVEL_OTHER] = get_string('edulevelother', 'local_hub');
        $mform->addElement('select', 'educationallevel', get_string('educationallevel', 'local_hub'), $options);
        $mform->setDefault('educationallevel', $course->educationallevel);
        unset($options);
        $mform->addHelpButton('educationallevel', 'educationallevel', 'local_hub');


        //setdefault is currently not supported making this required field not usable MDL-20988
        $editoroptions = array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false, 'forcehttps' => false);
        $mform->addElement('editor', 'creatornotes', get_string('creatornotes', 'hub'), '', $editoroptions);
        $mform->setType('creatornotes', PARAM_CLEANHTML);
        $mform->addHelpButton('creatornotes', 'creatornotes', 'hub');
        
        //set default value for creatornotes editor
        $data = new stdClass();
        $data->creatornotes = array();
        $data->creatornotes['text'] = $course->creatornotes;
        $data->creatornotes['format'] = $course->creatornotesformat;
        $this->set_data($data);

        //select screenshots to keep
        for ($screenshotnumber = 1; $screenshotnumber <= $course->screenshots; $screenshotnumber++) {
            $baseurl = new moodle_url($CFG->wwwroot . '/local/hub/webservice/download.php',
                                    array('courseid' => $course->id,
                                        'filetype' => HUB_SCREENSHOT_FILE_TYPE,
                                        'screenshotnumber' => $screenshotnumber,
                                        'alwaysreload' => time()));
            $screenshothtml = html_writer::empty_tag('img',
                            array('src' => $baseurl, 'alt' => $course->fullname));
            $coursescreenshot = html_writer::tag('div', $screenshothtml,
                            array('class' => ''));
            $mform->addElement('checkbox', 'screenshot_' . $screenshotnumber,
                    get_string('keepscreenshot', 'local_hub'), $coursescreenshot);
            $mform->addHelpButton('screenshot_' . $screenshotnumber, 'keepscreenshot', 'local_hub');
            $mform->setDefault('screenshot_' . $screenshotnumber, 1);
        }

        $mform->addElement('filemanager', 'addscreenshots', get_string('addscreenshots', 'local_hub'), null,
                array('subdirs' => 0,
                    'maxbytes' => 1000000,
                    'maxfiles' => MAXSCREENSHOTSNUMBER
        ));
        $mform->addHelpButton('addscreenshots', 'addscreenshots', 'local_hub');

        $this->add_action_buttons(false, get_string('update'));
    }

}