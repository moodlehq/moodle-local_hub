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
 * Display public site list
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../config.php');
require_once($CFG->dirroot.'/local/hub/lib.php');

if (!get_config('local_hub', 'hubenabled')) {
    throw new moodle_exception('hubnotenabled');
}

$PAGE->set_url('/local/hub/index.php');
if ($CFG->enablehubserver != HUBSERVERONLY ||
            has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
    $PAGE->set_pagelayout('incourse');
}
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('sitelist', 'local_hub'));


$search  = optional_param('search', '', PARAM_TEXT);

$hub = new local_hub();
$sites = $hub->get_sites($search);

$renderer = $PAGE->get_renderer('local_hub');
$contenthtml = $renderer->searchable_site_list($sites, $search);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('sitelist', 'local_hub'), 3, 'main');
echo $contenthtml;
echo $OUTPUT->footer();