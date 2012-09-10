<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

class local_moodleorg_useful_mapping_table extends table_sql {
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $fields = 'c.id, c.shortname, c.fullname AS coursename, m.id AS mappingid, m.lang, s.name AS scalename, s.scale';
        $from = '{course} c LEFT JOIN {moodleorg_useful_coursemap} m ON c.id = m.courseid LEFT JOIN {scale} s ON m.scaleid = s.id';
        $where = 'c.visible = 1 AND c.id != :siteid';
        $params = array('siteid' => SITEID);
        $this->define_headers(array('ID', 'Shortname', 'Course Name', 'Language', 'Scale', 'Edit'));
        $this->define_columns(array('id', 'shortname', 'coursename', 'lang', 'scale', 'edit'));
        $this->no_sorting('edit');
        $this->set_sql($fields, $from, $where, $params);
        $this->collapsible(false);
    }

    public function col_edit($row) {
        if (empty($row->mappingid)) {
            $str = 'Create';
        } else {
            $str = 'Edit';
        }
        return html_writer::link(new moodle_url('/local/moodleorg/admin/edit_coursemapping.php', array('courseid'=>$row->id)), $str);
    }

    public function col_scale($row) {
        if (!empty($row->scalename)) {
            return $row->scalename.' <br >('.$row->scale.')';
        }else {
            return '-';
        }
    }

    public function col_lang($row) {
        if (!empty($row->lang)) {
            return $row->lang;
        } else {
            return '-';
        }
    }
}

require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup('local_moodleorg_coursemapping');

echo $OUTPUT->header('Map courses');
$table = new local_moodleorg_useful_mapping_table('sfdssf1');
$table->define_baseurl($PAGE->url);
$table->out(100, false);
echo $OUTPUT->footer();
