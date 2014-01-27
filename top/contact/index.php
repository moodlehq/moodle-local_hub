<?php

/**
 * Provides the content of the moodle.org/contact/ page
 */

require(__DIR__.'/../../../../config.php');

$PAGE->set_url(new moodle_url('/contact/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Contact');
$PAGE->set_heading($PAGE->title);

$PAGE->navbar->add($PAGE->heading);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);
?>
<h3>Support</h3>
<p>For support with using Moodle software, please try the following options:</p>
<ul>
<li><a href="http://docs.moodle.org/">Moodle documentation</a></li>
<li><a href="/course/">Community discussions</a> - in many languages</li>
<li><a href="/books/">Moodle books</a></li>
<li><a href="http://moodle.com/partners/">Moodle partners</a></li>
</ul>

<h3>Moodle.org</h3>
<p>To report spam in forum posts or comments, please use the 'Report as spam' link.</p>
<p>To report spam sent via Moodle messaging, or for any Moodle.org account queries, such as problems resetting the password, email <a href="mailto:support@moodle.org">support@moodle.org</a>.</p>

<h3>Moodle.com</h3>
<p>For any queries relating to licensing, trademark or Moodle partner applications, as well as major Moodle developments, please <a href="http://moodle.com/contact/">contact Moodle.com</a>.

<h3>Bug reporting</h3>
<p>Problems (including security issues) or new ideas for Moodle can be reported by creating a new issue in the <a href="https://tracker.moodle.org/">Moodle Tracker</a>.</p>

<?php echo $OUTPUT->footer();
