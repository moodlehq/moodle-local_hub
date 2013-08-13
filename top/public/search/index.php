<?php

    require('../../../../../config.php');
    require_once('../../toplib.php');

    $PAGE->set_url('/public/search/');
    $PAGE->set_context(get_system_context());
    $PAGE->set_title('Search moodle.org');
    $PAGE->set_heading($PAGE->title);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($PAGE->heading);
    //print_header('Search moodle.org', 'Search moodle.org', 'Search moodle.org');

?>

<div class="generalbox boxaligncenter" style="text-align:center" >

<form action="https://moodle.org/public/search/" id="cse-search-box">
  <div>
    <input type="hidden" name="cx" value="017878793330196534763:-0qxztjngoy" />
    <input type="hidden" name="cof" value="FORID:9" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" name="q" size="31" />
    <input type="submit" name="sa" value="Search" />
  </div>
</form>

</div>

<div id="cse-search-results"></div>
<script type="text/javascript">
  var googleSearchIframeName = "cse-search-results";
  var googleSearchFormName = "cse-search-box";
  var googleSearchFrameWidth = ''
  var googleSearchDomain = "www.google.com";
  var googleSearchPath = "/cse";
</script>
<script type="text/javascript" src="https://moodle.org/public/search/cse.js"></script>


<?php
    echo $OUTPUT->footer();
