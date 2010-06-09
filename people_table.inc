<table class="unit_table">
<?php

// Generic functions for job_table and people_table:
require('table_functions.inc');

if (!isset($adminpersoncolspointstotal)) $adminpersoncolspointstotal =  $adminpersoncols;
if (!isset($personcolspointstotal)) $personcolspointstotal =  $personcols;


// some sites/institutions (JBS, English, for e.g.) use the concept of a points 'quota'.  In such cases,
// the following variable should be set to true (fetch from config.inc?):
global $show_quotas;
global $personitems;
global $showemail;
global $personcolshdr;

$n_linecount = 0;
$n_points_sum = 0;
$n_quota_sum = 0;

$firstheader = 1;
$button_cols_arr = array('uname' => 'show_jobs');
// Call the show table function, instead of bespoke code for people:

ShowGeneralTable($isadminuser,
                 $adminwantstoedit,
                 $firstheader,
                 $adminpersoncolshdr,
                 $result,
                 $adminpersoncolspointstotal,
                 $personitems,
                 $showemail,
                 $personcolshdr,
                 $csvmode,
                 $csv_file_handle,
                 $personcolspointstotal,
                 $button_cols_arr,
                 $show_quotas,
                 $n_linecount,
                 $n_points_sum,
                 $n_quota_sum);

// outputs:
$linecount = $n_linecount;
$points_sum = $n_points_sum;
$quota_sum = $n_quota_sum;

// AEC Copied this from job_table.inc 
if (($csvmode != 1) && ($csvmode != 2) && ($multi_tables != 1)) {
   echo "</tbody></table>\n";
}

// close the CSV file, if it is likely to be open,
// and echo a link to download the file:
if ($csvmode == 2)
{
    global $csv_filename;
    global $csv_filename_only;
    global $csv_url;
    // close file:
    @fclose($csv_file_handle);
    // show link:
    echo '<p>Right-click on this link and select \'Save target/link as...\': <a href="'.$csv_url.'" target="_blank">'.$csv_file_only.'</a></p>';
    echo "<p>Please click the <b>UPDATE</b> button to return to the on-screen output.</p>";
}

?>

