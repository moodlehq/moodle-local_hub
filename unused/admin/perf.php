<?php
    include_once('config.php');

    require_login();
    if (isadmin()) {
        $perf =&NewPerfMonitor($db);
        $perf->UI($pollsecs=5);
    } else {
        error("Sorry, admins only.");
    }

?>
