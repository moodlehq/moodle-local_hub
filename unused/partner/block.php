<?php 
///  Returns $advertisement as the output

    if (!empty($USER->country)) {
        $country = $USER->country;
    } else {
        $IP = $_SERVER['REMOTE_ADDR'];
    
        if ($countryinfo = get_record_sql("SELECT * FROM {$CFG->prefix}countries
                                       WHERE ipfrom <= inet_aton('$IP') AND inet_aton('$IP') <= ipto ")) {
            $country = $countryinfo->code2;
        } else {
            $country = "XX";
        }
    }

    if (!$ads = get_records_select('register_ads', "(country = 'XX' or country = '$country') AND country <> 'ZZ'")) {
        $advertisement =  "<a title=\"moodle.com\" href=\"http://partners.moodle.com/image/click.php?ad=moodle&u=http://moodle.com\"><img 
                              src=\"http://partners.moodle.com/image/file.php/moodle/block.gif\" 
                              height=\"169\" width=\"175\" border=\"0\"></a>";
    }

    $count = count($ads);
    $keys = array_keys($ads);
    $rand = rand(0, $count-1);

    $ad = $ads[$keys[$rand]];

    if ($ad->country != 'XX') {
        $ad->title .= " ($ad->country)";
    }
    
    $advertisement =  "<a title=\"$ad->title\" href=\"http://partners.moodle.com/image/click.php?ad=$ad->image&u=$ad->url\"><img 
                          src=\"http://partners.moodle.com/image/file.php/$ad->image/block.gif\" 
                          alt=\"$ad->title\" height=\"169\" width=\"175\" border=\"0\"></a>";
?>
