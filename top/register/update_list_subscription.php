<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Updates the subscription to the security alerts mailing-list. In the case
 * of a new user, the 'old' variables must be empty.  In the case of a user
 * that was just deleted, the 'new' variables must be empty.  In all other 
 * cases, all variables must be provided.
 * 
 * param string $oldemail old email adress
 * param int $oldstatus old status : 0 means 'unsubscribed', 1 means 'subscribed'
 * param string $newemail new email adress
 * param int $newstatus new status : 0 means 'unsubscribed', 1 means 'subscribed'
 * return true if operation succesful, false otherwise 
 */
function update_list_subscription($oldemail, $oldstatus, $newemail, $newstatus) {

    // first, ignore case where no action is needed 
    if ( (($oldstatus == 0) && ($newstatus == 0)) || // just don't want to receive emails
         (($oldemail == '') && ($newstatus == 0)) || // new subscription, don't want emails
         (($oldstatus == 0) && ($newemail == '')) || // user deleted, wasn't on the list
         (($oldstatus == 1) && ($newstatus == 1) && ($oldemail == $newemail)) ) // no change to subscription
    {
        return true;
    }

    // sanity check
    if ( (($newemail == '') && ($newstatus == 1)) ||
         (($oldemail == '') && ($oldstatus == 1)) ) {
        error('need an address to subscribe');
    }

    $return = true;
    if (($oldemail != '') && ($oldstatus == 1)) { // delete old user
        $return &= update_list_subscription_soap('del', $oldemail);
    }

    if (($newemail != '') && ($newstatus == 1)) { // subscribe new user 
        $return &= update_list_subscription_soap('add', $newemail);
    }

    return $return;
}

function update_list_subscription_soap($op, $email, $debug = false) {

    $return = true;
    
    // necessary for debug, use '1' on production server
    if ($debug) {
        ini_set("soap.wsdl_cache_enabled","0");
    }

    $wsdl = 'http://lists.moodle.org/wsdl';
    $sympa_admin_email = 'moodlerobot@cvs.moodle.org';
    $sympa_admin_pass = 'init58bad3f8';

    $soap_client_options = array("exceptions" => 0);
    if ($debug) {
        // the trace parameter is usefull for debugging, may be turned off otherwise
        $soap_client_options = array("trace" => 1);
    }
    $sympa = new SoapClient($wsdl, $soap_client_options);

    try {
        $md5 = $sympa->login($sympa_admin_email, $sympa_admin_pass);

        // $op has to be either 'add' or 'del'
        $answer = $sympa->AuthenticateAndRun($sympa_admin_email, $md5, $op, array('securityalerts@lists.moodle.org', $email, null, 1));

        if ($op == 'add') {
            echo ('Your new email address '.$email.' was added to <a href="http://lists.moodle.org/info/securityalerts">securityalerts@lists.moodle.org</a>');
        } else if ($op == 'del') {
            echo ('Your old email address '.$email.' was deleted from <a href="http://lists.moodle.org/info/securityalerts">securityalerts@lists.moodle.org</a>');
        }
    }
    catch (SoapFault $ex) {
        $return = false;

        if ($debug) { 
            $msg = $ex->detail ? $ex->detail : $ex->faultstring;
            error($msg);
        }

        define('SHOW_AUTH_INFO', false); // warning : this debug information shows the authentication cookie for a sympa admin account 
        if (SHOW_AUTH_INFO) {
            print "Request :\n".htmlspecialchars($sympa->__getLastRequest()) ."\n";
            print "Response:\n".htmlspecialchars($sympa->__getLastResponse())."\n";
            print "</pre>";

            echo '<pre>';
            print_r($answer); // TODO: this is not defined!
            echo '</pre>';
        }
    }

    return $return;
}

