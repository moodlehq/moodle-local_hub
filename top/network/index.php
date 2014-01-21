<?php

/**
 * Displays the list of mnet'ed sites
 */

require(__DIR__.'./../../../../config.php');

require_login();

$PAGE->set_url(new moodle_url('/network/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('networktitle', 'local_moodleorg'));
$PAGE->set_heading(get_string('networktitle', 'local_moodleorg'));

$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

if (!is_enabled_auth('mnet')) {
    // no need to query anything remote related
    echo 'No sites available.';
    echo $OUTPUT->footer();
    die();
}

$sql = "SELECT DISTINCT h.id, h.name, h.wwwroot, a.name application, a.display_name
          FROM {mnet_host} h
     LEFT JOIN {mnet_application} a ON a.id = h.applicationid
     LEFT JOIN {mnet_host2service} hs ON hs.hostid = h.id
     LEFT JOIN {mnet_service} s ON s.id = hs.serviceid
         WHERE h.id != ? AND h.deleted = ? AND s.name = 'sso_idp' AND hs.publish = ?
      ORDER BY a.display_name, h.name";

$params = array($CFG->mnet_localhost_id, 0, 1);
$hosts = $DB->get_records_sql($sql, $params);
$items = array();

foreach ($hosts as $host) {
    $icon = html_writer::empty_tag('img', array(
        'src' => $OUTPUT->pix_url('i/'.$host->application.'_host', 'moodle'),
        'alt' => get_string('server', 'block_mnet_hosts'),
    ));

    if ($host->id == $USER->mnethostid) {
        $link = html_writer::link(new moodle_url($host->wwwroot), s($host->name));
    } else {
        $link = html_writer::link(new moodle_url('/auth/mnet/jump.php', array('hostid' => $host->id)), s($host->name));
    }

    $items[] = $icon.' '.html_writer::span($link, 'hostname').' '.html_writer::span(s($host->wwwroot), 'hosturl');
}

if (empty($items)) {
    echo 'No sites found.';

} else {
    print_string('networkinfo', 'local_moodleorg');
    echo html_writer::alist($items, array('class' => 'mnethosts'));
}

echo $OUTPUT->footer();
