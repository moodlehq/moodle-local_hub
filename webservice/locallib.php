<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once($CFG->dirroot."/webservice/xmlrpc/locallib.php");
require_once($CFG->dirroot."/local/hub/lib.php");

/**
 * Hub XML-RPC web server.
 *
 * @package   localhub
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class hub_webservice_xmlrpc_server extends webservice_xmlrpc_server {

    /**
     * Authenticate user from the received hub token
     * If no token, so we use the "public" hub community token from the system (create one if don't exist)
     */
    protected function authenticate_user() {
        global $DB;

        //retrieve hub privacy
        $privacy = get_config('local_hub', 'privacy');

        // hub server public access (search, rate, comment, import)
        if ($this->token == 'publichub' and $privacy != HUBPRIVATE) {

            $hub = new local_hub();
            $publiccommunication = $hub->get_communication(WSSERVER, PUBLICSITE);

            if (empty($publiccommunication)) {
                $capabilities = array('local/hub:view');
                $token = $hub->create_hub_token('Public Hub User',
                        'Public site', 'public_hub_user', $capabilities);
                $publiccommunication = new stdClass();
                $publiccommunication->token = $token->token;
                $publiccommunication->type = WSSERVER;
                $publiccommunication->remotename = '';
                $publiccommunication->remoteentity = PUBLICSITE;
                $publiccommunication->confirmed = 1;
                $publiccommunication->id = $hub->add_communication($publiccommunication);
            }

            $this->token = $publiccommunication->token;
        }

        parent::authenticate_user();

    }
}
