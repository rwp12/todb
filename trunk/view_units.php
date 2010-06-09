<?php

//
// TEMPORARY NOTE TO SELF:  example sql to initialise a units table is
// 
// insert into units_2007_08 (uname, sgrps) select paper, replace
//    (group_concat(distinct prgroup separator ""), '+','') from 
//    jobs_2007_08 where ((NOT paper is NULL) && 
//                        (paper REGEXP '^[A-Z0-9]*$') &&
//                        (NOT prgroup is NULL))
//    group by paper;
// 
//
//  JPMG 08jan2008 refactored to only do unit management;  the function
//       of showing all the jobs for each unit has been moved to
//       view_ujobs.php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/units.inc');
require('locks.inc');

if (post_exists('editmodestate') && !post_exists('editmode')) {
   $_POST['editmode'] = $_POST['editmodestate'];
}

$tablename = 'units';
$tablething = 'Unit';
 
require('common1.inc');


// read the groups from the configuration:
global $all_categories;  //'group' => array('D','A','C','V', 'W', 'P1', 'P2', 'P3', 'P4', 'L')
//MJ, Dec 08: build a string of all group letters for queries below:
$all_groups_arr = $all_categories['group'];
$allgroups = implode('', $all_groups_arr);
//echo "<!-- allgroups is |$allgroups| -->";

#
# this deals with the "re-generate the index numbers" requirement
#
if (post_exists('reindex') && $isadminuser && $adminwantstoedit)
{
   open_db_write();
   $reindexquery = "SET @rownum:=0";
   if (!mysql_query($reindexquery))
   {
     $GLOBALS['inform_message'] .= "<p>sql error: <i>".mysql_error()."</i></p>";
   }
   $reindexquery = "UPDATE units_".$yearval." SET ordering=(@rownum:=@rownum+1)*10 ORDER BY ordering";
   if (!mysql_query($reindexquery))
   {
     $GLOBALS['inform_message'] .= "<p>sql error: <i>".mysql_error()."</i></p>";
   }
   close_db_write();
}

// -----------------------------------------------------------------
/*
# this deals with the "auto-create units that ought to exist".
# that is, if there's any jobs whose "paper" doesn't yet exist
# as a unit, create that unit using the initialisation code
# given at the top of this file - ie find all the jobs that
# have that paper, and create the unit with that papername,
# with subjectgroups being the union of the subjectgroups of
# all those jobs.
# */
// -----------------------------------------------------------------
if (post_exists('autocreate') && $isadminuser && $adminwantstoedit)
{
   open_db_write();

   # first we need to really delete any units that have been marked as
   # deleted, else we'll risk trying to create units that already appear
   # to exist ...

   $cleanquery = "DELETE FROM units_".$yearval." WHERE deleted IS TRUE";
   
   if (!mysql_query($cleanquery))
   {
     $GLOBALS['inform_message'] .= "<p>sql error: <i>".mysql_error()."</i></p>";
   }

   # got this one right first time!
   # it's a slight modification of the units initialisation code I gave
   # at the top of this file, so that it works to add new units to an
   # already populated table.  It does this by adding a left join of the
   # units table into the select on the jobs table, keyed on the unit name
   # matching the job's "paper", so that we can add an additional condition
   # that the select only matches if the unit doesn't exist (so shows up
   # as nulls in the join ...)

   # there are three fields specified for insertion that are defined 
   # immediately after the SELECT.  
   # The second creates the list of subject groups that the unit's jobs 
   # were in, by concatenating all the distinct ones (the '+' removal is 
   # because this was sometimes used in the database for a job shared 
   # between two or more units.
   # The third looks for any job names in the unit that begin 
   # "*Leader:, concatenates them, and gets rid of four different
   # common strings found in them, to produce a guessed name for the unit.
   # It occasionally messes up (eg units with multiple "*Leader:" jobs).
   # 

   /*$autocreatequery = "INSERT INTO units_".$yearval." (uname, sgrps, name)
                       SELECT paper, 
                              REPLACE (GROUP_CONCAT(DISTINCT prgroup SEPARATOR \"\"), '+',''), 
                              REPLACE (
                               REPLACE (     
                                REPLACE (
                                 REPLACE (GROUP_CONCAT(DISTINCT (if ((jobs_".$yearval.".name REGEXP \"^[[.asterisk.]]Leader:.*$\"), jobs_".$yearval.".name, \"\")) 
                                                    SEPARATOR \"\"), 
                                         '*Leader: ', '' ), 
                                        ': Coursework only', ''),
                                       ': Exam only', ''),
                                      ': Exam and Coursework', '')
                       FROM jobs_".$yearval." LEFT JOIN units_".$yearval." 
                       ON (jobs_".$yearval.".paper = units_".$yearval.".uname) 
                       WHERE ((NOT paper is NULL) && (paper REGEXP '^[A-Z0-9/\.]*$') && (NOT prgroup is NULL) && (units_".$yearval.".uname IS NULL)) 
                       GROUP BY ordering";
   */
   
   
   // MJ, Sept 2009:
   // simplified this for more general use (above requires Engineering-specific conventions); the user will have to go in
   // afterwards and clean up, although the unit name should provide enough information so that the user knows what is what.
   $autocreatequery =  "insert into units_$yearval (uname, name, sgrps) ".
                       "(select paper, substring(group_concat(DISTINCT trim(a.name)), 1, 50) as UnitName, substring(group_concat(DISTINCT prgroup),1,31) as mygrps ".
                       "from jobs_$yearval as a ".
                       "left join units_$yearval as b ".
                       "on instr(a.paper, b.uname)>0 ".
                       "where b.uname is null ".
                       "and a.paper is not null ".
                       "group by a.paper); ";
   

   //echo "<p>$autocreatequery</p>\n";

   if (!mysql_query($autocreatequery)) {
	$GLOBALS['inform_message'] .= "<p>sql error: <i>".mysql_error()."</i>; Looks like you will have enter units manually!</p>";
   }

   close_db_write();
   
}
// -------------- End of 'if autocreate' section
// -----------------------------------------------------------------


if (post_exists('purgedeleted') && $isadminuser && $adminwantstoedit)
{
   open_db_write();

   # first we need to really delete any units that have been marked as
   # deleted, else we'll risk trying to create units that already appear
   # to exist ...

   $cleanquery = "DELETE FROM units_".$yearval." WHERE deleted IS TRUE";
   
   if (!mysql_query($cleanquery)) {
	$GLOBALS['inform_message'] .= "<p>sql error: <i>".mysql_error()."</i></p>";
   }

   close_db_write();
}


	
// -----------
// determine whether we are going to show a list of units or a list of jobs.
$var_unit_uname = false;    // if this remains false we show units.
if (!post_exists('update')) { 
   $_POST['update'] = 'notset';
}

// this determines the nature of the buttons that common2.inc draws
$deletedjobmode = false;

// create list of subject groups from "subjectgroup" table
/*
$gpquery = 'SELECT * from subjectgroup';
$gpresult = mysql_query($gpquery, $dbread) or die('subjectgroup query failed: ' . mysql_error());

$querydescg = ""; 
$gpnotother = '';
mysql_data_seek($gpresult, 0);
while ($gparray = mysql_fetch_array($gpresult, MYSQL_ASSOC)) {
   $gpletter = $gparray['letter'];
   $gpvar = "gp".$gpletter;
   $gpnotother .= $gpletter;          // building a list of group letters to not match for 'other'
   if (post_exists('prg_'.$gpletter)) {
      $filter .= " || (INSTR(prgroup, '".$gpletter."'))"; $$gpvar = 'checked'; $querydescg .= $gpletter; 
   } else $$gpvar = '';
} */


// have to test for All and Deleted before any-of-ABCDEFGM or else
// they'll hit the strpbrk test.  This is still much cleaner than 
// the "do each case separately" approach ...
// AEC Make sure we ignore any deleted jobs

if ($_POST['update'] == 'All') {
  $unit_query_where = " WHERE ((units_".$yearval.".deleted = FALSE) && (jobs_".$yearval.".deleted = FALSE))";
  $querydesc = 'All';
} 
elseif ($_POST['update'] == 'Deleted') {
  $unit_query_where = " WHERE ((units_".$yearval.".deleted = TRUE) && (jobs_".$yearval.".deleted = FALSE))";
  $querydesc = 'Deleted';
}
/* MJ, Dec 08.  Was:
elseif (strpbrk($_POST['update'], 'ABCDEFGM')) {
   $unit_query_where = " WHERE ((units_".$yearval.".deleted  = FALSE) && instr(sgrps, '".
	$_POST['update'].
      	"'))";
now:
*/
elseif (strpbrk($_POST['update'], $allgroups)) {
   $unit_query_where = " WHERE ((units_".$yearval.".deleted  = FALSE) && (jobs_".$yearval.".deleted = FALSE) && instr(sgrps, '".
	$_POST['update'].
      	"'))";
	$querydesc = $querydesc = "for subject group ".$_POST['update'];

} 
else {
  $unit_query_where = " WHERE ((units_".$yearval.".deleted = FALSE) && (jobs_".$yearval.".deleted = FALSE))";
}

// start the output process here

require('config/header.inc');

// header.inc finishes just before the </head>, so we can stick javascript
// in here ...

require('view_units_java.inc');

require('config/top.inc');
require('common2.inc');

if (post_exists('update')) {
  echo "<input type=\"hidden\" name=\"updatestate\" value=\"";
  echo $_POST['update'];
  echo "\">\n";
}

?>


<!-- MJ, Dec 2008: these are hard-coded, but the code that processes them is (semi-) not...  Hmm. -->
<!-- p>
<input type="Submit" name="update" value="A">
<input type="Submit" name="update" value="B">
<input type="Submit" name="update" value="C">
<input type="Submit" name="update" value="D">
<input type="Submit" name="update" value="E">
<input type="Submit" name="update" value="F">
<input type="Submit" name="update" value="G">
<input type="Submit" name="update" value="M">
<input type="Submit" name="update" value="All" -->
<?php

// Use some kind of central configuration to drive the generation of HTML
// rather than simply hard-coding it:
// -----------------------------------------------------------------------
    echo '<P>';
    foreach ($all_categories['group'] as $catk => $cat)
    {
        echo '<input type="Submit" name="update" title="All units belonging to the subject group '.$all_captions['group'][$catk].'" value="'.$cat.'" />';
    }
    echo '<input type="Submit" name="update" title="All units" value="All" />';
    echo '</P>';
?>



<?php
if ($isadminuser) {
   echo '<input type="Submit" name="update" value="Deleted">';
}

// generate the CSV button:
InsertCSVApparatus();

$csvmode = 0;

// MJ, Dec 2008: if the user clicked on the 'Download as CSV' BUTTON (rather than
// link), the system should indicate that output should be in CSV mode.
// However, not the same csv mode as before!  Rather, mode = 2 indicates that a
// file should be created and the csv data output to it, as well as the screen,
// and the page then redirects to this.
if (post_exists('CSV_button'))
{
    $csvmode = 2;
    if (isset($_POST['last_show_jobs'])) $_POST['show_jobs'] = $_POST['last_show_jobs'];
}

?>
</p>

<?php



if ($isadminuser && $adminwantstoedit) {
   echo '<input type="Submit" name="reindex" value="Re-generate indices">';
   echo '<input type="Submit" name="autocreate" value="Auto-create necessary units">';
   echo '<input type="Submit" name="purgedeleted" value="Purge deleted units">';
}

$var_unit_unames = array();
$var_unit_running = array();

$multi_tables = 0;



# get the list of units to display:
#

if(!(isset($_POST['show_jobs']))) // test if one of the buttons to show by job was not pressed
{

/*  AEC with much help from jpmg1
 *  Each Unit has associated Group(s)/Division(s)
 *  The Unit may have jobs associated with it that are associated with Groups that are not already linked to the unit
 *  We want to find these and show them in another column - othergroups
 *  The GROUP_CONCAT is making a list (with no separator - \ are Escape chars) of any prgroups for the associated jobs 
 *  that are not already in sgrps.  The LEFT join uses straight equality as an earlier use of INSTR was picking up 5R13, 5R14 along with 5R1. 
 */

    $unit_query = "SELECT units_".$yearval.".*,
                       (GROUP_CONCAT(DISTINCT (if ((LOCATE(prgroup, sgrps) = 0), prgroup, \"\")) SEPARATOR \"\")) AS othergroups
               FROM units_".$yearval."
               LEFT JOIN jobs_".$yearval."
                 ON (jobs_".$yearval.".paper = units_".$yearval.".uname)"
               .$unit_query_where."
               GROUP BY id
               ORDER BY ordering";

    
    // AEC  print current criterion         
?>
  <table style="width: 100%;"><tr><td>
  <i><small>current criterion: <?php echo htmlspecialchars($querydesc); ?>
     </small></i></td>
  </tr></table>

<?php
         
               
    $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());

    // AEC Let units_table do the csv stuff so it gets correct columns
    require('units_table.inc'); // prints the table of results for this style
    /*
    if ($csvmode == 2)
    {
      global $csv_file_handle;
      WriteQueryToCSVFile($result, $csv_file_handle);
      CloseCSVFile();
      echo "<p>Please click <b>UPDATE</b> to return to the on-screen output.</p>";
      return;
    }
    else
    {
        require('units_table.inc'); // prints the table of results for this style
    }
    */
}

if (isset($_POST['show_jobs'])) // test if one of the buttons to show by job was pressed
{
    echo '<input name="last_show_jobs" value="'.$_POST['show_jobs'].'" type="hidden" />';
    $result = '';
	require('units_job_table.inc');
}

echo "</form>";

// Free resultset
mysql_free_result($result);

// Closing connection

close_db_read();

require('config/footer.inc');

if ((post_exists('origxscroll') || post_exists('origyscroll')) &&
    (($_POST['origxscroll'] > 0) || ($_POST['origyscroll'] > 0))) {
?>
<script type="text/javascript">
<!--

self.scrollTo(<?php echo $_POST['origxscroll']; ?>,
                <?php echo $_POST['origyscroll']; ?>)
//-->
</script>
<?php
}
?> 
