<?php

// Note: this requires db.inc, but should be included in the parent page.
// --->  require ('config/db.inc');

// valid_years: a list of year descriptions, each of which is an
// array of 
//      i) thing to append to table-name
//     ii) textual description for use in viewable web pages
//    iii) A 0/1 indication of whether or not the year is in flux.  If 0, in flux
// (and may get more entries added if necessary)
//
// KEEP THESE IN ASCENDING ORDER or the point-score reporting mechanism
// will break.

$valid_years = array (
   array("2008_09", "Oct 2008 to 2009", 1),
);

$current_year = "2008_09";

// MJ, July 2009:
/* --------------------------------------------------
I have moved the valid years storage to the database.
It is now stored in the 'config_years' table.
-----------------------------------------------------
Notes:
::::::
1. Columns mean the following:
COLUMN:             yearval     description       notinflux
MEANING:     array("2006_07", "Oct 2006 to 2007", 1),

2. The system tries to ensure that
   only years matching the yyyy_yy format convention
   are entered into the system.  The query uses a regular
   expression to select only matching tables and
   ordering is by yearval.

3. The current year is the year described as 'current_year'.
   That year must exist as an 'ordinary' year as well.
e.g.
mysql> select * from config_years;
+----+---------+-----------------+-----------+-----------+
| id | yearval | description     | notinflux | yearorder |
+----+---------+-----------------+-----------+-----------+
|  1 | 2008_09 | Oct 2008 - 2009 |         1 |         1 |
|  2 | 2009_10 | Oct 2009 - 2010 |         0 |         2 |
|  3 | 2012_13 | Oct 2012 - 2013 |         1 |         3 |
|  4 | 2008_09 | current_year    |         1 |      NULL |
+----+---------+-----------------+-----------+-----------+
4 rows in set (0.00 sec)


*/

$valid_years = null;
$valid_years = array();
$current_year = '';

open_db_read();
$years_query = "select yearval, description, notinflux from config_years where yearval regexp '^[0-9]+_[0-9]+' order by yearval;";
$years_res = mysql_query($years_query, $dbread);
$current_year_found = false;
if ($years_res)
{
    while ($row = mysql_fetch_assoc($years_res))
    {
        // find the current year and set if found:
        if ($row['description'] == 'current_year')
        {
            // assign value
            $current_year = $row['yearval'];
            // flag that the current year has been found
            $current_year_found = true;
        }
        // otherwise assign values to $valid_years array:
        else
        {
            // assign values:
            array_push($valid_years, array($row['yearval'], $row['description'], $row['notinflux']));
        }
    }
    
    // if no record for the current year was found, use the most recent one:
    if (!$current_year_found)
    {
        $year_count = count($valid_years);
        if ($year_count > 0)
        {
            $current_year = GetArrayValue($valid_years[$year_count-1], 0);
            //echo "<p>Current year set to $current_year.</p>";
        }
    }
    
    // free result set:
    mysql_free_result($years_res);
    // unset (destroy) some of the variables to ensure they do not clash later on in the program
    unset($years_res);
    unset($years_query);
}
else
{
    error_log('Defaulting to hard-coded valid_years (years.inc) settings due to MySQL error: '.mysql_error($dbread));
}
close_db_read();

//echo "<p>There are ".count($valid_years)." elements in valid_years; current year is $current_year</p>";


?>
