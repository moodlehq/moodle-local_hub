<?php

    require('../../../../../config.php');
    require_once('../../toplib.php');

    $PAGE->set_url('/public/search/');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title('Search moodle.org');
    $PAGE->set_heading($PAGE->title);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($PAGE->heading);
    //print_header('Search moodle.org', 'Search moodle.org', 'Search moodle.org');

?>
<div class="generalbox boxaligncenter" style='text-align: center'>
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

<?php
    echo $OUTPUT->footer();
