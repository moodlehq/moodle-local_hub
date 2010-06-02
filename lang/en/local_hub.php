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
 * Hub plugin related strings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activities'] = 'Activities';
$string['additionaldesc'] = 'Courses: {$a->courses} - Users: {$a->users} - Enrolments: {$a->enrolments} - Resources: {$a->resources} - Posts: {$a->posts}
    - Questions: {$a->questions} - Average number of participants: {$a->participantnumberaverage} - Average number of course modules: {$a->modulenumberaverage}';
$string['additionalcoursedesc'] = 'Creator: {$a->creatorname} - Publisher: {$a->publishername} - Subject: {$a->subject}
    - Audience: {$a->audience} - Educational level: {$a->educationallevel}';
$string['additionaladmindesc'] = 'Registered: {$a->timeregistered} - Modified: {$a->timemodified} - Privacy: {$a->privacy} -
    Contactable: {$a->contactable} - Email Notification: {$a->emailalert}';
$string['additionalcourseadmindesc'] = 'Download URL: {$a->downloadurl} - Original download URL: {$a->originaldownloadurl} - Time modified: {$a->timemodified} -
    Short name: {$a->shortname}';
$string['allowglobalsearch'] = 'Publish this hub and allow global search of all courses';
$string['allowpublicsearch'] = 'Publish this hub so people can join it';
$string['audience'] = 'Designed for';
$string['audience_help'] = 'What kind of course are you looking for?  As well as traditional courses intended for students, you might search for communities of Educators or Moodle Administrators';
$string['audienceeducators'] = 'Educators';
$string['audiencestudents'] = 'Students';
$string['audienceadmins'] = 'Moodle Administrators';
$string['badurlformat'] = 'Bad URL format';
$string['blocks'] = 'Blocks';
$string['cannotregisternotavailablesite'] = 'This hub cannot access your web site.';
$string['cannotregisterprivatehub'] = 'You cannot register your hub. Change the Privacy setting.';
$string['cannotsetpasswordforpublichub'] = 'Cannot set a password for public hub. Either delete your password, either set the hub as private.';
$string['community'] = 'Community';
$string['confirmregistration'] = 'Confirm registration';
$string['contactemail'] = 'Contact email';
$string['contactname'] = 'Contact name';
$string['contributornames'] = 'Contributor names';
$string['contributors'] = 'Contributors: {$a}';
$string['coursedesc'] = 'Description';
$string['courselang'] = 'Language';
$string['coursemap'] = 'Course map';
$string['coursename'] = 'Name';
$string['courseprivate'] = 'Private';
$string['coursepublic'] = 'Public';
$string['coursepublished'] = 'Course published';
$string['courseshortname'] = 'Shortname';
$string['coursesnumber'] = 'Number of courses ({$a})';
$string['coverage'] = 'Coverage: {$a}';
$string['creatorname'] = 'Creator name';
$string['creatornotes'] = 'Creator notes';
$string['deleteconfirmation'] = 'Are you sure to delete the {$a} site ?';
$string['deletecourseconfirmation'] = 'Are you sure to delete the {$a} course ?';
$string['demourl'] = 'Demo URL';
$string['description'] = 'Description';
$string['donotdeleteormodify'] = 'DO NOT DELETE OR MODIFY THIS USER !';
$string['downloadable'] = 'courses I can download';
$string['educationallevel'] = 'Educational level';
$string['educationallevel_help'] = 'What educational level are you searching for?  In the case of communities of educators, this level describes the level they are teaching.';
$string['edulevelassociation'] = 'Association';
$string['edulevelcorporate'] = 'Corporate';
$string['edulevelgovernment'] = 'Government';
$string['edulevelother'] = 'Other';
$string['edulevelprimary'] = 'Primary';
$string['edulevelsecondary'] = 'Secondary';
$string['eduleveltertiary'] = 'Tertiary';
$string['emailmessagesiteadded'] = 'A new site just registered with the hub at {$a->huburl}
    
Name: {$a->name}
URL: {$a->url}
Admin: {$a->contactname} ({$a->contactemail})
Language: {$a->language}

To manage registered sites, go to: {$a->managesiteurl}
';
$string['emailmessagesiteupdated'] = '{$a->name} just updated registration with the hub at {$a->huburl}

Name: {$a->name}
URL: {$a->url}
Admin: {$a->contactname} ({$a->contactemail})
Language: {$a->language}

To manage registered sites, go to: {$a->managesiteurl}
';
$string['emailmessagesiteurlchanged'] = '{$a->name} site has been updated. (previous name: {$a->oldname})
Its new url {$a->url} has been updated from ({$a->oldurl}).';
$string['emailtitlesiteadded'] = '{$a} site has been added to the hub';
$string['emailtitlesiteupdated'] = '{$a} site has been updated on the hub';
$string['emailtitlesiteurlchanged'] = '{$a} site has changed his url (please check it).';
$string['enabled'] = 'Enabled';
$string['enrollable'] = 'courses I can enrol in';
$string['enroldownload'] = 'Find';
$string['enroldownload_help'] = 'Some courses listed in this directory are being advertised so that people can come and participate in them on the original site.

Others are course templates provided for you to download and use on your own Moodle site.';
$string['errorbadimageheightwidth'] = 'The image should have a maximum size of {$a->width} X {$a->height}';
$string['errorlangnotrecognized'] = 'Language code is unknown by Moodle. Please contact {$a}';
$string['errorwrongpostdata'] = 'Some POST data are missing, please use the Moodle registration form.';
$string['hideguestbutton'] = 'Moodle changed the option \'Guest login button\' to the value \'Hide\'.';
$string['hub'] = 'Hub';
$string['hubdetails'] = 'Hub details';
$string['hubregister'] = 'Register your hub on Moodle.org';
$string['hubregisterupdate'] = 'Update your registration on Moodle.org';
$string['hubregistrationcomment'] = 'You are about to register your hub with Moodle.org.  Moodle.org will periodically contact this hub to make sure it is still active and also to refresh this information.';
$string['hubwsroledescription'] = 'WARNING: DO NOT DELETE OR MODIFY THIS ROLE. This role has been internally created for a registered site, a hub server or Moodle.org.';
$string['hubwsuserdescription'] = 'WARNING: DO NOT DELETE OR MODIFY THIS USER. This user has been internally created for a registered site, a hub server or Moodle.org.';
$string['imageurl'] = 'Image url';
$string['information'] = 'Information';
$string['keywords'] = 'Keywords';
$string['keywords_help'] = 'You can search for courses containing specific text in the name, description and other fields of the database.';
$string['language'] = 'Language';
$string['language_help'] = 'You can search for courses written in a specific language.';
$string['licence'] = 'License';
$string['licence_help'] = 'You can search for courses that are licensed in a particular way.';
$string['logourl'] = 'Logo URL';
$string['managecourses'] = 'Manage courses';
$string['managesites'] = 'Manage sites';
$string['modulenumberaverage'] = 'Average number of course modules ({$a})';
$string['moodleorg'] = 'Moodle.org';
$string['moredetails'] = 'More details';
$string['name'] = 'Name';
$string['no'] = 'No';
$string['nocourse'] = 'No courses match your search.';
$string['nosearch'] = 'Don\'t publish hub or courses';
$string['nosite'] = 'No sites have been registered yet or match the search.';
$string['notregisteredonhub'] = 'You cannot publish this course on a hub  different from Moodle.org, because this site isn\'t registered on any different hub. Contact your administrator if you want to do so.';
$string['notregisteredonmoodleorg'] = 'You cannot publish this course on Moodle.org hub, because this site isn\'t registered on Moodle.org hub. Contact your administrator if you want to do so.';
$string['operation'] = 'Operation';
$string['orenterprivatehub'] = 'or enter a private hub URL:';
$string['participantnumberaverage'] = 'Average number of participants ({$a})';
$string['password'] = 'Password';
$string['password_help'] = 'If you set your site as private (Don\'t publish), you can set a password (or not). You will need to communicate this password to site that want to register on your hub.';
$string['postaladdress'] = 'Postal address';
$string['postsnumber'] = 'Number of posts ({$a})';
$string['prioritise'] = 'Prioritise';
$string['privacy'] = 'Privacy';
$string['private'] = 'Private';
$string['privatehuburl'] = 'Private hub URL';
$string['publicationinfo'] = 'Course publication information';
$string['publichub'] = 'Public hub';
$string['publishcourseon'] = 'Publish on {$a}';
$string['publishername'] = 'Publisher';
$string['publishon'] = 'Publish on';
$string['publishonmoodleorg'] = 'Publish on Moodle.org';
$string['publishonspecifichub'] = 'Publish on a Hub';
$string['questionsnumber'] = 'Number of questions ({$a})';
$string['registeredcourses'] = 'Registered courses';
$string['registeredsites'] = 'Registered sites';
$string['registrationconfirmed'] = 'Registration successfull';
$string['registrationinfo'] = 'Registration information';
$string['registersite'] = 'Register on {$a}';
$string['registeron'] = 'Register on';
$string['registeronmoodleorg'] = 'Register on Moodle.org';
$string['registeronspecifichub'] = 'Register on a specific hub';
$string['registration'] = 'Hub server registration';
$string['registrationupdated'] = 'Registration has been updated.';
$string['registrationupdatedfailed'] = 'Registration update failed.';
$string['resourcesnumber'] = 'Number of resources ({$a})';
$string['roleassignmentsnumber'] = 'Number of role assignments ({$a})';
$string['screenshots'] = 'Screenshots';
$string['search'] = 'Search for courses';
$string['selecthub'] = 'Select hub';
$string['sendfollowinginfo'] = 'Send the following information:';
$string['settings'] = 'Settings';
$string['settingsupdated'] = 'Settings have been updated.';
$string['siteadmin'] = 'Administrator';
$string['sitecreated'] = 'Site created';
$string['sitedesc'] = 'Description';
$string['sitehelpexplanation'] = 'Listing of registered sites on your hub server. To display a site on the top of your
    search result / list, proritise it. Trusted sites will be marked as trusted on the search result / list, and they will be displayed before untrusted sites.
    Prioritised sites are automatically trusted. Finally, only visible sites are displayed on the search result / list (even if they are prioritised/trusted).
    Site that are unreachable for more than a month would be deleted from the database (except if they are trusted/prioritised)';
$string['sitelang'] = 'Language';
$string['sitelist'] = 'Site list';
$string['sitelinkpublished'] = 'Link published';
$string['sitename'] = 'Name';
$string['sitenamepublished'] = 'Publish name';
$string['siteoperation'] = 'Site operation';
$string['sitenotpublished'] = 'Not published';
$string['siteprivacy'] = 'Privacy';
$string['siteregconfcomment'] = 'Your site needs a final confirmation on {$a} (in order to avoid spam on {$a})';
$string['siteregistration'] = 'Site registration';
$string['siteregistrationupdated'] = 'Site registration updated';
$string['siteupdated'] = '{$a} has been updated successfully.';
$string['siteurl'] = 'Site URL';
$string['specifichub'] = 'Specific hub';
$string['specifichubpublicationdetail'] = 'You can publish on another hub.';
$string['specifichubregistrationdetail'] = 'You can register to other hub.';
$string['statistics'] = 'Statistics privacy';
$string['subject'] = 'Educational subject';
$string['subject_help'] = 'To narrow your search to courses about a particular subject, choose one from this list.';
$string['subjects'] = 'Subjects';
$string['tags'] = 'Tags';
$string['trustme'] = 'Trust';
$string['unlistedurl'] = 'Unlisted hub URL';
$string['unprioritise'] = 'Unprioritise';
$string['untrustme'] = 'Untrust';
$string['updatesite'] = 'Update registration on {$a}';
$string['url'] = 'hub URL';
$string['usersnumber'] = 'Number of users ({$a})';
$string['visibility'] = 'Visibility';
$string['visibility_help'] = 'Display visible courses, invisible courses or both at the same time.';
$string['visibilityall'] = 'Any';
$string['visibilityyes'] = 'Visible';
$string['visibilityno'] = 'Not visible';
$string['wrongurlformat'] = 'Bad URL format';
$string['wronghubpassword'] = 'Wrong hub password, press continue and try again.';
$string['yeswithmoodle'] = 'Yes, with Moodle';
$string['yeswithoutmoodle'] = 'Yes, without Moodle';

