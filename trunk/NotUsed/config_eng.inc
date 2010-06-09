<?php
session_start();
//
// This seems as sane a place as any to mention that the cvs for this
// set of scripts is in arch:/data/arch/cvs
//

error_reporting(E_ALL ^ E_NOTICE);

// these people have full edit permissions

$allowedusers = array ( 'rwp12', 'rlt23', 'jm367', 'jyj21', 'ar346', 'mrj35' );

// these people can view the latest year when it's in flux

$fluxusers = array ( 'rwp', 'rlt23', 'jm367', 'jyj21', 'rwp12',
              'nc229',
              'apd1',
              'wim1',
              'jmm1',
              'jw12',
              'rjm50',
              'sdg13',
              'mrj10',
              'cyb1',
              'mjg1000',
              'sjy11',
              'dw304',
              'tph1',
              'ar346');

$email_recipient = "mrj35@eng.cam.ac.uk";

// NB look in db.inc for the read/write db passwords ...

// $titletext is the identification info that appears in the title bar for
// all pages

$titletext = 'Dept. of Engineering - Teaching Office Database';

// $description is the description of the database that appears in the middle
// of the banner across the top of each page

$description = 'Teaching Office Database';

// $department is the description of the responsible department that appears
// on the right hand side of the banner across the top of each page

$department = 'Department of <br>Engineering';

// $officename is the description of who people should be contacting about
// things relating to the duties database, and emailing confirmation to

$officename = 'the Teaching Office';

// this next is what appears in the front page

$index_welcome = '

<p> Welcome to the teaching office database.  The teaching duties database does not contain information about when or where the
teaching takes place; please refer to the timetables for this:
<a href="http://www.eng.cam.ac.uk/teaching/rotas/Mich-Full-Lec.pdf" target="_blank">
Michaelmas,</a>
<a href="http://www.eng.cam.ac.uk/teaching/rotas/Lent-Full-Lec.pdf" target="_blank">
Lent</a> and
<a href="http://www.eng.cam.ac.uk/teaching/rotas/Easter-Full-Lec.pdf" target="_blank">
Easter.</a></p>
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
course, the engineering area, the type of job or any combination of these.
</li>
</ul>
<p>
Please note that this database is still under development.
If you have any comments or suggestions, please email Richard Prager
(rwp@eng) with anything related to the functionality of
the database, and Rachael Tuley (rlt23@eng) with anything
to do with the content.
</p>
<p>
You will notice that there is mention of teaching duties at the Judge
Business School at the end of all the personal job lists.  Please ignore this
at the moment as it is an experimental facility that we are still working
on.
</p>'; // end $index_welcome



$timetable_message = '<p>
	Please see the timetables for when and where the teaching is scheduled.  <!--:
	<a href="http://www.eng.cam.ac.uk/teaching/rotas/Mich-Full-Lec.pdf" target="_blank">
	Michaelmas 06,</a>
	<a href="http://www.eng.cam.ac.uk/teaching/rotas/Lent-Full-Lec.pdf" target="_blank">
	Lent 07</a> and 
	<a href="http://www.eng.cam.ac.uk/teaching/rotas/Easter-Full-Lec.pdf" target="_blank">
	Easter 07 (draft).--></a>
	</p>';
	
////////////////  JOBS filter CONFIGURATION  ///////////////////////////////////////////
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

/*
MJ, Jan 2009: It is suggested that these arrays, which depend on each other, should be
replaced with little objects (as data structures) for storing this information:
$year_obj = new CriterionObject();
$year_obj->title = 'Year';
$year_obj->prefix = 'cyr';
etc...

and then:
$AllCritObjs = array();
$AllCritObjs['year'] = $year_obj;
etc...
*/

// Titles for the filtering criterion:
$all_titles = array('year' => 'Year',
                'term' => 'Term',
                'type' => 'Job type',
                'group' => 'Group');

// Prefixes to use in naming HTML variables in forms, for each criterion
$all_prefixes = array('year' => 'cyr',
                  'term' => 'trm',
                  'type' => 'jty',
                  'group' => 'grp');
                  

// Column names (in the jobs_* tables), for each criterion:
$all_column_names = array('year' => 'year',
                          'term' => 'term',
                          'type' => 'type',
                          'group' => 'prgroup');

// categories for each filtering criterion:
$all_categories = array('year' => array('1','2','3','4'),
                        'term' => array('M','L','E'),
                        'type' => array('L','C','E','A', 'P'),
                        'group' => array('A','B','C','D','E','F','G','M','X')
                        );

// Captions to display on-screen for each of the filtering criteria:
$all_captions = array('year' => array('Year 1','Year 2','Year 3','Year 4'),
                      'term' => array('Michaelmas','Lent','Easter'),
                      'type' => array('Lectures','Coursework','Examining','Administration', 'Preparation'),
                      'group' => array('GrpA','GrpB','GrpC','GrpD','GrpE','GrpF','GrpG','Maths+Comp','Multi-Disc')
                     );

// what to display for the special 'other' categories for each of the filtering criteria:
$all_other_cats = array('year' => 'Other years',
                        'term' => 'Other terms',
                        'type' => 'Other job types',
                        'group' => 'Other groups');
                        
// END of array definitions for filtering criteria

// move to config.inc sometime:
// REMEMBER the slash at the end!
$csv_output_path = "C:\\webserver\\TODB\\app\\tod1.1\\static_csv\\";
$csv_url_path = "/todb/app/tod1.1/static_csv/";


//////////////// END CONFIGURATION  ////////////////////////////////////////

?>
