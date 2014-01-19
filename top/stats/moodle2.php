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
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2011 Martin Dougiamas
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');

error_reporting(E_ALL);
ini_set('display_errors', true);

$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('aboutstatisticstitle', 'local_moodleorg'));
$PAGE->set_heading('Registered sites: Moodle 2.0.x');
$PAGE->set_url(new moodle_url('/stats/moodle2.php'));
$PAGE->navbar->add($PAGE->heading, $PAGE->url);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading. ' ('.userdate(time()).')');

echo html_writer::start_tag('div', array('class'=>'boxaligncenter', 'style'=>'background-color:#FFF;padding:20px;'));

$table = new html_table();
$table->attributes = array('class'=>'generaltable boxaligncenter');
$table->width = '400px';
$table->align = array('left','right');
$table->data = array(
    array('Users >= 50000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND users >= 50000")),
    array('20000 <= Users < 50000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 20000) AND (users < 50000)")),
    array('10000 <= Users < 20000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 10000) AND (users < 20000)")),
    array('5000 <= Users < 10000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 5000) AND (users < 10000)")),
    array('2000 <= Users < 5000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 2000) AND (users < 5000)")),
    array('1000 <= Users < 2000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 1000) AND (users < 2000)")),
    array('500 <= Users < 1000',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 500) AND (users < 1000)")),
    array('200 <= Users < 500',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 200) AND (users < 500)")),
    array('100 <= Users < 200',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 100) AND (users < 200)")),
    array('50 <= Users < 100',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 50) AND (users < 100)")),
    array('20 <= Users < 50',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users >= 20) AND (users < 50)")),
    array('Users < 20',
          $DB->count_records_select('registry', "(moodlerelease LIKE '%2.0%') AND (users < 20)")),
    array('Total 2.0 sites',
          $DB->count_records_select('registry', "moodlerelease LIKE '%2.0%'")),
);
echo html_writer::table($table);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
