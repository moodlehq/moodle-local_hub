<?php
require_once('update_list_subscription.php');
$newemail = 'jordant@blue-ferret.com.au';
echo update_list_subscription_soap('add', $newemail);
