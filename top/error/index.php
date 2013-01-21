<?php
$httpstatus = $_SERVER["REDIRECT_STATUS"];
/*
if (array_key_exists('e', $_GET)) {
    $httpstatus = (int)$_GET['e'];
} */

// Set to true if refreshing the page won't solve the problem.
// (if the status is between 500 and 599 it is permanent)
$permanent = ($httpstatus >= 500 && $httpstatus <= 599);

// If this gets set to true an SMS will be sent to Jordan.
// Send an SMS for all server errors.
$sendsms = false;

switch ($httpstatus) {
    // Client errors
    case 400 :
        $title = 'Bad request';
        $blurb = 'The URL you are using is malformed. Please edit it and try again.';
        break;
    case 401 :
        $title = 'Authorization required';
        $blurb = 'You must authenticate in order to view this page.';
        break;
    case 403 :
        $title = 'Forbidden';
        $blurb = 'You can not view this page.';
        break;
    case 404 :
        $title = 'File not found';
        $blurb = 'An unusual error occurred (tried to reach a page that does not exist).';
        break;
    case 408 :
        $title = 'Request timeout';
        $blurb = 'Your request timed out. Please try again.';
        break;
    case 418 :
        $title = 'I\'m a teapot';
        $blurb = 'Teapots can\'t produce web pages. Please give up now.';
        break;
    // Server errors
    case 500 :
	header("HTTP/1.0 500 Internal Server Error");
	header("Status: 500 Internal Server Error");
        $title = 'Internal server error';
        $blurb = 'An unknown error occurred. Please try again.';
	$sendsms = true;
        break;
    case 501 :
        $title = 'Not implemented';
        $blurb = 'The server cannot process your request.';
        break;
    case 502 :
        $title = 'Bad gateway';
        $blurb = 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.';
        break;
    case 503 :
        $title = 'Service unavailable';
        $blurb = 'The server is currently unavailable (because it is overloaded or down for maintenance).';
        break;
    case 504 :
        $title = 'Gateway timeout';
        $blurb = 'The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.';
        break;
    case 505 :
        $title = 'HTTP version not supported';
        $blurb = 'The server does not support the HTTP protocol version used in the request.';
        break;
    default : 
        $title = 'Unknown error';
        $blurb = 'An unknown error occurred. Please try again.';
        break;
}

if ($sendsms) {
    // Add some code to send an SMS to Jordan.
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html  dir="ltr" lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <title>moodle.org: Error</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <meta name="keywords" content="moodle, moodle.org:Error" /> 
    <link rel="stylesheet" type="text/css" href="/error/styles.css" />
</head>
<body>
    <div id="header">
        <div id="header-logo">
            <a id="logo" href="http://moodle.org/">
                <img class="logo" src="/error/images/moodle-logo.gif" border="0" alt="moodle">
            </a>
        </div>
        <!-- moodle menu start -->
        <div id="moodle-menu"> 
            <form action="http://moodle.org/public/search" method="get" id="moodle-global-search"> 
                <div> 
                    <input type="hidden" value="017878793330196534763:-0qxztjngoy" name="cx" /> 
                    <input type="hidden" value="FORID:9" name="cof" /> 
                    <input type="hidden" value="UTF-8" name="ie" /> 
                    <input type="text" maxlength="255" size="15" name="q" class="input-text" /> 
                    <input type="submit" value="Search moodle.org" name="sa" class="input-submit" /> 
                </div> 
            </form> 
            <div class="moodle-menu-content"> 
                <ul> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/about/" title="An overview about Moodle" class="moodle-menu-label">About</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_1"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://docs.moodle.org/en/About_Moodle" title="What is Moodle?" class="moodle-menuitem-content">What is Moodle?</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/stats/" title="Moodle.org: Moodle Statistics" class="moodle-menuitem-content">Moodle.org: Moodle Statistics</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://demo.moodle.org/" title="Demonstration site" class="moodle-menuitem-content">Demonstration site</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/news/" title="An overview of current Moodle news" class="moodle-menu-label">News</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_2"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/news/" title="Official news about Moodle" class="moodle-menuitem-content">Recent news</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/security/" title="Important information about security issues" class="moodle-menuitem-content">Security news</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://planet.moodle.org/" title="Aggregated blogs from Moodle developers" class="moodle-menuitem-content">Planet Moodle</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/data/view.php?d=19" title="Moodle-related publications from around the world" class="moodle-menuitem-content">Moodle Buzz</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/calendar/view.php" title="Moodle-related events in the future" class="moodle-menuitem-content">Calendar</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/support/" title="An overview of Moodle support options" class="moodle-menu-label">Support</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_3"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://docs.moodle.org/?lang=en" title="Documentation" class="moodle-menuitem-content">Documentation</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/forums/" title="Forums" class="moodle-menuitem-content">Forums</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/data/view.php?id=7246" title="Books and manuals" class="moodle-menuitem-content">Books and manuals</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/support/commercial/" title="Commercial services" class="moodle-menuitem-content">Commercial services</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/community/" title="An overview of the Moodle community" class="moodle-menu-label">Community</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_4"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/forums/" title="Forums" class="moodle-menuitem-content">Forums</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/events/" title="Events" class="moodle-menuitem-content">Events</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/sites/" title="Registered sites" class="moodle-menuitem-content">Registered sites</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/network/" title="Connected sites" class="moodle-menuitem-content">Connected sites</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/data/view.php?id=7232" title="Moodle Jobs" class="moodle-menuitem-content">Moodle Jobs</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/userpics/" title="Recent participants" class="moodle-menuitem-content">Recent participants</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/donations/" title="Donations" class="moodle-menuitem-content">Donations</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://www.cafepress.com/moodle/" title="Moodle Shop" class="moodle-menuitem-content">Moodle Shop</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/development/" title="An overview of Moodle development" class="moodle-menu-label">Development</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_5"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/cvsadmin/view.php?cid=1" title="Developers" class="moodle-menuitem-content">Developers</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://docs.moodle.org/en/Development" title="Developer documentation" class="moodle-menuitem-content">Developer documentation</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://tracker.moodle.org/" title="Moodle Tracker" class="moodle-menuitem-content">Moodle Tracker</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem withchildren"> 
                        <a href="http://moodle.org/downloads/" title="An overview of Moodle downloads" class="moodle-menu-label">Downloads</a> 
                        <div class="moodle-sub-menu" id="cm_submenu_6"> 
                            <div class="moodle-menu-content"> 
                                <ul> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://download.moodle.org/" title="Standard Moodle packages" class="moodle-menuitem-content">Standard Moodle packages</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://download.moodle.org/macosx/" title="Moodle for Mac OS X" class="moodle-menuitem-content">Moodle for Mac OS X</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://download.moodle.org/windows/" title="Moodle for Windows" class="moodle-menuitem-content">Moodle for Windows</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/data/view.php?id=6009" title="Modules and plugins" class="moodle-menuitem-content">Modules and plugins</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/mod/data/view.php?id=6552" title="Themes" class="moodle-menuitem-content">Themes</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://download.moodle.org/lang16/" title="Language packs" class="moodle-menuitem-content">Language packs</a> 
                                    </li> 
                                    <li class="moodle-menuitem"> 
                                        <a href="http://moodle.org/logo/" title="Moodle logos" class="moodle-menuitem-content">Moodle logos</a> 
                                    </li> 
                                </ul> 
                            </div> 
                        </div> 
                    </li> 
                    <li class="moodle-menuitem finalitem"> 
                        <a href="http://moodle.org/forums/my/" title="See all the moodle.org courses you are enrolled in" class="moodle-menu-label">My courses</a> 
                    </li> 
                </ul> 
            </div> 
        </div> 
        <!-- Moodle menu end --> 
    </div> <!-- #header -->
    <div id="content">
        <div id="error-box">
            <h3 class="heading"><?php echo sprintf('Error %d - %s', $httpstatus, $title); ?></h3>
            <p class="blurb"><?php echo $blurb; ?></p>
            <?php if (!$permanent) { ?>
            <p id="links"><a href="http://moodle.org">Click here to return moodle.org</a></p>
            <?php } else { ?>
            <div id="links">
                <p>We are experiencing a problem related to rss feeds at the moment and we're working on it</p>
            </div>
            <?php } ?>
        </div>
    </div> <!-- #content -->
    <div id="moodlesitelink">
        All content on this web site is made available under the <a href="http://docs.moodle.org/en/License">GNU General Public License</a>, unless otherwise stated. <br />
        <a href="http://moodle.org/" class="moodle-logo-link">
            <img width="100" height="30" src="/error/images/moodle-logo-footer.gif" border="0" alt="moodlelogo" title="Return to Moodle.org" />
        </a>
    </div>
</body>
