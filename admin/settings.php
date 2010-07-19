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
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/admin/forms.php');
require_once($CFG->dirroot . '/webservice/lib.php');

admin_externalpage_setup('hubsettings');

$hubsettingsform = new hub_settings_form();
$fromform = $hubsettingsform->get_data();

echo $OUTPUT->header();

//check that the PHP xmlrpc extension is enabled
if (!extension_loaded('xmlrpc')) {
    $xmlrpcnotification = $OUTPUT->doc_link('admin/environment/php_extension/xmlrpc', '');
    $xmlrpcnotification .= get_string('xmlrpcdisabled', 'local_hub');
    echo $OUTPUT->notification($xmlrpcnotification);
    echo $OUTPUT->footer();
    die();
}

if (!empty($fromform)) {

    if ($fromform->privacy != HUBPRIVATE and !empty($fromform->password)) {
        throw new moodle_exception('cannotsetpasswordforpublichub', 'local_hub', new moodle_url('/local/hub/admin/settings.php'));
    }

    //Save settings
    set_config('name', $fromform->name, 'local_hub');
    set_config('hubenabled', $fromform->enabled, 'local_hub');
    set_config('description', $fromform->desc, 'local_hub');
    set_config('contactname', $fromform->contactname, 'local_hub');
    set_config('contactemail', $fromform->contactemail, 'local_hub');
    set_config('maxcoursesperday', $fromform->maxcoursesperday, 'local_hub');
    set_config('enablerssfeeds', $fromform->enablerssfeeds, 'local_hub');

    set_config('language', $fromform->lang, 'local_hub');
    set_config('password', $fromform->password, 'local_hub');

    //if privacy settings is downgraded to 'private', then unregister from the hub
    $currentprivacy = get_config('local_hub', 'privacy');
    $hubmanager = new local_hub();
    $hubtodirectorycommunication = $hubmanager->get_communication(WSCLIENT, HUBDIRECTORY, HUB_HUBDIRECTORYURL);
    if (($currentprivacy != HUBPRIVATE and $fromform->privacy == HUBPRIVATE) and !empty($hubtodirectorycommunication)
            and !empty($hubtodirectorycommunication->confirmed)) {

        $directorytohubcommunication = $hubmanager->get_communication(WSSERVER, HUBDIRECTORY, HUB_HUBDIRECTORYURL);

        $function = 'hubdirectory_unregister_hub';
        $params = array();
        $serverurl = HUB_HUBDIRECTORYURL . "/local/hubdirectory/webservice/webservices.php";
        require_once($CFG->dirroot . "/webservice/xmlrpc/lib.php");
        $xmlrpcclient = new webservice_xmlrpc_client($serverurl, $hubtodirectorycommunication->token);
        try {
            $result = $xmlrpcclient->call($function, $params);
        } catch (Exception $e) {
            $error = $OUTPUT->notification(get_string('failunregistrationofprivate', 'local_hub', $e->getMessage()));
        }

        if (empty($error)) {
            //delete the web service token
            $webservice_manager = new webservice();
            $tokentodelete = $webservice_manager->get_user_ws_token($directorytohubcommunication->token);
            $webservice_manager->delete_user_ws_token($tokentodelete->id);

            //delete the communication
            $hubmanager->delete_communication($directorytohubcommunication);
            $hubmanager->delete_communication($hubtodirectorycommunication);
            echo $OUTPUT->notification(get_string('unregistrationofprivate', 'local_hub'), 'notifysuccess');
        } else {
            echo $error;
        }
    }
    set_config('privacy', $fromform->privacy, 'local_hub');

    //save the image
    if (empty($fromform->keepcurrentimage)) {
           
        $file = $hubsettingsform->save_temp_file('hubimage');

        // if ($file->is_valid_image()) {
        $userdir = "hub/0/";

        //create directory if doesn't exist
        $directory = make_upload_directory($userdir);

        //save the image into the directory
        copy($file,  $directory . 'hublogo');

        set_config('hublogo', true, 'local_hub');
        $updatelogo = true;

        //   }
    }

    if (empty($updatelogo) and empty($fromform->keepcurrentimage)) {
        set_config('hublogo', false, 'local_hub');
    }

    //reload the form
    $hubsettingsform = new hub_settings_form();

    //display confirmation
    echo $OUTPUT->notification(get_string('settingsupdated', 'local_hub'), 'notifysuccess');
}

//check if Recaptcha is enabled
if (!$CFG->recaptchapublickey or !$CFG->recaptchaprivatekey) {
    $recaptchaurl = new moodle_url('/admin/search.php', array('query' => 'recaptcha'));
    $recaptchalink = html_writer::tag('a',
                    get_string('recaptcha', 'local_hub'),
                    array('href' => $recaptchaurl));
    echo $OUTPUT->notification(get_string('recaptchadisable', 'local_hub', $recaptchalink));
} else {
    $hubsettingsform->display();
}
echo $OUTPUT->footer();

