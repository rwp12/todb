<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/people.inc');
require('locks.inc');

$yearval = $_REQUEST["yearval"];

$tablename = 'summary';
$tablething = 'Summary';

// temporary - disable the Edit button
$isadminuser = FALSE;

require('common1.inc');

// Before we construct any SELECT statements, we want to create a string
// that defines the columns in the order we process them, rather than
// relying on the table columns staying in the same order in the database.
// We use this string instead of "SELECT *" throughout ...

$select_string = "";
$separator = "";
foreach (array_keys($jobitems) as $key) {
   $select_string .= $separator . "jobs_" . $yearval . "." . $jobitems[$key];
   $separator = ", ";
}

// create list of subject groups from "subjectgroup" table
/*
$gpquery = 'SELECT * from subjectgroup';
$gpresult = mysql_query($gpquery, $dbread) or die('subjectgroup query failed: ' . mysql_error());
// and a list of divisions from the "division" table
$divquery = 'SELECT * from division';
$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());
*/
// start the output process here
require('config/header.inc');
// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
require('view_jobs_java.inc');
// and finish off with the </head> and the top of page decoration here ...
require('config/top.inc');

require('common2.inc');

////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////


// =========================================================================================================
// Note:
// MJ 20090415:
// This page is borrowed from JBS; with minimal changes, should work for the English Faculty's
// stint point summary.
// 'type' => array('L','C','G','S', 'U', 'O'),
// 'type' => array('Lectures', 'Classes', 'Graduate supervision', 'Seminars',
//                                      'College supervision', 'College other duties'),
// =========================================================================================================




// MJ: different approach
// 2009-02-16

/*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Get sum (teaching hours = 'L') by division:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
select p.division,  sum(hours) as TotalPoints from jobs_$yearval j inner join people_$yearval p
on j.uname = p.uname  where j.type='L' group by p.division;
+----------+-------------+
| division | TotalPoints |
+----------+-------------+
| B        |       220.0 |
| F        |       412.5 |
| H        |       294.0 |
| M        |       162.0 |
| O        |       298.0 |
| S        |       447.0 |
| T        |        64.0 |
+----------+-------------+

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Get sum (supervision points = 'S') by division:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  select p.division,  sum(points)/100 as TotalPoints from jobs_$yearval j inner join people_$yearval p
  on j.uname = p.uname  where j.type='S' group by p.division;
  +----------+-------------+
  | division | TotalPoints |
  +----------+-------------+
  | NULL     |      3.0000 |
  | B        |      9.0000 |
  | F        |      6.0000 |
  | H        |      9.0000 |
  | M        |     12.0000 |
  | O        |     22.5000 |
  | S        |     21.0000 |
  | T        |      1.5000 |
  +----------+-------------+

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Get sum (school duties = 'D') by division:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
School duties = explicit duties (marked as 'D')
               + Course Leadership admin ('A')
               + Examining ('A')
               
  Examiner points:
  ----------------
  select s.longname, sum(points)/100 as ExamPoints from jobs_$yearval as j inner join people_$yearval as p
  on j.uname  = p.uname inner join subjectgroup as s
  on s.letter = p.division where j.type='A' and instr(j.name, 'Examiner:') group by p.division;

  simpler:
  select p.division, sum(points)/100 as ExamPoints from jobs_$yearval as j inner join people_$yearval as p
  on j.uname  = p.uname  where j.type='A' and instr(j.name, 'Examiner:') group by p.division;

  Course Leadership Points
  ------------------------
  select p.division, sum(points)/100 as LeadPoints from jobs_$yearval as j inner join people_$yearval as p
  on j.uname  = p.uname  where j.type='A' and instr(j.name, 'Leader:') group by p.division;
 
  Examiner + Leadership points:
  -----------------------------
  select p.division, sum(points)/100 as LeadPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname
  = p.uname where j.type = 'A' group by division;


All combined:
~~~~~~~~~~~~~
  select p.division, sum(points)/100 as LeadPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname
  = p.uname where j.type = 'D' or (j.type = 'A' and instr(upper(j.name), 'EXAM'))
  or  (j.type = 'A' and instr(upper(j.name), 'LEAD')) group by division;

  NOTE: If this query is used:
  For an admin task to be counted as a school duty, it MUST feature either 'exam' or 'lead' in the name of
  the job and should be marked 'A'.

  To make it broader, the query should be changed to:

select p.division, sum(points)/100 as LeadPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname
= p.uname where j.type in ( 'D', 'A') group by division;



~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Complete query for getting Teaching Hours, Supervision points, School Duty points:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
select teaching.division, teaching.LectHours, supervision.SuperPoints, duties.DutiesPoints from (select p.division,  sum(hours) as LectHours from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname  where j.type='L'  group by p.division) as teaching inner join (select p.division,  sum(points)/100 as SuperPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname  where j.type='S' group by p.division) as supervision on teaching.division = supervision.division inner join (select p.division, sum(j.points)/100 as DutiesPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type in ( 'D', 'A') group by p.division) as duties on teaching.division = duties.division group by teaching.division;

select s.longname as Division, teaching.division as Divi, IFNULL(teaching.LectHours, 0) as TeachingHours, IFNULL(supervision.SuperPoints, 0) as SupervisionPoints, IFNULL(duties.DutiesPoints, 0) as DutiesPoints, IFNULL(other.OtherPoints, 0) as OtherPoints from
(select p.division,  sum(hours) as LectHours from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname  where j.type='L'  group by p.division) as teaching
left join (select p.division,  sum(points)/100 as SuperPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname  where j.type='S' group by p.division) as supervision on teaching.division = supervision.division
left join (select p.division, sum(j.points)/100 as DutiesPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type in ( 'D', 'A') group by p.division) as duties on teaching.division = duties.division
left join (select p.division, sum(j.points)/100. as OtherPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type = 'O' group by p.division) as other on teaching.division = other.division
inner join subjectgroup as s on teaching.division = s.letter
group by teaching.division;
Yields:
+----------------------------------+------+---------------+-------------------+--------------+-------------+
| Division                         | Divi | TeachingHours | SupervisionPoints | DutiesPoints | OtherPoints |
+----------------------------------+------+---------------+-------------------+--------------+-------------+
| Business & Management Economics  | B    |         220.0 |            9.0000 |     103.5000 |      0.0000 |
| Finance & Accounting             | F    |         412.5 |            6.0000 |     124.5000 |      0.0000 |
| Human Resources & Organisations  | H    |         294.0 |            9.0000 |     123.5000 |     20.0000 |
| Management Science               | M    |         162.0 |           12.0000 |      82.5000 |      0.0000 |
| Operations, Information & Techno | O    |         298.0 |           22.5000 |     133.5000 |      0.0000 |
| Strategy & Marketing             | S    |         447.0 |           21.0000 |      67.0000 |     32.0000 |
| Joint Appointments CUED          | T    |          64.0 |            1.5000 |       0.0000 |      0.0000 |
+----------------------------------+------+---------------+-------------------+--------------+-------------+



*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////
// As the subjectgroup table has been removed, it is necessary to insert the names of the divisions
// manually.  This is easiest done as follows:
/////////////////////////////////////////////////////////////////////////////////////////////////
// list of divisions=>division names
global $division_longnames;
// the query string fragment:
$div_lookup = '';
// the number of closing brackets to add (this avoids recursion)
$brackets = 0;
foreach ($division_longnames as $dvl_key => $dvl_val)
{
    $div_lookup .= "IF(everything.division='$dvl_key', '$dvl_val', ";
    $brackets++;
}
// if there is no division match, call it 'other'
$div_lookup .= "'Other'";
// now add the closing brackets:
for ($i=0; $i<$brackets; $i++)
{
    $div_lookup .= ')';
}

// Which produces something like:
// ::::::::::::::::::::::::::::::
/*IF(everything.division='B', 'Business and Management Economics',
    IF(everything.division='F', 'Finance and Accounting',
       IF(everything.division='H', 'Human Resources Organisation',
          IF(everything.division='M', 'Management Science',
             IF(everything.division='O', 'Operations and IT',
                IF(everything.division='S', 'Strategy and Management',
                   IF(everything.division='T', 'Joint Appointments CUED',
                      IF(everything.division='U', 'Outsourced',
                         IF(everything.division='V', 'Postdocs & PhDs & TA & RA ', 'Other')))))))))
*/
// I apologise for this.  I admit that it is a bit of a compromise.
// On the good side, notice that no recursion was necessary?  Isn't that impressive?  No?  Ok...
/////////////////////////////////////////////////////////////////////////////////////////////////


// 'L','C','G','S', 'U', 'O'
$query = // get name of division and the number of points supplied,
         //"select s.longname as 'Subject Group', FORMAT(QuotaSupply, 1) as 'Points Supplied', ".
         //"select everything.division as `Division`, FORMAT(QuotaSupply, 1) as 'Points Supplied', ".
         "select $div_lookup as `Division`, FORMAT(QuotaSupply, 1) as 'Points Supplied', ".
         // total demand (calculated  as a whole)
         " FORMAT(everything.AllPoints, 1) as 'Total Demand', ".
         // the difference between the two
         " FORMAT(QuotaSupply - everything.AllPoints, 1) as 'Difference', ".
         // the sum of each category of points demand -> Total Demand
         //"FORMAT(IFNULL(teaching.LectHours, 0) + IFNULL(supervision.SuperPoints, 0) + IFNULL(duties.DutiesPoints, 0) + IFNULL(other.OtherPoints, 0) + IFNULL(SabbaticalPoints, 0) + IFNULL(buyoutoffset.BuyoutOffsetPoints, 0), 1) as 'Total Demand', ".
         // Teaching hours, supervision points, etc etc
         " FORMAT(IFNULL(teaching.LectHours, 0), 1) as 'Teaching Hours', FORMAT(IFNULL(supervision.SuperPoints, 0), 1) as 'Supervision Points', FORMAT(IFNULL(duties.DutiesPoints, 0), 1) as 'Duties Points', FORMAT(IFNULL(other.OtherPoints, 0), 1) as 'Other Points', FORMAT(IFNULL(SabbaticalPoints, 0), 1) as 'Sabbatical Points', FORMAT(IFNULL(buyoutoffset.BuyoutOffsetPoints, 0), 1) as 'Buyout/Offset Points' ".
         // get the sum of all points in categories addressed (L - lecturing, A - admin, D - duties, O - Other, S - supervision, I - sabbatical, V - buyout/offset)
         "from (select p.division, sum(points)/100. as AllPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.deleted = FALSE and j.type in ('L','A','D','O','S','I','V') group by p.division) as everything ".
         // select the hours from the lecturing jobs
         "left join(select p.division, sum(hours) as LectHours from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type='L' and j.deleted = FALSE group by p.division) as teaching on teaching.division = everything.division ".
         // select points from supervising jobs
         "left join (select p.division, sum(points)/100 as SuperPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type='S' and j.deleted = FALSE group by p.division) as supervision on everything.division = supervision.division ".
         // select points from 'School Duties' jobs
         "left join (select p.division, sum(j.points)/100 as DutiesPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type in ( 'D', 'A') and j.deleted = FALSE group by p.division) as duties on everything.division = duties.division ".
         // select points from 'Other' activities
         "left join (select p.division, sum(j.points)/100. as OtherPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type = 'O' and j.deleted = FALSE group by p.division) as other on everything.division = other.division ".
         // select points from Sabbaticals
         "left join (select p.division, sum(j.points)/100. as SabbaticalPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type = 'I' and j.deleted = FALSE group by p.division) as sabbatical on everything.division = sabbatical.division ".
         // select any points buyouts or offsets
         "left join (select p.division, sum(j.points)/100. as BuyoutOffsetPoints from jobs_$yearval as j inner join people_$yearval as p on j.uname = p.uname where j.type = 'V' and j.deleted = FALSE group by p.division) as buyoutoffset on everything.division = buyoutoffset.division ".
         // get the quotas (supply of points)
         "left join (select division, sum(quota) as QuotaSupply from people_$yearval group by division) as quotas on quotas.division = everything.division ".
         // and provide the list of long division names
         ";";
         //"inner join subjectgroup as s on everything.division = s.letter group by everything.division;";

// Show the table:
echo "<h3>Workload allocations by subject group</h3>";
echo "<P>\n";
ShowTable($dbread, $query, "unit_table");
echo "</P>\n";

////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Complete query for getting Teaching Hours, Supervision points, School Duty points
etc by programme rather than subject group:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////

$query = // get name of course
         "select everything.course as 'Programme',  ".
         // total demand (calculated  as a whole)
         " FORMAT(everything.AllPoints, 1) as 'Total Demand', ".
         // Teaching hours, supervision points, etc etc
         " FORMAT(IFNULL(teaching.LectHours, 0), 1) as 'Teaching Hours', FORMAT(IFNULL(supervision.SuperPoints, 0), 1) as 'Supervision Points', FORMAT(IFNULL(duties.DutiesPoints, 0), 1) as 'Duties Points', FORMAT(IFNULL(other.OtherPoints, 0), 1) as 'Other Points', FORMAT(IFNULL(SabbaticalPoints, 0), 1) as 'Sabbatical Points', FORMAT(IFNULL(buyoutoffset.BuyoutOffsetPoints, 0), 1) as 'Buyout/Offset Points' ".
         // get the sum of all points in categories addressed (L - lecturing, A - admin, D - duties, O - Other, S - supervision, I - sabbatical, V - buyout/offset)
         "from (select course, sum(points)/100. as AllPoints from jobs_$yearval as j where j.deleted = FALSE and j.type in ('L','A','D','O','S','I','V') group by j.course) as everything ".
         // select the hours from the lecturing jobs
         "left join(select course, sum(hours) as LectHours from jobs_$yearval as j where j.type='L' and j.deleted = FALSE group by j.course) as teaching on teaching.course = everything.course ".
         // select points from supervising jobs
         "left join (select course, sum(points)/100 as SuperPoints from jobs_$yearval as j where j.type='S' and j.deleted = FALSE group by j.course) as supervision on everything.course = supervision.course ".
         // select points from 'School Duties' jobs
         "left join (select course, sum(j.points)/100 as DutiesPoints from jobs_$yearval as j where j.type in ( 'D', 'A') and j.deleted = FALSE group by j.course) as duties on everything.course = duties.course ".
         // select points from 'Other' activities
         "left join (select course, sum(j.points)/100. as OtherPoints from jobs_$yearval as j where j.type = 'O' and j.deleted = FALSE group by j.course) as other on everything.course = other.course ".
         // select points from Sabbaticals
         "left join (select course, sum(j.points)/100. as SabbaticalPoints from jobs_$yearval as j where type = 'I' and j.deleted = FALSE group by j.course) as sabbatical on everything.course = sabbatical.course ".
         // select any points buyouts or offsets
         "left join (select course, sum(j.points)/100. as BuyoutOffsetPoints from jobs_$yearval as j where j.type = 'V' and j.deleted = FALSE group by j.course) as buyoutoffset on everything.course = buyoutoffset.course ".
         " group by everything.course;";


//echo "<p>$query</p>\n";


// Show the table:
echo "<h3>Workload allocations by programme</h3>";
echo "<P>\n";
ShowTable($dbread, $query, "unit_table");
echo "</P>\n";

////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////


close_db_read();

require('config/footer.inc');

?>






