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
 * Hub install
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//This is temporary till MDL-25115 is implemented
function xmldb_local_hub_install() {
    global $DB;
    $messageprovider = new stdClass();
    $messageprovider->name = 'coursehubmessage';
    $messageprovider->component = 'local/hub';
    $DB->insert_record('message_providers', $messageprovider);

    //create a new scale called featured, at this moment moment we have no alternative than adding it to the DB
    //core TODO: better way to create a scale or adding a scale MDL-21631, MDL-16474
    $scale = new stdClass();
    $scale->courseid = 0;
    $admin = get_admin();
    $scale->userid = empty($admin)?2:$admin->id; //if the script is run before an admin has ever been created, assign userid = 2 (usual admin default)
    $scale->name = 'coursefeatured';
    $scale->scale = get_string('featured', 'local_hub');
    $scale->description = get_string('featureddesc', 'local_hub');
    $scale->descriptionformat = 1;
    $scale->timemodified = time();
    $scale->id = $DB->insert_record('scale', $scale);
    //save the scale id into the config table
    set_config('courseratingscaleid', $scale->id, 'local_hub');
}