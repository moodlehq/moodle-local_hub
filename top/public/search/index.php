<?php

require(__DIR__.'/../../../../../config.php');

$PAGE->set_url('/public/search/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('searchmoodleorg', 'local_moodleorg'));
$PAGE->set_heading($PAGE->title);

echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

?>
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
<script type="text/javascript" src="https://www.google.com/afsonline/show_afs_search.js"></script>

<?php
echo $OUTPUT->footer();
