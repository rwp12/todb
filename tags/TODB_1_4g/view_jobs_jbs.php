<?php

require('config/config.inc');
require('config/years.inc');
require('config/db.inc');
require('useful.inc');
require('auth.inc');
require('config/jobs.inc');
require('locks.inc');

$tablename = 'jobs';
$tablething = 'Job';

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

$gpquery = 'SELECT * from subjectgroup';
$gpresult = mysql_query($gpquery, $dbread) or die('subjectgroup query failed: ' . mysql_error());
// and a list of divisions from the "division" table
$divquery = 'SELECT * from division';
$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());

////////////// below is part of the jbs 'select by paper' filter option
$papersquery = 'SELECT * from jobs_'.$yearval.' WHERE ( deleted = FALSE ) GROUP BY paper, name ORDER BY course, name';
$papersresult = mysql_query($papersquery, $dbread) or die('division query failed: ' . mysql_error());

////////////// below is part of the jbs 'select by course' filter option
$coursesquery = 'SELECT * from jobs_'.$yearval.' WHERE ( deleted = FALSE ) GROUP BY course ORDER BY course, name';
$coursesresult = mysql_query($coursesquery, $dbread) or die('division query failed: ' . mysql_error());
////////////// end select by course

if (post_exists('filter') || post_exists('filter_special') ||
    post_exists('editmode')) {

  $filter = '(jobs_'.$yearval.'.deleted = FALSE)';

  $querydesc = "";
  $querydescy = "";
  $querysep = "";

  $filter .= ' && (FALSE';

  $querydescg = ""; 
  $gpnotother = '';
  mysql_data_seek($gpresult, 0);
  while ($gparray = mysql_fetch_array($gpresult, MYSQL_ASSOC)) {
     $gpletter = $gparray['letter'];
     $gpvar = "gp".$gpletter;
     $gpnotother .= $gpletter;          // building a list of group letters to not match for 'other'
     if (post_exists('prg_'.$gpletter))
        { $filter .= " || (INSTR(prgroup, '".$gpletter."'))"; $$gpvar = 'checked'; $querydescg .= $gpletter; } else $$gpvar = '';
  }   
  if (post_exists('prg_other')) 
	{ $filter .= " || (select prgroup regexp '[^".$gpnotother."]') || (prgroup IS NULL)"; $gpoth = 'checked'; $querydescg .= '?'; } else $gpoth = '';
  $filter .= ')';

  if ($querydescg != "") {
     $querydesc .= $querysep."group is ".implode("/",str_split($querydescg));
     $querysep = ", ";
  }	

  $querydesccourse = ""; 
  $filter .= ' && (FALSE';

  mysql_data_seek($coursesresult, 0);
  while ($coursesarray = mysql_fetch_array($coursesresult, MYSQL_ASSOC)) {
  	$coursename = htmlspecialchars($coursesarray['course']);
  	$coursename_sp = str_replace(' ','__sp__',$coursename);
  	if (post_exists("course_$coursename_sp")) {
  		$filter .= " || (INSTR(course, '$coursename'))";
  	 	$querydesccourse .= $coursename.', ';
  	   	}
  	  else 
  	  {
  	  	$course_.$coursename_sp = '';
	  }
  }
  
  if (post_exists("course_blank")) {
  		$filter .= " || (course IS NULL)";
  	 	$querydesccourse .= "no course".', ';
  	   	}
  	  else 
  	  {
  	  	$course_blank = '';
	  }
  
  if (post_exists("course_all")) {
  		$filter .= " || TRUE";
  	   	}
	  
  $filter .= ')';

  if (post_exists("course_all")) { $querydesc .= ", any course, "; } else {
  if ($querydesccourse != "") {
  $querydesc .= $querysep."course is ".$querydesccourse;
  $querysep = " ";
  } }

  $querydescty = ""; 
  $tynotother = 'LCEAP'; 
  $filter .= ' && (FALSE';

  if (post_exists('jty_le')) { $filter .= " || (INSTR(type, 'L'))"; $jtyle = 'checked'; $querydescty .= 'L'; } else $jtyle = '';
  if (post_exists('jty_cw')) { $filter .= " || (INSTR(type, 'C'))"; $jtycw = 'checked'; $querydescty .= 'C'; } else $jtycw = '';
  if (post_exists('jty_ex')) { $filter .= " || (INSTR(type, 'E'))"; $jtyex = 'checked'; $querydescty .= 'E'; } else $jtyex = '';
  if (post_exists('jty_ad')) { $filter .= " || (INSTR(type, 'A'))"; $jtyad = 'checked'; $querydescty .= 'A'; } else $jtyad = '';
  if (post_exists('jty_pr')) { $filter .= " || (INSTR(type, 'P'))"; $jtypr = 'checked'; $querydescty .= 'P'; } else $jtypr = '';
  if (post_exists('jty_other')) { $filter .= " || ((! INSTR(type, 'L')) && (! INSTR(type, 'C')) && 
		 (! INSTR(type, 'E')) && (! INSTR(type, 'A')) && (! INSTR(type, 'P'))) || (type IS NULL)";
		 $jtyoth = 'checked'; $querydescty .= '?'; } else  $jtyoth ='';
  
  if (post_exists('jty_all')) {$filter .= " || TRUE"; $jtyall = 'checked';}
  $filter .= ')';

  if (post_exists('jty_all')) 
  	{
  		$querydesc .= "any job type";
  		$jtyle = 'checked'; $jtycw = 'checked'; $jtyex = 'checked';
  		$jtyad = 'checked'; $jtypr = 'checked'; $jtyoth = 'checked';
  	}
  else 
  {
	  if ($querydescty != "") 
	  	{
		 $querydesc .= $querysep."type is ".implode("/",str_split($querydescty));
		 $querysep = ", ";
  		}
  }

  $querydesctm = "";
  $tmnotother = 'MLE';
  $filter .= ' && (FALSE';
  if (post_exists('trm_m')) { $filter .= " || (INSTR(term, 'M'))"; $trmm = 'checked'; $querydesctm .= 'M'; } else $trmm = '';
  if (post_exists('trm_l')) { $filter .= " || (INSTR(term, 'L'))"; $trml = 'checked'; $querydesctm .= 'L'; } else $trml = '';
  if (post_exists('trm_e')) { $filter .= " || (INSTR(term, 'E'))"; $trme = 'checked'; $querydesctm .= 'E'; } else $trme = '';
  if (post_exists('trm_other')) { $filter .= " || ((! INSTR(term, 'M')) && (! INSTR(term, 'L')) && 
         (! INSTR(term, 'E'))) || (term IS NULL)";  $trmoth = 'checked'; $querydesctm .= '?'; } else  $trmoth ='';
  $filter .= ')';

  if ($querydesctm != "") {
     $querydesc .= $querysep."term is ".implode("/",str_split($querydesctm));
     $querysep = ", ";
  }	

  if (post_exists('unalloc')) { $filter .= " && (uname <=> NULL)"; $unalloc = 'checked'; $querydesc .= $querysep."unallocated"; $querysep = ", "; } else $unalloc = ''; 

  if (post_exists('modlead')) { $filter .= " && (INSTR(name, \"leader\") > 0) && NOT (paper IS NULL)"; $modlead = 'checked'; $querydesc .= querysep."module leaders"; $querysep = ", "; } else $modlead = ''; 

  if (post_exists('showemail')){ 
      $showemail = 'checked'; 
      $select_string .= ',people_'.$yearval.'.engid';
      $join_string = ' LEFT JOIN people_'.$yearval.' USING (uname, deleted)';
  } else {
      $showemail = '';
      $join_string = '';
  } 

  $full_query = 'SELECT '.$select_string.' FROM jobs_'.$yearval.$join_string.' WHERE ' . $filter . 'ORDER BY year, prgroup, paper, name';

} else { // use default queries
  $full_query = 'SELECT '.$select_string.' FROM jobs_'.$yearval.' WHERE (FALSE) ORDER BY prgroup, paper, name';
  $querydesc = "no query selected";
  $yr1 = 'checked'; $yr2 = ''; $yr3 = ''; $yr4 = ''; $yroth = '';

  mysql_data_seek($gpresult, 0);
  while ($gparray = mysql_fetch_array($gpresult, MYSQL_ASSOC)) {
     $gpvar = "gp".$gparray['letter'];
     $$gpvar = 'checked';
  }
  $gpoth = 'checked';

  $jtyle ='checked'; $jtycw ='checked'; $jtyex ='checked'; $jtyad = 'checked'; 
  $jtypr = 'checked'; $jtyoth = 'checked';
  $trmm = 'checked';  $trml = 'checked'; $trme = 'checked'; $trmoth = 'checked';
  $unalloc = '';
  $modlead = '';
  $showemail = '';
}

// various of the special filters produce output sorted by person, in 
// which case it's sensible and useful to provide teaching point summaries
$dopointsummary = FALSE;
$dopastyearsummary = FALSE;
$deletedjobmode = FALSE;

// to overwrite the query definition.
//
// the logic here is very similar to that for editmodestate;  if we've
// not clicked the button, but we're in that state anyway, then  
// treat it as if we have clicked the button
// NB but not if we were in that state, but had hit the filter button
$filter_special = "";

if (
     (
       (post_nomatch('filter_special', ''))
       || 
       (post_nomatch('filter_specialstate', 'nofilter_special'))
     ) 
   &&
     ( !post_exists('filter') )
   ) {

   // work out which of the special filters it was ...

   if (post_nomatch('filter_special', '')) {
      $filter_special = $_POST['filter_special'];
   } else { 
      $filter_special = $_POST['filter_specialstate'];
   }

   require('view_jobs_special.inc');
} else {
  // it's useful to know this is set so that it's easy to pre-select
  // the right item in the <select> section in the <form> below ...
  $filter_special = 'nofilter_special';
}

///////////////////////////////////
///////////////////////////////////
// new csv output section, using a button rather than a link (because it seems easier to me to do it this way, rather than fixing the get request version)

$do_csv = FALSE;
if (post_exists('produce_csv')) {$do_csv = TRUE;}
if ($do_csv)
{require("csv_jobs_new.inc");}
else
{

///////////////////////////////////
///////////////////////////////////


// start the output process here
require('config/header.inc');
// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
require('view_jobs_java.inc');
// and finish off with the </head> and the top of page decoration here ...
require('config/top.inc');

echo "<!-- MJ 002: after top.inc -->";

require('common2.inc');

// get the filter_specialstate variable correctly set
//  firstly if we're doing a filter ("Filter" button), we cancel the
//  special_filter
if (post_exists('filter')) {
?>
<input type=hidden name=filter_specialstate value=nofilter_special>
<?php 
} else {
   if (post_exists('filter_special')) {
      echo "<input type=hidden name=filter_specialstate value=\"";
      echo $_POST['filter_special'];
      echo "\">\n";
   } else {
?>
<input type=hidden name=filter_specialstate value=nofilter_special>
<?php
   }
}

?>

<table>
<tr>
<td width=100>Group:</td>
<?php 
  mysql_data_seek($gpresult, 0);
  while ($gparray = mysql_fetch_array($gpresult, MYSQL_ASSOC)) {
     $gpletter = $gparray['letter'];
     $gpshort = htmlspecialchars($gparray['shortname']);
     $gpvar = "gp".$gpletter;
     echo "<td><input type=\"Checkbox\" name=\"prg_$gpletter\" ".$$gpvar." ><b>$gpletter</b>($gpshort) &nbsp;</td>\n";
  }
?>   
<td><input type="Checkbox" name="prg_other" <?php echo $gpoth ?> >Other Grps &nbsp;</td>
</tr>
</table>

<table>
<tr>
<td width=100>Course:</td>
<?php 
  mysql_data_seek($coursesresult, 0);
  while ($coursesarray = mysql_fetch_array($coursesresult, MYSQL_ASSOC)) {
     $coursename = htmlentities($coursesarray['course']);
     $coursename_sp = str_replace(' ','__sp__',$coursename);
     if ($coursename_sp) {
     	$checkedcourse = '';
     	if (post_exists("course_$coursename_sp") || (post_exists("course_all"))) {$checkedcourse = 'checked';}
     	echo "<td><input type=\"Checkbox\" name=\"course_$coursename_sp\" $checkedcourse >$coursename</td>\n";
 		}
  }
  $checkedcourse = '';
  if (post_exists("course_blank") || (post_exists("course_all"))) {$checkedcourse = 'checked';}
  echo "<td><input type=\"Checkbox\" name=\"course_blank\" $checkedcourse >Jobs not assigned to a course</td>\n";
  if (post_exists("course_all")) {$checkedcourse = 'checked';}
?>
<td><input type="Checkbox" name="course_all" <?php echo $checkedcourse ?>>All courses</td>  
</tr>
</table>

<table>
<tr>
<td width=100> Type of job: </td>
<td><input type="Checkbox" name="jty_le" <?php echo $jtyle ?> >Lectures: L &nbsp; </td>
<td><input type="Checkbox" name="jty_cw" <?php echo $jtycw ?> >Coursework: C &nbsp; </td>
<td><input type="Checkbox" name="jty_ex" <?php echo $jtyex ?> >Examining: E &nbsp; </td>
<td><input type="Checkbox" name="jty_ad" <?php echo $jtyad ?> >Administration: A &nbsp; </td>
<td><input type="Checkbox" name="jty_pr" <?php echo $jtypr ?> >Preparation: P &nbsp; </td>
<td><input type="Checkbox" name="jty_other" <?php echo $jtyoth ?> >Other Types</td>
<td><input type="Checkbox" name="jty_all" <?php echo $jtyall ?> >All job types</td>
</tr>
</table>

<table>
<tr>
<td width=100> Term: </td>
<td><input type="Checkbox" name="trm_m" <?php echo $trmm ?> >Michaelmas: M &nbsp; </td>
<td><input type="Checkbox" name="trm_l" <?php echo $trml ?> >Lent: L &nbsp; </td>
<td><input type="Checkbox" name="trm_e" <?php echo $trme ?> >Easter: E &nbsp; </td>
<td><input type="Checkbox" name="trm_other" <?php echo $trmoth ?> >Other &nbsp; </td>
</tr>
</table>

<?php
/////////////
// checkbox for producing a csv file
?>

Produce CSV file?<input type="Checkbox" name="produce_csv"/>

<?php
///////////// 
?>

<table style="width: 100%;">
<tr>
<td>Only show unallocated jobs?:
<input type="Checkbox" name="unalloc" <?php echo $unalloc ?> >&nbsp;</td>
<?php  
if ($isadminuser) {
?>
&nbsp;&nbsp;
<td>Produce list of module leaders:
<input type="Checkbox" name="modlead" <?php echo $modlead ?> >&nbsp;</td>
<?php
}
?>
<td>Show email addresses:
<input type="Checkbox" name="showemail" <?php echo $showemail ?> ></td>
</tr>
</table>

<p>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td>
<input type="Submit" name="filter" value="Standard Filter">
</td>
<td align="right" valign="bottom">
<button type="submit" name="filter_special_button">Special Filter:</button>
<select size=1 name="filter_special">
  <option <?php if ($filter_special == 'nofilter_special') echo 'selected'; ?>
      value=nofilter_special>None</option>
  <option <?php if ($filter_special == 'filter_nonperson') echo 'selected'; ?>
      value=filter_nonperson>
      Only jobs allocated to people not found in database</option>
  <option <?php if ($filter_special == 'filter_nonallocated') echo 'selected'; ?>
      value=filter_nonallocated>
      Jobs not yet allocated to anyone</option>
<?php
  if ($isadminuser) {
?>
  <option <?php if ($filter_special == 'filter_deleted') echo 'selected'; ?>
      label=filter_deleted value=filter_deleted>
      Jobs that have been marked as deleted</option>
<?php
  }
?>     
  <optgroup label="show all jobs done by members of div X, that are not from subject group X">
<?php
  mysql_data_seek($divresult, 0); 
  while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
     $divletter = $divarray['letter'];
     $divshort = htmlspecialchars($divarray['shortname']);
     $divlong = htmlspecialchars($divarray['longname']);
     if ($filter_special == 'outdiv'.$divletter) {
        echo "<option selected value=\"outdiv".$divletter."\">all \"$divlong\" external jobs</option\n";
     } else {
        echo "<option value=\"outdiv".$divletter."\">all $divlong external</option\n";
     }
  }
?>
   </optgroup>
   <optgroup label="show all jobs done by members of div X"

<?php
  mysql_data_seek($divresult, 0); 
  while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
     $divletter = $divarray['letter'];
     $divshort = htmlspecialchars($divarray['shortname']);
     $divlong = htmlspecialchars($divarray['longname']);
     if ($filter_special == 'alldiv'.$divletter) {
        echo "<option selected value=\"alldiv".$divletter."\">all jobs done by \"$divlong\" people</option\n";
     } else {
        echo "<option value=\"alldiv".$divletter."\">all jobs done by $divlong people</option\n";
     }
  }
?>
   </optgroup> 
   <optgroup label="show all jobs for paper X"

<?php // section added to filter by paper //////////////////////////////////////////////////////////
	if (!strncmp($filter_special, "paper", 5))
	{
		$selected_paper_array = explode("@",$filter_special);
		$selected_paper = $selected_paper_array[1];
	}
	
	$paperslist = mysql_fetch_array($papersresult, MYSQL_ASSOC); // this is added as otherwise it adds a blank line; reason unknown (while loop?)
	while ($paperslist = mysql_fetch_array($papersresult, MYSQL_ASSOC))
	{
		$is_selected = '';
		if ( (!strncmp($filter_special, "paper", 5)) && ($paperslist['paper'] == $selected_paper) ) {$is_selected = 'selected';}
		if ($paperslist['paper'] == '') {$paperslist['paper'] = 'paper code not entered, cannot search';}
		echo '<option '.$is_selected.' value="paper@'.$paperslist['paper'].'">'.$paperslist['course'].':&nbsp;'.$paperslist['name'].'&nbsp;&nbsp; - '.$paperslist['paper'].'</option>';
	}
?>
</select>
</td>
</tr>
</table>
</p>
</form>

  <table style="width: 100%;"><tr><td>
  <i><small>current criterion: <?php echo htmlspecialchars($querydesc); ?>
     </small></i>
  </td><td>
<?php 
  // build up the csv_jobs.php description of the request
  $csvrequest = "<a href=\"csv_jobs.php?yearval=";
  $csvrequest .= $yearval;
  if (($filter_special == "") || ($filter_special == "nofilter_special")) {
     if ($querydescy != "") {
        $csvrequest .= "&year=";
        $csvrequest .= $querydescy;
     }
     if ($querydescg != "") {
       $csvrequest .= "&group=";
       $csvrequest .= $querydescg;
       $csvrequest .= "&allgrps=";
       $csvrequest .= $gpnotother;
     }
     if ($querydescty != "") {
       $csvrequest .= "&type=";
       $csvrequest .= $querydescty;
       $csvrequest .= "&alltypes=";
       $csvrequest .= $tynotother;
     }
     if ($querydesctm != "") {
       $csvrequest .= "&term=";
       $csvrequest .= $querydesctm;
       $csvrequest .= "&allterms=";
       $csvrequest .= $tmnotother;
     }
     if ($unalloc == "checked") {
       $csvrequest .= "&unalloc=1";
     }
     if ($modlead == "checked") {
       $csvrequest .= "&modlead=1";
     }
  } else {
     $csvrequest .= "&filter_special=";
     $csvrequest .= $filter_special;
  }
  // this applies to both specials and ordinary filters
  if ($showemail == "checked") {
     $csvrequest .= "&showemail=1";
  }

  $csvrequest .= "\">download as CSV</a>";
  //echo $csvrequest; - hidden because we now have a csv checkbox instead
?>
  </td></tr></table>

<?php

$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());
$do_buttons_in_job_table = 'TRUE';

require('job_table.inc');

// Free resultsets
mysql_free_result($result);
mysql_free_result($gpresult);
mysql_free_result($divresult);
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

} // end of if (not csv) scope
?> 
