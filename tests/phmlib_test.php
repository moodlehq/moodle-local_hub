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
 * PHM tests
 *
 * @package    local_moodleorg
 * @category   phpunit
 * @copyright  2013 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/moodleorg/locallib.php');

class local_moodleorg_phmlib_testcase extends advanced_testcase {
    public function test_phm_cohort_manager() {
        global $DB;

        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $cohortmanager1 = new local_moodleorg_phm_cohort_manager();

        // Should be no existing users in cohort as its been newly created.
        $this->assertEmpty($cohortmanager1->old_users());
        $this->assertEmpty($cohortmanager1->current_users());

        $cohortmanager1->add_member($user1->id);
        $cohortmanager1->add_member($user2->id);
        $cohortmanager1->add_member($user3->id);

        // Check the users are in the internal structures:
        $this->assertArrayHasKey($user1->id, $cohortmanager1->current_users());
        $this->assertArrayHasKey($user2->id, $cohortmanager1->current_users());
        $this->assertArrayHasKey($user3->id, $cohortmanager1->current_users());
        // Check the users are in the DB:
        $dbmembers = $DB->get_records('cohort_members', array('cohortid' => $cohortmanager1->cohort()->id), 'userid', 'userid');
        $this->assertArrayHasKey($user1->id, $dbmembers);
        $this->assertArrayHasKey($user2->id, $dbmembers);
        $this->assertArrayHasKey($user3->id, $dbmembers);

        $cohortmanager2 = new local_moodleorg_phm_cohort_manager();

        // Check both are using the same cohort..
        $this->assertEquals($cohortmanager1->cohort()->id, $cohortmanager2->cohort()->id);
        unset($cohortmanager1);

        // Check old users is correct:
        $oldusers = $cohortmanager2->old_users();
        $this->assertEquals(count($oldusers), 3);
        $this->assertArrayHasKey($user1->id, $oldusers);
        $this->assertArrayHasKey($user2->id, $oldusers);
        $this->assertArrayHasKey($user3->id, $oldusers);

        // Add users 3 and 4 to the cohort..
        $cohortmanager2->add_member($user3->id);
        $cohortmanager2->add_member($user4->id);

        $removedusers = $cohortmanager2->remove_old_users();

        // users 1 and 2 should be removed..
        $this->assertArrayNotHasKey($user1->id, $cohortmanager2->current_users());
        $this->assertArrayNotHasKey($user2->id, $cohortmanager2->current_users());

        // users 3 and 4 should be in place..
        $this->assertArrayHasKey($user3->id, $cohortmanager2->current_users());
        $this->assertArrayHasKey($user4->id, $cohortmanager2->current_users());

        // Check the users are in the DB:
        $dbmembers = $DB->get_records('cohort_members', array('cohortid' => $cohortmanager2->cohort()->id), 'userid', 'userid');
        // users 1 and 2 should be removed..
        $this->assertArrayNotHasKey($user1->id, $dbmembers);
        $this->assertArrayNotHasKey($user2->id, $dbmembers);

        // users 3 and 4 should be in place..
        $this->assertArrayHasKey($user3->id, $dbmembers);
        $this->assertArrayHasKey($user4->id, $dbmembers);
    }
}
