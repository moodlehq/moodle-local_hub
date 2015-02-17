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
 * Unit tests for hub
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

    public function test_check_secret_validity() {
        global $DB;

        $this->resetAfterTest(true);
        $hub = new local_hub();

        $token = 'this is my token';
        $md5 = md5($token);
        $this->assertTrue($hub->check_secret_validity($md5));

        // Mark it stolen.
        $stolensecret = new stdClass();
        $stolensecret->secret = $md5;
        $stolensecret->siteurl = 'http://example.com';
        $stolensecret->blockeddate = time();
        $DB->insert_record('hub_stolen_site_secrets', $stolensecret);

        $this->assertFalse($hub->check_secret_validity($md5));
    }

    public function test_create_hub_token() {
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $url = 'http://example.com';

        // These capabilities and the arguments to create_hub_token() are modeled
        // on the call in register_site() in lib.php.

        $capabilities = array('local/hub:updateinfo', 'local/hub:registercourse',
                'local/hub:view', 'local/hub:unregistercourse', 'local/hub:viewsmallinfo');

        $tokenusedbysite = $hub->create_hub_token('Registered Hub User',
                                                   'Registered site',
                                                   $url . '_registered_site_user',
                                                   $capabilities);

        // If it already exists, the existing token should be found.
        $foundtoken = $hub->create_hub_token('Registered Hub User',
                                             'Registered site',
                                             $url . '_registered_site_user',
                                             $capabilities);
        $this->assertEquals($foundtoken->id, $tokenusedbysite->id);
    }

    public function test_register_site() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $originalurl = 'http://example.com';
        $changedurl = 'http://example2.com';

        $sitevalues = $this->get_sitevalues($originalurl);

        // Missing language code.
        $sitevalues['language'] = '';
        try {
            $token = $hub->register_site($sitevalues);
            $this->fail('Exception expected due to invalid language code.');
        } catch (moodle_exception $e) {
            $this->assertEquals('errorlangnotrecognized', $e->errorcode);
        }

        // Should succeed.
        $sitevalues['language'] = 'en';
        $token = $hub->register_site($sitevalues);
        $siterecord = $DB->get_record('hub_site_directory', array('name' => $sitevalues['name']), '*', MUST_EXIST);
        $this->assertEquals($siterecord->url, $originalurl);

        $this->check_tokens($hub, $sitevalues['url']);

        // Trying to register the same site a second time should fail.
        try {
            $hub->register_site($sitevalues);
            $this->fail('Exception expected due to duplicate secret/token.');
        } catch (moodle_exception $e) {
            $this->assertEquals('sitesecretalreadyexist', $e->errorcode);
        }

        // Re-registering with a different URL.
        $sitevalues['url'] = $changedurl;
        $tokenupdated = $hub->register_site($sitevalues, $originalurl);
        $this->assertEquals($token, $tokenupdated); // Token should remain unchanged.
        $siterecord = $DB->get_record('hub_site_directory', array('name' => $sitevalues['name']), '*', MUST_EXIST);
        $this->assertEquals($siterecord->url, $changedurl);
    }

    public function test_unregister_site() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $url = "http://example.com";
        $sitevalues = $this->get_sitevalues($url);

        // Not sure why this behaviour is important but unregister_site() specificaly supports it.
        $hub->unregister_site(null);

        $token = $hub->register_site($sitevalues);

        $this->check_tokens($hub, $sitevalues['url']);

        $site = $hub->get_site_by_url($url);
        $hub->unregister_site($site);
        $this->assertEquals($site->deleted, 1);
        // Note that lib.php unregister_site() does not do anything but mark the site as deleted.
        // Deleting tokens is done by externallib.php local_hub_external::unregister_site() and lib.php delete_site().
        // Should externallib.php unregister_site() be calling lib.php delete_site() instead of lib.php unregister_site()?
    }

    public function test_get_site_by_url() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $url = "http://example.com";
        $sitevalues = $this->get_sitevalues($url);

        $token = $hub->register_site($sitevalues);

        $this->check_tokens($hub, $sitevalues['url']);

        // Try (and fail) to find a deleted site.
        $site = $hub->get_site_by_url($url, 1);
        $this->assertTrue($site == null);

        // Find a not deleted site.
        $site = $hub->get_site_by_url($url, 0);
        $this->assertTrue($site != null);
        // Should work the same with the default second parameter.
        $site = $hub->get_site_by_url($url);
        $this->assertTrue($site != null);
        // Find the not deleted site regardless of deletion status.
        $site = $hub->get_site_by_url($url, null);
        $this->assertTrue($site != null);

        // Delete the site.
        $hub->unregister_site($site);

        // Finding the deleted site should not happen without providing 1 as the second param.
        $site = $hub->get_site_by_url($url, 0);
        $this->assertTrue($site == null);
        $site = $hub->get_site_by_url($url);
        $this->assertTrue($site == null);

        // Explicitly request a deleted site.
        $site = $hub->get_site_by_url($url, 1);
        $this->assertTrue($site != null);

        // Find the deleted site regardless of deletion status.
        $site = $hub->get_site_by_url($url, null);
        $this->assertTrue($site != null);
    }

    public function test_get_sites() {
        global $DB;
        $this->resetAfterTest(true);
        set_config('noemailever', 1);

        $hub = new local_hub();
        $url = "http://example.com";
        $sitevalues = $this->get_sitevalues($url);

        $token = $hub->register_site($sitevalues);
        $this->check_tokens($hub, $sitevalues['url']);

        $sites = $hub->get_sites(array('urls' => array($url, 'http://doesntexist.com')));
        $this->assertTrue(count($sites) == 1);

        $sites = $hub->get_sites(array('contactemail' => $sitevalues['contactemail']));
        $this->assertTrue(count($sites) == 1);

        $sites = $hub->get_sites(array('contactemail' => 'doesntexist' . $sitevalues['contactemail']));
        $this->assertTrue(count($sites) == 0);
    }

    public function test_add_site() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $url = "http://example.com";
        $sitevalues = $this->get_sitevalues($url);

        $token = $hub->register_site($sitevalues);

        $this->check_tokens($hub, $sitevalues['url']);

        // Delete the site then re-add and check the deleted site is found.
        $site = $hub->get_site_by_url($url);
        $hub->delete_site($site->id);

        $oldid = $site->id;
        $site = $hub->add_site($site);
        $this->assertEquals($site->id, $oldid);

        // Attempt to re-add a not deleted site and check that the old one is found.
        $site = $hub->add_site($site);
        $this->assertEquals($site->id, $oldid);
    }

    public function test_delete_site() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();
        $url = "http://example.com";
        $sitevalues = $this->get_sitevalues($url);

        $token = $hub->register_site($sitevalues);

        $this->check_tokens($hub, $sitevalues['url']);

        $site = $hub->get_site_by_url($url);
        $hub->delete_site($site->id);

        $site = $hub->get_site($site->id); // Should we be able to retrieve deleted sites like this?
        $this->assertEquals($site->deleted, 1);

        // Verify the tokens are either deleted or marked as deleted.
        $username = $url . '_registered_site_user';
        $user = $DB->get_record('user', array('username' => $username, 'idnumber' => $username));
        $service = $DB->get_record('external_services', array('name' => 'Registered site'));

        $this->assertEquals($this->get_external_token($user, $service), null);
        $this->assertEquals($hub->get_communication(WSSERVER, REGISTEREDSITE, $sitevalues['url']), null);
    }

    public function test_add_communication() {
        global $DB;
        $this->resetAfterTest(true);

        $hub = new local_hub();

        $sitetohubcommunication = new stdClass();
        $sitetohubcommunication->token = '123456';
        $sitetohubcommunication->type = WSSERVER;
        $sitetohubcommunication->remoteentity = REGISTEREDSITE;
        $sitetohubcommunication->remotename = 'test site';
        $sitetohubcommunication->remoteurl = 'http://example.com';
        $sitetohubcommunication->confirmed = 1;
        $sitetohubcommunication->id = $hub->add_communication($sitetohubcommunication);
        $this->assertTrue($sitetohubcommunication->id > 0);

        // Deleting a communication just toggles a deleted flage.
        // Adding another communication with the same details
        // should result in the ID of the new and old communication matching.
        $originalID = $sitetohubcommunication->id;
        $hub->delete_communication($sitetohubcommunication);
        $newID = $hub->add_communication($sitetohubcommunication);
        $this->assertTrue($originalID == $newID);
    }

    private function get_sitevalues($url) {
        $sitevalues = array(
        'name' => 'test site',
        'url' => $url,
        'token' => '',
        'secret' => '',
        'description' => 'this is a test site',
        'contactname' => 'Fictional Fred',
        'contactemail' => 'fred@example.com',
        'contactphone' => '',
        'imageurl' => '',
        'privacy' => '',
        'language' => 'en',
        'users' => '',
        'courses' => '',
        'street' => '',
        'regioncode' => '',
        'countrycode' => '',
        'geolocation' => '',
        'contactable' => '',
        'emailalert' => '',
        'enrolments' => '',
        'posts' => '',
        'questions' => '',
        'resources' => '',
        'participantnumberaverage' => '',
        'modulenumberaverage' => '',
        'moodleversion' => '',
        'moodlerelease' => '',
        'password' => '',
        'badges' => '',
        'issuedbadges' => '');

        return $sitevalues;
    }

    private function check_tokens($hub, $url) {
        global $DB;

        // Check a token was created and inserted into external_tokens and hub_communications.
        $username = $url . '_registered_site_user';
        $user = $DB->get_record('user', array('username' => $username, 'idnumber' => $username));
        $service = $DB->get_record('external_services', array('name' => 'Registered site'));

        $externaltoken = $this->get_external_token($user, $service);
        $communication = $hub->get_communication(WSSERVER, REGISTEREDSITE, $url);
        $this->assertTrue($externaltoken->token == $communication->token);
    }

    private function get_external_token($user, $service) {
        global $DB;

        $resulttoken = new stdClass();
        $resulttoken->userid = $user->id;
        $resulttoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
        $resulttoken->externalserviceid = $service->id;
        $resulttoken->contextid = context_system::instance()->id;
        $resulttoken->creatorid = $user->id;
        return $DB->get_record('external_tokens', (array) $resulttoken);
    }
}
