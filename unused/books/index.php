<?php 

    require('../config.php');
    require('../toplib.php');

    $bookshtml = '
    <p class="booksdescription">
    The following books are all produced by members of the Moodle community.
    </p>

    <table class="bookstable">
    <tbody>
    <tr class="r0">
      <td class="c0">
        <a href="http://www.amazon.com/gp/product/059652918X?ie=UTF8&tag=httpmoodlcom-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=059652918X"><img border="0" src="http://moodle.org/books/059652918X.jpg" alt="Using Moodle book" title="Using Moodle book" /></a>
        <p><a href="http://www.amazon.com/gp/product/059652918X?ie=UTF8&tag=httpmoodlcom-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=059652918X">Using Moodle (2nd edition)</a><br />by Jason Cole and Helen Foster</p>
      </td>

      <td class="c1">
        <a href="http://www.packtpub.com/learning-moodle-1-9-course-development/book/mid/0205086e7wol"> <img width="97" height="123" src="http://moodle.org/books/1847193536.png" title="Moodle E-Learning Course Development book" alt="Moodle E-Learning Course Development book" /></a>
        <p><a href="http://www.amazon.com/gp/product/1904811299?ie=UTF8&tag=httpmoodlcom-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=1904811299">Moodle E-Learning Course Development</a><br />by William Rice</p> 
      </td>

      <td class="c2">
        <a href="http://www.packtpub.com/moodle-administration-guide/book/mid/0205086e7wol"> <img width="130" height="160" src="http://moodle.org/books/1847195628.jpg" title="Moodle Administration book" alt="Moodle Administration book" /></a>
        <p><a href="http://www.amazon.com/gp/product/1847195628?ie=UTF8&tag=httpmoodlcom-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=1847195628">Moodle Administration</a><br />by Alex Büchner</p> 
      </td>
    </tr>
    </table>
    ';

    //$section = get_record('course_sections', 'id', 1792);

    //$bookhtml = $section->summary;

    print_moodle_page('books', $bookshtml);

?>
