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
 * On this page administrator can change hub settings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/hub/admin/forms.php');

admin_externalpage_setup('hubsettings');

$hubsettingsform = new hub_settings_form();

$fromform = $hubsettingsform->get_data();

echo $OUTPUT->header();

if (!empty($fromform)) {

    //Save settings
    set_config('name', $fromform->name ,'local_hub');
    set_config('hubenabled', $fromform->enabled ,'local_hub');
    set_config('description', $fromform->desc ,'local_hub');
    set_config('contactname', $fromform->contactname ,'local_hub');
    set_config('contactemail', $fromform->contactemail ,'local_hub');
    //set_config('imageurl', $fromform->imageurl ,'local_hub');
    set_config('privacy', $fromform->privacy ,'local_hub');
    set_config('language', $fromform->lang ,'local_hub');

    //display confirmation
    echo $OUTPUT->notification(get_string('settingsupdated', 'local_hub'), 'notifysuccess');
}

$hubsettingsform->display();
echo $OUTPUT->footer();

