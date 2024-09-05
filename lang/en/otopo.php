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
 * Strings for the 'fr' language package.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Otopo';
$string['modulename'] = 'Otopo';
$string['modulenameplural'] = 'Otopo';
$string['modulename_help'] = 'The otopo activity module allows a teacher to create a repository from which students can self-assess at different times. The teacher can comment on each self-assessment and monitor its progress.';
$string['modulename_link'] = 'mod/otopo/view';
$string['pluginadministration'] = 'Otopo settings';

/************
 * General. *
 ************/
$string['stringlimit255'] = 'This field is limited to 255 characters.';
$string['paramsbtn'] = 'Change parameters';

$string['edit'] = 'Edit';

$string['start'] = 'Start';
$string['pending'] = 'Pending';
$string['end'] = 'End';
$string['closed'] = 'Closed';

$string['lastmodification'] = 'Last modification';

$string['allowsubmissionfromdate'] = 'Allow answers from';
$string['allowsubmissiontodate'] = 'Allow answers to';
$string['allowsubmissiondateerror'] = 'Closing date cannot be earlier than opening date';

$string['nosession'] = 'No session';

$string['comment'] = 'Comment';
$string['teachercommentglobal'] = 'Overall comment from my teacher';

$string['exportascsv'] = 'Export as CSV';
$string['print'] = 'Print';

// Auto eval strings.
$string['autoevaldesc'] = 'Estimate your progress for this item by clicking on the "+" and "-". If you need more details, you can click on the "...".';
$string['autoevalhelp'] = 'Help';
$string['autoevalyourjustification'] = 'Your justification...';
$string['autoevalnoteachercomment'] = 'Your teacher has not yet commented.';
$string['autoevalmodalsubtitle'] = 'Justify your positioning in a few lines.';
$string['autoevalmodalcontent'] = 'For example:';
$string['autoevalmodalcontent1'] = 'Which tools to use at which stage?';
$string['autoevalmodalcontent2'] = 'What methods are used at what stage?';
$string['autoevaldegree'] = 'Degree';
$string['autoevaldescription'] = 'Description';

$string['fillautoeval'] = 'My self-assessment {$a}';

/*************
 * Mod form. *
 *************/
$string['name'] = 'Name';
$string['description'] = 'Description';
$string['showintro'] = 'Display description on course page';
$string['showteachercomments'] = 'Display teacher comments';

$string['grade'] = 'Grade';

$string['sessionoptions'] = 'Session options';
$string['session'] = 'Session';
$string['session_help'] = 'If **Open session**: students must self-position to the sessions you define. If **Imposed session**: students can self-position as many times as they like, within the session limit defined.';
$string['sessionopen'] = 'Open session';
$string['sessionlimited'] = 'Imposed session';
$string['sessions'] = 'Number of sessions';
$string['limitsessions'] = 'Maximum number of sessions';

$string['disponibility'] = 'Availability';

$string['visual'] = 'Visual of associated statistics';
$string['sessionvisual'] = 'Items and sessions visuals';
$string['cohortvisual'] = 'Cohort visuals';

$string['otopoconditioncompletion'] = 'Otopo completion rules';

$string['completionsubmit'] = 'Display the activity as completed as soon as the Otopo completion rules are met.';
$string['completionsubmit_help'] = 'If **Open session**: the activity is considered completed if at least one self-positioning has been validated by the student. If **Imposed session** : the activity is considered completed if the last session has been filled.';

/**************************
 * Mod form and settings. *
 **************************/
$string['settings'] = 'Settings';
$string['defaultshowteachercomments'] = 'Default value for displaying teacher comments';
$string['defaultshowteachercomments_desc'] = 'Allows you to change the default value for displaying teacher comments when creating the activity.';
$string['defaultgradeonlyforteacher'] = 'Default value to hide note from students';
$string['defaultgradeonlyforteacher_desc'] = 'Allows you to change the default value for hiding the note from students when creating the activity.';
$string['defaultsessionvisual'] = 'Default value for visual selection of items and sessions';
$string['defaultsessionvisual_desc'] = 'Allows you to change the default value for the visualization of items and sessions when creating the activity.';
$string['defaultcohortvisual'] = 'Default value for cohort visuals';
$string['defaultcohortvisual_desc'] = 'Allows you to change the default value for the cohort display when creating the activity.';
$string['defaultsessions'] = 'Default number of sessions';
$string['defaultsessions_desc'] = 'Allows you to change the default value for the number of sessions when creating the activity with **Open session**.';
$string['defaultlimitsessions'] = 'Default session limit';
$string['defaultlimitsessions_desc'] = 'Allows you to change the default value for the number of sessions limit when creating the activity.';
$string['defaultsessionscalendar'] = 'Sessions calendar';
$string['defaultsessionscalendar_desc'] = 'Change the default behavior of the session calendar.';

$string['gradeonlyforteacher'] = 'Visible only to teachers';
$string['stackedbar'] = 'Stacked histogram';

/***************
 * Reset form. *
 ***************/
$string['deleteotopos'] = 'Delete all user self-assessments';
$string['deletegrader'] = 'Delete all teacher notes and comments';

/**************
 * Main tabs. *
 **************/
$string['menuconsult'] = 'View reports';
$string['menupreview'] = 'Preview';
$string['menuparams'] = 'Settings';
$string['menusessions'] = 'Sessions';
$string['menugrids'] = 'Grids';
$string['menutemplates'] = 'Templates';
$string['menuindividualreport'] = 'Individual report';
$string['menugroupreport'] = 'Group report';
$string['menusession'] = 'Session';
$string['menugrid'] = 'Grid';

/*******************
 * View fill page. *
 *******************/
$string['fill'] = 'Self-assessment';
$string['fillintro'] = 'You are about to begin your self-assessment with OTOPO. You\'ll find the general grid you\'ll use throughout this activity by clicking on the "My general evaluation grid" icon above. The evaluation elements you will be presented with are a collection of items with several levels of assessment. It\'s up to you to estimate the level you\'ve reached in your self-assessments.';
$string['fillencouragement'] = 'All the best!';
$string['fillmyprogression'] = 'My progress';
$string['fillmyevolution'] = 'My evolution';
$string['fillfrom'] = 'from';
$string['fillto'] = 'to';

$string['activityclosed'] = 'Closed activity.';
$string['nosessionavailable'] = 'No self-assessment session available';

// Evaluate page.
$string['autoevalassessing'] = 'I\'m self-assessing';
$string['autoevaljustify'] = 'I justify my decision';
$string['autoevalcomments'] = 'My teacher\'s comments';

$string['validate'] = 'Validate';
$string['validate_help'] = 'Your changes are automatically saved. If you close you will not be able to modify your self-assignment on this session.';

// Evolution page.
$string['evolutionchoosevisual'] = 'Chart type';
$string['radar'] = 'Radar';
$string['bar'] = 'Bar';

$string['evolutionperitem'] = 'See my evolution per item';
$string['chooseall'] = 'Choose all';

$string['evolutionyouchoosefor'] = 'You have chosen for';

$string['autoevalsvalidated'] = 'Validated self-assessments';

// General grid.
$string['fillmygrid'] = 'My general assessment grid';

/******************
 * Sessions page. *
 ******************/
$string['sessionssettings'] = 'Self-assessment session settings';

// Sessions form.
$string['sessionname'] = 'Name {no}';
$string['sessioncolor'] = 'Color {no}';
$string['sessionallowsubmissionfromdate'] = 'Opening date {no}';
$string['sessionallowsubmissiontodate'] = 'Closing date {no}';
$string['sessiondelete'] = 'Delete';
$string['sessionadd'] = 'Add';

/***************
 * Grids page. *
 ***************/
$string['gridcreateitem'] = 'Create/Modify the grid';
$string['gridcreatefromtemplate'] = 'Create from a template';
$string['gridimportcsv'] = 'Import a CSV';

// Create/Edit grid.
$string['itemadditem'] = 'Add an item';
$string['itemdeleteitem'] = 'Delete the item';
$string['itemchooseitemcolor'] = 'Choose the item\'s color';
$string['itemitem'] = 'Item';
$string['itemdegree'] = 'Degree';
$string['itemadddegree'] = 'Add a degree';
$string['itemdeletedegree'] = 'Delete the degree';
$string['itemdegreegrade'] = 'Degree grade';
$string['itemduplicateitem'] = 'Duplicate the item';

$string['saveastemplate'] = 'Save as template';
$string['saveitems'] = 'Save the grid';

// Template form.
$string['template'] = 'Template';
$string['templatename'] = 'Template name';
$string['templatechoosetemplate'] = 'Choose a template';

/*******************
 * Templates page. *
 *******************/
// Manage templates.
$string['returntolist'] = 'Back to list';

/*****************
 * Grading page. *
 *****************/
$string['viewreport'] = 'View all reports';

$string['validated'] = 'Validated';
$string['notvalidated'] = 'Not validated';
$string['notevaluated'] = 'Not evaluated';

// Grading form.
$string['autoeval'] = 'Overall comment';

/*****************
 * Reports page. *
 *****************/
$string['nocomments'] = 'No comment';
$string['sessionscomments'] = 'Session comments';
$string['studentdistributionbyitem'] = 'Distribution of students by item';

// Reports filters.
$string['choosesession'] = 'Choose a session';

/***********
 * Events. *
 ***********/
$string['activityviewed'] = 'Activity viewed';
$string['activityupdated'] = 'Modified activity';
$string['sessionclosed'] = 'Closed session';
$string['sessionsaved'] = 'Saved session';

/*****************
 * Capabilities. *
 *****************/
$string['otopo:addinstance'] = 'Add an Otopo activity';
$string['otopo:view'] = 'See the Otopo activity';
$string['otopo:managetemplates'] = 'Manage templates';
$string['otopo:admin'] = 'Manage the activity';
$string['otopo:exportresults'] = 'Export results';
$string['otopo:fill'] = 'Fill the activity';
$string['otopo:grade'] = 'Grading self-assessments';
$string['otopo:receivenotifications'] = 'Receive notifications';

/*******************************
 * Privacy API implementation. *
 *******************************/
$string['privacy:metadata:otopo_user_otopo'] = 'List of otopo instances.';
$string['privacy:metadata:otopo_user_otopo:userid'] = 'The user ID to whom the information is associated with.';
$string['privacy:metadata:otopo_user_otopo:session'] = 'The related session ID.';
$string['privacy:metadata:otopo_user_otopo:item'] = 'The related item ID.';
$string['privacy:metadata:otopo_user_otopo:degree'] = 'The related degree ID.';
$string['privacy:metadata:otopo_user_otopo:justification'] = 'The justification provided by the user.';
$string['privacy:metadata:otopo_user_otopo:lastmodificationdate'] = 'The last time the otopo instance was modified.';
$string['privacy:metadata:otopo_user_otopo:teacher_comment'] = 'Teacher\'s comment.';

$string['privacy:metadata:otopo_user_valid_session'] = 'List of valid user sessions.';
$string['privacy:metadata:otopo_user_valid_session:userid'] = 'The user ID to whom the session is related.';
$string['privacy:metadata:otopo_user_valid_session:otopo'] = 'The related otopo instance ID.';
$string['privacy:metadata:otopo_user_valid_session:session'] = 'The session ID.';

$string['privacy:metadata:otopo_grader'] = 'List of grades given by teachers.';
$string['privacy:metadata:otopo_grader:userid'] = 'The user ID to whom the grade is related.';
$string['privacy:metadata:otopo_grader:session'] = 'The related session ID.';
$string['privacy:metadata:otopo_grader:otopo'] = 'The related otopo instance ID.';
$string['privacy:metadata:otopo_grader:comment'] = 'Teacher\'s comment.';
$string['privacy:metadata:otopo_grader:grade'] = 'The grade assigned to the user by the teacher.';
