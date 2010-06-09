<?php

/*
CONFIGURE THE ADDITION OF NEW ACADEMIC YEARS TO THE TODB
::::::::::::::::::::::::::::::::::::::::::MJ, July 2009:

These arrays are lists of columns from tables in the
TODB database.  Insert into these arrays the column
names of data that you would like copied from one
year to the next.

Tables in the TODB are named according to the
following convention:

tablething_yearval

where:
tablething is what the table represents (e.g jobs, points_formulae, people)
yearval is the short description for the academic year (e.g. 2008_09, 2005_06)

This file uses a 2-D array, with the tablething as the array key.  Only
tablething (rather than the full table name) is used, because this applies
to all tables of a particular type (units, people, etc).

$copy_columns[tablething] = array(col1, col3, ..., coln)

e.g.
$copy_columns['units'] = array('uname', 'name');
$copy_columns['people'] = array('uname', 'surname', 'crsid');

::::::::::::::::::::::::::::::::::::::::::::::::::::::::

*/
// columns to carry over from one year to the next in the jobs tables
// The name of the person doing the job is dropped.  Most other info remains.
$copy_columns['jobs'] = array('course', 'year', 'paper', 'prgroup', 'name', 'type', 'hours', 'term', 'venue', 'timeslots', 'points', 'deleted', 'updatetime', 'formula_ref');

// columns to carry over from one year to the next in the people tables
// most of this information stays the same from year to year
$copy_columns['people'] = array('uname', 'crsid', 'engid', 'division', 'title', 'called', 'surname', 'initials', 'quota', 'room', 'PostalAddress', 'phone', 'mobile', 'homephone', 'ice', 'job_title', 'college', 'status', 'deleted', 'OK06', 'updatetime');

// columns to carry over from one year to the next in the units tables
// most units remain the same from year to year, but some might/might not be running
$copy_columns['units'] = array('uname', 'course', 'ordering', 'sgrps', 'name', 'assessmode', 'deleted', 'global');

// columns to carry over from one year to the next in the points_formulae tables
// the formulae are likely to remain largely the same every year
$copy_columns['point_formulae'] = array('Formula_ID', 'F_Name', 'F_Math_Desc', 'n_Multiplier', 'offset');

// columns to carry over from one year to the next in the studentspercourse tables
// the number of students per course changes from year to year
$copy_columns['studentspercourse'] = array('ID', 'coursename');

// --------------------------------------------------------------------------------
// columns to carry over from one year to the next in the editlocks tables
// it is NOT RECOMMENDED to edit this!
$copy_columns['editlocks'] = array('dbname', 'inuse', 'currentuser', 'lastuser');

?>
