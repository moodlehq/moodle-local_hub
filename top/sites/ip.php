<?php

//This generates an IP file suitable for http://geostats.hostip.info/

require('../../../../config.php');

$type = optional_param('type', 'sites');

header("Content-Type: text/plain\n");

if ($type == 'hits') {

    $time = time() - (24 * 3600);

    $hits = $DB->get_records_sql_menu("SELECT ip,count(*) FROM {log} WHERE time >= ? GROUP BY ip", array($weekago));

    foreach ($hits as $ip => $count) {
        if (!empty($ip)) {
            mtrace("$ip,$count");
        }
    }


} else if ($type == 'sites') {

    $sites = $DB->get_records("registry", null, 'id, ipaddress, host');

    foreach ($sites as $site) {
        if (!empty($site->ipaddress)) {   // Already done
            echo $site->ipaddress."\n";
            flush();
            continue;
        }

        // Try to recalculate it ...

        if (empty($site->host)) {
            continue;
        }

        if (preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/',$site->host, $match)){
            $newsite->id = $site->id;
            $newsite->ipaddress = $site->host;
            $DB->update_record('registry', $newsite);
            echo "$host\n";
            continue;
        }

        $host = `/usr/bin/host $site->host`;

        if (strpos($host, 'has address')) {
            $host = explode('has address ', $host);
            $host = trim($host[1]);
        } else if (strpos($host, 'domain name pointer')) {
            $host = trim($site->host);
        } else if (strpos($host, 'no servers could be reached')) {
            $host = '0.0.0.0';
        } else if (strpos($host, 'not found')) {
            $host = '0.0.0.0';
        } else if (strpos($host, 'SERVFAIL')) {
            $host = '0.0.0.0';
        } else {
            continue;
        }

        $newsite->id = $site->id;
        $newsite->ipaddress = $host;
        $DB->update_record('registry', $newsite);

        echo "$host\n";

        flush();
    }
}

?>
