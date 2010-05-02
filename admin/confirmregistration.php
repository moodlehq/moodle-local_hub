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
 * This is the page where the admin is redirected to from the hub directory once
 * the hub directory saved the hub information.
 * This page save the token that the hub directory gave us, in order to call the hub
 * directory later by web service.
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/hub/lib.php');
require_once($CFG->dirroot.'/lib/hublib.php'); //HUBDIRECTORYURL

admin_externalpage_setup('registrationconfirmed');

$newtoken        = optional_param('newtoken', '', PARAM_ALPHANUM);
$url             = optional_param('url', '', PARAM_URL);
$token           = optional_param('token', '', PARAM_ALPHANUM);

$hub = new local_hub();

//check that the token/url couple exist and is not confirmed
$directorytohubcommunication = $hub->get_communication(WSSERVER, HUBDIRECTORY, $url);
if (!empty($directorytohubcommunication) and  $directorytohubcommunication->confirmed == 0
        and $directorytohubcommunication->token == $token) {

    $hub->confirm_communication($directorytohubcommunication);

    $hubtodirectorycommunication = new stdClass();
    $hubtodirectorycommunication->token = $newtoken;
    $hubtodirectorycommunication->type = WSCLIENT;
    $hubtodirectorycommunication->remotename = 'Moodle.org hub directory';
    $hubtodirectorycommunication->remoteentity = HUBDIRECTORY;
    $hubtodirectorycommunication->remoteurl = HUBDIRECTORYURL;
    $hubtodirectorycommunication->confirmed = 1;
    $hubtodirectorycommunication->id = $hub->add_communication($hubtodirectorycommunication);

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('registrationconfirmed', 'local_hub'), 'notifysuccess');
    echo $OUTPUT->footer();
} else {
    throw new moodle_exception('wrongtoken');
}


