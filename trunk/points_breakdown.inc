<?php
// =====================================================================================
// MJ, 20090415
// Display a points breakdown...
// this page constructs a query, executes it and displays the result.
// =====================================================================================
// PRE-REQUISITES:
// :::::::::::::::
//  $var_person_uname - uname of the person investigated for a points breakdown
//  $yearval - year that is being investigated
//  $dbread - connection to database
//  $full_query - an existing database query; 'type' and 'points' columns to be specified:
//  $q_points_col - the name of the points column in $full_query
//  $q_type_col - the name of the type column in $full_query
//  $q_point_div - should we divide by 1, 10, 100?

global $var_person_uname, $yearval, $dbread, $full_query, $q_points_col, $q_type_col, $q_point_div;

// defaults:
$q_point_div = 100;
$q_type_col = 'type';
//if (!isset($q_points_col)) $q_points_col = 'points';
$q_points_col = 'points';

// INPUTS:
// =======
// This page initially used the query that generated the main people page listings.  This
// proved to be a little inconsistent, because either a list of jobs  or
// a list effectively of people is provided, and this points breakdown really needs jobs every time.
// Input buttons are:
// * Alphabetical buttons (start of surname) => POST.update = [A-Z]
// * group buttons                           => POST.update = [ChHist, GrpA, etc]
// * other group                             => POST.update = 'Other'
// * deleted                                 => POST.update = 'Deleted'
// * all                                     => POST.update = 'All'
// * A person's name                         => POST.show_jobs = 'Jones', 'Smith', etc

if (!(isset($_POST['show_jobs']) && strlen(trim($_POST['show_jobs']))>0))
{
    $person_query = "select A.uname, A.$q_type_col, A.hours, A.$q_points_col from jobs_$yearval as A inner join people_$yearval as B on A.uname = B.uname where ";
    $where = '';
    // Alphabetical
    if (ctype_upper($_POST['update']) && (strlen($_POST['update']) == 1))
    {
        $where = "SUBSTRING(B.surname, 1, 1) = '".$_POST['update']."' and A.deleted = FALSE and B.deleted = FALSE " ;
    }
    // Non-IE browsers: check for division match
    elseif (($matchdivision = array_search($_POST['update'], $divshortarray)) > 0)
    {
        $letter = $divletterarray[$matchdivision];
        $where = "(instr(B.division, '$letter') > 0)  and A.deleted = FALSE and B.deleted = FALSE ";
    }
    // IE: check for division match
    elseif (($matchdivision = array_search($_POST['update'], $divshortarrayie)) > 0)
    {
        $letter = $divletterarray[$matchdivision];
        $where = "(instr(B.division, '$letter') > 0) and A.deleted = FALSE and B.deleted = FALSE ";
    }
    // Other groups
    elseif ($_POST['update'] == ' Other ')
    {
        // because group letters can be 'A', 'B' or 'A, B', a simple "not in ('A', 'B') ..." is not sufficient
        $sub_wheres = array();
        // use the INSTR function for each division letter
        foreach ($divletterarray as $letter)
        {
           array_push($sub_wheres, "(instr(division, $letter) < 1)");
        }
        // and build the where query
        $where = implode('&& ', $sub_wheres).' and A.deleted = FALSE and B.deleted = FALSE ';
    }
    // All groups
    elseif ($_POST['update'] == ' All ')
    {
        $where = ' A.deleted = FALSE and B.deleted = FALSE ';
    }
    // deleted people?
    elseif ($_POST['update'] == ' Deleted ')
    {
        $where = ' B.deleted = TRUE ';
    }
    else
    {
        $where = "SUBSTRING(B.surname, 1, 1) = 'A' and A.deleted = FALSE and B.deleted = FALSE ";
    }
    
    //echo "<P><b>Full query:</b><br />$full_query<br /><hr />$person_query$where<hr/></p>";
    $full_query = $person_query.$where;

}
// otherwise, if show_jobs is set, a person's jobs have been selected.  In this case just use
// the predefinied $full_query:
else
{
    //echo "<P><b>Full query:</b><br />$full_query<br /><hr /></p>";
    //echo "<P><b>Show jobs:</b>".$_POST['show_jobs']."</p>";
}



// OUTPUTS:
// ::::::::
//  $points_array

// =====================================================================================
// MJ, 20090415:
// Trying the following alternative for calculating points:
// 1 - use the SQL sum function
// 2 - group by category (the intention is separate between college and faculty teaching and admin

//$points_query = "select type, sum(points) as pointsum from jobs_$yearval where ((deleted = FALSE) && (uname = '$var_person_uname' )) group by type;";

$points_query = " select dt.$q_type_col, sum(dt.$q_points_col) div $q_point_div  as pointsum, count(*) as JobCount, sum(dt.hours) as HoursSum ".
                " from ($full_query) as dt".
                " group by dt.$q_type_col;";
                
//echo "<p>$points_query</p>";

$points_result = mysql_query($points_query, $dbread);

if (!$points_result)
{
    echo "<P>Problem with the query:<br /> ".mysql_error()."</P>";
}
else
{
    $points_array = array();
    $hours_array = array();
    $count_array = array();
    
    while ($points_line = mysql_fetch_array($points_result, MYSQL_ASSOC))
    {
        // copy from query result to array:
        $points_array[$points_line['type']] = $points_line['pointsum'];
        $count_array[$points_line['type']] = $points_line['JobCount'];
        $hours_array[$points_line['type']] = $points_line['HoursSum'];
    }
    mysql_free_result($points_result);


    // look up what the job type codes mean:
    $codes = $all_categories['type'];
    $meanings = $all_captions['type'];
    $code_lookup = array_combine($codes, $meanings);
    $code_lookup['-'] = 'Other Jobs';

    // now draw the table on-screen:
    echo "<p>Jobs breakdown:</p>\n";
    echo "<table class='points_table'>\n";
    // headings:
    echo "<tr><th>Activity type</th><th>Points</th><th>Number of jobs</th><th>Hours</th></tr>\n";
    // data:
    foreach ($points_array as $type=>$points)
    {
        if (strlen(trim($type)) < 1) $type = '-';
        $hours = $hours_array[$type];
        $numjobs = $count_array[$type];
        echo "<tr><td>".$code_lookup[$type]." [$type]</td><td>$points</td><td>$numjobs</td><td>$hours</td></tr>";
    }
    //close table
    echo "</table>";
}
// =====================================================================================


?>
