<?php 

$partners = array(
         "AU" => array("http://moodle.com/", "Visit moodle.com for professional services"),
         "IT" => array("http://www.mediatouch.it/", "www.mediatouch.it")
         );

if (!empty($USER->country)) {
    $country = $USER->country;
} else {
    $IP = $_SERVER['REMOTE_ADDR'];

    if ($countryinfo = get_record_sql("SELECT * FROM {$CFG->prefix}countries
                                   WHERE ipfrom <= inet_aton('$IP') AND inet_aton('$IP') <= ipto ")) {
        $country = $countryinfo->code2;
    } else {
        $country = "AU";
    }
}

$realcountry = $country;

if (!file_exists("$CFG->dirroot/partner/pix/$country.gif")) {
    $country = "AU";
}

if (empty($partners[$country])) {
    $country = "AU";
}

$partner = $partners[$country];

$url = $partner[0];
$info = $partner[1];

echo "<center><span title=\"Detected country: $realcountry\"><font size=\"1\">Advertisement</font></span><br />";
echo "<a title=\"$info\" href=\"$url\"><img 
         src=\"/partner/pix/$country.gif\" alt=\"$info\" height=\"169\" width=\"175\" border=\"0\"></a>";

#
#$realcountry = strtolower($realcountry);
#echo "<p><img title=\"Your detected country\" align=bottom src=\"/sites/flags/$realcountry.png\" height=15 width=25></p>"
?>

