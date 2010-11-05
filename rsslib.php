<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Hub rss library
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the path to the cached rss feed contents. Creates/updates the cache if necessary.
 * The RSS feed content is a course search result.
 * @param array $args the arguments received in the url
 * $args[0] => context id = 2 (Front page) - not used
 * $args[1] => token
 * $args[2] => module name (it was needed by the rss/file.php to call this function) - not used
 * $args[3] => downloadable - PARAM_INT
 * $args[4] => audience - PARAM_ALPHA
 * $args[5] => educationallevel - PARAM_ALPHA
 * $args[6] => subject - PARAM_ALPHA
 * $args[7] => licence - PARAM_ALPHA
 * $args[8] => language - PARAM_ALPHANUMEXT
 * $args[9] => search - PARAM_TEXT (url encoded)
 * @return string the full path to the cached RSS feed directory. Null if there is a problem.
 */
function hub_rss_get_feed($context, $args) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/local/hub/lib.php');

    //are RSS feeds enabled?
    $enablerssfeeds = get_config('local_hub', 'enablerssfeeds');

    if (empty($enablerssfeeds)) {
        debugging('DISABLED (module configuration)');
        return null;
    }

    //check capabilities
    if (!has_capability('local/hub:view', $context)) {
        return null;
    }

    //TODO: cache
    $filename = 'rsssearch_' . $args[3] . '_' . $args[4] . '_' . $args[5]
            . '_' . $args[6] . '_' . $args[7] . '_' . $args[8] . '_' . $args[9];
    $cachedfilepath = rss_get_file_full_name('local_hub', $filename);

    //get the courses from the search
    if ($args[7] != 'all') {
        $options['licenceshortname'] = $args[7];
    }
    if ($args[6] != 'all') {
        $options['subject'] = $args[6];
    }
    if ($args[4] != 'all') {
        $options['audience'] = $args[4];
    }
    if ($args[5] != 'all') {
        $options['educationallevel'] = $args[5];
    }
    if ($args[8] != 'all') {
        $options['language'] = $args[8];
    }

    //if the RSS invisible secret is passed as parameter, display not visible course
    $rsssecret = get_config('local_hub', 'rsssecret');
    if (!empty($rsssecret) and
            ($rsssecret == optional_param('rsssecret', false, PARAM_RAW))) {
        $options['visibility'] = COURSEVISIBILITY_NOTVISIBLE;
    } else {
        $options['visibility'] = COURSEVISIBILITY_VISIBLE;
    }

    //get courses
    $options['search'] = empty($args[9]) ? '' : urldecode($args[9]);
    $options['downloadable'] = $args[3];
    $options['enrollable'] = !$args[3];

    $hub = new local_hub();
    $options['orderby'] = 'timemodified DESC, fullname ASC';
    $courses = $hub->get_courses($options, 0 , 10);

    //generate the information for rss
    $rssfeedinfo = local_hub_rss_generate_feed_info($courses);

    //generate the rss content
    require_once($CFG->libdir . "/rsslib.php");


    //First the RSS header
    $searchurl = new moodle_url($CFG->wwwroot . '/', array('downloadable' => $args[3],
                'audience' => $args[4], 'educationallevel' => $args[5], 'subject' => $args[6],
                'licence' => $args[7], 'language' => $args[8], 'search' => $args[9],
                'submitbutton' => 'Search+for+courses'));
    $rsscontent = rss_standard_header(get_config('local_hub', 'name'),
                    $searchurl->out(),
                    get_string('hubcoursessearch', 'local_hub'));

    $rsscontent .= rss_add_items($rssfeedinfo);

    //Now the RSS footer

    $rsscontent .= rss_standard_footer();


    if (!empty($rsscontent)) {
        rss_save_file('local_hub', $filename, $rsscontent);
    }

    //return the path to the cached version
    return $cachedfilepath;
}

/**
 * Generate courses feed content
 * @param object $courses
 * @return array
 */
function local_hub_rss_generate_feed_info($courses) {
    global $CFG;

    $rssfeedinfo = array();
    foreach ($courses as $course) {
        $courserss = new stdClass();
        $courserss->title = $course->fullname;
        $courserss->author = $course->creatorname;
        $courserss->pubdate = $course->timemodified;

        $courseurl = new moodle_url($CFG->wwwroot . '/index.php',
                array('courseid' => $course->id, 'rss' => true));

        $courserss->link = $courseurl->out(false);

        //create description
        $course->subject = get_string($course->subject, 'edufields');
        $course->audience = get_string('audience' . $course->audience, 'hub');
        $course->educationallevel = get_string('edulevel' . $course->educationallevel, 'hub');

        $deschtml = '';
        $deschtml .= $course->description; //the description


        //create the additional description
        $additionaldesc = html_writer::empty_tag('br');
        $additionaldesc .= get_string('userinfo', 'local_hub', $course);
        if ($course->contributornames) {
            $additionaldesc .= ' - ';
            $additionaldesc .= get_string('contributors', 'local_hub', $course->contributornames);       
        }

        if (empty($course->coverage)) {
            $course->coverage = '';
        } else {
            $additionaldesc .= ' - ';
            $additionaldesc .= get_string('coverage', 'local_hub', $course->coverage);        
        }

        //retrieve language string
        //construct languages array
        if (!empty($course->language)) {
            $languages = get_string_manager()->get_list_of_languages();
            $course->lang = $languages[$course->language];
        } else {
            $course->lang = '';
        }
        //licence
        require_once($CFG->libdir . "/licenselib.php");
        $licensemanager = new license_manager();
        $licenses = $licensemanager->get_licenses();
        foreach ($licenses as $license) {
            if ($license->shortname == $course->licenceshortname) {
                $course->license = $license->fullname;
            }
        }
        //time modified
        $course->timeupdated = userdate($course->timemodified);
        $additionaldesc .= ' - ' . get_string('fileinfo', 'local_hub', $course);
        //subject/audience/level
        $additionaldesc .= ' - ' . get_string('contentinfo', 'local_hub', $course);
        $deschtml .= html_writer::tag('span', $additionaldesc, array('class' => 'additionaldesc'));

        $courserss->description = $deschtml;

        $rssfeedinfo[] = $courserss;
    }
    return $rssfeedinfo;
}

