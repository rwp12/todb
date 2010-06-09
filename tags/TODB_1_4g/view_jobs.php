<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('locks.inc');

$beginning = DateStampNow();
//echo "<!-- Starting: $beginning -->";

$tablename = 'jobs';
$tablething = 'Job';


// config:
global $csv_output_path, $csv_url_path;




require('common1.inc');

// :::::::::::::::::::::::::::::::::::::::::::::::::::
// The following was removed by MJ, August 2009 and replaced with a more easily-configurable
// hard-coded PHP solution, via these variables:
global $division_longnames, $division_shortnames;
// the variables are defined in config.inc
// :::::::::::::::::::::::::::::::::::::::::::::::::::
// and a list of divisions from the "division" table
/*
$divquery = 'SELECT * FROM division WHERE letter REGEXP \'[[:upper:]]\' ORDER by disporder';
$divresult = mysql_query($divquery, $dbread) or die('division query failed: ' . mysql_error());
*/
// :::::::::::::::::::::::::::::::::::::::::::::::::::


require('build_jobs_select.php');


// start the output process here
require('config/header.inc');

// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
require('view_jobs_java.inc');

// MJ: if the CSV option was selected (i.e. the user has just clicked the
// 'Download as CSV' button, include the redirect to the CSV file:
/*if(post_exists('CSV_button'))
{
    // where the CSV files are stored, on the local filesystem and
    // as a URL
    global $csv_output_path;
    global $csv_url_path;
    
    // names of CSV-related files and URL
    $csv_filename = '';
    $csv_file_only = '';
    $csv_url = '';

    // create a local filename that is unique:
    list($usec, $sec) = explode(' ', microtime());
    $time_in_sec = (float) (((float) $sec) + ((float) $usec));
    $csv_file_only = $tablename.'_'.$time_in_sec.'.csv';
    $csv_filename = $csv_output_path.$csv_file_only;
    $csv_url = $csv_url_path.$csv_file_only;

} */


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

<!-- MJ Nov 08 added function-based generation of table: -->
<?php
// generate HTML checkboxes:
/*
    $criterion = 'course';
    $title = $all_titles[$criterion];
    $prefix = $all_prefixes[$criterion];
    $column_name = $all_column_names[$criterion];
    $categories = $all_categories[$criterion];
    $captions = $all_captions[$criterion];
    $other_cats = $all_other_cats[$criterion];

    // get the HTML checkbox string
    $year_HTML = GetCheckboxString($title, $prefix, $categories, $captions, $other_cats);

    // show onscreen:
    echo $year_HTML;
*/
  // END of checkbox generation
?>



<?php
// generate HTML checkboxes:
    foreach (array_keys($all_titles) as $criterion)
    {
        //$criterion = 'year';
        $title = $all_titles[$criterion];
        $prefix = $all_prefixes[$criterion];
        $column_name = $all_column_names[$criterion];
        $categories = $all_categories[$criterion];
        $captions = $all_captions[$criterion];
        $other_cats = $all_other_cats[$criterion];

        // get the HTML checkbox string
        $year_HTML = GetCheckboxString($title, $prefix, $categories, $captions, $other_cats);
    
        // show onscreen:
        echo $year_HTML;
    }
  // END of checkbox generation
?>
<!-- END ** MJ Nov 08 added function-based generation of table. -->


<table style="width: 100%;">
<tr>
<td>Only show unallocated jobs?:
<input title="Tick this box and then click 'Standard Filter' to display jobs that have not yet been allocated to a person.  All other filters still apply." type="Checkbox" name="unalloc" <?php echo $unalloc ?> >&nbsp;</td>
<?php  
if ($isadminuser) {
?>
<!-- &nbsp;&nbsp;
<td>Produce list of module leaders:
<input type="Checkbox" name="modlead" <?php echo $modlead ?> >&nbsp;</td> -->
<?php
}
?>
<!--td>Show email addresses:
<input type="Checkbox" name="showemail" <?php echo $showemail ?> ></td-->
</tr>
</table>

<p>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td>
<input title="Click here to refresh the list of jobs displayed.  Any changes to the filters above will be reflected below." type="Submit" name="filter" value="Standard Filter">
<?php
  // MJ, December 2008:
  // create a BUTTON rather than a link for generating the CSV download
  InsertCSVApparatus();
  //echo '<input type="submit" value="Download as CSV" name="CSV_button" />';
?>
</td>
<td align="right" valign="bottom">
<input title="Select a 'special filter' from the adjacent list and click this button to display results from special queries that cannot be easily described using the filters above." type="submit" name="filter_special_button" value="Special Filter:">
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
  // MJ, August 2009: replaced database calls with references to variables in PHP
/*
  mysql_data_seek($divresult, 0);
  while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
     $divletter = $divarray['letter'];
     $divshort = htmlspecialchars($divarray['shortname']);
     $divlong = htmlspecialchars($divarray['longname']);
     if ($filter_special == 'outdiv'.$divletter) {
        echo "<option selected value=\"outdiv".$divletter."\">all \"$divlong\" external jobs</option>\n";
     } else {
        echo "<option value=\"outdiv".$divletter."\">all $divlong external</option>\n";
     }
  }
*/

  foreach ($division_longnames as $divletter=>$divlong)
  {
     $divshort = $division_shortnames[$divletter];
     if ($filter_special == 'outdiv'.$divletter) {
        echo "<option selected value=\"outdiv".$divletter."\">all '$divlong' external jobs</option>\n";
     } else {
        echo "<option value=\"outdiv".$divletter."\">all '$divlong' external</option>\n";
     }
  }

?>
   </optgroup>
   <optgroup label="show all jobs done by members of div X">

<?php
// MJ, August 2009: replaced database calls with references to variables in PHP
/*
  mysql_data_seek($divresult, 0); 
  while ($divarray = mysql_fetch_array($divresult, MYSQL_ASSOC)) {
     $divletter = $divarray['letter'];
     $divshort = htmlspecialchars($divarray['shortname']);
     $divlong = htmlspecialchars($divarray['longname']);
     if ($filter_special == 'alldiv'.$divletter) {
        echo "<option selected value=\"alldiv".$divletter."\">all jobs done by \"$divlong\" people</option>\n";
     } else {
        echo "<option value=\"alldiv".$divletter."\">all jobs done by $divlong people</option>\n";
     }
  }
*/

  foreach ($division_longnames as $divletter=>$divlong)
  {
     $divshort = $division_shortnames[$divletter];
     if ($filter_special == 'alldiv'.$divletter) {
        echo "<option selected value=\"alldiv".$divletter."\">all jobs done by '$divlong' people</option>\n";
     } else {
        echo "<option value=\"alldiv".$divletter."\">all jobs done by '$divlong' people</option>\n";
     }
  }

?>
   </optgroup>
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
  </td></tr></table>

<?php
//echo "<p> \nfull query: <br />\n".$full_query." \n</p>";
$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());

$firstheader = 1;


require('job_table.inc');

// Free resultsets
mysql_free_result($result);
//mysql_free_result($gpresult);
//mysql_free_result($divresult);
// Closing connection

close_db_read();


$ending = DateStampNow();
//echo "<!-- Ending: $ending -->";
//echo "\n<!-- began: $beginning; ending: $ending -->";
$diff = round($ending-$beginning, 4);
echo '<p>Processing time: '.$diff.'s </p>';

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



// MJ, 20090316: worried about performance; get times:
function DateStampNow()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);

}



?> 
