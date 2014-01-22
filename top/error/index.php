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
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300&amp;subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic' rel='stylesheet' type='text/css'>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="moodle, moodle.org, error" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <link rel="stylesheet" type="text/css" href="/error/styles.css" />
</head>
<body style="text-align:center;font-family:'Open Sans',Helvetica,Arial,sans-serif;  ">
    <div id="content">
        <h1 style="color: #F98012;font-size:500%;font-weight:300;margin:1em 0 0.1em">D'oh!</h1>
        <h2 style="color: #F98012;font-weight:300;">
            <?php if (!empty($httpstatus)):?>
            <span style=""><?php echo $httpstatus; ?></span>
            <?php endif; ?>
            <?php echo $title; ?>
        </h2>
        <strong><?php echo $blurb; ?></strong>
        <div style="margin-top:5em;">
            <?php if (!$permanent): ?>
            <a style="color: #0088CC;text-decoration:none;" href="/">
                <img src="/error/moodle-logo.png" alt="Moodle logo" /><br />
                Return to moodle.org
            </a>
            <?php else: ?>
            <img src="/error/moodle-logo.png" alt="Moodle logo" /><br />
            <?php endif; ?>
        </div>
    </div>
</body>
