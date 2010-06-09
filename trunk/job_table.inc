<?php


/* ///////////////////////////////////////////////////////////////////////////////////
   MJ, 2009-05-01:
   
   INPUTS
   :::::::::::::::::::::::::::::::::
   
   Constants:
   ---------------------------------
   |Variable name    | Where defn'd? |Scope     | Type              | Meaning
   +-----------------+---------------+----------+-------------------+--------------------------------------------------------
   | adminjobcolshdr | jobs.inc      | Global   | Array(string)     | The display aliases of the columns shown in edit mode
   | csv_file_only   | view_jobs.php | Protected| String            | The filename only of the full path&filename for the CSV file
   | csv_filename    | view_jobs.php | Global   |                   | The full path&filename for the CSV file
   | csv_url         | view_jobs.php | Global   | String            | The URL path of the CSV file (as it is generated on the server file system)
   | jobcols         | jobs.inc      | Global   | Array(string)     | Array of column names of jobs table which define what the non-admin user sees in the jobs output
   | jobcolshdr      | jobs.inc      | Global   | Array(string)     | Array of on-screen aliases corresponding with column names jobcols
   | jobitems        | jobs.inc      | Global   | Array(string)     | Column names to be extracted from jobs table in DB; probably used in edit mode because id is in this list
   | valid_years     | years.inc     | Global   | Array(array(strin | Years which are reflected in the system
   +-----------------+---------------+----------+-------------------+--------------------------------------------------------
   
   Variables:
   ------------------------------------
   |Variable name              | Meaning
   +---------------------------+---------------------------------------------------------------------------------------------
   | adminwantstoedit          | If the user is in edit mode, and has clicked on the 'edit' button, this will be true
   | csvmode                   | Values 1 and 2 indicate that job_table should generate CSV output, rather than HTML
   | dbread                    | Database connection resource id, for reading
   | do_buttons_in_job_table   | A feature quirk of the original JBS version code.  Instructs the system to display buttons for papers / courses in the job listings, such that the user can narrow down jobs to a single course or paper.
   | dopastyearsummary         | Whether or not to display a points/jobs summary for the previous year in the per_person_count function
   | dopointsummary            | Whether or not to display a points/jobs summary in the per_person_count function
   | firstheader               | Not entirely sure; possibly controls whether or not a heading is printed (i.e. Thead rather than tbody)
   | isadminuser               | Indicates whether or not the user has edit rights; page will only be displayed in edit mode if the user is an admin user
   | multi_tables              | Indicates whether nor not more than one table is output; appears to be used only to limit CSV output to single-table situations
   | order_string              | Apparently unused (not set) variable which allowed the user to specify the order in which columns were displayed
   | result                    | Resultset from query of jobs to display
   | showemail                 | Indicates whether or not to display the email address of the person doing the job (the engid column in the people table).  If true, contains value 'checked'.
   | yearval                   | The year value (e.g. 2008_09) reflecting the user's current choice of year.  This is generally passed in the URL.
   +---------------------------+---------------------------------------------------------------------------------------------


   Internal Variables:
   ------------------------------------
   |Variable name              | Meaning
   +---------------------------+---------------------------------------------------------------------------------------------
   | align                     | Local string variable
   | colour                    | The display colour of points vs quota calculations
   | csv_file_handle           | File handle of the CSV file, defined when opened for writing
   | currenttotal              | Totals users' points totals for calculating quota stuff, in per_person_count function
   | currentuser               | The uname of a person whose jobs are under scrutiny in one of the reports.  Used for calculating personal totals and things
   | curyrindex                | The index of the valid years array defined in years.inc that matches the current year
   | cvsout                    | Supposed to mean 'CSV out', and indicates whether or not job info should be output as CSV or not.
   | decor                     | Starts HTML 'decoration' code - bold, etc
   | diff                      | The difference between quota and points
   | enddecor                  | Ends HTML 'decoration' code - bold, etc
   | engid                     | The 'Engid' field in the people table, for a record from this table.
   | href                      | The href of the links inserted when job_table / page is in edit mode
   | i                         | A counter variable used in loops
   | item                      | The name of a database field in the resultset used in the job table
   | jobitem                   | Used in foreach loop
   | line                      | Array representing record from result, the SQL query result
   | linecount                 | Line counter variable
   | numcols                   | Number of elements in an array, parameter to per_person_count function
   | quota_current_person      | Quota extracted from the result from a query to the DB regarding a person's quota (quota_current_person_result)
   | quota_current_person_resul| Result from query to DB regarding a person's quota (quota_query)
   | quota_query               | Query to DB regarding a person's quota
   | scorequery                | Query if dopastyearsummary is true; gets the points total for a particular person and year
   | scoreresult               | Result of $scorequery
   | separator                 | Used internally to separate fields in queries and HTML output (I think), in the editable_entry function; seems to be there to be escaped correctly.
   | str                       | Local string variable
   | thisscore                 | Result from $scorequery
   +---------------------------+---------------------------------------------------------------------------------------------

   OUTPUTS
   :::::::::::::::::::::::::::::::::
     * Variables - None (values of internal variables changed only)
     * HTML (complete table) or CSV format text is echoed.
     
   ///////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////// */

// Generic functions for job_table and people_table:
require('table_functions.inc');


global $csvmode;
global $modlead;
global $showemail;
global $multi_tables;
global $firstheader;
global $chosen_ordering;
global $jobitems;

/*
// use the CSV filename constructed in view_jobs.php:
if ($csvmode == 2)
{
    global $csv_filename;
    //require('csvhdr_redir.inc');
    // open the csv file for writing:
    $csv_file_handle = fopen($csv_filename, 'wb');
} */

// output a table header if not csvmode and only a single table is
// indicated for output:
if (($csvmode != 1) && ($csvmode != 2) && ($multi_tables != 1)) {
   //echo "<table class=\"restab\" rules=groups>\n";
   echo "<table class=\"unit_table\">\n";
}

$currentuser = "";
$currenttotal = 0;

// find out the index for the current year in the
// valid_years array.  This is used in the per_person_count function.
$curyrindex = -1;
for ($i=0; $i<count($valid_years); $i++) {
   if ($valid_years[$i][0] == $yearval) {
      $curyrindex = $i;
      break;
   }
}  


// some placeholder variables;
$a = 0;
$b = 0;
$c = 0;
// Call the show table function, instead of bespoke code for jobs:
ShowGeneralTable($isadminuser,
                 $adminwantstoedit,
                 $firstheader,
                 $adminjobcolshdr,
                 $result,
                 $adminjobcols,
                 $jobitems,
                 $showemail,
                 $jobcolshdr,
                 $csvmode,
                 $csv_file_handle,
                 $jobcols,
                 null,
                 $show_quotas,
                 $a,
                 $b,
                 $c);


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
    echo "<p>Please click the appropriate <b>FILTER</B> button to return to the on-screen output.</p>";
}






?>
