<?php

require('../../../../config.php');

$minimum = 10;

if ($data = data_submitted()) {
    if (!empty($data->verify_sign) and empty($SESSION->paymentcode)) {  // Paypal input
        $form->amount = $data->mc_gross;
        $form->name = "$data->first_name $data->last_name";
        $form->code = $SESSION->paymentcode = $data->verify_sign;

        // Send an email

        $messagesubject = 'Moodle.org donation: Paypal info';
        $messagetext = "
        A donation was just made to Moodle.org.\n
        Amount: USD $data->mc_gross\n
        Name: $data->address_name
        Email: $data->payer_email\n
        Street: $data->address_street
        City: $data->address_city
        State: $data->address_state
        Postcode: $data->address_zip
        Country: $data->address_country\n";

        $support = $DB->get_record('user', array('id' => 1));
        $support->email = 'support@moodle.com';
        $support->firstname = 'Moodle';
        $support->lastname = 'Donations';
        @email_to_user($martin, $martin, $messagesubject, $messagetext);

    } else if (!empty($data->card_holder_name) and empty($SESSION->paymentcode)) {  // 2checkout
        $form->amount = $data->quantity;
        $form->name = urldecode($data->card_holder_name);
        $form->code = $SESSION->paymentcode = $data->key;

    } else if ((!empty($SESSION->paymentcode) and ($data->code == $SESSION->paymentcode)) or isadmin()) {
        unset($SESSION->paymentcode);
        //if ((strlen($data->url) > 6) and !(substr($data->url, 0, 4) == "http")) {
            //$data->url = "http://".$data->url;
        //}
        $data->timedonated = time();
        if (!empty($USER->id)) {
            $data->userid = $USER->id;
        } else {
            $data->userid = 0;
        }
        if ($DB->insert_record("register_donations", $data)) {
            redirect("/donations/", "You have been added to the list!");
        } else {
            redirect("/donations/", "An unusual error occurred!  Please contact payment@moodle.com to have the link added manually.");
        }
    } else {
        unset($SESSION->paymentcode);
    }
} else {
    unset($SESSION->paymentcode);
}

/// Print headings
$PAGE->set_url('/donations/');
$PAGE->set_context(get_system_context());
$PAGE->set_title('Moodle: Donations: Thanks!');
$PAGE->set_heading('Donations: Thanks!');
$PAGE->navbar->add($PAGE->heading, $PAGE->url);

echo $OUTPUT->header();

echo "<a name=\"top\"></a>";
echo html_writer::start_tag('div', array('class'=>'boxaligncenter boxwidthwide', 'style'=>'padding:20px;'));

echo "<h2 align=center>Thank you for your donation!</h2>\n";

echo "<P align=center>You should have received a confirmation in your email.</P>\n";

echo "<p align=center>Every dollar is very much appreciated!</p>";

if (is_siteadmin()) {
    $form->amount = 100;
    $form->name = fullname($USER);
    $form->code = $SESSION->paymentcode = 'fakeentry';
}

if (!empty($form)) {
  echo "<center><hr>";
  if ($form->amount < $minimum) {
      echo "<p>(If you were wanting to put your name on the donors list we are very sorry, but due to recent abuse we now require that donations be over US\$$minimum to get a listing there.)</p><br />";
      unset($SESSION->paymentcode);
  } else {
      echo "<center><hr>";
      echo "<p>If you want to you can add your name to the public list of donors.</p><br />";
      echo "<form action=thankyou.php method=post><table align=center>";
      echo "<tr><td align=right>Name:</td><td><input type=text name=name value=\"".s($form->name)."\">(optional)</td></tr>";
      echo "<tr><td align=right>Organisation name:</td><td><input type=text name=org>(optional)</td></tr>";
      echo "</table>";
      echo "<input type=submit value=\"Please add me to the public list of donors\">";
      echo "<input type=hidden name=amount value=\"".s($form->amount)."\">";
      echo "<input type=hidden name=code value=\"".s($form->code)."\">";
      echo "</form>";
  }
  echo "</center>";
}

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
