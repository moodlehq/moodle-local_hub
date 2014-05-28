<?php

require(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot.'/local/moodleorg/top/register/update_list_subscription.php');

if (!$site = get_site()) {
    error("Site isn't defined!");
}

if (!$admin = get_admin()) {
    error("Admin isn't defined!");
}

#    $notify[] = $DB->get_record('user', array('id' => '1'));      // Martin
#    $notify[] = $DB->get_record('user', array('id' => '1074'));   // Sean
#    $notify[] = $DB->get_record('user', array('id' => '5514'));   // Jon Bolton
#    $notify[] = $DB->get_record('user', array('id' => '2942'));   // Jeffrey Watkins
#    $notify[] = $DB->get_record('user', array('id' => '1519'));   // Sergio
#    $notify[] = $DB->get_record('user', array('id' => '1323'));   // Bernard
#
#    $notify[] = $DB->get_record('user', array('id' => '24152'));  // Helen
#    $notify[] = $DB->get_record('user', array('id' => '40774'));  // Don
#    $notify[] = $DB->get_record('user', array('id' => '23713'));  // Samuli
#    $notify[] = $DB->get_record('user', array('id' => '15677'));  // Mark Stevens
#    $notify[] = $DB->get_record('user', array('id' => '141092')); // Michael Blake

/// Print headings

$stradministration = get_string("administration");
$strregistration = get_string("registration");
$strregistrationinfo = get_string("registrationinfo");
$countries = get_string_manager()->get_list_of_countries();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/register/'));
$PAGE->set_title("$site->shortname: $strregistration");
$PAGE->set_heading($site->fullname);
$PAGE->navbar->add($strregistration);

echo $OUTPUT->header();
echo $OUTPUT->heading($strregistration);

/// Check the registration

if (!$form = data_submitted()) {
    notice("Sorry, but you need to call this page from a Moodle registration page.");
}

if (empty($form->url)) {
    notice(get_string("missingurl"));

} else if (substr_count(strtolower($form->url), "localhost") or
           substr_count(strtolower($form->url), "127.0.0.1") or
           substr_count(strtolower($form->url), ".local/") or
           substr_count(strtolower($form->url), "http://10.") or
           substr_count(strtolower($form->url), "http://192.168.")) {
    notice("Sorry, but your URL \"$form->url\" looks like a local-only address.<p>Such internal sites can not be registered.  <p>If you can, re-edit config.php and set the wwwroot variable to a real external address.");

} else if (substr_count(strtolower($form->url), "www.opensourcecms.com") or
           substr_count(strtolower($form->url), "demo.moodle.org")
          ) {
    notice("Sorry, but this site is a public demo, and can not be registered on moodle.org.");

} else if (!urlcheck($form->url)) {
    notice("Sorry, but your URL \"$form->url\" is either malformed, or failed the DNS check.");

} else if (empty($form->secret)) {
    notice("Missing secret key");

} else if (empty($form->sitename)) {
    notice(get_string("missingsitename"));

} else if (empty($form->country)) {
    notice(get_string("missingcountry"));

} else if (!isset($form->public)) {
    $form->public = 0;

} else if (empty($form->adminname)) {
    notice(get_string("missingfullname"));

} else if (empty($form->adminemail)) {
    notice(get_string("missingemail"));

} else if (preg_match("/\@localhost$/i", $form->adminemail) or !verifymxr($form->adminemail)) {
    notice("Sorry, but your email \"$form->adminemail\" is not valid.");

} else if (isset($form->enrolments) && ($form->enrolments < 2)) {

    notice("We're very sorry, but this site seems to have no students!
            <p>We do not accept empty sites for registration.  We hope you will
               try registering again later once your site has grown!
            <p>Please click the button below to return to your site.",
            $form->url.'/admin/index.php');

} else if (!isset($form->mailme)) {
    $form->mailme = 0;

} else {  // Everything is OK, so proceed

    $timenow = time();
    $entry = $form; //$entry contains (see below) transformed data for 2.x compatibility

    //1.9 to 2.x field map 'public' -> 'privacy' (for syncs with moodle.net)
    $map = array(
        0 => 'notdisplayed',
        1 => 'named',
        2 => 'linked',
    );
    $entry->privacy = $map[$entry->public];
    unset($entry->public);
    //other 1.9->2.x in moodle.net related refactorings MDLSITE-3041
    $entry->contactname = $entry->adminname;
    $entry->contactemail = $entry->adminemail;
    $entry->contactphone = $entry->adminphone;
    unset($entry->adminname);
    unset($entry->adminemail);
    unset($entry->adminphone);
    $entry->name = $entry->sitename;
    $entry->language = $entry->lang;
    $entry->countrycode = $entry->country;
    unset($entry->sitename);
    unset($entry->lang);
    unset($entry->country);

    $entry->timemodified = $timenow;
    $entry->url = clean_text($entry->url);
    $entry->name = clean_text(strip_tags($entry->name));
    switch ($entry->countrycode) {
       case 'CT':  // Catalan is Spain
           $entry->countrycode = 'ES';
           break;
       case 'ZR':
           $entry->countrycode = 'CD';
           break;
       case 'TP':
           $entry->countrycode = 'TL';
           break;
       case 'FX':
           $entry->countrycode = 'FR';
           break;
       case 'KO':
           $entry->countrycode = 'RS';
           break;
       case 'WA':
           $entry->countrycode = 'GB';
           break;
       case 'CS':
           $entry->countrycode = 'RS';
           break;
    }

    $entry->ip = getremoteaddr();

    $from->email = $entry->contactemail;
    $from->firstname = $entry->contactname;
    $from->lastname = "";
    $from->maildisplay = true;

    $message = "Management form: http://moodle.net/local/hub/admin/managesites.php\n\n".
               "     URL: $entry->url\n".
               "    Site: ".$entry->name."\n".
               " Version: $entry->release ($entry->version)\n".
               "    Host: $entry->host\n".
               "  Secret: $entry->secret\n".
               "Language: $entry->language\n".
               " Country: ".$countries[$entry->countrycode]."\n".
               "   Admin: $entry->contactname ($entry->contactemail)\n".
               "  Public: $entry->privacy\n".
               "  Mailme: $entry->mailme\n";

    $destination = $entry->url.'/admin/index.php?id='.$entry->secret;


    $confirmed = $DB->get_record("registry", array("secret"=>$entry->secret, "confirmed"=>1, 'host'=>$entry->host));    // By secret first

    if (!$pending = $DB->get_record("registry", array("secret"=>$entry->secret, "confirmed"=>0, 'host'=>$entry->host))) {
        $pending = $DB->get_record("registry", array("url"=>$entry->url));   // Anything with that URL
    }

    if ($confirmed) {
        $authenticated = ($entry->secret == $confirmed->secret);
    } else {
        $authenticated = false;
    }

    $entry->moodlerelease = $entry->release;
    $entry->moodleversion = $entry->version;

    if ($authenticated) {    // simply update the main entry
        $entry->id = $confirmed->id;
        if ($DB->update_record("registry", $entry)) {
            update_list_subscription($confirmed->contactemail, $confirmed->mailme, $entry->contactemail, $entry->mailme);
            if (!empty($entry->mailme)) {
                //email_to_user($admin, $from, "Moodle Registry updated - $entry->name", $message);
            }
            notice("Thank you for keeping your entry updated.  <p>Your previous information
                    has been overwritten and your new information is active immediately.
                    <p>Click the button below to return to your site and disable any registration reminders.",
                    $destination);
        } else {
            $databaseerror = true;
        }

    } else if ($pending) {   // simply updated the pending entry, don't subscribe to list
        $entry->confirmed = 1;    // Always now!  We don't check numbers of things any more. Rely no sitechecker later  MD 22/8/11
        $entry->id = $pending->id;
        if ($DB->update_record("registry", $entry)) {
            if (!empty($entry->mailme)) {
                //email_to_user($admin, $from, "Moodle Registry updated (unverified) - $entry->name", $message);
            }
            if ($entry->confirmed) {
                notice("Thank you for registering your information.
                        <p>Your site has been added to the site database.
                        <p>Click the button below to return to your site and disable any registration reminders.", $destination);
            } else {
               notice("Thank you for updating your entry!  <p>Your entry has not yet been checked by a human being but it will be.
               <p>Click the button below to return to your site and disable any registration reminders.", $destination);
            }
        } else {
            $databaseerror = true;
        }

    } else {   // No entry exists yet, so make a new one.
        $entry->confirmed = 1;    // Always now!  We don't check numbers of things any more. Rely on sitechecker later  MD 22/8/11
        $entry->timeregistered = $timenow;

        if ($DB->insert_record("registry", $entry)) {
            if ($entry->confirmed) {
                update_list_subscription('', 0, $entry->contactemail, $entry->mailme);
            }
            if (!empty($entry->mailme)) {
               // email_to_user($admin, $from, "Moodle Registry added - $entry->name", $message);
            }
            /*foreach ($notify as $notifyuser) {
                email_to_user($notifyuser, $from, "Moodle registry added - $entry->name", $message);
            }*/
            if ($entry->confirmed) {
                notice("Thank you for registering your information.
                <p>Your site has been added
                        to the site database.
                        <p>Click the button below to return to your site and disable any registration reminders.", $destination);
            } else {
                notice("Thank you for registering your information.  <p>Your site has been added
                        to the site database but will be checked soon by one of our team to make sure the site is
                        a valid Moodle site.
                        <p>Click the button below to return to your site and disable any registration reminders.", $destination);
            }
        } else {
            $databaseerror = true;
        }
    }
}

if (!empty($databaseerror)) {
    email_to_user($admin, $from, "Moodle Registry error - $entry->name", $message);
    error("Sorry, but your entry could not be updated due to a database error:
           please contact martin@moodle.org");
}

error("Sorry, but there was an error in your registration - please go back to your 
       registration form and try again.");

function urlcheck($url) {
    // Jordans ultra noob function
    if (@$parsetest = parse_url($url)) {
        if (!$parsetest['scheme']) {
            return false;
        }
        if ($parsetest['host']) {
            if (!preg_match("/^(\d{1,3}).(\d{1,3}).(\d{1,3}).(\d{1,3})$/", $parsetest['host'])) {
                if (!gethostbynamel($parsetest['host'])) {
                    return false;
                }
            }
        }else {
            return false;
        }
    }else {
        return false;
    }
    return true;
}

function verifymxr($email) {
    list($userName, $mailDomain) = split("@", $email);
    if (checkdnsrr($mailDomain, "MX")) {
        // this is a valid email domain!
        return true;
    } else {
        // this email domain doesn't exist! bad dog! no biscuit!
        return false;
    }
}
