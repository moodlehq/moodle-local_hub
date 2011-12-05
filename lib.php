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
 * Version details.
 *
 * @package    local
 * @subpackage moodleorg
 * @copyright  Sam Hemelryk 2011
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Edits the moodle.org navigation as we desire.
 *
 * Can be removed once http://tracker.moodle.org/browse/MDL-30583 has been implemented.
 *
 * @param global_navigation $nav
 */
function moodleorg_extends_navigation(global_navigation $nav) {
    // First up: Hide the activities under site pages.
    // Find the site pages node and make sure it's valid
    $node = $nav->get(SITEID, navigation_node::TYPE_COURSE);
    if ($node) {
        // Find all activites within it.
        $activities = $node->find_all_of_type(navigation_node::TYPE_ACTIVITY);
        // Make sure there are some
        if (!empty($activities)) {
            // Iterate over each activity and hide it.
            foreach ($activities as $activity) {
                // We set display to false because the active node will still
                // override this and be printed. Means that the navbar continues
                // to work.
                $activity->display = false;
            }
        }
    }
}

