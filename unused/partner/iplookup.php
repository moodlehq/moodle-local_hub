<?php

require_once("../config.php");

$systemcontext = get_context_instance(CONTEXT_SYSTEM);
if (!has_capability('moodle/site:doanything', $systemcontext)) {
  echo "You do not have administration privileges on this Moodle site. These are required for running this script.{$settings['eolchar']}";
  die();
}


$IP = gethostbyname("hq.moodle.com");
if (!empty($_GET['ip'])) {
  $IP = $_GET['ip'];
}
echo "IP: ".$IP."<br>\n";
if (!empty($_GET['host'])) {
  $IP = gethostbyname($_GET['host']);
  echo "Host: ".$_GET['host']."<br>\n";
}
$ipint = ip2long($IP);

echo "Long: ".$ipint."<br>\n";

if (!$countryinfo = get_record_sql("SELECT * FROM countries WHERE ".$ipint." >= ipfrom AND ".$ipint." <= ipto")) {
  echo "Not in database<br>\n";
}else {
  echo "Country: ".$countryinfo->countryname." (".$countryinfo->code2.")<br>\n";
}


?>
