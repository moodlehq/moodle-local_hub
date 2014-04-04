<?php

/**
 * Display an error message
 */

if (isset($_GET['status'])) {
    // HTTP status explicitly provided
    $httpstatus = (int) $_GET['status'];

} else if (isset($_SERVER["REDIRECT_STATUS"]) and is_numeric($_SERVER["REDIRECT_STATUS"])) {
    $httpstatus = (int) $_SERVER["REDIRECT_STATUS"];

} else {
    $httpstatus = null;
}

// Set to true if refreshing the page won't solve the problem.
// (if the status is between 500 and 599 it is permanent)
$permanent = ($httpstatus >= 500 && $httpstatus <= 599);

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
        $blurb = 'It\'s possible the page you were looking for might have been moved, updated or deleted.';
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

?><!DOCTYPE html>
<html dir="ltr" lang="en" xml:lang="en">
<head>
	<title>moodle.org error</title>
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300&amp;subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic' rel='stylesheet' type='text/css'>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="keywords" content="moodle, moodle.org, error"/>
	<meta http-equiv="pragma" content="no-cache"/>
	<meta http-equiv="expires" content="0"/>
	<!-- <link rel="stylesheet" type="text/css" href="/error/styles.css"/> -->
	<style>
		html,
		body {
		    height: 100%;
		}
		body {
			font-family:'Open Sans',Helvetica,Arial,sans-serif;
			color: #595959;
			margin: 0;
		    padding-top: 0;
		}
		.wrapper {
		    min-height: 100%;
		    height: auto !important;
		    height: 100%;
		    margin: 0 auto -126px;
		}
		.wrapper > .container {
			padding-top: 40px;
			padding-bottom: 126px;
			position: relative;
		}
		.navbar {
			height: 40px;
			background-color: #1b1b1b;
			background-image: linear-gradient(to bottom, #222, #111);
			border-bottom: 5px solid #F98012;
			box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.75);
			position: fixed;
			right: 0;
			left: 0;
			top: 0;
		}
		.navbar .container {
			max-width: 1680px;
			margin: 0 auto;
		}
		.sitelogo {
			display: block;
			padding-left: 20px;
			padding-right: 20px;
			padding-top: 7px;
		}
		.sitelogo .img {
			width: 96px;
			height: 25px;
		}
		.content {
			text-align:center;
			padding: 20px;
			margin-top: 120px;
			margin-bottom: 50px;
		}
		h1 {
			color: #504f4f;
			font-size: 200px;
			font-weight:600;
			margin: 0;
			/*text-shadow: 0px 2px 1px #bbbaba;*/
		}
		h2 {
			font-weight:300;
			letter-spacing: 7px;
			text-transform: uppercase;
			margin-top: -30px;
			margin-bottom: 40px;
		}
		.content p {
			font-size: 18px;
			font-weight: 100;
			margin: 4% auto 20px auto;
			padding-left: 10%;
			padding-right: 10%;
		}
        #searchboxwrapper {
            max-width: 600px;
            margin: 0 auto;
        }
        .gsc-wrapper {
            text-align: left;
        }
		footer {
		    color: #868686;
		    text-align: center;
		    background-color: #1b1b1b;
			background-image: linear-gradient(to bottom, #222, #111);
		    border-top: 5px solid #F98012;
		    padding-top: 25px;
		    padding-bottom: 20px;
		    height: 76px;
		}
		footer p {
		    font-size: 11.9px;
		    line-height: 20px;

		}
		a {
			color: #0070a8;
			text-decoration: none;
		}
		a:hover {
			color: #003d5c;
		}
		@media only screen and (max-width: 767px) {
			.content {
				margin-top: 20px;
				margin-bottom: 20px;
			}
			h1 {
				font-size: 120px;
			}
			h2 {
				letter-spacing: -2px;
				margin-top: -15px;
			}
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="container">
			<header class="navbar">
				<div class="container">
					<a class="sitelogo" alt="Moodle logo" href="https://moodle.org/">
						<img alt="Moodle.org" src="/error/moodle-logo.png" />
		            </a>
				</div>
			</header>
			<div class="content">
                <?php if (!empty($httpstatus)):?>
                <h1><?php echo $httpstatus; ?></h1>
                <?php else: ?>
                <?php endif; ?>
                <h2><?php echo $title; ?></h2>
                <p class="blurb"><?php echo $blurb; ?></p>
				<p class="hint">Please click the back button or try a search.</p>
                <div id='searchboxwrapper'>
                <script>
                  (function() {
                    var cx = '017878793330196534763:-0qxztjngoy'; //017878793330196534763:-0qxztjngoy
                    var gcse = document.createElement('script');
                    gcse.type = 'text/javascript';
                    gcse.async = true;
                    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
                        '//www.google.com/cse/cse.js?cx=' + cx;
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(gcse, s);
                  })();
                </script>
                <gcse:search></gcse:search>
                </div>
			</div>
		</div>
	</div>
	<footer class="footer">
        <p>Moodle™ is a <a href="http://docs.moodle.org/dev/License">registered trademark</a></p>
    </footer>
</body>
</html>
