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
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/hub/admin/forms.php');
require_once($CFG->dirroot.'/webservice/lib.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot.'/lib/hublib.php'); //HUBDIRECTORYURL

admin_externalpage_setup('hubregistration');

/* communication DB table
-----------------------------------------------------------------------------
Local Type | Token | Local WS | Remote Type | Remote URL        | Confirmed
-----------------------------------------------------------------------------
  HUB        4er4e   server    HUB-DIRECTORY  http...moodle.org      Yes
  HUB        73j53   client    HUB-DIRECTORY  http...moodle.org      Yes
  SITE       fd8fd   client    HUB            http...hub             Yes
  HUB        ds78s   server    SITE           http...site.com        Yes
  HUB-DIR.   d7d8s   server    HUB            http...hub             Yes
-----------------------------------------------------------------------------
*/

$hub = new local_hub();

$directorytohubcommunication = $hub->get_communication(WSSERVER, HUBDIRECTORY, HUBDIRECTORYURL);

$hubtodirectorycommunication = $hub->get_communication(WSCLIENT, HUBDIRECTORY, HUBDIRECTORYURL);

$hubregistrationform = new hub_registration_form('', array('alreadyregistered' => !empty($hubtodirectorycommunication->confirmed)));
$fromform = $hubregistrationform->get_data();


/////// UNREGISTER ACTION //////
// TODO



/////// UPDATE ACTION ////////

// update the hub registration (in fact it is a new registration)
$update     = optional_param('update', 0, PARAM_INT);
if ($update && confirm_sesskey()) {
    //update the registration
    $function = 'hubdirectory_update_hub_info';
    $hubinfo = $hub->get_info();
    $params = array($hubinfo);
    $serverurl = HUBDIRECTORYURL."/local/hubdirectory/webservice/webservices.php";
    require_once($CFG->dirroot."/webservice/xmlrpc/lib.php");
    $xmlrpcclient = new webservice_xmlrpc_client();
    $result = $xmlrpcclient->call($serverurl, $hubtodirectorycommunication->token, $function, $params);
}


/////// FORM REGISTRATION ACTION //////

// retrieve the privacy setting
$privacy = get_config('local_hub', 'privacy');

if (!empty($fromform) and confirm_sesskey()) { // if the register button has been clicked
    $params = (array) $fromform; //we are using the form input as the redirection parameters (token, url and name)

    //first time we press the registration button (and only time if no failure)
    if (empty($directorytohubcommunication)) {

        //create new token for the hub directory to call the hub
        $capabilities = array('moodle/hub:viewinfo', 'moodle/hub:confirmhubregistration');
        $token = $hub->create_hub_token('Moodle.org Hub Directory', 'Hub directory', HUBDIRECTORYURL.'_directory_user',
                $capabilities);

        //we save the token into the communication table in order to have a reference to the hidden token
        $directorytohubcommunication = new stdClass();
        $directorytohubcommunication->token = $token->token;
        $directorytohubcommunication->type = WSSERVER;
        $directorytohubcommunication->remotename = 'Moodle.org hub directory';
        $directorytohubcommunication->remoteentity = HUBDIRECTORY;
        $directorytohubcommunication->remoteurl = HUBDIRECTORYURL;
        $directorytohubcommunication->confirmed = 0;
        $directorytohubcommunication->id = $hub->add_communication($directorytohubcommunication);

        $params['token'] = $token->token;

    } else {
        $params['token'] = $directorytohubcommunication->token;
    }

    //if the hub is private do not redirect to moodle.org
    if ($privacy != HUBPRIVATE) {
        redirect(new moodle_url(HUBDIRECTORYURL.'/local/hubdirectory/hubregistration.php', $params));
    }

}


/////// OUTPUT SECTION /////////////


echo $OUTPUT->header();

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

    $url = new moodle_url("/local/hub/admin/register.php",
            array('sesskey' => sesskey(), 'update' => 1));
    $button = new single_button($url, get_string('hubregisterupdate', 'local_hub'));
    $button->class = "buttoncenter";
    echo $OUTPUT->render($button);
}

echo $OUTPUT->footer();

