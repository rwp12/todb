<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/people.inc');
require('locks.inc');


$tablename = 'quotas';
$tablething = 'Job';

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
*/
// and a list of divisions from the "division" table
//$divquery = 'SELECT * from division';
//$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());
// divisions now listed in config.inc, here:
global $division_longnames, $division_shortnames;

$button_pressed = false;

foreach ($_POST as $postk => $postv)
{
    $pos = strpos($postk, 'update');
    if ($pos > -1)
	{
	$divletter = substr($postk, 6);
	$divletter_const = $divletter;
	//echo "<p>You entered $divletter</p>";
	//if ($divletter == 'Other') {$divletter =' ';}
	//$full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (prgroup="'.$divletter.'")) ORDER BY prgroup, paper, name';
	//if ($divletter == 'All') {$full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (TRUE)) ORDER BY prgroup, paper, name';}
    $button_pressed = true;
    break;
    }
}

/*
if (!$button_pressed)
	{ // use default queries (previous : $full_query = 'SELECT '.$select_string.' FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && 	(year = 1)) ORDER BY prgroup, paper, name';
	  // this was the previous default query $full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (TRUE))ORDER BY prgroup, paper, name';
	  // instead just set the division letter to the first one
	  $divarray = mysql_fetch_array($divresult, MYSQL_ASSOC);
	  $divletter = 'B';//$divarray[1]; 
	  $divletter_const = $divletter;
	  mysql_data_seek($divresult, 0);
	}
*/


// of there was no input (i.e. just navigated to this page)
if (!$button_pressed)
{
	// Set the division letter to the first one
	// get the first array KEY (hence array_keys())
	$divletter = GetArrayValue(array_keys($division_shortnames), 0);
    $divletter_const = $divletter;
}

$full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (prgroup="'.$divletter.'")) ORDER BY prgroup, paper, name';
$querydesc = "All jobs for group $divletter";

$unalloc = '';
$modlead = '';
$showemail = '';


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



// start the output process here
require('config/header.inc');
// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
require('view_jobs_java.inc');
// and finish off with the </head> and the top of page decoration here ...
require('config/top.inc');

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

<?php
//////////////////////////////////
// get the list of divisions and create the buttons along the top
//////////////////////////////////
/*
mysql_data_seek($divresult, 0); 
while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
   $divletter = $divarray['letter'];
   $divshort = htmlspecialchars($divarray['shortname']);
   //echo "<button type=\"Submit\" name=\"update\" value=\"".$divletter."\"><b>".$divletter."</b>(".$divshort.")</button>";
   // MJ 20090311: changed the to input type=submit rather than button to accommodate IE
   echo '<input type="Submit" name="update'.$divletter.'" value="'.$divletter.'('.$divshort.')" />';
} */

foreach ($division_longnames as $divletter=>$divlong)
{
     $divshort = $division_shortnames[$divletter];
     echo '<input type="Submit" name="update'.$divletter.'" title="Click here to view jobs arranged into units for the \''.$divlong.'\' subject group/division" '.
          'value="'.$divletter.' ('.$divshort.')">'."\n";
}

//////////// finish creating the buttons
?>

<hr></hr>
</p>

<h4>Jobs for group <?php echo $divletter_const;?></h4>

</form>

  <table style="width: 100%;"><tr><td width=80%>
  &nbsp;<!--<i><small>current criterion: <?php echo htmlspecialchars($querydesc); ?>
     </small></i>-->
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
  //echo $csvrequest;
?>
  </td></tr></table>

<?php

$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// code for the database query and points tariff calcs



global $cvsmode;
global $modlead;
global $showemail;

// this function determines if particular entries get highlighted,
// boldified, italicised, or whatever, based on column and sometimes
// on content 

if ($csvmode != 1) {
   echo "<table rules=groups>\n";
}

if (!function_exists('decorate')) {
function decorate ($line, $item) {
   global $csvmode;

   if ($csvmode == 1) {
	return array("", ",");
   }

   // if it's the name of the job, and it starts with a '*', then boldify
   if (($item == "name") && (strncmp('*', $line[$item], 1) == 0)) {
      $decor = "<b>";  $enddecor = "</b>";
   // if it's the term, then italicise
   } elseif ($item == "term") {
      $decor = "<i>";  $enddecor = "</i>";
   } else {
      $decor = ""; $enddecor = "";
   }
   return array ($decor, $enddecor);
}
} 

$currentuser = "";
$currenttotal = 0;

$curyrindex = -1;
for ($i=0; $i<count($valid_years); $i++) {
   if ($valid_years[$i][0] == $yearval) {
      $curyrindex = $i;
      break;
   }
}  

if (!function_exists('per_person_count')) {
function per_person_count($line, $numcols) {
   global $dopointsummary;
   global $dopastyearsummary;
   global $currentuser;
   global $currenttotal;
   global $curyrindex;
   global $valid_years;
   global $dbread;
   global $csvmode;
   global $yearval;

   if (! $dopointsummary) {
      return TRUE;
   }

   if (($currentuser != "") && ($currentuser != $line['uname'])) {
      // we've changed users, and we're not at the first user, so we
      // should output a summary.
      if ($csvmode != 1) {

         echo "<tr>";

         // we want to print a summary line, including past years' data
         // so we do the current total in the last column[*], and the past
         // years' data right-aligned in a colspan covering all the 
         // other columns 
         //
         // [*] now second last, since Notes has turned up as the last col
         echo "<td align=right style=\"border-bottom: solid 1px;\" colspan=".
              ($numcols-2)."><i>";
      }
      if ($dopastyearsummary) {
         for ($i = 0; $i<$curyrindex; $i++) {
            $scorequery = 'SELECT SUM(points) FROM jobs_'.
                           $valid_years[$i][0].
                           ' WHERE uname=\''.$currentuser.'\'';
            $scoreresult = mysql_query($scorequery, $dbread);
            $thisscore = mysql_result($scoreresult, 0);
            if ($thisscore == FALSE) {
               $thisscore = 0;
            }
	    # have to divide by 100, because of change to the way we 
            # store points
            $thisscore /= 100; 
	    if ($csvmode == 1) {
	       echo $valid_years[$i][1].", ".$thisscore."\n";
            } else {
               echo "(".$valid_years[$i][1]." : ".$thisscore.")&nbsp;&nbsp; ";
            }
         }
      }
      if ($csvmode == 1) {
         echo $yearval.", ".$currenttotal."\n";
      } else { 
         echo "</i></td>";
         echo "<td style=\"border-top: solid 1px; border-bottom: solid 1px;\"><b>$currenttotal</b></td>\n";
      

         echo "</tr>";
      }
      $currenttotal = 0;
   }
   $currenttotal += $line['points'];
   $currentuser = $line['uname'];
   return TRUE;
}
}

if (($isadminuser) && ($adminwantstoedit)) {

   function editable_entry($line, $item) {
      global $jobitems;
      global $showemail;
      $separator = "'";
      if (($item == 'hours') || ($item == 'points')) {
         $align=" align=\"right\" style=\"padding-right: 4px;\"";
      } else {
         $align="";
      }

      if (($showemail == 'checked') && ($item == 'uname')) {
         if ($cvsout) {
             $engid = ', '.str_replace(',', ';', $line['engid']);
         } else {
             $engid = ', '.$line['engid'];
         }
      } else { 
         $engid = '';
      }

      echo "<td".$align.">\n";
      $href = "<a href=\"javascript:void(0)\" onclick=\"SelectLine(";
      foreach ($jobitems as $jobitem) {
         $href .= $separator.addcslashes($line[$jobitem], "',()\n\r");
	 $separator = "', '";
      }
      $decor = decorate($line, $item);
      $href .= "')\">".$decor[0].$line[$item].$engid.$decor[1]."</a></td>\n";
      echo $href;
   }

   echo "<thead><tr>";
   foreach ($adminjobcolshdr as $item) {
      echo "<th>$item</th>";
   }
   echo "</tr></thead><tbody>";

   $tariff = 0; // points tariff per group
   $linecount = 0;
   while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
      // we're storing points*100 to avoid having to change the column type 8-( 
      $line['points'] /= 100;
      $tariff = $tariff + $line['points']; // may as well count them up here...
      per_person_count($line, array_search('points', $adminjobcols)+1);
      echo "<tr>\n";
      foreach ($adminjobcols as $item) {
          editable_entry($line, $item);
      }
      echo "</tr>\n";
      $linecount++;
   }
   if ($linecount == 0) {
      echo "<tr><td>No Jobs Found</td></tr>\n";
   }
   // and flush the last person's totals
   per_person_count($line, count($adminjobcols));

} else {  // NOT $isadminuser or NOT $adminwantstoedit

   if ($csvmode == 1) {
      $separator = "";
      foreach ($jobcolshdr as $item) {
        if (($item == 'Person') && ($showemail)) {
           echo "$separator$item";
           $separator = ", ";
           echo $separator."email";
        } else {
          echo "$separator$item";
        }
        $separator = ", ";
      }
      echo "\n";
   } else {
      echo "<thead><tr>";
      foreach ($jobcolshdr as $item) {
         if (($item == 'Person') && ($showemail)) {
            echo "<th>$item, email</th>";
         } else { 
            echo "<th>$item</th>";
         }
      }
      echo "</tr></thead><tbody>";
   }   

   $tariff = $tariff + $line['points']; // may as well count them up here...
   $linecount = 0;
   while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
      // we're storing points*100 to avoid having to change the column type 8-( 
      $line['points'] /= 100;
      $tariff = $tariff + $line['points']; // may as well count them up here...
      per_person_count($line, count($jobcols));
      if ($csvmode != 1) {
         echo "<tr>\n";
      }
      foreach ($jobcols as $item) {
          $decor = decorate($line, $item);
          if (($item == 'hours') || ($item == 'points')) {
             $align=" align=\"right\" style=\"padding-right: 4px;\"";
          } else {
             $align="";
          }

          if (($showemail == 'checked') && ($item == 'uname')) {
             if ($cvsout) {
                 $engid = ', '.str_replace(',', ';', $line['engid']);
             } else {
                 $engid = ', '.$line['engid'];
             }
          } else { 
             $engid = '';
          }

	  if ($csvmode == 1) {
             echo $decor[0].str_replace(',', ';', $line[$item]).$engid.$decor[1];
          } else {
              echo "<td".$align."> ".$decor[0].$line[$item].$engid.$decor[1]." </td>\n";
          }
      } 
      if ($csvmode == 1) {
         echo "\n";
      } else {
         echo "</tr>\n";
      }
      $linecount++;
   }
   if (($linecount == 0) && ($csvmode != 1))  {
      echo "<tr><td>No Jobs Found</td></tr>\n";
   }
   // and flush the last person's totals
   per_person_count($line, count($jobcols));

}
if ($csvmode != 1) {
   echo "</tbody></table>\n";
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo '<hr></hr>';
echo "<h4>People in group $divletter_const </h4><br>";

mysql_free_result($result);
$full_query = "SELECT p.".implode(",p.",$personitems).", SUM(j.points)/100 AS sum FROM people_".$yearval." p, jobs_".$yearval." j WHERE ((p.deleted = FALSE) && (j.deleted = FALSE) && (division = '".$divletter_const."') && (p.uname=j.uname)) GROUP BY p.".implode(",p.",$personitems)." ORDER BY p.uname";
$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());
echo '<form method="post" action="view_people.php?yearval="'.$yearval.'">';
require('people_table.inc');

echo "<hr></hr>Group tariff for jobs: ".$tariff." teaching points<br>";

if ($linecount > 0)
  { echo "Total teaching points for this group: $points_sum<br>\n";
    //echo "Total quota for these people: $quota_sum<br>\n";
    $difference_tariff_points_sum = $points_sum-$tariff;
    echo "Difference: ".$difference_tariff_points_sum."<br>\n";
  }
echo '</form><br>';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//require('job_table.inc');

// Free resultsets
mysql_free_result($result);
//mysql_free_result($gpresult);
//mysql_free_result($divresult);
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
