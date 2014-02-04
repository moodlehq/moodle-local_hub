<?php

/**
 * Provides the content of the moodle.org/logo/ page
 */

require(__DIR__.'/../../../../config.php');

$PAGE->set_url(new moodle_url('/logo/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('logotitle', 'local_moodleorg'));
$PAGE->set_heading(get_string('logotitle', 'local_moodleorg'));

$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::div(format_text(get_string('logoinfo', 'local_moodleorg'), FORMAT_MARKDOWN), 'logoinfo');
?>
<hr />
<div class="row-fluid">
    <div class="span6">
        <div class="logo moodle-logo">
            <img src="preview-moodle-logo.png" width="260" height="66" alt="Moodle logo" />
            <div>
                <a href="moodle-logo.png">PNG (4010 x 1023 px)</a> | <a href="moodle-logo.svg">SVG</a>
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="logo moodle-logo-white">
            <img src="preview-moodle-logo-white.png" width="260" height="66" alt="Moodle logo - white" />
            <div>
                <a href="moodle-logo-white.png">PNG (4010 x 1023 px)</a>
            </div>
        </div>
    </div>
</div>
<hr />
<?php
echo $OUTPUT->footer();
