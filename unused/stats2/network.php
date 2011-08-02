<?PHP // $Id$
      // 

if (isset($_GET['record'])) {
  //$fopen = fopen("http://status.moodle.com/status.php?record=".$_GET['record'], "r");
  //$record = fread($fopen, 
  echo file_get_contents("http://status.moodle.com/status.php?record=".$_GET['record']);
  exit;
}

	require_once("../config.php");

/// Print headings

    $navlinks = array();
    $navlinks[] = array('name' => 'Server availability', 'link' => "/stats/network.php", 'type' => 'misc');

    print_header("Moodle.org: Moodle Server availability", "moodle", build_navigation($navlinks), "", "", true, false);

    print_heading('Moodle Server availability');
  
    echo '<div id="networkstatus">';
?>
    <style type="text/css">
      .clear {
        clear: both;
      }
      table.status {
        border-width: 0px 0px 0px 0px;
        border-spacing: 0px;
        border-style: none none none none;
        border-color: black black black black;
        border-collapse: collapse;
        background-color: white;
      }
      table.status th {
        border-width: 1px 1px 1px 1px;
        padding: 4px 4px 4px 4px;
        border-style: inset inset inset inset;
        border-color: black black black black;
        background-color: rgb(250, 240, 230);
        -moz-border-radius: 0px 0px 0px 0px;
      }
      table.status td {
        border-width: 1px 1px 1px 1px;
        padding: 4px 4px 4px 4px;
        border-style: inset inset inset inset;
        border-color: black black black black;
        background-color: rgb(250, 240, 230);
        -moz-border-radius: 0px 0px 0px 0px;
      }
      .serviceOK { font-family: arial,serif;  font-size: 10pt; text-align: center;  background-color: #33FF00;  font-weight: bold; }
      .serviceWARNING { font-family: arial,serif;  font-size: 10pt;  text-align: center;  background-color: #FFFF00;  font-weight: bold; float: left; }
      .serviceUNKNOWN { font-family: arial,serif;  font-size: 10pt;  text-align: center;  background-color: #FF9900;  font-weight: bold; float: left; }
      .serviceCRITICAL { font-family: arial,serif;  font-size: 10pt;  text-align: center;  background-color: #F83838;  font-weight: bold; float: left; }
    </style>
    <script src="/stats/js/ajax.js" type="text/javascript"></script>
    <script src="/stats/js/ajax-dynamic-content.js" type="text/javascript"></script>
    <center><table class="status">
  <tr><td align="center"><strong>Service</strong></td><td align="center"><strong>Status</strong></td></tr>
<?php
  $hosts = array("http://moodle.org!234",
                "http://moodle.com!244",
                "http://partners.moodle.com!246",
                "http://docs.moodle.org!50",
                "http://cvs.moodle.org!31",
                "http://download.moodle.org!66",
                "http://tracker.moodle.com!38",
                "http://lists.moodle.org!32",
                "http://demo.moodle.org!58",
                "cvs://us.cvs.moodle.org!15",
                "cvs://uk.cvs.moodle.org!14",
                "cvs://eu.cvs.moodle.org!13",
                "cvs://es.cvs.moodle.org!12",
                );
  foreach ($hosts as &$host) {
    $array = explode("!", $host);
    //<div id=\"host1\"><!-- Empty div for dynamic content --></div>
    echo "<tr><td>".$array[0]."</td><td><div id=\"".$array[0]."\"><!-- Empty div for dynamic content --></div></td></tr>\n";
  }
?>
  </table>
    <script type="text/javascript">
<?php
  foreach ($hosts as &$host) {
    $array = explode("!", $host);
    // ajax_loadContent('host1','status.php?host=server10.moodle.com');
    echo "ajax_loadContent('".$array[0]."','/stats/network.php?record=".$array[1]."');\n";
  }
?>
  </script></center>
<?php
    echo '</div>';

    print_footer();
?>
