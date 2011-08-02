<?php

require('../../../../config.php');

require_login();

if (!is_siteadmin() or $USER->id != 1) {
    error("Admin only!");
}

$sendmail = optional_param('sendmail', false, PARAM_BOOL);

$PAGE->set_context(get_system_context());
$PAGE->set_url(new moodle_url('/register/mail.php'));
$PAGE->set_title('moodle.org: Mail out');
$PAGE->set_heading('Mail out');
$PAGE->navbar->add('Sites');
$PAGE->navbar->add('Mail out');

/// Print headings
echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

if (!$sendmail) {
    if ($usehtmleditor = can_use_richtext_editor()) {
        $defaultformat = FORMAT_HTML;
    } else {
        $defaultformat = FORMAT_MOODLE;
    }

?>

<center>
<form name="theform" method="post" action="mail.php" enctype="multipart/form-data">
<table border="0" cellpadding="5">
<tr valign="top">
    <td align=right><p><b><?php echo get_string("subject", "forum"); ?>:</b></p></td>
    <td>
        <input type="text" name="subject" size=60 value="">
    </td>
</tr>
<tr valign="top">
    <td align=right><p><b>
     <?php echo get_string("message", "forum"); ?>:
     </b></p></td>
    <td align=left rowspan=2>
    <?php
    if ($usehtmleditor) {
        editors_head_setup();
        $editor = editors_get_preferred_editor(FORMAT_HTML);
        $editor->use_editor('edit_message', array('legacy'=>true));
    }
    echo html_writer::tag('textarea', '', array('rows'=>25, 'cols'=>65, 'width'=>630, 'height'=>400, 'class'=>'form', 'id'=>'edit_message', 'name'=>'message'));
    ?>
    </td>
</tr>
<tr valign="top">
    <td align="right" valign="center" nowrap>

    <font SIZE="1">
     <?php
        echo $OUTPUT->old_help_icon('reading', get_string("helpreading"));
        echo "<br />";
        echo $OUTPUT->old_help_icon('writing', get_string("helpwriting"));
        echo "<br />";
        echo $OUTPUT->old_help_icon('questions', get_string("helpquestions"));
        echo "<br />";
        if ($usehtmleditor) {
            echo $OUTPUT->old_help_icon('richtext', get_string("helprichtext"));
        } 
      ?>
     <br />
     </font>

    </td>
</tr>

<tr valign=top>
    <td align=right><p><b><?php print_string("formattexttype"); ?>:</b></p></td>
    <td>
    <?php
        echo html_writer::select(format_text_menu(), 'format', $defaultformat);
     ?>
    <font SIZE="1">
    <?php
        echo $OUTPUT->old_help_icon('textformat', get_string("helpformatting"));
     ?>
    </font>

    </td>
</tr>

<tr>
    <td align=center colspan=2>
    <input type="hidden" name=sendmail value="1">
    <input type="submit" value="<?php echo get_string("savechanges"); ?>">
    </td>

</tr>
</table>
</form>
</center>

<?php

    echo $OUTPUT->footer();
    exit;
}

if (!$post = data_submitted()) {
    error("No data found!");
}

$post->subject = stripslashes(strip_tags($post->subject));  // Strip all tags
$options = new object;
$options->filter=false;
$post->message = format_text($post->message, $post->format, $options);   // Clean up any bad tags
$post->textmessage = format_text_email($post->message, $post->format);
$post->created = $post->modified = time();

if (!$post->subject or !$post->message) {
    $post->error = get_string("emptymessage", "forum");
}


/// Get the confirmed sites
if (!$sites = $DB->get_records("registry", array("confirmed"=>"1"))) {
    echo $OUTPUT->notification('No sites found!');
    echo $OUTPUT->footer();
    exit;
}

$from->email = "martin@moodle.com";
$from->firstname  = "Martin Dougiamas";
$from->lastname  = "";

$count = 0;
$sendmail = true;   // safety catch

@set_time_limit(0);
@ob_implicit_flush(true);
@ob_end_flush();

echo html_writer::start_tag('div', array('class'=>'boxaligncenter'));

foreach ($sites as $site) {
    if ($site->mailme > 0) {

        $to->email = $site->adminemail;
        $to->firstname  = $site->adminname;
        $to->lastname  = "";

        $count++;
        echo "$count - ";
        if ($sendmail) {
            email_to_user($to, $from, $post->subject, $post->textmessage, $post->message);
            echo "$site->adminemail $site->adminname SENT<br>";
        } else {
            echo "$site->adminemail $site->adminname NOT SENT<br>";
        }

    }
}

echo html_writer::end_tag('div');

echo $OUTPUT->footer();