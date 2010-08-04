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

require_once($CFG->dirroot . "/admin/registration/lib.php");
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

        $renderedhtml = '';

        $brtag = html_writer::empty_tag('br');

        $table = new html_table();

        if ($withwriteaccess) {
            $table->head = array(get_string('bulkoperation', 'local_hub'),
                get_string('coursename', 'local_hub'),
                get_string('coursedesc', 'local_hub'),
                get_string('visible'),
                '');

            $table->align = array('center', 'left', 'left', 'center', 'center');
            $table->size = array('1%', '20%', '78%', '1%', '1%');
        } else {
            $table->head = array(get_string('coursename', 'local_hub'),
                get_string('coursedesc', 'local_hub'));

            $table->align = array('left', 'left');
            $table->size = array('25%', '75%');
        }

        if (empty($courses)) {
            if (isset($courses)) {
                $renderedhtml .= get_string('nocourse', 'local_hub');
            }
        } else {

            $table->width = '100%';
            $table->data = array();
            $table->attributes['class'] = 'sitedirectory';

            // iterate through sites and add to the display table
            $courseiteration = 0;
            foreach ($courses as $course) {
                $courseiteration = $courseiteration + 1;
                //create site name with link
                if (!empty($course->courseurl)) {
                    $courseurl = new moodle_url($course->courseurl);
                } else {
                    $courseurl = new moodle_url($course->demourl);
                }
                if ($withwriteaccess) {
                    $courseatag = html_writer::tag('a', $course->fullname, array('href' => $courseurl));
                } else {
                    $courseurl = new moodle_url('', array('sesskey' => sesskey(),
                                'redirectcourseid' => $course->id));
                    $courseatag = html_writer::tag('a', $course->fullname, array('href' => $courseurl));
                }
                if ($course->privacy) {
                    $coursenamehtml = html_writer::tag('span', $courseatag, array());
                } else {
                    $coursenamehtml = html_writer::tag('span', $courseatag,
                                    array('class' => 'dimmed_text'));
                }

                // add screenshots
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

                //create description to display
                $course->subject = get_string($course->subject, 'edufields');
                $course->audience = get_string('audience' . $course->audience, 'hub');
                $course->educationallevel = get_string('edulevel' . $course->educationallevel, 'hub');
                if (!empty($course->contributornames)) {
                    $course->contributorname = get_string('contributors', 'block_community',
                                    $course->contributorname);
                }
                if (empty($course->coverage)) {
                    $course->coverage = '';
                }
                $deschtml = html_writer::tag('div', $screenshothtml,
                                array('class' => 'coursescreenshot'));
                $deschtml .= $course->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= $brtag;
                //create the additional description
                $additionaldesc = '';
                if ($course->contributornames) {
                    $additionaldesc .= get_string('contributors', 'local_hub',
                                    $course->contributornames);
                    $additionaldesc .= ' - ';
                }
                if ($course->coverage) {

                    $additionaldesc .= get_string('coverage', 'local_hub', $course->coverage);
                    $additionaldesc .= ' - ';
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
                require_once($CFG->dirroot . "/lib/licenselib.php");
                $licensemanager = new license_manager();
                $licenses = $licensemanager->get_licenses();
                foreach ($licenses as $license) {
                    if ($license->shortname == $course->licenceshortname) {
                        $course->license = $license->fullname;
                    }
                }

                $additionaldesc .= get_string('additionalcoursedesc', 'local_hub', $course);
                $deschtml .= html_writer::tag('span', $additionaldesc,
                                array('class' => 'additionaldesc'));
                /// time registered and time modified only display for administrator
                if ($withwriteaccess) {
                    $admindisplayedinfo = new stdClass();
                    $admindisplayedinfo->timemodified = userdate($course->timemodified);
                    $additionaladmindesc = $brtag;
                    $admindisplayedinfo->shortname = $course->shortname;
                    $additionaladmindesc .= get_string('additionalcourseadmindesc', 'local_hub',
                                    $admindisplayedinfo);
                    $deschtml .= html_writer::tag('span', $additionaladmindesc,
                                    array('class' => 'additionaladmindesc'));
                }
                //add content to the course description
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

                    $blocksandactivities = html_writer::tag('span',
                                    get_string('activities', 'local_hub') . " : " . $activitieshtml);
                    $blocksandactivities .= $brtag . html_writer::tag('span',
                                    get_string('blocks', 'local_hub') . " : " . $blockhtml);

                    $deschtml .= print_collapsible_region($blocksandactivities, 'blockdescription',
                                    'blocksandactivities-' . $course->id,
                                    get_string('moredetails', 'local_hub'), '', false, true);
                }

                //create download button if necessary
                //in order to avoid form conflict, it is a button tag
                if (!$course->enrollable) {
                    $params['courseid'] = $course->id;
                    $params['filetype'] = HUB_BACKUP_FILE_TYPE;
                    $params['remotemoodleurl'] = $CFG->wwwroot;
                    $addurl = new moodle_url('/local/hub/webservice/download.php', $params);
                    $downloadbutton = html_writer::tag('button',
                                    get_string('download', 'block_community'));
                    $downloadbutton = html_writer::tag('a', $downloadbutton,
                                    array('href' => $addurl, 'class' => 'centeredbutton'));
                    $deschtml .= $brtag . $downloadbutton;
                }

                if ($withwriteaccess) {
                    //bulk operations
                    $checkboxhtml = html_writer::checkbox('bulk-' . $courseiteration,
                                    $course->id, false, '', array());

                    //visible
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
                    $visiblehtml = html_writer::tag('a', $hideimgtag, array('href' => $visibleurl));

                    //add site link under course name
                    $managesiteurl = new moodle_url($CFG->wwwroot . '/local/hub/admin/managesites.php',
                                    array('search' => $course->site->name, 'sesskey' => sesskey()));
                    $siteatag = html_writer::tag('a', $course->site->name,
                                    array('href' => $managesiteurl));
                    $coursenamehtml .= $brtag . html_writer::tag('span', $siteatag,
                                    array('class' => 'coursesitelink'));

                    //settings link
                    $settingsurl = new moodle_url("/local/hub/admin/coursesettings.php",
                                    array('sesskey' => sesskey(), 'id' => $course->id));
                    $settingslinkhtml = html_writer::tag('a', get_string('settings'),
                                    array('href' => $settingsurl));

                    // add a row to the table
                    $cells = array($checkboxhtml, $coursenamehtml, $deschtml, $visiblehtml, $settingslinkhtml);
                } else {
                    // add a row to the table
                    $cells = array($coursenamehtml, $deschtml);
                }

                $row = new html_table_row($cells);

                $table->data[] = $row;
            }

            $renderedhtml .= html_writer::table($table);

            //add the select bulk operation
            if ($withwriteaccess) {
                $selecthtml = html_writer::select(array(
                            'bulkdelete' => get_string('bulkdelete', 'local_hub'),
                            'bulkvisible' => get_string('bulkvisible', 'local_hub'),
                            'bulknotvisible' => get_string('bulknotvisible', 'local_hub')),
                                'bulkselect', '',
                                array('' => get_string('bulkselectoperation', 'local_hub')));
                $renderedhtml .= html_writer::tag('div', $selecthtml);

                //perform button
                $optionalurlparams['sesskey'] = sesskey();
                $bulkformparam['method'] = 'post';
                $bulkformparam['action'] = new moodle_url('', $optionalurlparams);
                $bulkbutton = html_writer::empty_tag('input',
                                array('name' => 'bulksubmitbutton', 'id' => 'bulksubmit',
                                    'type' => 'submit',
                                    'value' => get_string('bulkoperationperform', 'local_hub')));
                $renderedhtml .= html_writer::tag('div', $bulkbutton);
                $renderedhtml = html_writer::tag('form', $renderedhtml, $bulkformparam);
                $renderedhtml = html_writer::tag('div', $renderedhtml);
            }
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

        $table = new html_table();

        if ($withwriteaccess) {
            $table->head = array('', get_string('sitename', 'local_hub'),
                get_string('sitedesc', 'local_hub'),
                get_string('sitelang', 'local_hub'),
                get_string('siteadmin', 'local_hub'),
                get_string('visible'),
                '',
                '',
                get_string('operation', 'local_hub'),
                '');

            $table->align = array('center', 'left', 'left', 'center', 'center', 'center',
                'center', 'center', 'center', 'center');
            $table->size = array('5%', '25%', '40%', '5%', '15%', '%5', '%5');
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
                $imageurl = new moodle_url($site->imageurl);
                $imagehtml = html_writer::empty_tag('img', array('src' => $imageurl,
                            'alt' => $site->name));

                //create description to display
                $deschtml = $site->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= html_writer::empty_tag('br');
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
                    $additionaladmindesc = html_writer::empty_tag('br');
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
                        $visiblehtml, $trustbuttonhtml, $prioritisebuttonhtml, 
                        $deletelinkhtml, $settingslinkhtml);
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
