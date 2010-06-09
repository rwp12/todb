<?php
// JBS
///////////
// this checks the currently selected user to see if they are doing any teaching at Engineering too
///////////

$dbread_eng = NULL;
$dbwrite_eng = NULL;

function open_db_read_eng() {

   global $dbread_eng;

   $dbread_eng = mysql_connect('localhost', 'todread', 'todread_pass')
      or die('Could not connect: ' . mysql_error());
   mysql_select_db('teachingoffice', $dbread_eng);

}

function close_db_read_eng() {

   global $dbread_eng;
   mysql_close($dbread_eng);

   $dbread_eng = null;

}



//global $dbread_eng;
//global $dbwrite_eng;

if ($var_person_uname) {
	
	// this section gets their CRSID - uname may not match between eng and the judge
	
	open_db_read();
	$crsidquery = "SELECT crsid FROM people_$yearval WHERE ((deleted = FALSE) && (uname=\"$var_person_uname\")) LIMIT 1";
	//echo "<p>Query is... $crsidquery</p>";
	$crsidresult = mysql_query($crsidquery, $dbread) or die('CRSID query failed, perhaps this person\'s CRSID is not in the teaching office database: ' . mysql_error());
	$nullvar = mysql_fetch_array($crsidresult, MYSQL_ASSOC);
	$crsid_to_match = $nullvar['crsid'];
	mysql_free_result($crsidresult);
	close_db_read();
	
	//echo "<p>CRSID is... $crsid_to_match</p>";
	
	// end get crsid section

open_db_read_eng();

	// get the uname in the jbs database from the crsid to use in the jobs query
	
	$unamequery = "SELECT uname from people_$yearval where ((deleted = false) && (crsid=\"$crsid_to_match\")) limit 1";
	$unameresult =  mysql_query($unamequery, $dbread_eng) or die('Uname query failed, perhaps this person\'s CRSID is not in the ENG database: ' . mysql_error());
	$nullvar = mysql_fetch_array($unameresult, MYSQL_ASSOC);
	$uname_to_match = $nullvar['uname'];
	mysql_free_result($unameresult);
			
	// end get uname section

//echo $yearval; //because the jbs one doesn't have 2008/09 in yet
//$yearval='2007_08';// echo $yearval;// because the jbs one doesn't have 2008/09 in yet

  /*$myownjobs = FALSE; 
 
  // first check to see if the user is looking at their own teaching load.
  $query = 'SELECT crsid FROM people_'.$yearval.' WHERE ((deleted = FALSE) && (uname="' . $var_person_uname . '")) LIMIT 1';
  $result = mysql_query($query, $dbread) or die('check user/crsid failed: ' . mysql_error());
  if (($line = mysql_fetch_array($result, MYSQL_ASSOC)) &&
      ($line["crsid"] == $thisuser)) {
     $myownjobs = TRUE;
  }*/

  $full_query_eng = 'SELECT course, year, paper, prgroup, name, type, hours, -100 as points, uname, note FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (uname = "' . $uname_to_match . '")) ORDER BY year, prgroup, paper, name';
  $result = mysql_query($full_query_eng, $dbread_eng) or die('Query1 failed: ' . mysql_error());
?>
 
<?php

   $linecount2 = 0;
   if ($line2 = mysql_fetch_array($result, MYSQL_ASSOC)) {$linecount2++;}
   if ($linecount2 == 0) {echo "No teaching duties at Engineering.";}
   if ($linecount2>0) {mysql_data_seek($result, 0);}

if ($linecount2!=0)
 {

  echo '<h3>Teaching duties at the Engineering appear below.</h3>';
  echo '<p>Note: Engineering teaching points have a different meaning to those in the JBS, so to avoid confusion they are not displayed.</p></p>';

  $dopointsummary = TRUE;
  $dopastyearsummary = TRUE;

  // temporary switch of variables to make job_table.inc run correctly (as it references db_read)
  $temporary_db_variable_store = $dbread;
  $dbread = $dbread_eng;
  
  // MJ 20090311: also need to prevent the judge section from becoming editable if the user
  // happens to try...
  $editmode_bk = $adminwantstoedit;
  $adminwantstoedit = false;
  
  // was including the Engineering job table
  require('job_table.inc');
  // and switch back again
  $dbread = $temporary_db_variable_store; // probably not needed, but keeps it tidier
  
  // revert editmode information:
  $adminwantstoedit = $editmode_bk;

  /*if ($myownjobs) {
     if ($viewmyjobs) { 

         require('../jbs/viewmyjobs.inc');
 
     } else {
?>
    <button type="submit" name="view_my_jobs" value=1>Go to Job Confirmation Page</button>
<?php

     } 
  }*/	
 }
  echo "<input type=\"hidden\" name=\"show_jobsstate\" value=\"".$var_person_uname."\">";
  // Free resultset
  mysql_free_result($result);
  // Closing connection

close_db_read_eng();
 
}

else {;}

?>
