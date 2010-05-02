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

