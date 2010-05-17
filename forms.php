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

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Registration confirmation form - a captcha is required to be filled by the registered hub
 * => if any spam occurs, it will be manual spam
 */
class site_registration_confirmation_form extends moodleform {

    public function definition() {
        global $CFG, $SITE;

        $strrequired = get_string('required');
        $mform =& $this->_form;
        $mform->addElement('header', 'moodle', get_string('siteregistration', 'local_hub'));

        $mform->addElement('static', 'comment','', get_string('siteregconfcomment', 'local_hub', $SITE->fullname));
        $mform->addElement('static', 'sitenamestring',get_string('sitename', 'local_hub'), $this->_customdata['name']);
        $mform->addElement('hidden', 'name',   $this->_customdata['name']);
        $mform->addElement('hidden', 'url',   $this->_customdata['url']);
        $mform->addElement('hidden', 'token',   $this->_customdata['token']);
        $mform->addElement('hidden', 'description',   $this->_customdata['description']);
        $mform->addElement('hidden', 'contactname',   $this->_customdata['contactname']);
        $mform->addElement('hidden', 'contactemail',   $this->_customdata['contactemail']);
        $mform->addElement('hidden', 'contactphone',   $this->_customdata['contactphone']);
        $mform->addElement('hidden', 'imageurl',   $this->_customdata['imageurl']);
        $mform->addElement('hidden', 'privacy',   $this->_customdata['privacy']);
        $mform->addElement('hidden', 'language',   $this->_customdata['language']);
        $mform->addElement('hidden', 'users',   $this->_customdata['users']);
        $mform->addElement('hidden', 'courses',   $this->_customdata['courses']);
        $mform->addElement('hidden', 'street',   $this->_customdata['street']);
        $mform->addElement('hidden', 'regioncode',   $this->_customdata['regioncode']);
        $mform->addElement('hidden', 'countrycode',   $this->_customdata['countrycode']);
        $mform->addElement('hidden', 'geolocation',   $this->_customdata['geolocation']);
        $mform->addElement('hidden', 'contactable',   $this->_customdata['contactable']);
        $mform->addElement('hidden', 'emailalert',   $this->_customdata['emailalert']);
        $mform->addElement('hidden', 'enrolments',   $this->_customdata['enrolments']);
        $mform->addElement('hidden', 'posts',   $this->_customdata['posts']);
        $mform->addElement('hidden', 'questions',   $this->_customdata['questions']);
        $mform->addElement('hidden', 'resources',   $this->_customdata['resources']);
        if (!empty($this->_customdata['password'])) {
            $mform->addElement('hidden', 'password',   $this->_customdata['password']);
        }
        $mform->addElement('hidden', 'participantnumberaverage',   $this->_customdata['participantnumberaverage']);
        $mform->addElement('hidden', 'modulenumberaverage',   $this->_customdata['modulenumberaverage']);
        $mform->addElement('hidden', 'moodleversion',   $this->_customdata['moodleversion']);
        $mform->addElement('hidden', 'moodlerelease',   $this->_customdata['moodlerelease']);
        $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
        $mform->setHelpButton('recaptcha_element', array('recaptcha', get_string('recaptcha', 'auth')));

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
        $mform =& $this->_form;
        $search = $this->_customdata['search'];
        $mform->addElement('header', 'site', get_string('search', 'local_hub'));

        $options = array(0 => get_string('enrollable', 'local_hub'),
                1 => get_string('downloadable', 'local_hub'));
        $mform->addElement('select', 'downloadable', '',
                $options);

        $options = array();
        $options['all'] = get_string('any');
        $options[AUDIENCE_EDUCATORS] = get_string('audienceeducators', 'hub');
        $options[AUDIENCE_STUDENTS] = get_string('audiencestudents', 'hub');
        $options[AUDIENCE_ADMINS] = get_string('audienceadmins', 'hub');
        $mform->addElement('select', 'audience', get_string('audience', 'hub'), $options);
        $mform->setDefault('audience', 'all');
        unset($options);
        $mform->addHelpButton('audience', 'audience', 'hub');

        $options = array();
        $options['all'] = get_string('any');
        $options[EDULEVEL_PRIMARY] = get_string('edulevelprimary', 'hub');
        $options[EDULEVEL_SECONDARY] = get_string('edulevelsecondary', 'hub');
        $options[EDULEVEL_TERTIARY] = get_string('eduleveltertiary', 'hub');
        $options[EDULEVEL_GOVERNMENT] = get_string('edulevelgovernment', 'hub');
        $options[EDULEVEL_ASSOCIATION] = get_string('edulevelassociation', 'hub');
        $options[EDULEVEL_CORPORATE] = get_string('edulevelcorporate', 'hub');
        $options[EDULEVEL_OTHER] = get_string('edulevelother', 'hub');
        $mform->addElement('select', 'educationallevel', get_string('educationallevel', 'hub'), $options);
        $mform->setDefault('educationallevel', 'all');
        unset($options);
        $mform->addHelpButton('educationallevel', 'educationallevel', 'hub');

        $options = get_string_manager()->load_component_strings('edufields', current_language());
        foreach ($options as $key => &$option) {
            $keylength = strlen ( $key );
            if ( $keylength == 10) {
                $option = "&nbsp;&nbsp;" . $option;
            } else  if ( $keylength == 12) {
                $option = "&nbsp;&nbsp;&nbsp;&nbsp;" . $option;
            }
        }
        $options = array_merge (array('all' => get_string('any')),$options);
        $mform->addElement('select', 'subject', get_string('subject', 'hub'), $options);
        $mform->setDefault('subject', 'all');
        unset($options);
        $mform->addHelpButton('subject', 'subject', 'hub');

        require_once($CFG->dirroot."/lib/licenselib.php");
        $licensemanager = new license_manager();
        $licences = $licensemanager->get_licenses();
        $options = array();
        $options['all'] = get_string('any');
        foreach ($licences as $license) {
            $options[$license->shortname] = get_string($license->shortname, 'license');
        }
        $mform->addElement('select', 'licence', get_string('license'), $options);
        $mform->setDefault('licence', 'cc');
        unset($options);
        $mform->addHelpButton('licence', 'licence', 'hub');
        $mform->setDefault('licence', 'all');

        $languages = get_string_manager()->get_list_of_languages();
        asort($languages, SORT_LOCALE_STRING);
        $languages = array_merge (array('all' => get_string('any')),$languages);
        $mform->addElement('select', 'language',get_string('language'), $languages);
        $mform->setDefault('language', 'all');


        $mform->addElement('text','search' , get_string('search', 'local_hub'));

        $this->add_action_buttons(false, get_string('search', 'local_hub'));
    }

}
