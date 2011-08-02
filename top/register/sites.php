<?php

require('../../../../config.php');
require_once($CFG->dirroot."/lib/countries.php");

if (!$sites = $DB->get_records("registry", array('confirmed'=>'1'))) {
    error("No sites found!");
}

$hide_all_links = true;

$PAGE->set_context(get_system_context());
$PAGE->set_url(new moodle_url('/register/sites.php'));
$PAGE->set_title("moodle.com: Moodle sites");
$PAGE->set_heading("Moodle sites");
$PAGE->navbar->add('Moodle sites');

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::start_tag('div', array('class'=>'boxaligncenter'));

echo "<p align=center>Some of the growing community of Moodle users are listed below.</p>";
echo "<p align=center>To add your site to this list, just use the \"Registration\" button on<br />";
echo "the administration page of Moodle 1.0.9 or later.</p>";
//    echo "<a href='ma&#105&#108t&#111:ma%72%74in@%6d%6fo%64%6c%65.o%72g' title='mar&#116&#105n@&#109oo&#100&#108&#101&#46&#111&#114g'>m&#97&#114&#116&#105&#110&#64m&#111&#111&#100&#108&#101&#46&#111&#114g</a>  &nbsp;<img height=15 src=\"http://moodle.com/pix/s/smiley.gif\" width=15 align=absMiddle border=0></p>";

echo "<p align=center>In total, there are ".$DB->count_records("registry")." registered sites.</p>";
echo "<p align=center>(Links have been temporarily switched off)</p>";


$countries = get_string_manager()->get_list_of_countries();

/// Sort the sites

asort($countries);

foreach ($countries as $code => $fullname) {
    $list[$code]->name = $fullname;
    $list[$code]->count = 0;
}
foreach ($sites as $key => $site) {
    $list[$site->country]->count++;
    if ($site->public > 0) {
        $list[$site->country]->sites[$site->sitename] = $site;
    }
}

foreach ($list as $country) {
    if ($country->count) {
        echo "<p><b>$country->name &nbsp;&nbsp;&nbsp;<font color=\"#dddddd\">- $country->count sites</font></b></p>\n";
        if (!empty($country->sites)) {
            echo "<ul>\n";
            uksort($country->sites, 'strnatcasecmp');
            foreach ($country->sites as $site) {
                if (empty($site->lang)) {
                    echo "<li>";
                } else {
                    echo "<li lang=\"$site->lang\">";
                }
                if ($site->public == 1 or ! $site->url or $hide_all_links) {
                    echo "$site->sitename";
                } else if ($site->public == 2) {
                    echo "<a href=\"$site->url\">$site->sitename</a>";
                }
                echo "</li>\n";
            }
            echo "</ul>\n";
        }
    }
}

echo html_writer::end_tag('div');
echo $OUTPUT->footer();