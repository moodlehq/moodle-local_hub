<?php

/**
 * Provides the content of the maintenance page. This is expected to be
 * included early from the config.php.
 */

$host = strtolower($_SERVER['HTTP_HOST']);
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title><?php echo $host; ?> is currently unavailable</title>
        <style>
        body {
            background-image: url(<?php echo $CFG->wwwroot.'/maintenance/tile.png' ?>);
            background-repeat: repeat;
            background-color: #F98012;
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
            font-size: 14px;
            line-height: 20px;
        }
        #logo {
            margin-left: auto;
            margin-right: auto;
            width: 184px;
            height: 48px;
            padding-top: 120px;
        }
        #message {
            background-color: #ffffff;
            -webkit-box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            -webkit-border-radius: 15px;
            -moz-border-radius: 15px;
            border-radius: 15px;
            padding: 15px;
            width: 80%;
            margin-top: 60px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        #resources {
            display: table;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        #resource-row {
            display: table-row;
        }
        #resource {
            display: table-cell;
            padding: 10px;
            width: 100px;
        }
        #resource a {
            text-decoration: none;
            font-weight: bold;
            color: #000;
        }
        #resource span {
            font-size: 12px;
        }
        #host {
            font-weight: bold;
        }
        </style>
    </head>
    <body>
        <div id='logo'><img src='<?php echo $CFG->wwwroot.'/maintenance/moodle.png' ?>' /></div>
        <div id='message'>
            <p><span id='host'><?php echo $host; ?></span> is currently unavailable.</p>
            <p>We apologise for the inconvenience.</p>
            <p>You can follow <a href="https://twitter.com/moodlesites">@moodlesites</a> for updates.</p>
            <p>In the meantime, how about visiting one of our other Moodle community sites:</p>
            <div id="resources">
                <div id="resource-row">
                    <div id="resource">
                        <a href="http://docs.moodle.org">docs.moodle.org</a><br />
                        <span>User documentation in different languages</span>
                    </div>
                    <div id="resource">
                        <a href="https://tracker.moodle.org">tracker.moodle.org</a><br />
                        <span>Moodle tracker</span>
                    </div>
                    <div id="resource">
                        <a href="http://docs.moodle.org/dev">docs.moodle.org/dev</a><br />
                        <span>Moodle developer documentation</span>
                    </div>
                </div>
                <div id="resource-row">
                    <div id="resource">
                        <a href="http://download.moodle.org">download.moodle.org</a><br />
                        <span>Standard Moodle packages for download</span>
                    </div>
                    <div id="resource">
                        <a href="http://moodle.net">moodle.net</a><br />
                        <span>Moodle courses and content</span>
                    </div>
                    <div id="resource">
                        <a href="http://lang.moodle.org">lang.moodle.org</a><br />
                        <span>Moodle translation portal</span>
                    </div>
                </div>
                <div id="resource-row">
                    <div id="resource">
                        <a href="http://school.demo.moodle.net">school.demo.moodle.net</a><br />
                        <span>Mount Orange School (Moodle demo)</span>
                    </div>
                    <div id="resource">
                        <a href="http://moodle.com/">moodle.com</a><br />
                        <span>Moodle Pty Ltd</span>
                    </div>
                    <div id="resource">
                        <a href="http://research.moodle.net">research.moodle.net</a><br />
                        <span>Moodle research library and research conference info</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
