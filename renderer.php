<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once($CFG->dirroot . "/" . $CFG->admin . "/registration/lib.php");
require_once($CFG->dirroot . "/course/publish/lib.php");

/**
 * Hub renderer.
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_hub_renderer extends plugin_renderer_base {

    /**
     * Display a box message confirming a site registration (add or update)
     * @param string $confirmationmessage
     * @return string
     */
    public function registration_confirmation($confirmationmessage) {
        $linktositelist = html_writer::tag('a', get_string('sitelist', 'local_hub'),
                        array('href' => new moodle_url('/index.php')));
        $message = $confirmationmessage . html_writer::empty_tag('br') . $linktositelist;
        return $this->output->box($message);
    }

    /**
     * Display a confirm box for the hub unregistration (add or update)
     * @param string $confirmationmessage
     * @return string
     */
    public function unregistration_confirmation($force) {
        if (empty($force)) {
            $confirmationmessage = get_string('confirmunregistration', 'local_hub');
            $buttonyeslabel = get_string('hubunregister', 'local_hub');
        } else {
            $confirmationmessage = get_string('confirmforceunregistration', 'local_hub');
            $buttonyeslabel = get_string('hubforceunregister', 'local_hub');
        }
        $optionsyes['sesskey'] = sesskey();
        $optionsyes['unregister'] = true;
        $optionsyes['confirm'] = true;
        $optionsyes['force'] = $force;
        $optionsno = array();
        $formcontinue = new single_button(new moodle_url("/local/hub/admin/register.php", $optionsyes),
                        $buttonyeslabel, 'post');
        $formcancel = new single_button(new moodle_url("/local/hub/admin/register.php", $optionsno),
                        get_string('cancel'), 'get');
        return $this->output->confirm($confirmationmessage, $formcontinue, $formcancel);
    }

    /**
     * Display a box confirmation message for removing a site from the directory
     * @param object $site - site to delete
     * @return string
     */
    public function delete_confirmation($site) {
        $managecourseurl = new moodle_url('/local/hub/admin/managecourses.php',
                        array('siteid' => $site->id, 'lastmodified' => 'all',
                            'visibility' => 'all', 'sesskey' => sesskey()));
        $managecourselink = html_writer::tag('a', get_string('deleterelatedcourseslink', 'local_hub'),
                        array('href' => $managecourseurl));
        $site->relatedcourses = $managecourselink;
        $optionsyes = array('delete' => $site->id, 'confirm' => 1, 'sesskey' => sesskey());
        $optionsno = array('sesskey' => sesskey());
        $deletesitebutton = new single_button(new moodle_url("/local/hub/admin/managesites.php",
                                $optionsyes), get_string('unregistersite', 'local_hub'), 'post');
        $optionsyes['unregistercourses'] = true;
        $deletesiteandcoursebutton = new single_button(
                        new moodle_url("/local/hub/admin/managesites.php", $optionsyes),
                        get_string('unregistersiteandcourses', 'local_hub'), 'post');
        $cancelbutton = new single_button(
                        new moodle_url("/local/hub/admin/managesites.php", $optionsno),
                        get_string('cancel'), 'get');
        $site->name = html_writer::tag('strong', $site->name);
        $output = $this->box_start('generalbox', 'notice');
        $output .= html_writer::tag('p', get_string('deleteconfirmation', 'local_hub', $site));
        $output .= html_writer::tag('div', $this->output->render($deletesitebutton)
                        . ' ' . $this->output->render($deletesiteandcoursebutton) .
                        ' ' . $this->output->render($cancelbutton),
                        array('class' => 'buttons'));
        $output .= $this->box_end();
        return $output;
    }

    /**
     * Display a box confirmation message for bulk operation on courses
     * @param array $courses - course to apply the operation
     * @param string $bulkoperation the operation (bulkdelete, bulkvisible, bulknotvisible)
     * @return string
     */
    public function course_bulk_operation_confirmation($courses, $bulkoperation) {
        $confirmationmessage = get_string('confirmmessage' . $bulkoperation, 'local_hub');
        $brtag = html_writer::empty_tag('br');
        $courseiteration = 0;
        foreach ($courses as $course) {
            $courseiteration = $courseiteration + 1;
            if (empty($course->demourl)) {
                $href = $course->courseurl;
            } else {
                $href = $course->demourl;
            }
            $confirmationmessage .= $brtag .
                    html_writer::tag('a', $course->fullname, array('href' => $href));
            $key = 'bulk-' . $courseiteration;
            $optionsyes[$key] = $course->id;
        }
        $optionsyes['bulkselect'] = $bulkoperation;
        $optionsyes['confirm'] = 1;
        $optionsyes['sesskey'] = sesskey();
        $optionsno = array('sesskey' => sesskey());

        $formcontinue = new single_button(new moodle_url("/local/hub/admin/managecourses.php",
                                $optionsyes), get_string($bulkoperation, 'local_hub'), 'post');
        $formcancel = new single_button(new moodle_url("/local/hub/admin/managecourses.php",
                                $optionsno), get_string('cancel'), 'get');


        return $this->output->confirm($confirmationmessage, $formcontinue, $formcancel);
    }

    /**
     * Display a list of sites with a search box + title
     * @param array $sites
     * @param string $searchdefaultvalue the default value of the search text field
     * @param boolean $withwriteaccess
     * @return string
     */
    public function searchable_site_list($sites, $searchdefaultvalue = '', $withwriteaccess=false) {
        return $this->search_box($searchdefaultvalue) .
        html_writer::empty_tag('br') .
        $this->site_list($sites, $withwriteaccess);
    }

    /**
     * Display a list of course with a search box + title
     * @param array $courses
     * @param string $searchdefaultvalue the default value of the search text field
     * @param boolean $withwriteaccess
     * @return string
     */
    public function searchable_course_list($courses, $searchdefaultvalue = '', $withwriteaccess=false) {
        return $this->search_box($searchdefaultvalue) .
        html_writer::empty_tag('br') .
        $this->course_list($courses, $withwriteaccess);
    }

    /**
     * Display a search box
     * @param string $searchdefaultvalue the default value of the search text field
     * @return string
     */
    public function search_box($searchdefaultvalue = '') {
        $searchtextfield = html_writer::empty_tag('input', array('type' => 'text',
                    'name' => 'search', 'id' => 'search', 'value' => $searchdefaultvalue));
        $submitbutton = html_writer::empty_tag('input', array('type' => 'submit',
                    'value' => get_string('search', 'local_hub')));
        $formcontent = $searchtextfield . $submitbutton;
        //input element cannot be straight
        //into a form element (XHTML strict)
        $formcontent = html_writer::tag('div', $formcontent, array());
        $searchform = html_writer::tag('form', $formcontent, array('action' => '',
                    'method' => 'post'));
        return $searchform;
    }

    /**
     * Display a list of courses
     * If $withwriteaccess = true, we display visible field,
     * @param array $courses
     * @param boolean $withwriteaccess
     * @return string
     */
    public function course_list($courses, $withwriteaccess=false, $optionalurlparams = array()) {
        global $CFG;

        require_once($CFG->dirroot . '/comment/lib.php');
        comment::init();

        $renderedhtml = '';

        if (empty($courses)) {
            if (isset($courses)) {
                $renderedhtml .= get_string('nocourse', 'local_hub');
            }
        } else {
            $courseiteration = 0;
            foreach ($courses as $course) {
                $courseiteration = $courseiteration + 1;

                //create html specific to hub administrator
                if ($withwriteaccess) {
                    //create site link html
                    $managesiteurl = new moodle_url($CFG->wwwroot . '/local/hub/admin/managesites.php',
                                    array('search' => $course->site->name, 'sesskey' => sesskey()));
                    $siteatag = html_writer::tag('a', get_string('site', 'local_hub') . ': '
                                    . $course->site->name,
                                    array('href' => $managesiteurl));
                    $sitehtml = html_writer::tag('div', $siteatag,
                                    array('class' => 'coursesitelink'));

                    //bulk operation checkbox html
                    $checkboxhtml = html_writer::tag('div',
                                    html_writer::checkbox('bulk-' . $courseiteration,
                                            $course->id, false, '', array()),
                                    array('class' => 'hubcoursedelete'));

                    //visible icon html
                    if ($course->privacy) {
                        $imgparams = array('src' => $this->output->pix_url('i/hide'),
                            'class' => 'siteimage', 'alt' => get_string('disable'));
                        $makevisible = false;
                    } else {
                        $imgparams = array('src' => $this->output->pix_url('i/show'),
                            'class' => 'siteimage', 'alt' => get_string('enable'));
                        $makevisible = true;
                    }
                    $hideimgtag = html_writer::empty_tag('img', $imgparams);
                    $visibleurlparams = array('sesskey' => sesskey(), 'visible' => $makevisible,
                        'id' => $course->id);
                    if (!empty($optionalurlparams)) {
                        $visibleurlparams = array_merge($visibleurlparams, $optionalurlparams);
                    }
                    $visibleurl = new moodle_url("/local/hub/admin/managecourses.php",
                                    $visibleurlparams);
                    $visiblehtml = html_writer::tag('div',
                                    html_writer::tag('a', $hideimgtag, array('href' => $visibleurl)),
                                    array('class' => 'hubcoursevisible'));

                    //settings link html
                    $settingsurl = new moodle_url("/local/hub/admin/coursesettings.php",
                                    array('sesskey' => sesskey(), 'id' => $course->id));
                    $settingslinkhtml = html_writer::tag('div',
                                    html_writer::tag('a', get_string('settings'),
                                            array('href' => $settingsurl)),
                                    array('class' => 'hubcoursesettings'));
                } else {
                    $visiblehtml = "";
                    $settingslinkhtml = "";
                    $checkboxhtml = "";
                    if (is_siteadmin ()) {
                        //create Edit course link
                        $managecourseurl = new moodle_url($CFG->wwwroot . '/local/hub/admin/managecourses.php',
                                        array('search' => $course->fullname,
                                            'sesskey' => sesskey(), 'visibility' => COURSEVISIBILITY_ALL,
                                            'lastmodified' => 'all'));
                        $courseatag = html_writer::tag('a', get_string('editcourse', 'local_hub'),
                                        array('href' => $managecourseurl));
                        $sitehtml = html_writer::tag('div', $courseatag,
                                        array('class' => 'coursesitelink'));
                    } else {
                        $sitehtml = "";
                    }
                }

                //create visit link html
                if (!empty($course->courseurl)) {
                    $courseurl = new moodle_url($course->courseurl);
                    $linktext = get_string('visitsite', 'local_hub');
                } else {
                    $courseurl = new moodle_url($course->demourl);
                    $linktext = get_string('visitdemo', 'local_hub');
                }
                if (!$withwriteaccess) {
                    $courseurl = new moodle_url('', array('sesskey' => sesskey(),
                                'redirectcourseid' => $course->id));
                }
                $visitlinkhtml = html_writer::tag('a', $linktext,
                                array('href' => $courseurl, 'class' => 'hubcoursedownload'));

                //create title html
                $coursename = html_writer::tag('h3', $course->fullname,
                                array('class' => 'hubcoursetitle'));
                $coursenamehtml = html_writer::tag('div', $coursename,
                                $course->privacy ? array() : array('class' => 'dimmed_text'))
                        . $sitehtml;

                // create screenshots html
                $screenshothtml = '';

                if (!empty($course->screenshots)) {
                    $images = array();
                    $baseurl = new moodle_url($CFG->wwwroot . '/local/hub/webservice/download.php',
                                    array('courseid' => $course->id,
                                        'filetype' => HUB_SCREENSHOT_FILE_TYPE));
                    for ($i = 1; $i <= $course->screenshots; $i = $i + 1) {
                        $params['screenshotnumber'] = $i;
                        $images[] = array(
                            'thumburl' => new moodle_url($baseurl, array('screenshotnumber' => $i)),
                            'imageurl' => new moodle_url($baseurl,
                                    array('screenshotnumber' => $i, 'imagewidth' => 'original')),
                            'title' => $course->fullname,
                            'alt' => $course->fullname
                        );
                    }
                    $imagegallery = new image_gallery($images, $course->shortname);
                    $imagegallery->displayfirstimageonly = true;
                    $screenshothtml = $this->output->render($imagegallery);
                }
                $coursescreenshot = html_writer::tag('div', $screenshothtml,
                                array('class' => 'coursescreenshot'));


                //create description html
                $deschtml = html_writer::tag('div', $course->description,
                                array('class' => 'hubcoursedescription'));

                //create users related information html
                $courseuserinfo = get_string('userinfo', 'local_hub', $course);
                if ($course->contributornames) {
                    $courseuserinfo .= ' - ' . get_string('contributors', 'local_hub',
                                    $course->contributornames);
                }

                $courseuserinfo .= html_writer::tag('a',
                                html_writer::empty_tag('img',
                                        array('src' => $this->output->pix_url('i/email'),
                                                'class' => 'hubcoursemail',
                                            'alt' => get_string('msgtopublisher', 'local_hub', $course->fullname))),
                                array('href' => new moodle_url('/local/hub/sendmessage.php',
                                        array('id' => $course->id, 'admin' => $withwriteaccess))));
                $courseuserinfohtml = html_writer::tag('div', $courseuserinfo,
                                array('class' => 'hubcourseuserinfo'));

                //create course content related information html
                $course->subject = get_string($course->subject, 'edufields');
                $course->audience = get_string('audience' . $course->audience, 'hub');
                $course->educationallevel = get_string('edulevel' . $course->educationallevel, 'hub');
                if (empty($course->coverage)) {
                    $course->coverage = '';
                } else {
                    $coursecontentinfo .= get_string('coverage', 'local_hub', $course->coverage);
                    $coursecontentinfo .= ' - ';
                }
                $coursecontentinfo = get_string('contentinfo', 'local_hub', $course);
                $coursecontentinfohtml = html_writer::tag('div', $coursecontentinfo,
                                array('class' => 'hubcoursecontentinfo'));

                ///create course file related information html
                //language
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
                $course->timeupdated = userdate($course->timemodified);
                $coursefileinfo = get_string('fileinfo', 'local_hub', $course);
                $coursefileinfohtml = html_writer::tag('div', $coursefileinfo,
                                array('class' => 'hubcoursefileinfo'));



                //Create course content html
                if (!empty($course->contents)) {
                    $activitieshtml = '';
                    $blockhtml = '';
                    foreach ($course->contents as $content) {

                        if ($content->moduletype == 'block') {
                            if (!empty($blockhtml)) {
                                $blockhtml .= ' - ';
                            }
                            $blockhtml .= get_string('pluginname', 'block_' . $content->modulename)
                                    . " (" . $content->contentcount . ")";
                        } else {
                            if (!empty($activitieshtml)) {
                                $activitieshtml .= ' - ';
                            }
                            $activitieshtml .= get_string('modulename', $content->modulename)
                                    . " (" . $content->contentcount . ")";
                        }
                    }

                    $blocksandactivities = html_writer::tag('div',
                                    get_string('activities', 'local_hub') . " : " . $activitieshtml);

                    //Uncomment following lines to display blocks information
//                    $blocksandactivities .= html_writer::tag('span',
//                                    get_string('blocks', 'local_hub') . " : " . $blockhtml);
                }
 
                //Create outcomes html
                $outcomes= '';
                if (!empty($course->outcomes)) {
                    $outcomes = get_string('outcomes', 'local_hub',
                            implode(', ', $course->outcomes));
                }
                $outcomeshtml = html_writer::tag('div', $outcomes, array('class' => 'hubcourseoutcomes'));
 
                //create additional information html
                $additionaldesc = $courseuserinfohtml . $coursecontentinfohtml
                        . $coursefileinfohtml . $blocksandactivities . $outcomeshtml;
                $additionaldeschtml = html_writer::tag('div', $additionaldesc,
                                array('class' => 'additionaldesc'));

                //create download button html
                $downloadbuttonhtml = "";
                if (!$course->enrollable) {
                    $params['courseid'] = $course->id;
                    $params['filetype'] = HUB_BACKUP_FILE_TYPE;
                    $params['remotemoodleurl'] = $CFG->wwwroot;
                    $addurl = new moodle_url('/local/hub/webservice/download.php', $params);
                    $downloadbuttonhtml = html_writer::tag('a', get_string('download', 'block_community'),
                                    array('href' => $addurl, 'class' => 'centeredbutton, hubcoursedownload'));
                }

                //Create rating html
                if (!empty($course->rating) and
                        ($course->rating->count > 0
                        or has_capability('moodle/rating:rate', $course->rating->context))) {
                    $rating = html_writer::tag('div', $this->output->render($course->rating),
                                    array('class' => 'hubcourserating'));
                } else {
                    $rating = html_writer::tag('div', get_string('noratings', 'local_hub'),
                                    array('class' => 'norating'));
                }

                //Create comments html
                $comment = html_writer::tag('div', get_string('nocomments', 'local_hub'),
                                array('class' => 'nocomments'));
                if (!empty($course->comment)) {
                    //display only if there is some comment if there is some comment
                    if ((!empty($course->comment->count) and $course->comment->count != '(0)')
                            or has_capability('moodle/comment:post', $course->comment->args->context)) {
                        $comment = html_writer::tag('div', $course->comment->output(true),
                                        array('class' => 'hubcoursecomments'));
                    }
                }

                //the main DIV tags
                $buttonsdiv = html_writer::tag('div',
                                $downloadbuttonhtml . $visitlinkhtml,
                                array('class' => 'courseoperations'));
                $screenshotbuttonsdiv = html_writer::tag('div',
                                $coursescreenshot . $buttonsdiv,
                                array('class' => 'courselinks'));

                $coursedescdiv = html_writer::tag('div',
                                $deschtml . $additionaldeschtml
                                . $comment . $rating,
                                array('class' => 'coursedescription'));
                $coursehtml =
                        $coursenamehtml . html_writer::tag('div',
                                $coursedescdiv . $screenshotbuttonsdiv,
                                array('class' => 'hubcourseinfo clearfix'));
                $coursehtml .= html_writer::tag('div',
                                $checkboxhtml . $visiblehtml . $settingslinkhtml,
                                array('class' => 'hubadminoperations clearfix'));

                $renderedhtml .=html_writer::tag('div', $coursehtml,
                                array('class' => 'fullhubcourse clearfix'));
            }

            $renderedhtml = html_writer::tag('div', $renderedhtml,
                            array('class' => 'hubcourseresult'));
        }

        //add the select bulk operation
        if ($withwriteaccess) {
            $selecthtml = html_writer::select(array(
                        'bulkdelete' => get_string('bulkdelete', 'local_hub'),
                        'bulkvisible' => get_string('bulkvisible', 'local_hub'),
                        'bulknotvisible' => get_string('bulknotvisible', 'local_hub')),
                            'bulkselect', '',
                            array('' => get_string('bulkselectoperation', 'local_hub')));
            $renderedhtml .= html_writer::tag('div', $selecthtml,
                            array('class' => 'hubbulkselect'));

            //perform button
            $optionalurlparams['sesskey'] = sesskey();
            $bulkformparam['method'] = 'post';
            $bulkformparam['action'] = new moodle_url('', $optionalurlparams);
            $bulkbutton = html_writer::empty_tag('input',
                            array('name' => 'bulksubmitbutton', 'id' => 'bulksubmit',
                                'type' => 'submit',
                                'value' => get_string('bulkoperationperform', 'local_hub')));
            $renderedhtml .= html_writer::tag('div', $bulkbutton,
                            array('class' => 'hubbulkbutton'));
            $renderedhtml = html_writer::tag('form', $renderedhtml, $bulkformparam);
            $renderedhtml = html_writer::tag('div', $renderedhtml);
        }
       
        return $renderedhtml;
    }

    /**
     * Display a list of sites
     * If $withwriteaccess = true, we display visible field,
     * trust/prioritise button, and timecreated/modified information.
     * @param array $sites
     * @param boolean $withwriteaccess
     * @return string
     */
    public function site_list($sites, $withwriteaccess=false) {
        global $CFG;

        $renderedhtml = '';
        $brtag = html_writer::empty_tag('br');

        $table = new html_table();

        if ($withwriteaccess) {
            $table->head = array('', get_string('sitename', 'local_hub'),
                get_string('sitedesc', 'local_hub'),
                get_string('sitelang', 'local_hub'),
                get_string('siteadmin', 'local_hub'),
                get_string('visible'),
                get_string('operation', 'local_hub'),
                '');

            $table->align = array('center', 'left', 'left', 'center', 'center', 'center',
                'center', 'center');
            $table->size = array('1%', '25%', '40%', '5%', '5%');
        } else {
            $table->head = array('', get_string('sitename', 'local_hub'),
                get_string('sitedesc', 'local_hub'),
                get_string('sitelang', 'local_hub'));

            $table->align = array('center', 'left', 'left', 'center');
            $table->size = array('10%', '25%', '60%', '5%');
        }

        if (empty($sites)) {
            if (isset($sites)) {
                $renderedhtml .= get_string('nosite', 'local_hub');
            }
        } else {
            $table->width = '100%';
            $table->data = array();
            $table->attributes['class'] = 'sitedirectory';

            // iterate through sites and add to the display table
            foreach ($sites as $site) {

                //create site name with link
                $siteurl = new moodle_url($site->url);
                $siteatag = html_writer::tag('a', $site->name, array('href' => $siteurl));
                if ($site->visible) {
                    $sitenamehtml = html_writer::tag('span', $siteatag, array());
                } else {
                    $sitenamehtml = html_writer::tag('span', $siteatag,
                                    array('class' => 'dimmed_text'));
                }

                //create image tag
                if (!empty($site->imageurl)) {
                    $imageurl = new moodle_url($site->imageurl);
                    $imagehtml = html_writer::empty_tag('img', array('src' => $imageurl,
                            'alt' => $site->name));
                } else {
                    $imagehtml = '';
                }

                //create description to display
                $deschtml = $site->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= $brtag;
                $additionaldesc = get_string('additionaldesc', 'local_hub', $site);
                $deschtml .= html_writer::tag('span', $additionaldesc,
                                array('class' => 'additionaldesc'));
                /// time registered and time modified only display for administrator
                if ($withwriteaccess) {
                    $admindisplayedinfo = new stdClass();
                    $admindisplayedinfo->timeregistered = userdate($site->timeregistered);
                    if (!empty($site->timemodified)) {
                        $admindisplayedinfo->timemodified = userdate($site->timemodified);
                    } else {
                        $admindisplayedinfo->timemodified = '-';
                    }

                    $registrationmanager = new registration_manager();
                    $admindisplayedinfo->privacy =
                            $registrationmanager->get_site_privacy_string($site->privacy);
                    $admindisplayedinfo->contactable = $site->contactable ?
                            get_string('yes') : get_string('no');
                    $admindisplayedinfo->emailalert = $site->emailalert ?
                            get_string('yes') : get_string('no');
                    $additionaladmindesc = $brtag;
                    $additionaladmindesc .= get_string('additionaladmindesc',
                                    'local_hub', $admindisplayedinfo);
                    $deschtml .= html_writer::tag('span', $additionaladmindesc,
                                    array('class' => 'additionaladmindesc'));
                }

                //retrieve language string
                //construct languages array
                if (!empty($site->language)) {
                    $languages = get_string_manager()->get_list_of_languages();
                    $language = $languages[$site->language];
                } else {
                    $language = '';
                }

                //retrieve country string
                if (!empty($site->countrycode)) {
                    $country = get_string_manager()->get_list_of_countries();
                    $language .= ' (' . $country[$site->countrycode] . ')';
                }

                if ($withwriteaccess) {
                    //create site administrator name with email link
                    $adminnamehtml = html_writer::tag('a', $site->contactname,
                                    array('href' => 'mailto:' . $site->contactemail));

                    //create trust button
                    if ($site->trusted) {
                        $trustmsg = get_string('untrustme', 'local_hub');
                        $trust = false;
                    } else {
                        $trustmsg = get_string('trustme', 'local_hub');
                        $trust = true;
                    }
                    $trusturl = new moodle_url("/local/hub/admin/managesites.php",
                                    array('sesskey' => sesskey(), 'trust' => $trust,
                                        'id' => $site->id));
                    $trustedbutton = new single_button($trusturl, $trustmsg);
                    $trustbuttonhtml = $this->output->render($trustedbutton);

                    //create prioritise button
                    if ($site->prioritise) {
                        $prioritisemsg = get_string('unprioritise', 'local_hub');
                        $makeprioritise = false;
                        $trustbuttonhtml = '';
                    } else {
                        $prioritisemsg = get_string('prioritise', 'local_hub');
                        $makeprioritise = true;
                    }
                    $prioritiseurl = new moodle_url("/local/hub/admin/managesites.php",
                                    array('sesskey' => sesskey(), 'prioritise' => $makeprioritise,
                                        'id' => $site->id));
                    $prioritisebutton = new single_button($prioritiseurl, $prioritisemsg);
                    $prioritisebuttonhtml = $this->output->render($prioritisebutton);

                    //visible
                    if ($site->visible) {
                        $hideimgtag = html_writer::empty_tag('img',
                                        array('src' => $this->output->pix_url('i/hide'),
                                            'class' => 'siteimage', 'alt' => get_string('disable')));
                        $makevisible = false;
                    } else {
                        $hideimgtag = html_writer::empty_tag('img',
                                        array('src' => $this->output->pix_url('i/show'),
                                            'class' => 'siteimage', 'alt' => get_string('enable')));
                        $makevisible = true;
                    }
                    if ($site->privacy != HUB_SITENOTPUBLISHED) {
                        $visibleurl = new moodle_url("/local/hub/admin/managesites.php",
                                        array('sesskey' => sesskey(), 'visible' => $makevisible,
                                            'id' => $site->id));
                        $visiblehtml = html_writer::tag('a', $hideimgtag,
                                        array('href' => $visibleurl));
                    } else {
                        $visiblehtml = get_string('private', 'local_hub');
                    }

                    //delete link
                    $deleteurl = new moodle_url("/local/hub/admin/managesites.php",
                                    array('sesskey' => sesskey(), 'delete' => $site->id));
                    $deletelinkhtml = html_writer::tag('a', get_string('delete'),
                                    array('href' => $deleteurl));

                    //settings link
                    $settingsurl = new moodle_url("/local/hub/admin/sitesettings.php",
                                    array('sesskey' => sesskey(), 'id' => $site->id));
                    $settingslinkhtml = html_writer::tag('a', get_string('settings'),
                                    array('href' => $settingsurl));


                    // add a row to the table
                    $cells = array($imagehtml, $sitenamehtml, $deschtml, $language, $adminnamehtml,
                        $visiblehtml, $deletelinkhtml . $brtag . $trustbuttonhtml
                        . $prioritisebuttonhtml, $settingslinkhtml);
                } else {
                    // add a row to the table
                    $cells = array($imagehtml, $sitenamehtml, $deschtml, $languages[$site->language]);
                }


                $row = new html_table_row($cells);
                if ($site->prioritise) {
                    $row->attributes['class'] = 'prioritisetr';
                } else if ($site->trusted) {
                    $row->attributes['class'] = 'trustedtr';
                }

                $table->data[] = $row;
            }
            $renderedhtml .= html_writer::table($table);
        }
        return $renderedhtml;
    }

}
