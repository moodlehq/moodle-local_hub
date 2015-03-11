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

/**
 * Library of functions for local_hub
 *
 * @package local_hub
 * @copyright 2015 Andrew Davis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Construct an object to supply to email_to_user()
 *
 * @param string $email the required email address
 * @param string $firstname The desired user first name
 * @return stdClass An object that can be supplied to email_to_user()
 */
function local_hub_create_contact_user($email, $firstname) {
    $contactuser = new stdClass();

    // Need an id, can't use id=0. would rather not use id=1, lets fool the api while we're still trying to use a fake user to send to.
    $contactuser->id = 2; // Used to retrieve the mailcharset user preference which defaults to 0.

    $contactuser->email = $email;
    $contactuser->firstname = $firstname;
    $contactuser->lastname = '';

    foreach (get_all_user_name_fields() as $namefield) {
        if (!isset($contactuser->$namefield)) {
            $contactuser->$namefield = '';
        }
    }

    return $contactuser;
}
