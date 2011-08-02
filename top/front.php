<?php defined('MOODLE_INTERNAL') || die(); ?>
<div class="showroom clearfix">
 <div>
   <h3><?php print_string('frontpagewelcometitle', 'moodle.org'); ?></h3>
   <p><?php print_string('frontpagewelcome1', 'moodle.org'); ?></p>
   <p><?php print_string('frontpagewelcome2', 'moodle.org'); ?></p>
</div>
</div>

<table class="frontpagetable" width="100%">
<tr>
 <td class="frontpageimage c0"><div>
   <a class="frontpagelink" href="about/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/about.gif" alt="" />
   <br /><?php print_string('nameaboutmoodle', 'moodle.org'); ?></a>
 </div></td>

 <td class="frontpageimage c1"><div>
   <a class="frontpagelink" href="news/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/news.gif" alt="" />
   <br /><?php print_string('namenews', 'moodle.org'); ?></a>
 </div></td>

 <td class="frontpageimage c2"><div>
   <a class="frontpagelink" href="support/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/support.gif" alt="" />
   <br /><?php print_string('namesupport', 'moodle.org'); ?></a>
 </div></td>

</tr>

<tr>
 <td class="frontpageimage c0"><div>
   <a class="frontpagelink" href="community/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/community.gif" alt="" />
   <br /><?php print_string('namecommunity', 'moodle.org'); ?></a>
 </div></td>

 <td class="frontpageimage c1"><div>
   <a class="frontpagelink" href="development/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/development.gif" alt="" />
   <br /><?php print_string('namedevelopment', 'moodle.org'); ?></a>
 </div></td>

 <td class="frontpageimage c2"><div>
   <a class="frontpagelink" href="downloads/"><img src="<?php echo $CFG->wwwroot ?>/theme/moodleofficial/pix/downloads.gif" alt="" />
   <br /><?php print_string('namedownloads', 'moodle.org'); ?></a>
 </div></td>

</tr>
</table>

<div class="frontpagefootericons">
  <a href="http://www.opensource.org/" title="Moodle uses the GPL, a certified Open Source license"><img src="<?php echo $CFG->wwwroot ?>/images/opensource.png" alt="Moodle is certified Open Source" /></a>
&nbsp;
  <a href="http://www.adlnet.org/Technologies/scorm/default.aspx" title="Moodle is certified SCORM 1.2 compliant"><img src="<?php echo $CFG->wwwroot ?>/images/scorm12.png" alt="Moodle is certified SCORM 1.2 compliant" /></a>
&nbsp;
  <a href="http://www.imsglobal.org/" title="Moodle is a contributing member of the IMS Global standards group"><img src="<?php echo $CFG->wwwroot ?>/images/imsglobal.png" alt="Moodle is a contributing member of the IMS Global standards group" /></a>
</div>
