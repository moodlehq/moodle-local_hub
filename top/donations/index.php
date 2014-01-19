<?php

require('../../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/donations/'));
$PAGE->set_title('Moodle.org: donations');
$PAGE->set_heading(get_string('donationstitle', 'local_moodleorg'));
$PAGE->navbar->add($PAGE->heading, $PAGE->url);

echo $OUTPUT->header();
echo html_writer::start_tag('div', array('class'=>'boxaligncenter boxwidthwide', 'style'=>'padding:20px;'));

echo html_writer::tag('h2', 'Donating to Moodle', array('class'=>'mdl-align'));
echo html_writer::tag('p', get_string('donations', 'local_moodleorg'));
echo html_writer::tag('p', get_string('donationstrust', 'local_moodleorg'));

echo html_writer::start_tag('ol');
echo html_writer::tag('li', get_string('donationsservices', 'local_moodleorg'));
echo html_writer::tag('li', get_string('donationsmoney', 'local_moodleorg'));
echo html_writer::end_tag('ol');

echo '<table style="margin-left:auto; margin-right:auto; text-align: center;"><tr>';
echo '<td style="font-size:xx-large;text-decoration:blink">&raquo;&raquo;<br /></td>';
echo '<td><form style="display:inline;" method="post" action="https://www.paypal.com/cgi-bin/webscr">';
echo '<p><br /><input type="hidden" value="_xclick" name="cmd" />';
echo '<input type="hidden" value="donations@moodle.org" name="business" />';
echo '<input type="hidden" value="DONATION towards Moodle Development" name="item_name" />';
echo '<input type="hidden" value="http://moodle.org/donations/thankyou.php" name="return" />';
echo '<input type="hidden" value="http://moodle.org/" name="cancel_return" />';
echo '<input type="hidden" name="cbt" value="Click here to add your name to the Donations Page" />';
echo '<input type="hidden" name="page_style" value="donations" />';
echo '<input type="hidden" name="rm" value="2" />';
echo '<input type="hidden" value="Moodle" name="item_number" />';
echo '<input type="hidden" value="Optional Information or Notes" name="cn" />';
echo '<input type="submit" value="Click here to make a donation to Moodle!" /></p>';
echo '</form></td>';
echo '<td style="font-size:xx-large;text-decoration:blink">&laquo;&laquo;<br /></td>';
echo '</tr></table>';
echo '<p style="text-align: center;">Thanks!</p>';

echo html_writer::end_tag('div');

echo "<br />";

$fromdate = time() - 31536000;

echo html_writer::start_tag('div', array('class'=>'boxaligncenter boxwidthwide', 'style'=>'padding:20px;'));

$bigdonations = $DB->get_records_select("register_donations", "timedonated > ? AND ".$DB->sql_cast_char2real('amount')." >= 1000", array($fromdate), "timedonated DESC");

foreach ($bigdonations as $key => $donation) {
    $donations[] = $donation;
}

$otherdonations = $DB->get_records_select("register_donations", "timedonated > ? AND ".$DB->sql_cast_char2real('amount')." >= 500 AND ".$DB->sql_cast_char2real('amount')." < 1000", array($fromdate), "timedonated DESC");

foreach ($otherdonations as $key => $donation) {
    $donations[] = $donation;
}

$otherdonations = $DB->get_records_select("register_donations", "timedonated > ? AND ".$DB->sql_cast_char2real('amount')." >= 200 AND ".$DB->sql_cast_char2real('amount')." < 500", array($fromdate), "timedonated DESC");

foreach ($otherdonations as $key => $donation) {
    $donations[] = $donation;
}

$otherdonations = $DB->get_records_select("register_donations", "timedonated > ? AND ".$DB->sql_cast_char2real('amount')." < 200", array($fromdate), "timedonated DESC");

foreach ($otherdonations as $key => $donation) {
    $donations[] = $donation;
}

echo "<table style=\"margin-left:auto; margin-right:auto; text-align: center;\" cellpadding=\"3\">";
echo "<tr><td style=\"font-size:large\" colspan=\"3\"><hr /><b>Previous donations over $1000</b></td></tr>";
foreach ($donations as $donation) {
    $string = '';
    // Make proper xhtml
    $donation->name = trim(htmlspecialchars($donation->name, ENT_COMPAT, 'UTF-8'));
    $donation->org = trim(htmlspecialchars($donation->org, ENT_COMPAT, 'UTF-8'));
    $donation->url = trim($donation->url);
    if ($donation->name) {
        $string = $donation->name;
    }

    $donation->url = '';   // 4 September 2008  -  New policy from MD: no links at all

    if ($donation->org and $donation->url) {
        if ($string) { $string .= ", "; }
        $string .= "<a rel=\"nofollow\" href=\"$donation->url\">$donation->org</a>";
    } else if ($donation->org) {
        if ($string) { $string .= ", "; }
        $string .= "$donation->org";
    } else if ($donation->url) {
        if (!$string) { $string = $donation->url;}
        $string = "<a rel=\"nofollow\" href=\"$donation->url\">$string</a>";
    }
    if ($donation->amount >= 1000) {
        $star = "**";
        $amount = round($donation->amount);
        $string = "<b>$string</b> (\$$amount)";
        $section = 1000;
    } else if ($donation->amount >= 500) {
        if ($section > 500) {
            $section = 500;
            echo "<tr><td style=\"font-size:medium\" colspan=\"3\"><hr /><b>Other donations over $500</b></td></tr>";
        }
        $star = "**";
    } else if ($donation->amount >= 200) {
        if ($section > 200) {
            $section = 200;
            echo "<tr><td style=\"font-size:medium\" colspan=\"3\"><hr /><b>Other donations over $200</b></td></tr>";
        }
        $star = "*";
    } else {
        if ($section > 10) {
            $section = 10;
            echo "<tr><td style=\"font-size:medium\" colspan=\"3\"><hr /><b>Other donations of $10 or over</b></td></tr>";
        }
        $star = "";
    }
    $time = userdate($donation->timedonated, '%d %B %Y');
    echo "<tr style=\"font-size:small;white-space: nowrap;\" valign=\"top\"><td style=\"width:5pt\">$star</td><td align=\"left\">$string</td>".
         "<td align=\"right\">$time</td></tr>";
}
echo "</table>";

echo html_writer::end_tag('div');

echo $OUTPUT->footer();