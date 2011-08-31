<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Anti spam captcha wall.
 *
 * @author     "Jordan Tomkinson" <sysadmin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();
require('../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once('HTML/QuickForm/input.php');

class spam_captcha_form extends moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form;

        if ($this->captcha_enabled()) {
	    $mform->addElement('header', '', 'Captcha Verification','');
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $this->add_action_buttons(false, " Verify I am human ");
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($this->captcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha'] = $result;
                }
            } else {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }
        return $errors;
    }

    function captcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey);
    }
}

$PAGE->set_url('/spam/');
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$mform_captcha = new spam_captcha_form();

$memcache_obj = new Memcache;
$memcache_obj->pconnect("127.0.0.1");

if ($mform_captcha->is_cancelled()) {
    redirect("/spam/");
} else if ($user = $mform_captcha->get_data()) {
    $expiry = time() + 3600;
    $memcache_obj->set("antispam_".$_SERVER['REMOTE_ADDR'], $expiry, false, $expiry);
    redirect("/spam/");
}

$PAGE->requires->css('/spam/spam.css');
$PAGE->set_cacheable(false);
$PAGE->set_title("Captcha Verification");
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add('Error');
$boxheading = "Access Denied - Spammer Detected";
$displaycaptcha = false;

if (isset($_SERVER['ANTISPAM_THREAT'])) {
  if ($_SERVER['ANTISPAM_THREAT'] < '25') {
    if ($memcache_obj->get("antispam_".$_SERVER['REMOTE_ADDR'])) {
      // user is on blacklist and threat level below threshold and passed captcha
      $boxheading = "Access Granted";
      $PAGE->requires->js_function_call('document.location.replace', array('/'), false, '20');
      $blurb = "Your IP Address [".$_SERVER['REMOTE_ADDR']."] <b>is</b> listed on our spam blocklist and you have <b>temporary</b> access to <a href='http://moodle.org'>moodle.org</a><br />
      <b><u>Note:</u></b> You still need to <a href='http://www.projecthoneypot.org/ip_".$_SERVER['REMOTE_ADDR']."' target='new'><b><u>Follow the procedure to remove yourself from the blocklist</u></b></a> to regain permanent access.";
    }else {
      // user is on blacklist and threat level below threshold and not passed captcha
      $blurb = "Your IP address [".$_SERVER['REMOTE_ADDR']."] is listed on a public spam blocklist<br />
      If you feel you should not be on the spam blocklist, then you need to <a href='http://www.projecthoneypot.org/ip_".$_SERVER['REMOTE_ADDR']."' target='new'><b><u>Follow the procedure to remove yourself from the blocklist</u></b></a>.<br />
      <br />Your <a href='http://www.projecthoneypot.org/threat_info.php' target='new'>Threat Score</a> is below the threshold for permanent blocking.<br />
      You will be granted temporary access for <b>30 minutes</b> upon successfull captcha verification below.<br/>
      <b><u>Note:</u></b> You still need to <a href='http://www.projecthoneypot.org/ip_".$_SERVER['REMOTE_ADDR']."' target='new'><b><u>Follow the procedure to remove yourself from the blocklist</u></b></a> to regain permanent access.";
      $displaycaptcha = true;
    }
  }else {
    $boxheading = "Access Denied - Spammer Detected";
    // user is on blacklist and threat level over threshold
    $blurb = "Your IP address [".$_SERVER['REMOTE_ADDR']."] is listed on a public spam blocklist, so we will not allow you to login or post on moodle.org.<br />
    If you feel you should not be on the spam blocklist, then you need to <a href='http://www.projecthoneypot.org/ip_".$_SERVER['REMOTE_ADDR']."' target='new'><b><u>Follow the procedure to remove yourself from the blocklist</u></b></a>.";
  }
}else {
  $boxheading = "Access Granted";
  $PAGE->requires->js_function_call('document.location.replace', array('/'), false, '20');
  $blurb = "Your IP Address [".$_SERVER['REMOTE_ADDR']."] is <b>not</b> listed on our spam blocklist and you have <b>full</b> access to <a href='http://moodle.org'>moodle.org</a>";
}

echo $OUTPUT->header();
?>
    <div id="content">
        <div id="error-box">
            <h3 class="heading"><? echo $boxheading; ?></h3>
            <?
                echo '<p class="blurb">'.$blurb.'<br />If that doesn\'t work and you really need help, email sysadmin [AT] moodle [DOT] org for assistance.</p>';
                if ($displaycaptcha === true) { $mform_captcha->display(); }
            ?>
            <p id="links"><a href="http://moodle.org">Click here to return to moodle.org</a></p>
        </div>
    </div> <!-- #content --> 
<?php

echo $OUTPUT->footer();
