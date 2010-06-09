<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/people.inc');
require('config/units.inc');
require('locks.inc');

$tablename = 'jobs';
$tablething = 'Jobs';
$whatweview = 'Jobs';

// temporary - disable the Edit button
$isadminuser = FALSE;


// MJ: some of the output columns are suppressed in the output on the printable page.
// It is necessary to remove them from the display columns array in the config.
// The variables defined in jobs.inc are overriden here (jobs_table.inc reads them):
// It is necessary to switch between these and the 'correct' ones under certain circumstances (e.g. Orphaned Jobs)
$alt_jobcols = array ("prgroup", "name", "type", "hours", "term", "uname", "Meeting_note", "points", "note", "F_Math_Desc");
// $jobcolshdr are the strings for the header line in non-edit mode
$alt_jobcolshdr = array ("Grp", "Name", "Type", "Hours", "Term", "Person", "Meeting notes", "Pts", "Notes", "Pts Formula");




require('common1.inc');

// this determines the nature of the buttons that common2.inc draws
$deletedjobmode = false;
$bysubjgroup = false;

// start the output process here
require('config/header.inc');

// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
//require('view_jobs_java.inc');

require('config/top.inc');

// we set $tablename to be the table to be edited before common1.inc ,
// but here it's the name of the script to be submitted to ...
$tablename = 'ujobs';

require('common2.inc'); 

// MJ, Jan 2009:  The buttons at the top of the page are part of the use apparatus, but have no place on
// a printed page...  Put all the HTML output into a variable and then choose whether or not to display it:
$HTML_out = '';

// create list of divisions from the "division" table
// ignore divisions that are lowercase, on policy grounds (as directed by rwp 12feb2008)
/*
$divquery = 'SELECT * FROM division WHERE letter REGEXP \'[[:upper:]]\' ORDER by disporder';
$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());
// create list of the subject groups from "subjectgroup" table that aren't in the division table
$gpquery = 'SELECT subjectgroup.letter,subjectgroup.shortname,subjectgroup.longname FROM subjectgroup LEFT JOIN division USING(letter) WHERE division.shortname IS NULL';
$gpresult = mysql_query($gpquery, $dbread) or die('subjectgroup query failed: ' . mysql_error());
*/
//echo "<div class=noprint>";
$HTML_out .= "<div class=noprint>\n";
// create the buttons for selecting things.
/*
while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
     $divletter = $divarray['letter'];
     $divshort = htmlspecialchars($divarray['shortname']);
     $divlong = htmlspecialchars($divarray['longname']);
     //echo '<input type="Submit" name="update'.$divletter.'" value="'.$divletter.' ('.$divshort.')">';
     $HTML_out .= '<input type="Submit" name="update'.$divletter.'" value="'.$divletter.' ('.$divshort.')">'."\n";
     $updateopts[$divletter]=$divshort;
}
mysql_free_result($divresult);
*/

foreach ($division_longnames as $divletter=>$divlong)
{
     $divshort = $division_shortnames[$divletter];
     //echo '<input type="Submit" name="update'.$divletter.'" value="'.$divletter.' ('.$divshort.')">';
     $HTML_out .= '<input type="Submit" name="update'.$divletter.'" title="Click here to view jobs arranged into units for the \''.$divlong.'\' subject group/division" value="'.$divletter.' ('.$divshort.')">'."\n";
     $updateopts[$divletter]=$divshort;
}
/*
while ($gparray = mysql_fetch_array($gpresult, MYSQL_ASSOC)) {
     $gpletter = $gparray['letter'];
     $gpshort = htmlspecialchars($gparray['shortname']);
     $gplong = htmlspecialchars($gparray['longname']);
     //echo '<input type="Submit" name="update'.$gpletter.'" value="'.$gpletter.' ('.$gpshort.')">';
     $HTML_out .= '<input type="Submit" name="update'.$gpletter.'" value="'.$gpletter.' ('.$gpshort.')">'."\n";
     $updateopts[$gpletter]=$gpshort;
}
mysql_free_result($gpresult);
*/

//echo '<input type="Submit" name="updateNoUnit" value="Orphans">';
$HTML_out .= '<input type="Submit" name="updateNoUnit" value="Orphans">'."\n";

//echo "</div>";
$HTML_out .= "</div>\n";


if ($isadminuser && $adminwantstoedit && !post_exists('show_jobs')) {
   //echo '<input type="Submit" name="reindex" value="Re-generate indices">';
   $HTML_out .= '<input type="Submit" name="reindex" value="Re-generate indices">'."\n";
}


// PRINTABLE PAGE response
// ::::::::::::::::::::::::
// Was the print button pressed?
$printable_version = false;
if (post_exists('doprint'))
{
  if (($_POST['doprint'] == 'print_yes'))
  {
    // if the print button was pressed, do the following:
    $printable_version = true;
  }
}
// END PRINTABLE PAGE
// ::::::::::::::::::




// DETERMINE WHICH BUTTON WAS PRESSED
// ...and so decided which group's information to display
// ::::::::::::::::::::::::::::::::::::::::::::::::::::::

// Javascript for printable page sets 'lastbuttonchoice' in the case of
// printing, but no updateX will be set.  This code will set updateX
// to whatever was last selected:
if ($printable_version)
{
     // build the post index as if the user had clicked on one of the choice buttons:
     $post_index = 'update'.$_POST['lastbuttonchoice'];
     // insert into the post array to fool the rest of the page logic:
     $_POST[$post_index] = 'YES!';
}

// parse the 'update*' post variables
if (post_exists('updateNoUnit')) {
   $subjgroupoutput = 0;
   $orphanoutput = 1;
   $unit_selector_descrip = 'NoUnit';
} else {
   $subjgroupoutput = 0;
   $orphanoutput = 0;
   foreach (array_keys($updateopts) as $updateopt) {
      if (post_exists('update'.$updateopt)) {
	 $unit_selector_descrip = $updateopt;
         $subjgroupoutput = 1;
      }
   }
}
// ::::::::::::::::::::::::::::::::::::::::::::::::::::::


// PRINT BUTTON setup
// ::::::::::::::::::::::::::::::::::
// Create javascript to handle the print button:
echo "<input type='hidden' name='doprint' value='print_no' />";
echo "<input type='hidden' name='lastbuttonchoice' value='' />";
// the javascript sets the forms target to a new window; it then instructs the page to submit (to the new window);
// it then sets the target back to itself, so that subsequent form submissions stay in this window.
echo "\n".'<script>';
echo "\n".'// <!--';
echo "\n".' function OpenPrintablePage() ';
echo "\n".'{';
// set a form variable to indicate that this should be printed:
echo "\n".'  mainform.doprint.value = \'print_yes\'; ';
echo "\n".'  mainform.lastbuttonchoice.value = \''.$unit_selector_descrip.'\';';
//echo "\n".'  mainform.update'.$unit_selector_descrip.'.value = "YES";';
echo "\n".'  mainform.target=\'_blank\'; mainform.submit(); mainform.target=\'_self\';';
// and then change it back to non-print mode
echo "\n".'  mainform.doprint.value = \'print_no\'; ';
echo "\n".'  mainform.lastbuttonchoice.value = \'\';';
// and restore the previous value of update+$unit_selector_descrip
// echo "\n".'  mainform.update'.$unit_selector_descrip.'.value = null;';
echo "\n".'}';
echo "\n".'// -->';
echo "\n".'</script>';

// and in the form, set up the print button
//echo "\n".'<input type="button" onClick="OpenPrintablePage();" name="printbutton" value="Printable Version" />'."\n";
$HTML_out .= '<input type="button" onClick="OpenPrintablePage();" name="printbutton" value="Printable Version" />'."\n";

// END PRINT BUTTON setup
// ::::::::::::::::::::::::::::::::::



//echo "<p></p>";
$HTML_out .= "<p></p>";



// echo HTML, iff the print button was not pressed (i.e. this should be shown in the on-screen version only)
if (!$printable_version) echo $HTML_out;
// and then clear HTML_out for recycling:
$HTML_out = '';





// this is the rather complicated ordering requirement from RWP.  Note that
// we invariably want our boolean test to be sorted in descending order;  that
// is, the ones that match (1) before the ones that don't (0).  We could get
// rid of the DESC's by inverting the tests, but that would be even more
// confusing.

$joborder = "ORDER BY (LEFT(LTRIM(name), 2) = '*L') DESC, ".
            "(INSTR(type, 'A')) DESC, ".
            "(INSTR(type, 'P')) DESC, ".
            "(INSTR(type, 'L') && NOT INSTR(name, 'example')) DESC, ".
            "(INSTR(type, 'L')) DESC, ".
            "(INSTR(type, 'E') && INSTR(name, 'examiner')) DESC, ".
            "(INSTR(type, 'E') && INSTR(name, 'principal')) DESC, ".
            "(INSTR(type, 'E')) DESC, ".
            "(INSTR(type, 'CW')) DESC";

$multi_tables = 1;

$firstheader = 0;

if ($orphanoutput == 1) {

   // MJ: all output to be buffered in this string:
   $output = '';
   // Tell them what they've selected !
   echo "<h2 class='units_print'>Orphaned Jobs</h2>";
   //$output .= "<p><font size='+2'><h2>Orphaned Jobs</h2></p>\n";

   // initialise the array;  you can't push onto a non-existent array 8-(
   $var_unit_unames = array();

   // start the big jobs table!

   //echo "<table rules=groups>";
   //$output .= "<table rules=groups>\n";

   // get the list of units that aren't owned by any subject group

   $block_header = 'UNITS WITHOUT A (VALID) SUBJECT GROUP';

   $makematchquery = "SELECT concat('[', group_concat(distinct(letter) SEPARATOR ''), ']') FROM subjectgroup group by NULL";

   $result = mysql_query($makematchquery, $dbread) or die('Query failed: ' . mysql_error());

   $mymatcher = mysql_result($result,0,0);

   mysql_free_result($result);

   $unit_query = "SELECT DISTINCT uname,running,name
                  FROM units_".$yearval."
                  WHERE ((deleted = FALSE) && (NOT (sgrps REGEXP '".$mymatcher."')))";

   $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());

   //echo "<!--  MJ 021 \n  $unit_query  \n-->\n";

   while ($uname = mysql_fetch_array($result, MYSQL_ASSOC)) {

      // only add it if it's not already there
      if (array_search($uname['uname'], $var_unit_unames) === FALSE) {
         array_push($var_unit_unames, $uname['uname']);
         $var_unit_running[$uname['uname']] = $uname['running'];
         $var_unit_alljobs[$uname['uname']] = 1;
         $var_unit_name[$uname['uname']] = $uname['name'];
         // make sure the first unit in this section gets a header
         $var_unit_header[$uname['uname']] = $block_header;
         $block_header = "";
      }
   }
   mysql_free_result($result);

   foreach ($var_unit_unames as $unit) {

      if ($var_unit_header[$unit] != "") {
         echo "<tbody><tr><td colspan=\"10\"><b><font size=\"+2\">".$var_unit_header[$unit]."</font></b></td></tr></tbody>";
         //$output .= "<tbody><tr><td colspan=\"12\"><b><font size=\"+2\">".$var_unit_header[$unit]."</font></b></td></tr></tbody>";
      }

      echo "<tbody><tr><td colspan=\"10\"><b>".$unit.": ".$var_unit_name[$unit]."</b></td></tr></tbody>";
      //$output .= "<tbody><tr><td colspan=\"12\"><b>".$unit.": ".$var_unit_name[$unit]."</b></td></tr></tbody>\n";

      $unit_query = 'SELECT jobs_'.$yearval.'.*
                     FROM jobs_'.$yearval.'
                     LEFT JOIN people_'.$yearval.' USING(uname)
                     WHERE ((jobs_'.$yearval.'.deleted = FALSE) && (paper = "'. $unit.'"))'.$joborder;


      $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());


      // write the output to the screen:
      echo $output;


      if (mysql_num_rows($result) > 0) {

         $jobssearchedfor = $unit;
         $notrunning = "";
         if ($var_unit_running[$unit] == 0) {
             $notrunning = "NOT RUNNING";
         }

         $firstheader = 1;

         require('job_table.inc');
      }
      mysql_free_result($result);
   }

   // and now the jobs that aren't in a valid unit
   // MJ: This is the ORPHANS button!
   // :::::::::::::::::::::::::::::::
   echo "<H2 class='units_print'>JOBS THAT ARE NOT IN A VALID UNIT</H2>";

   echo "<p><table class='unit_table'>\n";

   // MJ, Jan 2009: replaced the following to include points formulae:
   /*$unit_query = 'SELECT jobs_'.$yearval.'.* FROM jobs_'.$yearval.'
                     LEFT JOIN units_'.$yearval.'
                     ON (jobs_'.$yearval.'.paper = units_'.$yearval.'.uname)

                     WHERE ((units_'.$yearval.'.uname IS NULL) && (jobs_'.$yearval.'.deleted = FALSE))';
   */

   $unit_query = ' SELECT B.*, D.F_Math_Desc FROM jobs_'.$yearval.' AS B '.
                 ' LEFT JOIN units_'.$yearval.' AS A '.
                 ' ON (B.paper = A.uname) '.
                 ' LEFT JOIN point_formulae_'.$yearval.' as D on B.Formula_ref = D.Formula_ID '.
                 ' WHERE ((A.uname IS NULL) && (B.deleted = FALSE))';


   $result = mysql_query($unit_query, $dbread) or die('Query failed: '. mysql_error());

   if (mysql_num_rows($result) > 0) {
      $jobssearchedfor = $unit;
      $notrunning = "";
      if ($var_unit_running[$unit] == 0) {
          $notrunning = "NOT RUNNING";
      }

      $firstheader = 1;
      require('job_table.inc');
   }
   
   echo "</table></p>\n";

   mysql_free_result($result);
   // END of ORPHANS
   // :::::::::::::::::::::::::::::::::::



   // and finally, jobs that haven't been assigned to anyone
   // JOBS THAT HAVE NOT BEEN ASSIGNED TO A PERSON
   // ::::::::::::::::::::::::::::::::::::::::::::
   echo "<H2 class='units_print'>JOBS THAT HAVE NOT BEEN ASSIGNED</H2>";
   echo "<p><table class='unit_table'>\n";

   // MJ Jan 2009: replaced the following query for two reasons:
   // 1 - it does not (cannot) work, as far as I can see
   //     [Update: it does work if an unassigned job is one with a '?' instead of NULL]
   // 2 - it needs to support point formulae
   /*
   $unit_query = 'SELECT * FROM jobs_'.$yearval.
                 ' LEFT JOIN people_'.$yearval.' USING(uname) '.
                 ' WHERE (jobs_'.$yearval.'.deleted = FALSE) && '.
                 ' (people_'.$yearval.'.deleted = FALSE) && '.
                 ' ((people_'.$yearval.'.uname IS NULL) || (uname = ""))'.
                 ' ORDER BY year, paper';
   */
   $unit_query = 'SELECT B.*, D.F_Math_Desc FROM jobs_'.$yearval.
                 ' as B LEFT JOIN point_formulae_'.$yearval.' as D ON B.Formula_ref = D.Formula_ID '.
                 ' WHERE (B.deleted = FALSE) &&  (B.uname IS NULL or B.uname="?") ORDER BY year, paper;';

   $result = mysql_query($unit_query, $dbread) or die('Query failed: '. mysql_error());

   if (mysql_num_rows($result) > 0) {
      $jobssearchedfor = $unit;
      $notrunning = "";
      if ($var_unit_running[$unit] == 0) {
          $notrunning = "NOT RUNNING";
      }

      $firstheader = 1;
      require('job_table.inc');
   }
   
   echo "</table></p>\n";

   mysql_free_result($result);
   // END of JOBS THAT HAVE NOT BEEN ASSIGNED TO A PERSON
   // :::::::::::::::::::::::::::::::::::::::::::::::::::


   echo "</tbody></table>";
   echo "<input type=\"hidden\" name=\"show_jobsstate\" value=\"".$var_unit_uname."\">";

   echo "</form>";

} elseif ($subjgroupoutput == 1) {

   // Tell them what they've selected !
   echo "<h1>Job Summary for ".$updateopts[$unit_selector_descrip]."</h1>";

   // initialise the array;  you can't push onto a non-existent array 8-(
   $var_unit_unames = array();

   // start the big jobs table!

   //echo "<table rules=groups>";

   //
   // get the list of units where one of the groups owning the unit is the group of interest
   // (but we ignore groups marked as "global", so that they're left until last (rwp request))
   //

   $block_header = 'UNITS INVOLVING SUBJECT GROUP '.$unit_selector_descrip;


   // MJ: to display student counts per course, I think it would be good to determine it here, and then
   // make it available in one of the variables:
   $unit_query = "SELECT DISTINCT A.uname, A.running, CONCAT(A.name, ' [',  IFNULL(B.student_count, '?'), ' student(s)]') as name from units_".$yearval." as A left join studentspercourse_".$yearval." as B on UPPER(A.uname) = UPPER(B.coursename) WHERE ((A.deleted  = FALSE) && instr(A.sgrps, '". $unit_selector_descrip ."') && (NOT global is TRUE)) ORDER BY A.ordering";

   $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());

   while ($uname = mysql_fetch_array($result, MYSQL_ASSOC)) {

      // only add it if it's not already there
      if (array_search($uname['uname'], $var_unit_unames) === FALSE) {
         array_push($var_unit_unames, $uname['uname']);
         $var_unit_running[$uname['uname']] = $uname['running'];
         $var_unit_alljobs[$uname['uname']] = 1;
         $var_unit_alldivowned[$uname['uname']] = 0;
         $var_unit_name[$uname['uname']] = $uname['name'];
         // make sure the first unit in this section gets a header
         $var_unit_header[$uname['uname']] = $block_header;
         $block_header = "";
      }
   }
   mysql_free_result($result);

   //
   // get the list of units where at least one person from the division with the same letter as the group of interest
   // is doing one of the jobs in that unit.
   // (but we ignore groups marked as "global", so that they're left until last (rwp request))
   //

   $block_header = 'JOBS FROM OTHER UNITS, DONE BY DIVISION '.$unit_selector_descrip.' PERSONNEL';

   // MJ, Jan 2009: the following query was tidied to make it more readable:
   //$unit_query = 'SELECT DISTINCT units_'.$yearval.'.uname,running,units_'.$yearval.'.name from units_'.$yearval.' LEFT JOIN jobs_'.$yearval.' ON (jobs_'.$yearval.'.paper = units_'.$yearval.'.uname) LEFT JOIN people_'.$yearval.' ON jobs_'.$yearval.'.uname=people_'.$yearval.'.uname WHERE ((binary(people_'.$yearval.'.division) = \''.$unit_selector_descrip.'\') && (units_'.$yearval.'.deleted = FALSE) && (NOT global IS TRUE) && (jobs_'.$yearval.'.deleted = FALSE))';
   $unit_query = 'SELECT DISTINCT A.uname, running, A.name '.
                 'from units_'.$yearval.' as A LEFT JOIN jobs_'.$yearval.' as B ON (B.paper = A.uname) '.
                 'LEFT JOIN people_'.$yearval.' as C ON B.uname = C.uname '.
                 'WHERE ((binary(C.division) = \''.$unit_selector_descrip.'\') '.
                 '&& (A.deleted = FALSE) && (NOT global IS TRUE) && (B.deleted = FALSE))';

   $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());

   while ($uname = mysql_fetch_array($result, MYSQL_ASSOC)) {

      // only add it if it's not already there
      if (array_search($uname['uname'], $var_unit_unames) === FALSE) {
         array_push($var_unit_unames, $uname['uname']);
         $var_unit_running[$uname['uname']] = $uname['running'];
         $var_unit_alljobs[$uname['uname']] = 0;
         $var_unit_alldivowned[$uname['uname']] = 0;
         $var_unit_name[$uname['uname']] = $uname['name'];
         // make sure the first unit in this section gets a header
         $var_unit_header[$uname['uname']] = $block_header;
         $block_header = "";
      }

   }
   mysql_free_result($result);

   //
   // get the list of globally interesting units to display.
   // rwp request is that we do this last
   //

   $block_header = 'UNITS OF GLOBAL INTEREST';

   $unit_query = 'SELECT DISTINCT uname,running,name from units_'.$yearval.' WHERE global IS TRUE && deleted IS FALSE';

   $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());

   while ($uname = mysql_fetch_array($result, MYSQL_ASSOC)) {

      if (array_search($uname['uname'], $var_unit_unames) === FALSE) {
         array_push($var_unit_unames, $uname['uname']);
         $var_unit_running[$uname['uname']] = $uname['running'];
         $var_unit_alljobs[$uname['uname']] = 0;
         $var_unit_alldivowned[$uname['uname']] = 1;
         $var_unit_name[$uname['uname']] = $uname['name'];
         // make sure the first unit in this section gets a header
         $var_unit_header[$uname['uname']] = $block_header;
         $block_header = "";
      }
   }
   mysql_free_result($result);

   // make select_expr out of the $jobitems array, knocking out uname,
   // because we're going to magic it
   // ----
   // note (MJ): with the windows/newer version of MySQL, if there are two
   // identically-named columns
   // in the result it gives an error, rather than making an assumption regarding
   // which one you want...
   // id, updatetime appear in both tables in the join query that $select_expr selects within.
   // Because we're considering only the items in the jobs table, it should be sufficient to prepend
   // the name of the table followed by a dot (e.g. 'jobs_2008_09.') to each field name:

   // #20
   // MJ, Jan 2009: in order to support Formula display in ujobs, the following was changed:
   //$select_expr = "jobs_".$yearval.'.'.implode(", jobs_".$yearval.'.', array_diff($jobitems, array("uname")));
   
   // back up the original job cols arrays:
   $tmp_jobcols = $jobcols;
   $tmp_jobcolshdr = $jobcolshdr;
   
   // and set the new ones for display:
   $jobcolshdr =  $alt_jobcolshdr;
   $jobcols = $alt_jobcols;
   
   // and then the following changes made to suppress some of the columns:
   $suppressed_cols = array('course','year','paper',"uname");

   $select_expr = 'B.'.implode(", B.", array_diff($jobitems, $suppressed_cols));



   // Loop through the sets of unit choices, displaying the headings and the data:
   foreach ($var_unit_unames as $unit)
   {

      // display the block header:
      if ($var_unit_header[$unit] != "") {
         echo "<h2 class='units_print'>".$var_unit_header[$unit]."</h2>";
      }

      if ($var_unit_alljobs[$unit] == 0) {
         if ($var_unit_alldivowned[$unit] == 0) {
            $restrict = "(binary(C.division) = '".$unit_selector_descrip."')";
         } else {
            $restrict = "((binary(C.division) = '".$unit_selector_descrip."') OR (instr(prgroup, '". $unit_selector_descrip ."')))";
         }
      } else {
         // this test is implicit in the above , but needs to be explicit in this case:
         // (request from rlt23 that we not show jobs that haven't been assigned)
         $restrict = "((B.uname IS NOT NULL) && (B.uname != \"\"))";
      }

      // MJ, Jan 2009: in order to support Formula display in ujobs, the following was changed:
      /*   $unit_query = 'SELECT '.$select_expr.',
                            IF ((division=\''.$unit_selector_descrip.'\'),
                                uname,
                                CONCAT(\'[\',uname,\']\')) AS uname
                     FROM jobs_'.$yearval.'
                     LEFT JOIN people_'.$yearval.' USING(uname)
                     WHERE ((jobs_'.$yearval.'.deleted = FALSE) && (paper = "'.$unit.'") && ('.$restrict.'))'.$joborder;
      */
      // MJ, Jan 2009: ...to:
      $unit_query = 'SELECT '.$select_expr.', '.
                    'IF ((division=\''.$unit_selector_descrip.'\'), '.
                         'uname, '.
                         'CONCAT(\'[\',uname,\']\')) AS uname, '.
                    'D.F_Math_Desc '.
                    'FROM jobs_'.$yearval.' as B '.
                    'LEFT JOIN people_'.$yearval.' as C USING(uname) '.
                    'LEFT JOIN point_formulae_'.$yearval.' as D on B.Formula_ref = D.Formula_ID '.
                    'WHERE ((B.deleted = FALSE) && (paper = "'.$unit.'") && ('.$restrict.'))'.$joborder;
      // MJ: echo query
      // echo "\n<!-- Full query [$unit]: \n $unit_query \n -->";

      $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());
               // troubleshoot:

      echo "<p><div class='unit_table'>\n";
      echo "<table class='unit_table'>\n";
      // MJ: table header
      echo "<thead><tr><th colspan=\"12\">".$unit.": ".$var_unit_name[$unit]."</th></tr></thead>\n";
      //echo "<tbody>\n";


      if (mysql_num_rows($result) > 0)
      {

         $jobssearchedfor = $unit;
         $notrunning = "";
         if ($var_unit_running[$unit] == 0) {
             $notrunning = "NOT RUNNING";
         }
         $firstheader = 1;
         require('job_table.inc');

      }
      /* else
      {
         // even if there's no jobs found, we still want the header in the case that
         // we've set "view all jobs" as well (basically, this is a match for the
         // ones that are of interest to the subject group _whatever_).  But this
         // bit means that in (say) the global-interest section, we elide anything
         // that isn't matching the subject group at all
         if ($var_unit_alljobs[$unit] == 1) {
             echo "<tbody><tr><td colspan=\"12\"><b>".$unit.": ".$var_unit_name[$unit]."</b></td></tr></tbody>";
        }
      } */
      
      // finish off the table
      echo "\n</tbody>\n</table>\n</div>\n</p>\n";

      mysql_free_result($result);
   }
   // END of loop through unit choices
   
   // and correct again, for good measure:
   // back up the original job cols arrays:
   $jobcols = $tmp_jobcols;
   $jobcolshdr = $tmp_jobcolshdr;

   //echo "</tbody></table>";
   echo "<input type=\"hidden\" name=\"show_jobsstate\" value=\"".$var_unit_uname."\">";

   echo "</form>";

} else {

   echo "<p>Please select a Subject Group letter</p>";

}

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
