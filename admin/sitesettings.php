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
 * On this page administrator can change site settings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/admin/forms.php');
require_once($CFG->dirroot . '/webservice/lib.php');

admin_externalpage_setup('sitesettings');

$id = optional_param('id', 0, PARAM_INT);
$hub = new local_hub();
$site = $hub->get_site($id, MUST_EXIST);

//define nav bar
$PAGE->set_url('/local/hub/admin/sitesettings.php', array('id' => $id));
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('hub', 'local_hub'));
$PAGE->navbar->add(get_string('managesites', 'local_hub'),
        new moodle_url('/local/hub/admin/managesites.php', 
                array('search' => $site->name, 'sesskey' => sesskey())));
$PAGE->navbar->add(get_string('sitesettings', 'local_hub'),
        new moodle_url('/local/hub/admin/sitesettings.php', array('id' => $id)));


$sitesettingsform = new hub_site_settings_form('',
        array('id' => $id));
$fromform = $sitesettingsform->get_data();

//Save settings and redirect to search site page
if (!empty($fromform)) {    
    if ($fromform->publicationmax === '') {
        $site->publicationmax = null;
    } else {
        $site->publicationmax = $fromform->publicationmax;
    }
    $site->name = $fromform->name;
    $site->description = $fromform->description;
    $site->contactname = $fromform->contactname;
    $site->contactemail = $fromform->contactemail;
    $site->language = $fromform->language;
    $site->countrycode = $fromform->countrycode;
    $site->url = $fromform->url;

    $hub->update_site($site);
   
    redirect(new moodle_url('/local/hub/admin/managesites.php', 
            array('sitesettings' => $site->name, 'sesskey' => sesskey(),
                'search' => $site->name)));
}

//OUTPUT
echo $OUTPUT->header();
if (!empty($message)) {
    echo $message;
}
$sitesettingsform->display();
echo $OUTPUT->footer();

