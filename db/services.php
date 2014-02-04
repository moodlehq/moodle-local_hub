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
 * Hub external functions and service definitions.
 *
 * @package    localhub
 * @copyright  2010 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

        'hub_get_info' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'get_info',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Get hub information',
                'type'        => 'read',
        ),

        'hub_update_site_info' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'update_site_info',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Update the site information and call confirmation',
                'type'        => 'write',
        ),

        'hub_register_courses' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'register_courses',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Register multiple courses',
                'type'        => 'write',
        ),

        'hub_unregister_courses' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'unregister_courses',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Unregister multiple courses',
                'type'        => 'write',
        ),

        'hub_unregister_site' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'unregister_site',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Unregister a site (the caller)',
                'type'        => 'write',
        ),

        'hub_get_courses' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'get_courses',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Get multiple courses',
                'type'        => 'read',
        ),

        'hub_get_sites' => array(
                'classname'   => 'local_hub_external',
                'methodname'  => 'get_sites',
                'classpath'   => 'local/hub/externallib.php',
                'description' => 'Get multiple sites',
                'type'        => 'read',
                'capabilities' => 'local/hub:viewinfo'
        ),
);

$services = array(
        'Hub directory' => array(
                'functions' => array ('hub_get_info'),
                'enabled'=>1,
        ),

        'Registered site' => array(
                'functions' => array ('hub_update_site_info', 'hub_register_courses', 
                    'hub_get_courses', 'hub_unregister_courses', 'hub_unregister_site', 'hub_get_info'),
                'enabled'=>1,
        ),

        'Public site' => array(
                'functions' => array ('hub_get_courses'),
                'enabled'=>1,
        ),

        'Moodle.org statistics' => array(
                'functions' => array ('hub_get_sites'),
                'enabled'=>1,
        ),
);