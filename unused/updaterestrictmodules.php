<?php 

exit;  //done

include('config.php');

$courses = get_records('course');

$mods = array(1,3,5,7,8,10,14,15,17,22,23,24,25,28);

$db->debug = true;

foreach ($courses as $course) {

    if (empty($course->category)) {
        continue;
    }

    $newcourse->id = $course->id;
    $newcourse->restrictmodules = 1;
    update_record('course', $newcourse);


    $course->restrictmodules = 1;

    update_restricted_mods($course, $mods);
}


?>
