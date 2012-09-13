<?php defined('MOODLE_INTERNAL') || die(); 

$lang = isset($SESSION->lang) ? $SESSION->lang : 'en';
if (!$mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang))) {
    //FIXME: hack, hack, hack.
    $lang = 'en';
    $mapping = $DB->get_record('moodleorg_useful_coursemap', array('lang' => $lang));
}

?>

<div style="width: 100%; overflow: hidden;">
<div style="width: 25%; float: left;">
<h1>Announcement</h1>
<ul>
<li>One</li>
<li>Two</li>
<li>Three</li>
</ul>
</div>
<div style="width: 25%; float: left;">
<h1>Useful Posts</h1>
<?php require($CFG->cachedir.'/moodleorg/useful/frontpage-'.$lang.'.html');?>
</div>
<div style="width: 25%; float: left;">
<h1>Events</h1>
<ul>
<li>One</li>
<li>Two</li>
<li>Three</li>
</ul>
</div>
<div style="width: 25%; float: left;">
<h1>Recent Resources</h1>
<ul>
<li>One</li>
<li>Two</li>
<li>Three</li>
</ul>
</div>
</div>
<div class="frontpagefootericons">
  <a href="http://www.opensource.org/" title="Moodle uses the GPL, a certified Open Source license"><img src="<?php echo $CFG->wwwroot ?>/images/opensource.png" alt="Moodle is certified Open Source" /></a>
&nbsp;
  <a href="http://www.adlnet.org/Technologies/scorm/default.aspx" title="Moodle is certified SCORM 1.2 compliant"><img src="<?php echo $CFG->wwwroot ?>/images/scorm12.png" alt="Moodle is certified SCORM 1.2 compliant" /></a>
&nbsp;
  <a href="http://www.imsglobal.org/" title="Moodle is a contributing member of the IMS Global standards group"><img src="<?php echo $CFG->wwwroot ?>/images/imsglobal.png" alt="Moodle is a contributing member of the IMS Global standards group" /></a>
</div>
