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
 * On this page the hub administrator register on the hub directory.
 * It redirects the admin on a confirmation page on the hub directory.
 * This page also handles update by web services.
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/admin/forms.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->dirroot . '/local/hub/lib.php');

admin_externalpage_setup('hubregistration');

//check that the PHP xmlrpc extension is enabled
if (!extension_loaded('xmlrpc')) {
    echo $OUTPUT->header();
    $xmlrpcnotification = $OUTPUT->doc_link('admin/environment/php_extension/xmlrpc', '');
    $xmlrpcnotification .= get_string('xmlrpcdisabled', 'local_hub');
    echo $OUTPUT->notification($xmlrpcnotification);
    echo $OUTPUT->footer();
    die();
}

$error = optional_param('error', '', PARAM_TEXT);

$hub = new local_hub();

$directorytohubcommunication = $hub->get_communication(WSSERVER, HUBDIRECTORY, HUB_HUBDIRECTORYURL);

$hubtodirectorycommunication = $hub->get_communication(WSCLIENT, HUBDIRECTORY, HUB_HUBDIRECTORYURL);

$hubregistrationform = new hub_registration_form('',
                array('alreadyregistered' => !empty($hubtodirectorycommunication->confirmed)));


/////// UNREGISTER ACTION //////
$unregister = optional_param('unregister', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$force = optional_param('force', 0, PARAM_INT);
if ($unregister && $confirm && confirm_sesskey()) {

    if (!$force) {
        $function = 'hubdirectory_unregister_hub';
        $params = array();
        $serverurl = HUB_HUBDIRECTORYURL . "/local/hubdirectory/webservice/webservices.php";
        require_once($CFG->dirroot . "/webservice/xmlrpc/lib.php");
        $xmlrpcclient = new webservice_xmlrpc_client($serverurl, $hubtodirectorycommunication->token);
        try {
            $result = $xmlrpcclient->call($function, $params);
        } catch (Exception $e) {
            $error = get_string('errorunregistration', 'local_hub', $e->getMessage());
        }
    }

    if (empty($error)) {
        if (!empty($directorytohubcommunication)) {
            //delete the web service token
            $webservice_manager = new webservice();
            $tokentodelete = $webservice_manager->get_user_ws_token($directorytohubcommunication->token);
            $webservice_manager->delete_user_ws_token($tokentodelete->id);

            //delete the communication
            $hub->delete_communication($directorytohubcommunication);
        }

        if (!empty($hubtodirectorycommunication)) {
            $hub->delete_communication($hubtodirectorycommunication);
        }
    }

    redirect(new moodle_url('/local/hub/admin/register.php',
                    array('sesskey' => sesskey(), 'error' => $error)));
}


/////// UPDATE ACTION ////////
// update the hub registration (in fact it is a new registration)
$update = optional_param('update', 0, PARAM_INT);
if ($update && confirm_sesskey()) {
    //update the registration
    $function = 'hubdirectory_update_hub_info';
    $hubinfo = $hub->get_info();
    $hubinfo['name'] = clean_param($hubinfo['name'], PARAM_TEXT);
    $hubinfo['description'] = clean_param($hubinfo['description'], PARAM_TEXT);
    $hubinfo['contactname'] = clean_param($hubinfo['contactname'], PARAM_TEXT);
    $params = array('hubinfo' => $hubinfo);
    $serverurl = HUB_HUBDIRECTORYURL . "/local/hubdirectory/webservice/webservices.php";
    require_once($CFG->dirroot . "/webservice/xmlrpc/lib.php");
    $xmlrpcclient = new webservice_xmlrpc_client($serverurl, $hubtodirectorycommunication->token);
    try {
        $result = $xmlrpcclient->call($function, $params);
    } catch (Exception $e) {
        $error = get_string('errorregistration', 'local_hub', $e->getMessage());
    }
}


/////// FORM REGISTRATION ACTION //////
// retrieve the privacy setting
$privacy = get_config('local_hub', 'privacy');
$fromform = $hubregistrationform->get_data();
if (!empty($fromform) and confirm_sesskey()) { // if the register button has been clicked
    //we are using the form input as the redirection parameters
    //(token, url and name)
    $params = (array) $fromform;
    //first time we press the registration button (and only time if no failure)
    if (empty($directorytohubcommunication)) {

        //create new token for the hub directory to call the hub
        $capabilities = array('local/hub:viewinfo');
        $token = $hub->create_hub_token('Moodle.org Hub Directory', 'Hub directory',
                        HUB_HUBDIRECTORYURL . '_directory_user', $capabilities);

        //we save the token into the communication table in order to have a reference to the hidden token
        $directorytohubcommunication = new stdClass();
        $directorytohubcommunication->token = $token->token;
        $directorytohubcommunication->type = WSSERVER;
        $directorytohubcommunication->remotename = 'Moodle.org hub directory';
        $directorytohubcommunication->remoteentity = HUBDIRECTORY;
        $directorytohubcommunication->remoteurl = HUB_HUBDIRECTORYURL;
        $directorytohubcommunication->confirmed = 0;
        $directorytohubcommunication->id = $hub->add_communication($directorytohubcommunication);

        $params['token'] = $token->token;
    } else {
        $params['token'] = $directorytohubcommunication->token;
    }

    //if the hub is private do not redirect to moodle.org
    if ($privacy != HUBPRIVATE) {
        redirect(new moodle_url(HUB_HUBDIRECTORYURL . '/local/hubdirectory/hubregistration.php', $params));
    }
}


/////// OUTPUT SECTION /////////////
echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_hub');
//unregister confirmation page
if ($unregister && empty($confirm)) {
    echo $renderer->unregistration_confirmation($force);
} else {
    $hubregistrationform->display();

//if the hub is private, do not display the register button
    if ($privacy == HUBPRIVATE) {
        echo $OUTPUT->notification(get_string('cannotregisterprivatehub', 'local_hub'));
    }

//Display update single button if needed
    if (!empty($hubtodirectorycommunication->confirmed) and $privacy != HUBPRIVATE) {

        //display update result
        if (!empty($result)) {
            echo $OUTPUT->notification(get_string('registrationupdated', 'local_hub'), 'notifysuccess');
        }

        if (!empty($error) and confirm_sesskey()) {
            echo $OUTPUT->notification($error);
        }

        $url = new moodle_url("/local/hub/admin/register.php",
                        array('sesskey' => sesskey(), 'update' => 1));
        $button = new single_button($url, get_string('hubregisterupdate', 'local_hub'));
        $button->class = "buttoncenter";
        echo $OUTPUT->render($button);

        $url = new moodle_url("/local/hub/admin/register.php",
                        array('confirm' => 0, 'unregister' => 1));
        $button = new single_button($url, get_string('hubunregister', 'local_hub'));
        $button->class = "buttoncenter";
        echo $OUTPUT->render($button);
    }
}

echo $OUTPUT->footer();

