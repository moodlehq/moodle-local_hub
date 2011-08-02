<?PHP // $Id: cron.php,v 1.30 2003/12/15 00:56:12 stronk7 Exp $

/// This script looks through all the module directories for cron.php files
/// and runs them.  These files can contain cleanup functions, email functions
/// or anything that needs to be run on a regular basis.
///
/// This file is best run from cron on the host system (ie outside PHP).
/// The script can either be invoked via the web server or via a standalone
/// version of PHP compiled for CGI.
///
/// eg   wget -q -O /dev/null 'http://moodle.somewhere.edu/admin/cron.php'
/// or   php /web/moodle/admin/cron.php 

    $FULLME = "cron";

    $starttime = microtime();

    require_once("../config.php");

    echo "<pre>\n";

    $timenow  = time();

    //Execute backup's cron
    //Perhaps a long time and memory could help in large sites
    ini_set("max_execution_time","3000");
    ini_set("memory_limit","56M");
    if (file_exists("$CFG->dirroot/backup/backup_scheduled.php") and
        file_exists("$CFG->dirroot/backup/backuplib.php") and
        file_exists("$CFG->dirroot/backup/lib.php")) {
        include_once("$CFG->dirroot/backup/backup_scheduled.php");
        include_once("$CFG->dirroot/backup/backuplib.php");
        include_once("$CFG->dirroot/backup/lib.php");
        echo "Running backups if required...\n";
        flush();

        if (! schedule_backup_cron()) {
            echo "Something went wrong while performing backup tasks!!!\n";
        } else {
            echo "Backup tasks finished\n";
        }
    }

    echo "Cron script completed correctly\n";

    $difftime = microtime_diff($starttime, microtime());
    echo "Execution took ".$difftime." seconds\n"; 

?>
