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

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot.'/lib/hublib.php');

/**
 * This form display registration form to Moodle.org
 * TODO: this form had some none hidden inputs originally, it has been changed since this time
 *       delete this Moodle form for a renderer with a single button
 */
class hub_registration_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $SITE;
        $hub = new local_hub();

        $mform =& $this->_form;
        $mform->addElement('header', 'moodle', get_string('hubdetails', 'local_hub'));
        $mform->addElement('static', 'comment','', get_string('hubregistrationcomment', 'local_hub'));
        $mform->addElement('hidden', 'url',   $CFG->wwwroot);

        $languages = get_string_manager()->get_list_of_languages();

        $hubname = get_config('local_hub', 'name');
        $hubdescription = get_config('local_hub', 'description');
        $contactname = get_config('local_hub', 'contactname');
        $contactemail = get_config('local_hub', 'contactemail');
        $hublogo = get_config('local_hub', 'hublogo');
        $privacy = get_config('local_hub', 'privacy');
        $hublanguage = get_config('local_hub', 'language');

        $mform->addElement('static', 'hubnamestring', get_string('name', 'local_hub'), $hubname);
        $mform->addElement('hidden', 'name', $hubname);
        $mform->addElement('static', 'hubprivacystring', get_string('privacy', 'local_hub'),
                $hub->get_privacy_string($privacy));
        $mform->addElement('hidden', 'privacy', $privacy);
        $mform->addElement('static', 'languagestring', get_string('language'), $languages[$hublanguage]);
        $mform->addElement('hidden', 'language', $hublanguage);
        $mform->addElement('static', 'hubdescriptionstring', get_string('description', 'local_hub'), $hubdescription);
        $mform->addElement('hidden', 'description', $hubdescription);
        $mform->addElement('static', 'contactnamestring', get_string('contactname','local_hub'), $contactname);
        $mform->addElement('hidden', 'contactname', $contactname);
        $mform->addElement('static', 'contactemailstring', get_string('contactemail', 'local_hub'), $contactemail);
        $mform->addElement('hidden', 'contactemail', $contactemail);
        if (!empty($hublogo)) {
            $params = array('filetype' => HUB_HUBSCREENSHOT_FILE_TYPE, 'time' => time());
            $imageurl = new moodle_url($CFG->wwwroot . "/local/hub/webservice/download.php", $params);
            $imagetag = html_writer::empty_tag('img',
                    array('src' => $imageurl, 'alt' => $hubname));
            $mform->addElement('static', 'logourlstring', get_string('image', 'local_hub'), $imagetag);
            

        } else {
            $hublogo = 0;
        }
        $mform->addElement('hidden', 'hublogo', $hublogo);
        
        $mform->addElement('static', 'urlstring', get_string('url', 'local_hub'), $CFG->wwwroot);

        $registeredsites = $hub->get_registered_sites_total();
        $registeredcourses = $hub->get_registered_courses_total();

        $mform->addElement('static', 'sitesstring', get_string('registeredsites', 'local_hub'), $registeredsites);
        $mform->addElement('hidden', 'sites', $registeredsites);
        $mform->addElement('static', 'coursesstring', get_string('registeredcourses', 'local_hub'), $registeredcourses);
        $mform->addElement('hidden', 'courses', $registeredcourses);

        //if the hub is private do not display the register button
        if ($privacy != HUBPRIVATE and empty($this->_customdata['alreadyregistered'])) {
            $buttonlabel = get_string('hubregister','local_hub');
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
        if ($hubname  === false) {
            $hubname = $SITE->fullname;
        }

        //description (default) value
        $hubdescription = get_config('local_hub', 'description');
        if ($hubdescription  === false) {
            $hubdescription = $SITE->summary;
        }

        //contactname (default) value
        $contactname = get_config('local_hub', 'contactname');
        if ($contactname  === false) {
            $contactname = $USER->firstname." ".$USER->lastname;
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

         //language (default) value
        $password = get_config('local_hub', 'password');

        $enabled = get_config('local_hub', 'hubenabled');

        $languages = get_string_manager()->get_list_of_languages();

        $strrequired = get_string('required');
        $mform =& $this->_form;
        $mform->addElement('header', 'moodle', get_string('settings', 'local_hub'));

        $mform->addElement('text', 'name', get_string('name', 'local_hub'), array('class' => 'admintextfield'));
        $mform->setDefault('name', $hubname);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addHelpButton('name', 'name', 'local_hub');

        $privacyoptions = array(HUBPRIVATE => get_string('nosearch', 'local_hub'),
                HUBALLOWPUBLICSEARCH => get_string('allowpublicsearch', 'local_hub'),
                HUBALLOWGLOBALSEARCH => get_string('allowglobalsearch', 'local_hub'));
        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'local_hub'),'');
        $mform->setDefault('enabled', $enabled);
        $mform->addHelpButton('enabled', 'enabled', 'local_hub');

        $mform->addElement('select', 'privacy', get_string('privacy', 'local_hub'), $privacyoptions);
        $mform->setDefault('privacy', $privacy);
        $mform->addHelpButton('privacy', 'privacy', 'local_hub');

        $mform->addElement('select', 'lang', get_string('language', 'local_hub'), $languages);
        $mform->setDefault('lang', $hublanguage);
        $mform->addHelpButton('lang', 'hublang', 'local_hub');

        $mform->addElement('textarea', 'desc', get_string('description', 'local_hub'), 
                array('rows' => 10, 'cols' => 15, 'class' => 'adminhubdescription'));
        $mform->addRule('desc', get_string('required'), 'required');
        $mform->setDefault('desc', $hubdescription);
        $mform->addHelpButton('desc', 'description', 'local_hub');

        $mform->addElement('text', 'contactname', get_string('contactname','local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('contactname', $contactname);
        $mform->addRule('contactname', get_string('required'), 'required');
        $mform->addHelpButton('contactname', 'contactname', 'local_hub');

        $mform->addElement('text', 'contactemail', get_string('contactemail', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('contactemail', $contactemail);
        $mform->addRule('contactemail', get_string('required'), 'required');
        $mform->addHelpButton('contactemail', 'contactemail', 'local_hub');

        $hublogo = get_config('local_hub', 'hublogo');
        if (!empty($hublogo)) {
            $params = array('filetype' => HUB_HUBSCREENSHOT_FILE_TYPE, 'time' => time());
            $imageurl = new moodle_url($CFG->wwwroot . "/local/hub/webservice/download.php", $params);
            $imagetag = html_writer::empty_tag('img', 
                    array('src' => $imageurl, 'alt' => $hubname, 'class' => 'admincurrentimage'));
            $mform->addElement('checkbox', 'keepcurrentimage', get_string('keepcurrentimage', 'local_hub'), ' '.$imagetag);
            $mform->addHelpButton('keepcurrentimage', 'keepcurrentimage', 'local_hub');
            $mform->setDefault('keepcurrentimage', true);
        }

        $mform->addElement('filepicker', 'hubimage', get_string('hubimage','local_hub'), null,
                array('subdirs'=>0,
                'maxfiles'=>1
        ));
        $mform->addHelpButton('hubimage', 'hubimage', 'local_hub');


        $mform->addElement('text', 'password', get_string('password', 'local_hub')
                , array('class' => 'admintextfield'));
        $mform->setDefault('password', $password);
        $mform->disabledIf('password', 'privacy', 'eq', HUBALLOWPUBLICSEARCH);
        $mform->disabledIf('password', 'privacy', 'eq', HUBALLOWGLOBALSEARCH);
        $mform->addHelpButton('password', 'hubpassword', 'local_hub');

        $this->add_action_buttons(false, get_string('update'));

    }

}
