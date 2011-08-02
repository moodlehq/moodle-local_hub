<?php 

require('../../../../config.php');

$PAGE->set_url('/donations/test.php');
$PAGE->set_context(get_system_context());
$PAGE->set_title('Test');
$PAGE->set_heading('Testing 2checkout');


echo $OUTPUT->header();
echo $OUTPUT->heading($PAGE->heading);

echo html_writer::start_tag('div', array('class'=>'boxaligncenter boxwidthwide', 'style'=>'padding:20px;'));

?>
<form action='https://www2.2checkout.com/2co/buyer/purchase' method='post'>
<input type='hidden' name='sid' value='249869' >
<input type='hidden' name='product_id' value='1' >
Donate AUD$<input name='quantity' type='text' class="ctrl-ed" size='5' > (Australian Dollars)
<input name="submit" type='submit' value='Click to pay thru 2CheckOut' >
</form>
<?php

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
