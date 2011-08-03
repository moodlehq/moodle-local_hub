<?php

require('../../../../config.php');

$PAGE->set_url(new moodle_url('/logo/index.php'));
$PAGE->set_context(get_system_context());

// print_header("Moodle.org: Images", "moodle", build_navigation($navlinks), "", "", true, false);
$PAGE->set_title('Moodle.org: Moodle logos');
$PAGE->set_heading('Moodle logos');

// $navlinks[] = array('name' => 'Downloads', 'link' => "/downloads/", 'type' => 'misc');
$PAGE->navbar->add('Downloads', new moodle_url('/downloads/'));
// $navlinks[] = array('name' => 'Moodle Logos', 'link' => "/logo/", 'type' => 'misc');
$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

// print_simple_box_start("center", "501", "#FFFFFF", 20);
echo html_writer::start_tag('div', array('class'=>'boxaligncenter', 'style'=>'background-color:#FFF;padding:20px;'));
echo '<p>The name Moodle™ is a registered trademark of the Moodle Trust.</p>';
echo '<p>If you are intending to use the name and/or the logo to advertise generic Moodle services (eg Moodle Hosting, Moodle Support, Moodle Certification, Moodle Training, Moodle Consulting, Moodle Customisation, Moodle Courseware Development, Moodle Theme design, Moodle Integrations, Moodle Installations, etc) or as the name of a software package, then you must seek and receive direct permission in writing from the Moodle Trust via the <a href="http://moodle.com/helpdesk/">moodle.com helpdesk</a>, in accordance with normal trademark restrictions.</p>';
echo '<p>There are no restrictions on how you use the name in other contexts (for example, if you use Moodle just to provide courses then you can use the name freely to refer to it.) If you aren\'t sure of a particular case, please ask us via the <a href="http://moodle.com/helpdesk/">moodle.com helpdesk</a>: we\'ll be happy to either provide you with official permission in writing or help you fix your wording.</p>';
echo '<p>(Source: <a href="http://docs.moodle.org/en/License">license for Moodle</a>)</p>';
// print_simple_box_end();
echo html_writer::end_tag('div');

// print_simple_box_start("center", "501", "#FFFFFF", 20);
echo html_writer::start_tag('div', array('class'=>'boxaligncenter', 'style'=>'background-color:#FFF;padding:20px;'));
// print_heading('Moodle logo');
echo $OUTPUT->heading('Moodle logo');
echo '<p class="centerpara smalltext"><img src="logo-240x60.gif" alt="Moodle logo" /><br />
<a href="logo-240x60.gif" title="logo-240x60.gif 6.3KB">[240 x 60]</a>&nbsp; &nbsp; &nbsp;
<a href="logo-1024x254.jpg" title="logo-1024x254.jpg 59.4KB">[1024 x 254]</a>&nbsp; &nbsp; &nbsp;
<a href="logo-4045x1000.jpg" title="logo-4045x1000.jpg 285.3KB">[4045 x 1000]</a>
</p>';
// print_heading('Moodle `M` logo');
echo $OUTPUT->heading('Moodle `M` logo');
echo '<p class="centerpara smalltext"><img src="mlogo-126x100.gif" alt="Moodle M logo" /><br />
<a href="mlogo-126x100.gif" title="mlogo-126x100.gif 5.0KB">[126 x 100]</a>
</p>';
// print_heading('Desktop wallpaper');
echo $OUTPUT->heading('Desktop wallpaper');
echo '<p class="centerpara smalltext"><img src="wallpaper-thumbnail.jpg" alt="Moodle desktop wallpaper" /><br />
<a href="wallpaper-1024x768.jpg" title="wallpaper-1024x768.jpg 42.6KB">[1024 x 768]</a>&nbsp; &nbsp; &nbsp;
<a href="wallpaper-1280x960.jpg" title="wallpaper-1280x960.jpg 60.8KB">[1280 x 960]</a>&nbsp; &nbsp; &nbsp;
<a href="wallpaper-1600x1200.jpg" title="wallpaper-1600x1200.jpg 69.7KB">[1600 x 1200]</a>
</p>';
// print_simple_box_end();
echo html_writer::end_tag('div');

// print_footer();
echo $OUTPUT->footer();
