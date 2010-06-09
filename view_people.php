<?php

// *** JBS ***

require('config/config.inc');

require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/people.inc');
require('locks.inc');

// some sites/institutions (JBS, English, for e.g.) use the concept of a points 'quota'.  In such cases,
// the following variable should be set to true (fetch from config.inc?):
global $show_quotas;
global $dbread;
// something like 'cam.ac.uk'; the email address domain.  So emails could be sent to someone@institutional_domain_name
// defined in config.inc
global $institutional_domain_name;

// MJ, Dec 2008: if the user clicked on the 'Download as CSV' BUTTON (rather than
// link), the system should indicate that output should be in CSV mode.
// However, not the same csv mode as before!  Rather, mode = 2 indicates that a
// file should be created and the csv data output to it, as well as the screen,
// and the page then redirects to this.
if (post_exists('CSV_button')) $csvmode = 2;


if (post_exists('update')) {
   $_POST['show_jobs'] = "";
} 

if (!post_exists('update') && !post_exists('show_jobs') &&
    post_exists('show_jobsstate')) {
   $_POST['show_jobs'] = $_POST['show_jobsstate'];
}

if (!post_exists('show_jobs') && post_exists('updatestate')) {
   $_POST['update'] = $_POST['updatestate'];
}

if (post_exists('editmodestate') && !post_exists('editmode')) {
   $_POST['editmode'] = $_POST['editmodestate'];
}

// but when we're in the "show a single person's jobs" mode of this script,
// then most of the common code winds up wanting to behave like it would
// in the view_jobs.php script ... 

if (post_exists('show_jobs') && post_nomatch('show_jobs', '')) {
   // Added by MJ, 2009-05-06
   $firstheader = 1;
   
   $tablename = 'jobs';
   $tablething = 'Job';
} else {
   $tablename = 'people';
   $tablething = 'Person';
}
 
// make it possible to redirect from 
// view_my_jobs.php => view_people.php?view_my_jobs

$viewmyjobs = FALSE;
if (get_exists('view_my_jobs') || post_exists('view_my_jobs')) {
    //AEC Nov 09 Need to display headings here
    $firstheader = 1;
	$viewmyjobs = TRUE;
}

require('common1.inc');

// create list of subject groups from "subjectgroup" table
//$gpquery = 'SELECT * from subjectgroup';
//$gpresult = mysql_query($gpquery, $dbread) or die('Subjectgroup query failed: ' . mysql_error());
// and a list of divisions from the "division" table
/*
$divquery = 'SELECT * from division';
$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());
*/

if (post_exists('send_message')) {

  if (strlen($_POST["email_text"]) > 0) {

     // MJ, April 2009: Get the user's email address:
     /*$email_q = "select crsid from people_$yearval where uname = '$thisuser';";
     $eq_res  = mysql_query($email_q, $dbread);
     if (isset($eq_res)) $eq_line = mysql_fetch_assoc($eq_res);
     if (isset($eq_line)) $user_email = $eq_line['crsid'];*/
     //$u_email = $thisuser.'@cam.ac.uk';
     // MJ, July 2009: replaced cam address with 'institutional domain name'
     $u_email = $thisuser.'@'.$institutional_domain_name;

     mail($email_recipient, "Comments from " . $thisuser . " on teaching duties (".$yeartext.")", 
          wordwrap($_POST["email_text"], 65), "From: $u_email\r\nReply-To: $u_email\r\n");

     $duty_message_ack = "<p><b>Message has been sent to $officename</b></p>";
  } else {
    $duty_message_ack = "<p><b>You appear to have attempted to send an empty message;  perhaps you meant to click the other button further up the page?</b></p>";
  }
}

if (post_exists('send_confirmation')) {

  // NOTE !  The column is called OK06 to maintain compatibility, but there's
  // a confirmation ok column called OK06 in the people_ table for _every_
  // year!
  date_default_timezone_set('Europe/London');    // Added AEC Dec 09 to stop warning
  $tt=getdate();
  $conf_query = 'UPDATE people_'.$yearval.' SET OK06="'.$tt["year"].'-'.
                 $tt["mon"].'-'.$tt["mday"].' '. $tt["hours"].':'.
                 $tt["minutes"].':'.$tt["seconds"]. 
                 '" WHERE ((deleted = FALSE) && (crsid="' .$thisuser. '")) '.
                 'LIMIT 1';
  open_db_write();
  if (mysql_query($conf_query) === FALSE) {
     $duty_confirm_ack = "<p><b>Confirmation failed, please contact $officename directly</b></p>";
  } else { 
     $duty_confirm_ack = "<p><b>Teaching duties confirmation accepted</b></p>";
  }
  close_db_write();
}

// determine whether we are going to show a list of people or a list of jobs.
$var_person_uname = false;    // if this remains false we show people.
if (post_exists('person_uname') && post_exists('send_message')) {
  $var_person_uname = $_POST['person_uname'];
}
if (post_exists('show_jobs') && post_nomatch('show_jobs', '')) {
  $var_person_uname = $_POST['show_jobs'];
}
if (!post_exists('update')) { 
   $_POST['update'] = 'notset';
}

// this determines the nature of the buttons that common2.inc draws
$deletedjobmode = FALSE;


// create an array of matching "division" strings to search in for the
// update post variable's current value
// :::::::::::::::::::::::::::::::::::::
//   Code replaced by MJ August 2009 to have config in PHP rather than inaccessible DB tables
// :::::::::::::::::::::::::::::::::::::
/*
mysql_data_seek($divresult, 0); 
$divarraycount=1;

while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC))
{
   $divletter = $divarray['letter'];
   $divshort = htmlspecialchars($divarray['shortname']);
   $divletterarray[$divarraycount] = $divletter;
   $divshortarray[$divarraycount] = " $divshort ";
   $divshortarrayie[$divarraycount++] = "<B>".$divletter."</B>(".$divshort.")";

} */
// :::::::::::::::::::::::::::::::::::::
// code above replaced with this:
$divarraycount=1;
foreach ($division_longnames as $divletter=>$divlong)
{
    $divshort = $division_shortnames[$divletter];
    # IE is very special, and doesn't use the value attribute in a button
    $divletterarray[$divarraycount] = $divletter;
    $divshortarray[$divarraycount] = " $divshort ";
    $divshortarrayie[$divarraycount++] = "<B>".$divletter."</B>(".$divshort.")";
}



$matchdivision=0;

$full_query = "SELECT p.".implode(",p.",$personitems).", ifnull(SUM(j.points)/100,0) AS sum, j.type FROM people_".$yearval." p LEFT JOIN jobs_".$yearval." j ON ( (p.uname=j.uname) && (j.deleted = FALSE) )";

if (ctype_upper($_POST['update']) && (strlen($_POST['update']) == 1)) {
  // changed to search surnames rather than unames:
  $full_query = $full_query." WHERE ((p.deleted = FALSE) && (LEFT(LTRIM(p.surname),1) = '".$_POST['update']."') ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "surname beginning with ".$_POST['update'];
}
elseif (($matchdivision = array_search($_POST['update'], $divshortarray)) > 0)
{ // has been edited by ar346 to include quota and tariff information
  // and by MJ April 09 to allow staff members to belong to several groups:
  $full_query = $full_query."WHERE ((p.deleted = FALSE) && (instr(division , '".$divletterarray[$matchdivision]."')) ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "in division ".$_POST['update'];
}
elseif (($matchdivision = array_search($_POST['update'], $divshortarrayie)) > 0)
{
  //$full_query = "SELECT * FROM people_".$yearval." WHERE ((deleted = FALSE) && (division = '".$divletterarray[$matchdivision]."')) ORDER BY uname"; // <-- old query
  // edited by MJ April 09 to allow staff members to belong to several groups:
  //$full_query = $full_query."WHERE ((p.deleted = FALSE) && (division = '".$divletterarray[$matchdivision]."') ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $full_query = $full_query."WHERE ((p.deleted = FALSE) && (instr(division, '".$divletterarray[$matchdivision]."')) ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "in division ".$divletterarray[$matchdivision];
}
elseif ($_POST['update'] == ' Other ') {
  //$full_query = "SELECT * FROM people_".$yearval." WHERE ((deleted = FALSE) && ((division IS NULL) || (";
  $full_query = $full_query."WHERE ((p.deleted = FALSE) && ((division IS NULL) || (";
  $separator = '';
  foreach ($divletterarray as $letter) {
 	 // AEC because group letters might be 'A', 'B' or 'A, B', a simple "not in ('A', 'B') ..." is not sufficient
     $full_query .= $separator . "(instr(division, '$letter') < 1)";
     //$full_query .= $separator . "(division <> '" . $letter . "')";
     $separator = ' && '; 
  }
  //$full_query .= ")) ) ORDER BY uname";
  $full_query .= ")) ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "not in a division";
} elseif ($_POST['update'] == ' All ') {
  //$full_query = "SELECT * FROM people_".$yearval." WHERE (deleted = FALSE) ORDER BY uname";
  $full_query = $full_query."WHERE (p.deleted = FALSE) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "everyone";
} elseif ($_POST['update'] == ' Deleted ') {
  //$full_query = "SELECT * FROM people_".$yearval." WHERE (deleted = TRUE) ORDER BY uname";
  $full_query = $full_query." WHERE (p.deleted = TRUE) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $deletedjobmode = TRUE;
  $querydesc = "deleted entry";
} else {
  $full_query = $full_query." WHERE ((p.deleted = FALSE) && (LEFT(LTRIM(p.uname),1) = 'A') ) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
  $querydesc = "surname beginning with A";
}

// start the output process here
require('config/header.inc');
// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
if ($tablename == 'people') {
  require('view_people_java.inc');
} else {
  require('view_jobs_java.inc');
}
require('config/top.inc');

// we set $tablename to be the table to be edited before common1.inc ,
// but here it's the name of the script to be submitted to ...
$tablename = 'people';

require('common2.inc'); 


if (post_exists('update')) {
  echo "<input type=\"hidden\" name=\"updatestate\" value=\"";
  echo $_POST['update'];
  echo "\">\n";
}

if (!$viewmyjobs)
{
?>
<p>
<input type="Submit" name="update" value="A">
<input type="Submit" name="update" value="B">
<input type="Submit" name="update" value="C">
<input type="Submit" name="update" value="D">
<input type="Submit" name="update" value="E">
<input type="Submit" name="update" value="F">
<input type="Submit" name="update" value="G">
<input type="Submit" name="update" value="H">
<input type="Submit" name="update" value="I">
<input type="Submit" name="update" value="J">
<input type="Submit" name="update" value="K">
<input type="Submit" name="update" value="L">
<input type="Submit" name="update" value="M">
<input type="Submit" name="update" value="N">
<input type="Submit" name="update" value="O">
<input type="Submit" name="update" value="P">
<input type="Submit" name="update" value="Q">
<input type="Submit" name="update" value="R">
<input type="Submit" name="update" value="S">
<input type="Submit" name="update" value="T">
<input type="Submit" name="update" value="U">
<input type="Submit" name="update" value="V">
<input type="Submit" name="update" value="W">
<input type="Submit" name="update" value="X">
<input type="Submit" name="update" value="Y">
<input type="Submit" name="update" value="Z">
</p>

<p>
<?php

// show checkboxes for display of timetable and points breakdown:
if (isset($_POST['showtimetable'])) $tt_ticked = 'checked';
if (isset($_POST['showptsbreakdown'])) $pb_ticked = 'checked';
echo '<fieldset style="float:right; border: 1px solid silver">';
echo '<legend>Options</legend>';
echo '<input title="If this is ticked, a timetable will be displayed showing the jobs done by people listed below" '.
     'type="checkbox" name="showtimetable" '.$tt_ticked.'/>Show timetable';
echo '<input title="If this is ticked, a points breakdown will be displayed for the people listed below" '.
     'type="checkbox" name="showptsbreakdown" '.$pb_ticked.'/>Show jobs breakdown';
echo '</fieldset>';


/*mysql_data_seek($divresult, 0);
while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC))
{
   $divletter = $divarray['letter'];
   $divshort = htmlspecialchars($divarray['shortname']);
   echo "<button type=\"Submit\" name=\"update\" value=\" ".$divshort." \"><b>".$divletter."</b>(".$divshort.")</button>";
   //echo "<input type=\"Submit\" name=\"update\" value=\"".$divletter."(".$divshort.")\">";
} */

// code above replaced with this (MJ, August 2009):
foreach ($division_longnames as $divletter=>$divlong)
{
    $divshort = $division_shortnames[$divletter];
   echo "<button type=\"Submit\" name=\"update\" title=\"Show people in '$divlong' subject group (division)\" value=\" ".$divshort." \"><b>".$divletter."</b>(".$divshort.")</button>";
}


echo '<input type="Submit" name="update" value=" Other ">';
echo '<input type="Submit" name="update" value=" All ">';

if ($isadminuser) echo '<input type="Submit" name="update" value=" Deleted ">';

// generate the CSV button:
echo "<br />\n";
InsertCSVApparatus();
echo '<input type="Submit" name="dummyupdate" value="Update" />';




echo '</p>';
}
else
{ // end of if (!$viewmyjobs) 

   // have to fix up var_person_uname here ...
   $query = 'SELECT uname FROM people_'.$yearval.' WHERE ((deleted = FALSE) && (crsid="'.$thisuser.'")) LIMIT 1';
   $result = mysql_query($query, $dbread) or die('uname from engid failed: ' . mysql_error());
   if ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $var_person_uname = $line["uname"];
   }
  
?>
<input type="hidden" name="view_my_jobs" value=1>
<?php

}


// ===============================================================================

if ($var_person_uname)
{

  $myownjobs = FALSE; 
 
  // first check to see if the user is looking at their own teaching load.
  $query = 'SELECT crsid FROM people_'.$yearval.' WHERE ((deleted = FALSE) && (uname="' . $var_person_uname . '")) LIMIT 1';
  $result = mysql_query($query, $dbread) or die('check user/crsid failed: ' . mysql_error());
  if (($line = mysql_fetch_array($result, MYSQL_ASSOC)) &&
      ($line["crsid"] == $thisuser)) {
     $myownjobs = TRUE;
  }

  $full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (uname = "' . $var_person_uname . '")) ORDER BY '.$jobordering;
  $result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());
?>
  <p>
  <table style="width: 100%;"><tr><td> 
  Showing jobs for
  <?php

    // MJ, April 2009: instead of displaying 'Showing jobs for uname', it is now '...for Dr A Soandso':
    $person_detail_query = "select title, initials, surname from people_$yearval where uname = '$var_person_uname';";
    $pd_res = mysql_query($person_detail_query, $dbread);
    // get result:
    $fullname = '';
    $pd_line = mysql_fetch_assoc($pd_res);
    if (isset($pd_line)) $fullname = $pd_line['title'].' '.$pd_line['initials'].' '.$pd_line['surname'];
    else $fullname = '-';
    mysql_free_result($pd_res);
    // display the full name and uname:
    echo $fullname.' ['.$var_person_uname.']';
  ?>
  </td><td>
  <!--a href="csv_people.php?yearval=<?php echo "$yearval";?>&show_jobs=<?php echo
 "$var_person_uname";?>">download as CSV</a-->
  </td></tr></table>
  </p>
  <p>
<?php

  $dopointsummary = TRUE;
  $dopastyearsummary = TRUE;

  require('job_table.inc');

  // MJ 20090311:
  // checks for teaching duties at the Engineering
  //require('other_duties.php');
  
  // show the points breakdown:
  if (isset($_POST['showptsbreakdown']))
  {
    $q_points_col = 'points';
    $q_type_col = 'type';
    require('points_breakdown.inc');
  }
  
  // show the timetable
  if (isset($_POST['showtimetable']))
  {
    require('timetable_functions.inc');
    ShowTimetable("'".$var_person_uname."'");
  }


  if ($myownjobs) {
     if ($viewmyjobs)
     { 
        require('viewmyjobs.inc');
     }
     else
     {
        echo '<button type="submit" name="view_my_jobs" value=1>Go to Job Confirmation Page</button>';
     }
  }	

  echo "<input type=\"hidden\" name=\"show_jobsstate\" value=\"".$var_person_uname."\">";
  // Free resultset
  mysql_free_result($result);
  // Closing connection

  echo "</form>";
}

// ===============================================================================

elseif ($viewmyjobs)
{
    // we asked for viewmyjobs, but didn't exist
    // in the people database
?>

<p>The database could not find an entry corresponding to your userid
(<?php echo $thisuser; ?>).  Please contact <?php echo $officename; ?> directly
if you think there should be such an entry, because you expect to have
teaching duties in the year <?php echo $yeartext ?>.  You can if necessary
do so here:
</p>
<p>
<textarea name="email_text" rows="10" cols="65">
</textarea>
</p>
<p>
<button type="Submit" name="send_message" 
   value="SendMessage<?php echo $yearval; ?>">
Send the above message to <?php echo $officename; ?></button>
<?php if (isset($duty_message_ack)) { echo $duty_message_ack; }; ?>
</p>

<?php  

}

// ===============================================================================

else
{
         // this relates to if show jobs.  below is if we're not doing
         // a single user:

?>

  <table style="width: 100%;"><tr><td>
  <i><small>current criterion: <?php echo htmlspecialchars($querydesc); ?>
     </small></i></td>

  </tr></table>

<?php

  $result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());

  //echo "<p>Query is <br />$full_query</p>";
  
  // AEC people_table does all the csv stuff so special processing for csv here.  This makes sure columns are correct.
  require('people_table.inc');
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
    require('people_table.inc');
  }*/

  echo '</form><br>';


  // summary of teaching points and possibly quota information

  if ($linecount > 0)
  {
       // total teaching points
       echo "<div style='float: right'>";
       echo "<p>Total teaching points for these people: $points_sum</p>\n";
     
       // if instructed to consider quotas, calculate and display:
       if ($show_quotas)
       {
          echo "Total quota for these people: $quota_sum<br>\n";
          $difference_sum = $points_sum-$quota_sum;
          echo "Difference: ".$difference_sum."<br>\n";
       }
       echo "</div>";
   }

  // show a breakdown of how points are earned (i.e. by job type)
  if (isset($_POST['showptsbreakdown']))
  {
     $q_points_col = 'sum';
     $q_type_col = 'type';
     $q_point_div = 1;
     require('points_breakdown.inc');
  }
  // show the timetable
  if (isset($_POST['showtimetable']))
  {
    require('timetable_functions.inc');
    ShowPeopleTimetable($result);
  }

  // Free resultset
mysql_free_result($result);

} // end of if/else $var_person_uname

// ===============================================================================


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
