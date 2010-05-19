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

require_once($CFG->dirroot. "/lib/hublib.php"); //SITENOTPUBLISHED, get_site_privacy_string

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
        global $OUTPUT;
        $linktositelist = html_writer::tag('a', get_string('sitelist','local_hub'), array('href' => new moodle_url('/local/hub/index.php')));
        $message = $confirmationmessage.html_writer::empty_tag('br').$linktositelist;
        return $OUTPUT->box($message);
    }

    /**
     * Display a box confirmation message for removing a site from the directory
     * @param object $site - site to delete
     * @return string
     */
    public function delete_confirmation($site) {
        global $OUTPUT;
        $optionsyes = array('delete'=>$site->id, 'confirm'=>1, 'sesskey'=>sesskey());
        $optionsno  = array('sesskey'=>sesskey());
        $formcontinue = new single_button(new moodle_url("/local/hub/admin/managesites.php", $optionsyes), get_string('delete'), 'post');
        $formcancel = new single_button(new moodle_url("/local/hub/admin/managesites.php", $optionsno), get_string('cancel'), 'get');
        $sitename = html_writer::tag('strong', $site->name);
        return $OUTPUT->confirm(get_string('deleteconfirmation', 'local_hub', $sitename), $formcontinue, $formcancel);
    }

    /**
     * Display a box confirmation message for removing a course from the directory
     * @param object $course - site to delete
     * @return string
     */
    public function delete_course_confirmation($course) {
        global $OUTPUT;
        $optionsyes = array('delete'=>$course->id, 'confirm'=>1, 'sesskey'=>sesskey());
        $optionsno  = array('sesskey'=>sesskey());
        $formcontinue = new single_button(new moodle_url("/local/hub/admin/managecourses.php", $optionsyes), get_string('delete'), 'post');
        $formcancel = new single_button(new moodle_url("/local/hub/admin/managecourses.php", $optionsno), get_string('cancel'), 'get');
        $coursename = html_writer::tag('strong', $course->fullname);
        return $OUTPUT->confirm(get_string('deletecourseconfirmation', 'local_hub', $coursename), $formcontinue, $formcancel);
    }

    /**
     * Display a list of sites with a search box + title
     * @param array $sites
     * @param string $searchdefaultvalue the default value of the search text field
     * @param boolean $withwriteaccess
     * @return string
     */
    public function searchable_site_list($sites, $searchdefaultvalue = '', $withwriteaccess=false) {
        global $OUTPUT;
        return $this->search_box($searchdefaultvalue).
                html_writer::empty_tag('br').
                $this->site_list($sites,  $withwriteaccess);
    }

    /**
     * Display a list of course with a search box + title
     * @param array $courses
     * @param string $searchdefaultvalue the default value of the search text field
     * @param boolean $withwriteaccess
     * @return string
     */
    public function searchable_course_list($courses, $searchdefaultvalue = '', $withwriteaccess=false) {
        global $OUTPUT;
        return $this->search_box($searchdefaultvalue).
                html_writer::empty_tag('br').
                $this->course_list($courses,  $withwriteaccess);
    }


    /**
     * Display a search box
     * @param string $searchdefaultvalue the default value of the search text field
     * @return string
     */
    public function search_box($searchdefaultvalue = '') {
        global $OUTPUT;
        $searchtextfield = html_writer::empty_tag('input', array('type' => 'text',
                'name' => 'search', 'id' => 'search', 'value' => $searchdefaultvalue));
        $submitbutton = html_writer::empty_tag('input', array('type' => 'submit',
                'value' => get_string('search', 'local_hub')));
        $formcontent = $searchtextfield . $submitbutton;
        $formcontent = html_writer::tag('div', $formcontent, array()); //input element cannot be straight
        //into a form element (XHTML strict)
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
    public function course_list($courses,  $withwriteaccess=false) {
        global $OUTPUT, $CFG;

        $renderedhtml = '';

        $table = new html_table();

        if ($withwriteaccess) {
            $table->head  = array(get_string('coursename', 'local_hub'),
                    get_string('coursedesc', 'local_hub'),
                    get_string('courselang', 'local_hub'),
                    get_string('visible'),
                    get_string('operation', 'local_hub'));

            $table->align = array('left', 'left', 'center', 'center', 'center');
            $table->size = array('20%', '40%', '10%', '5%', '5%');
        } else {
            $table->head  = array(get_string('coursename', 'local_hub'),
                    get_string('coursedesc', 'local_hub'),
                    get_string('courselang', 'local_hub'));

            $table->align = array('left', 'left', 'center');
            $table->size = array('25%', '60%', '5%');
        }

        if (empty($courses)) {
            if (isset($courses)) {
                $renderedhtml .= get_string('nocourse', 'local_hub');
            }
        } else {

            $table->width = '100%';
            $table->data  = array();
            $table->attributes['class'] = 'sitedirectory';

            // iterate through sites and add to the display table
            foreach ($courses as $course) {

                //create site name with link
                if (!empty($course->courseurl)) {
                    $courseurl = new moodle_url($course->courseurl);
                } else {
                    $courseurl = new moodle_url($course->demourl);
                }
                $courseatag = html_writer::tag('a', $course->fullname, array('href' => $courseurl));
                if ($course->privacy) {
                    $coursenamehtml = html_writer::tag('span', $courseatag, array());
                } else {
                    $coursenamehtml = html_writer::tag('span', $courseatag, array('class' => 'dimmed_text'));
                }

                //create description to display
                $course->subject = get_string($course->subject, 'edufields');
                $course->audience = get_string('audience'.$course->audience, 'hub');
                $course->educationallevel = get_string('edulevel'.$course->educationallevel, 'hub');
                if (!empty($course->contributornames)) {
                    $course->contributorname = get_string('contributors', 'block_community', $course->contributorname);
                }
                if (empty($course->coverage)) {
                    $course->coverage = '';
                }
                $deschtml = $course->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= html_writer::empty_tag('br');
                $additionaldesc = get_string('additionalcoursedesc', 'local_hub', $course);
                $deschtml .= html_writer::tag('span', $additionaldesc, array('class' => 'additionaldesc'));
                /// time registered and time modified only display for administrator
                if ($withwriteaccess) {
                    $admindisplayedinfo = new stdClass();
                    $admindisplayedinfo->timemodified = userdate($course->timemodified);
                    require_once($CFG->dirroot.'/local/hub/lib.php');
                    $hub = new local_hub();
                    $admindisplayedinfo->downloadurl = $course->downloadurl;
                    $admindisplayedinfo->originaldownloadurl = $course->originaldownloadurl;
                    $additionaladmindesc = html_writer::empty_tag('br');
                    $additionaladmindesc .= get_string('additionalcourseadmindesc', 'local_hub', $admindisplayedinfo);
                    $deschtml .= html_writer::tag('span', $additionaladmindesc, array('class' => 'additionaladmindesc'));
                }

                //retrieve language string
                //construct languages array
                if (!empty($course->language)) {
                    $languages = get_string_manager()->get_list_of_languages();
                    $language = $languages[$course->language];
                } else {
                    $language= '';
                }

                if ($withwriteaccess) {
                
                    //visible
                    if ($course->privacy) {
                        $hideimgtag = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/hide'),
                                'class' => 'siteimage', 'alt' => get_string('disable')));
                        $makevisible = false;
                    } else {
                        $hideimgtag = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/show'),
                                'class' => 'siteimage', 'alt' => get_string('enable')));
                        $makevisible = true;
                    }

                    $visibleurl = new moodle_url("/local/hub/admin/managecourses.php",
                            array('sesskey' => sesskey(), 'visible' => $makevisible, 'id' => $course->id));
                    $visiblehtml = html_writer::tag('a', $hideimgtag, array('href' => $visibleurl));



                    //delete link
                    $deleteeurl = new moodle_url("/local/hub/admin/managecourses.php",
                            array('sesskey' => sesskey(), 'delete' => $course->id));
                    $deletelinkhtml = html_writer::tag('a', get_string('delete'), array('href' => $deleteeurl));

                    // add a row to the table
                    $cells = array($coursenamehtml, $deschtml, $language,
                            $visiblehtml, $deletelinkhtml);

                } else {
                    // add a row to the table
                    $cells = array($coursenamehtml, $deschtml, $languages[$course->language]);
                }


                $row = new html_table_row($cells);              

                $table->data[] = $row;
            }
            $renderedhtml .= html_writer::table($table);
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
    public function site_list($sites,  $withwriteaccess=false) {
        global $OUTPUT, $CFG;

        $renderedhtml = '';

        $table = new html_table();

        if ($withwriteaccess) {
            $table->head  = array('', get_string('sitename', 'local_hub'),
                    get_string('sitedesc', 'local_hub'),
                    get_string('sitelang', 'local_hub'),
                    get_string('siteadmin', 'local_hub'),
                    get_string('visible'),
                    '',
                    '',
                    get_string('operation', 'local_hub'));

            $table->align = array('center', 'left', 'left', 'center', 'center', 'center', 'center', 'center', 'center');
            $table->size = array('5%', '25%', '40%', '5%', '15%', '%5', '%5');
        } else {
            $table->head  = array('', get_string('sitename', 'local_hub'),
                    get_string('sitedesc', 'local_hub'),
                    get_string('sitelang', 'local_hub'));

            $table->align = array('center', 'left', 'left', 'center');
            $table->size = array('10%', '25%', '60%', '5%');
        }

        if (empty($sites)) {
            $renderedhtml .= get_string('nosite', 'local_hub');
        } else {

            $table->width = '100%';
            $table->data  = array();
            $table->attributes['class'] = 'sitedirectory';

            // iterate through sites and add to the display table
            foreach ($sites as $site) {

                //create site name with link
                $siteurl = new moodle_url($site->url);
                $siteatag = html_writer::tag('a', $site->name, array('href' => $siteurl));
                if ($site->visible) {
                    $sitenamehtml = html_writer::tag('span', $siteatag, array());
                } else {
                    $sitenamehtml = html_writer::tag('span', $siteatag, array('class' => 'dimmed_text'));
                }

                //create image tag
                $imageurl = new moodle_url($site->imageurl);
                $imagehtml = html_writer::empty_tag('img', array('src' => $imageurl, 'alt' => $site->name));

                //create description to display
                $deschtml = $site->description; //the description
                /// courses and sites number display under the description, in smaller
                $deschtml .= html_writer::empty_tag('br');
                $additionaldesc = get_string('additionaldesc', 'local_hub', $site);
                $deschtml .= html_writer::tag('span', $additionaldesc, array('class' => 'additionaldesc'));
                /// time registered and time modified only display for administrator
                if ($withwriteaccess) {
                    $admindisplayedinfo = new stdClass();
                    $admindisplayedinfo->timeregistered = userdate($site->timeregistered);
                    if (!empty($site->timemodified)) {
                        $admindisplayedinfo->timemodified = userdate($site->timemodified);
                    } else {
                        $admindisplayedinfo->timemodified = '-';
                    }
                    require_once($CFG->dirroot.'/local/hub/lib.php');
                    $hub = new hub();
                    $admindisplayedinfo->privacy = $hub->get_site_privacy_string($site->privacy);
                    $admindisplayedinfo->contactable = $site->contactable?get_string('yes'):get_string('no');
                    $admindisplayedinfo->emailalert = $site->emailalert?get_string('yes'):get_string('no');
                    $additionaladmindesc = html_writer::empty_tag('br');
                    $additionaladmindesc .= get_string('additionaladmindesc', 'local_hub', $admindisplayedinfo);
                    $deschtml .= html_writer::tag('span', $additionaladmindesc, array('class' => 'additionaladmindesc'));
                }

                //retrieve language string
                //construct languages array
                if (!empty($site->language)) {
                    $languages = get_string_manager()->get_list_of_languages();
                    $language = $languages[$site->language];
                } else {
                    $language= '';
                }

                if ($withwriteaccess) {
                    //create site administrator name with email link
                    $adminnamehtml = html_writer::tag('a', $site->contactname,
                            array('href' => 'mailto:'.$site->contactemail));


                    //create trust button
                    if ($site->trusted) {
                        $trustmsg = get_string('untrustme', 'local_hub');
                        $trust = false;
                    } else {
                        $trustmsg = get_string('trustme', 'local_hub');
                        $trust = true;
                    }
                    $trusturl = new moodle_url("/local/hub/admin/managesites.php",
                            array('sesskey' => sesskey(), 'trust' => $trust, 'id' => $site->id));
                    $trustedbutton = new single_button($trusturl, $trustmsg);
                    $trustbuttonhtml = $OUTPUT->render($trustedbutton);

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
                            array('sesskey' => sesskey(), 'prioritise' => $makeprioritise, 'id' => $site->id));
                    $prioritisebutton = new single_button($prioritiseurl, $prioritisemsg);
                    $prioritisebuttonhtml = $OUTPUT->render($prioritisebutton);

                    //visible
                    if ($site->visible) {
                        $hideimgtag = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/hide'),
                                'class' => 'siteimage', 'alt' => get_string('disable')));
                        $makevisible = false;
                    } else {
                        $hideimgtag = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/show'),
                                'class' => 'siteimage', 'alt' => get_string('enable')));
                        $makevisible = true;
                    }
                    if ($site->privacy != SITENOTPUBLISHED) {
                        $visibleurl = new moodle_url("/local/hub/admin/managesites.php",
                                array('sesskey' => sesskey(), 'visible' => $makevisible, 'id' => $site->id));
                        $visiblehtml = html_writer::tag('a', $hideimgtag, array('href' => $visibleurl));
                    } else {
                        $visiblehtml = get_string('private', 'local_hub');
                    }

                    //delete link
                    $deleteeurl = new moodle_url("/local/hub/admin/managesites.php",
                            array('sesskey' => sesskey(), 'delete' => $site->id));
                    $deletelinkhtml = html_writer::tag('a', get_string('delete'), array('href' => $deleteeurl));

                    // add a row to the table
                    $cells = array($imagehtml, $sitenamehtml, $deschtml, $language, $adminnamehtml,
                            $visiblehtml, $trustbuttonhtml, $prioritisebuttonhtml, $deletelinkhtml);

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
