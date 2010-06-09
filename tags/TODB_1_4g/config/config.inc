<?php
session_start();
// This is the main configuration file for the TODB system
//

error_reporting(E_ALL ^ E_NOTICE);

// The name of the user if this software is running on and accessed from localhost (i.e. a test installation)
$default_local_user = 'gen1';

// these people have full edit permissions
// ac690 - Anne Clarke
// an10007 - Avi Naim
// aewp2 - Amyas Phillips
// vla22 - Verity Allan
$allowedusers = array ( 'gen1', 'gen2', 'ac690', 'an10007', 'aewp2', 'vla22' );

// AEC Set $usertestmode to allow adminusers to view screens as if they are non-admin users
// In normal use this should remain unset
$usertestmode = '';
//$usertestmode = 'TRUE';

// these people can view the latest year when it's in flux
$fluxusers = array ( 'gen1', 'gen2', 'ac690', 'an10007', 'aewp2', 'val22');

// demonstration admin users:
// These people are allowed to view/edit/add/remove demonstration-related records only:
$demonstration_admin_users = array ('test1');

// email address of responsible person in the teaching office, to which system-generated emails are sent.
$email_recipient = "gen1@yourdept.ac.uk";
// something like 'cam.ac.uk'; the email address domain.  So emails could be sent to someone@institutional_domain_name
$institutional_domain_name = 'cam.ac.uk';


// NB look in db.inc for the read/write db passwords ...

// $titletext is the identification info that appears in the title bar for
// all pages

$titletext = 'Generic Dept - Teaching Duties Database';

// $description is the description of the database that appears in the middle
// of the banner across the top of each page

$description = 'Teaching Duties Database';

// $department is the description of the responsible department that appears
// on the right hand side of the banner across the top of each page

$department = 'Generic Department';

// $officename is the description of who people should be contacting about
// things relating to the duties database, and emailing confirmation to

$officename = 'the Teaching Duties Office';

// this next is what appears in the front page

$index_welcome = '

<p> Welcome to the teaching office database.  The teaching duties database does not contain information about when or where the
teaching takes place; please refer to the timetables for this.</p>
<p>The three main ways to access this database are:
</p>
<ul>
<li>To review the list of your teaching duties and confirm that they
are correct to the Teaching Office, please press <I>View list of my teaching duties</I>.
<li>If you click on <I>View list of people</I> you will see a list of the
staff involved in teaching.  The buttons near the top of the page can
be used to see staff grouped by Division or by the first letter of
their surname.  The buttons on the left, labelled with surnames, can be used to 
display the jobs currently allocated to each person.  To get back to the
Home page, click on the button in the top right-hand corner.
</li>
<li>
If you click on <I>View list of jobs</I> you will see a list of the 
jobs in the database.  You can change which jobs are displayed by adding or
removing the ticks in the various boxes near the top of the page and
then clicking the <I>Filter</I> button.  You can filter by the year of the 
course, the subject area, the type of job or any combination of these.
</li>
</ul>
<p>
If you have any comments or suggestions, please email Caret Support
(todb-support@caret.cam.ac.uk) with anything related to the functionality of
the database or its content.
</p>

'; // end $index_welcome

$timetable_message = '<p>
	Please see the timetables for when and where the teaching is scheduled.
	</p>';
	
	
	
// DIVISIONS (SUBJECTGROUPS!)
// --------------------------
$division_longnames =  array('1'=>'Materials','2'=>'Electrical','3'=>'Information','4'=>'Civil','5'=>'Manufacturing',);
$division_shortnames = array('1'=>'Mat','2'=>'Elect','3'=>'Info','4'=>'Civil','5'=>'Man',);



/////////////  JOBS filter CONFIGURATION  ///////////////////////////////////////////
// -------------------------------------------------------------------------
// MJ: set up arrays for each of the filter criteria to facilitate more
// elegant calls:

// NOTE: these values should be fetched from the database at some stage.
/* IDEA: when the user first accesses the site and sets a new session ID,
   the system should access the database, download the configuration from
   the table:

   | criterion | title | prefix | column_name | categories | captions | other_category |
   +-----------+-------+--------+-------------+------------+----------+----------------+
   | year      | Year  | cyr    | year        | 1, 2, 3, 4 | Year1,etc| Other years    |
   | term      |

   ...and generate a session file containing the database table
   data expressed in the form below.  This file can then be included into
   the PHP file and for the rest of the session will read the variables
   as local PHP vars (fast!) instead of database calls all the time.
   The user will have to log out and in again if the config changes.
*/

$all_titles = array(
                'year' => 'Year',
                'term' => 'Term',
                'type' => 'Job type',
                'group' => 'Subject');

$all_prefixes = array(
                  'year' => 'cyr',
                  'term' => 'trm',
                  'type' => 'jty',
                  'group' => 'grp');

$all_column_names = array('year' => 'year',
                          'term' => 'term',
                          'type' => 'type',
                          'group' => 'prgroup');

$all_categories = array('year' => array('1','2','3'),
                        'term' => array('M','L','E'),
                        'type' => array('L','P','E','A', 'S', 'V'),
                        'group' => array_keys($division_shortnames)
                        );

$all_captions = array(                      
                      'year' => array('I - Year 1','II - Year 2','III - Year 3'),
                      'term' => array('Michaelmas','Lent','Easter'),
                      'type' => array('Lectures','Practicals','Examining','Administration', 'Seminars','Supervision'),
                      'group' => array_values($division_shortnames)
                     );

$all_other_cats = array(
                        'year' => 'Other years',
                        'term' => 'Other terms',
                        'type' => 'Other job types',
                        'group' => 'Other subjects');

// END of array definitions for filtering criteria


// CSV output directories
// :::::::::::::::::::::::
$csv_output_path = "./static_csv/";
$csv_url_path = './static_csv/';

// Points Calculator:
// If the points calculator should be displayed, configure here:
// :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// show the calculator link in the edit screen?  True or false.
$show_points_calculator = true;
// description of independent variable - e.g. 'answers marked' or 'students registered'
$calculator_x_desc = "students supervised"; //"answers marked";

// Timetabling:
// ------------
// These need to match the start times in the timeslots field in the jobs table
// any start times not in this list will be omitted from the timetable.  It is the
// lesser of two weevils, I promise!   (because of issues with ordering, mainly)
// the order of start times needs to be correct here too.
$tt_start_times = array('9', '10', '11', '11.3', '12', '2', '3', '4');
// which days of the week to consider
$tt_days_of_week = array('M', 'Tu', 'W', 'Th', 'F');
// which years/parts to consider?
$tt_yearlist = array('1', '2', '3');

// Generic Page View
// ::::::::::::::::::
// List of available page options to display in the generic operation box visible on home page in admin mode
$generic_operations = array('view_people.php','view_jobs.php','view_units.php','view_faculty_summary.php', 'view_points_formulae.php','view_student_counts.php', 'view_timetable.php' );
// Corresponding list of captions for each operation
$generic_operation_names = array('View People','View Jobs','View Units','Show stint summary', 'View Points Formulae', 'View students per course', 'View Timetable');

// Raven user file name:
// This list of users is CONSTRUCTED, so do not overwrite as changes will be lost:
$raven_user_filename = 'TODB_Raven_Users.txt';


//////////////// END CONFIGURATION  ////////////////////////////////////////

?>
