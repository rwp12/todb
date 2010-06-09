<table class="unit_table">
<?php

// Generic functions for job_table and people_table:
require('table_functions.inc');

$firstheader = 1;
$n_linecount = 0;
$n_points_sum = 0;
$n_quota_sum  = 0;

$button_cols_arr['uname'] = 'show_jobs';

// Call the show table function, instead of bespoke code for jobs:
ShowGeneralTable($isadminuser,
                 $adminwantstoedit,
                 $firstheader,
                 $adminunitcolshdr,
                 $result,
                 $adminunitcols,
                 $unititems,
                 $showemail,
                 $unitcolshdr,
                 $csvmode,
                 $csv_file_handle,
                 $unitcols,
                 $button_cols_arr,
                 $show_quotas,
                 $n_linecount,
                 $n_points_sum,
                 $n_quota_sum);

// AEC Copied this from job_table.inc to get CSV columns correct
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
    echo "<p>Please click the <b>Home</B> button to return to the on-screen output.</p>";
}

?>

