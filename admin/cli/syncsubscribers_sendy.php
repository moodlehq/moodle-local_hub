<?php

/**
 * Basically a batch script to blindly send all current hubsite emails and 
 * contactable/subscribing status to Sendy @ lists.moodle.org.
 * 
 * This was created since running a long sync like this during a live upgrade 
 * would be a little too silly. Hub will update the list on demand whenever a 
 * site is modified.
 * 
 * @copyright  2014 Aparup Banerjee
 */

define('CLI_SCRIPT', true);

require('../../../../config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions


// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute subscribers sync to sendy on lists.moodle.org enmasse

Options:
-h, --help            Print out this help

Example:
\$sudo -u vh-moodlenet /usr/bin/php local/hub/admin/cli/syncsubscribers_sendy.php
";

    echo $help;
    die;
}

require($CFG->dirroot. '/local/hub/lib.php');
$hub = new local_hub();
raise_memory_limit(MEMORY_HUGE);
$sites = $hub->get_sites();
update_sendy_list_batch($sites);
