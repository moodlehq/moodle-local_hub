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
 * Unit tests for badges
 *
 * @package    local_hub
 * @copyright  2014 Dan Poltawski <dan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class local_hub_lib_testcase extends advanced_testcase
{

    public function test_is_remote_site_valid() {
        $hub = new local_hub();

        // TODO: move to: https://github.com/moodlehq/moodle-exttests
        $invalidsites = array(
            'http://localhost',
            'http://127.0.0.1',
            'https://download.moodle.org/does-not-exist',
        );

        foreach ($invalidsites as $site) {
            $this->assertFalse($hub->is_remote_site_valid($site), "$site reported valid");
        }

        $validsites = array(
            'http://download.moodle.org', // NOTE the http redirect
            'http://learn.moodle.net',  // More tests..
            'https://learn.gold.ac.uk', // Normal site dan worked with
            'http://devmooc.net/', // Problem site MDLSITE-3433
        );

        foreach ($validsites as $site) {
            $this->assertTrue($hub->is_remote_site_valid($site), "$site reported invalid");
        }

    }
}