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
 * Add hub administration menu settings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/// Add hub administration pages to the Moodle administration menu
$ADMIN->add('root', new admin_category('local_hub', get_string('hub', 'local_hub')));

$ADMIN->add('local_hub', new admin_externalpage('hubsettings', get_string('settings', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/settings.php",
        'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('managesites', get_string('managesites', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/managesites.php",
        'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('managecourses', get_string('managecourses', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/managecourses.php",
        'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('hubregistration', get_string('registration', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/register.php",
        'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('registrationconfirmed',
        get_string('registrationconfirmed', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/confirmregistration.php",
        'moodle/site:config', true));

$ADMIN->add('local_hub', new admin_externalpage('sitesettings', get_string('sitesettings', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/sitesettings.php",
        'moodle/site:config', true));

$ADMIN->add('local_hub', new admin_externalpage('hubcoursesettings', get_string('coursesettings', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/coursesettings.php",
        'moodle/site:config', true));

$ADMIN->add('local_hub', new admin_externalpage('hubstolensecret', get_string('stolensecret', 'local_hub'),
        $CFG->wwwroot."/local/hub/admin/stolensecret.php",
        'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('checksiteconnectivity', get_string('checksiteconnectivity', 'local_hub'),
    "/local/hub/admin/checksiteconnectivity.php",
    'moodle/site:config'));

$ADMIN->add('local_hub', new admin_externalpage('checkemailsendystatus', get_string('checkemailsendystatus', 'local_hub'),
    "/local/hub/admin/checksendystatus.php",
    'moodle/site:config'));

