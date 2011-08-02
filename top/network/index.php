<?php

require('../../../../config.php');
require_once('../toplib.php');

require_login();

// only for logged in users!
///if (!isloggedin() || isguest()) {
if (isguestuser()) {
    return false;
}

if (!is_enabled_auth('mnet')) {
    // no need to query anything remote related
    debugging( 'mnet authentication plugin is not enabled', DEBUG_ALL );
    return '';
}

// check for outgoing roaming permission first
if (!has_capability('moodle/site:mnetlogintoremote', get_context_instance(CONTEXT_SYSTEM), NULL, false)) {
    return '';
}

$sql = 'SELECT
            DISTINCT h.id,
            h.name,
            h.wwwroot,
            a.name application,
            a.display_name
        FROM {mnet_host} h
        LEFT JOIN {mnet_application} a ON a.id = h.applicationid
        LEFT JOIN {mnet_host2service} hs ON hs.hostid = h.id
        LEFT JOIN {mnet_service} s ON s.id = hs.serviceid
        WHERE h.id != ? AND h.deleted = 0 AND s.name = \'sso_idp\' AND hs.publish = \'1\'
        ORDER BY a.display_name, h.name';

$params = array($CFG->mnet_localhost_id);

$hosts = $DB->get_records_sql($sql, $params);

$content = '<div class="moodletop intro">'.get_string('networkintro', 'moodle.org').'</div>';

$content .= '<ul class="moodletop networkhosts">';
if ($hosts) {

    $PAGE->set_context(get_system_context());
    $PAGE->set_pagelayout('standard');

    foreach ($hosts as $host) {

        $icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/'.$host->application.'_host', 'moodle'), 'alt'=>get_string('server', 'block_mnet_hosts')));

        if ($host->id == $USER->mnethostid) {
            $items="<a title=\"" .s($host->name).
                "\" href=\"{$host->wwwroot}\">". s($host->name) ."</a>";
        } else {
            $items="<a title=\"" .s($host->name).
                "\" href=\"{$CFG->wwwroot}/auth/mnet/jump.php?hostid={$host->id}\">" . s($host->name) ."</a>";
        }

        $content .= '<li>'.$icon . $items.'</li>';
    }
}
$content .= '</ul>';

print_moodle_page('network', $content);

