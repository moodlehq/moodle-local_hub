<?php

require('../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/top/sites/siteslib.php');

require_login();

if (!ismoodlesiteadmin()) {
    print_error('erroradminonly', 'local_moodleorg');
}

$cool = optional_param('cool', '', PARAM_INT);
$uncool = optional_param('uncool', '', PARAM_INT);
$edit = optional_param('edit', '', PARAM_INT);
$delete = optional_param('delete', '', PARAM_INT);

if (!empty($cool) and confirm_sesskey()) {
    if ($site = $DB->get_record("registry", array("id"=>$cool))) {
        $site->cool = MAXVOTES;
        $site->cooldate = time();
        $DB->update_record("registry", $site);
        add_to_log($SITE->id, "resource", "cool", "view.php?id=380", "COOL: $site->url, $site->sitename", 380, $USER->id);
        redirect("index.php?country=$site->country", "$site->sitename marked as COOL!", 1);
    }
}

if (!empty($uncool) and confirm_sesskey()) {
    if ($site = $DB->get_record("registry", array("id" => $uncool))) {
        $site->cool = 0;
        $site->cooldate = 0;
        $DB->update_record("registry", $site);
        add_to_log($SITE->id, "resource", "uncool", "view.php?id=380", "UNCOOL: $site->url, $site->sitename", 380, $USER->id);
        redirect("index.php?country=$site->country", "$site->sitename suddenly seems NOT SO COOL! ", 1);
    }
}

if (!empty($delete) and confirm_sesskey()) {
    if ($site = $DB->get_record("registry", array("id"=>$delete))) {
        $DB->delete_records("registry", array("id"=>$delete));
    }
    add_to_log($SITE->id, "resource", "delete", "view.php?id=380", "DELETE: $site->url, $site->sitename", 380, $USER->id);
    $SESSION->lang = "en";
    redirect("index.php?country=$site->country", "$site->sitename has been completely DELETED!", 1);
}

if ($site = data_submitted() and confirm_sesskey()) {
    $DB->update_record("registry", $site);
    add_to_log($SITE->id, "resource", "edit", "view.php?id=380", "UPDATE: $site->url, $site->sitename", 380, $USER->id);
    $SESSION->lang = "en";
    redirect("index.php?country=$site->country", "$site->sitename has been UPDATED!", 1);
}

if (empty($edit)) {
    redirect("index.php", "Edit who?", 1);
}

if (!$site = $DB->get_record("registry", array("id"=>$edit))) {
    redirect("index.php", "Edit who?", 1);
}

$SESSION->lang = $site->lang;

/// Print headings

$url = new moodle_url('/sites/edit.php', array());
if (!empty($cool)) {
    $url->param('cool', $cool);
}
if (!empty($uncool)) {
    $url->param('uncool', $uncool);
}
if (!empty($edit)) {
    $url->param('edit', $edit);
}
if (!empty($delete)) {
    $url->param('delete', $delete);
}
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('moodle.org: Moodle sites management');
$PAGE->set_heading('Moodle sites management');

$PAGE->navbar->add('Sites', new moodle_url('/sites/'));
$PAGE->navbar->add('Edit site');

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::start_tag('div', array('class'=>'mdl-align'));
echo html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'name'=>'form'));
echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>s($site->id)));
echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));

$table = new html_table();
$table->cellpadding = 9;
$table->data = array();
$table->attributes = array('class'=>'generaltable', 'style'=>'margin:1em auto;border:1px solid #ddd;');
unset($site->id);
foreach ($site as $name => $value) {
    if (strlen($name) > 2) {
        $table->data[] = array(
            $name,
            html_writer::empty_tag('input', array('type'=>'text', 'name'=>$name, 'value'=>$value, 'style'=>'width:400px'))
        );
    }
}
echo html_writer::table($table);
echo html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('submit')));
echo html_writer::end_tag('div');

echo $OUTPUT->footer();