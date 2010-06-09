<?php

require('config/config.inc');
require('useful.inc');


// first see if we've hit the home button, and do a redirect
if (post_match('return', 'Home')) {
   header("HTTP/1.1 303 See Other");
   // redirects are required to be fully qualified URIs
   $redirect = "http://".$_SERVER['HTTP_HOST'].
   dirname($_SERVER['PHP_SELF'])."/index.php";
   header("Location: $redirect");

   exit;
}

// show the page header
require('config/header.inc');

// include the top of the page
require('config/top.inc');

// access functions for connecting to the database
require('config/db.inc');

// find out which years are in use
require('config/years.inc');

// check the status of the user:
require('auth.inc');

// include the functions for making this happen!
require('simple_table_io.inc');

/* AC moved down - need $yearval
     '<form action="'.$_SERVER['PHP_SELF'].'" name="recalcform" method="POST">'.
     '<input type="submit" value="Recalculate Points" name="recalc_points" />'.
     '</form>';     */

// What is the yearval?
if (!isset($yearval))
{
    // look in the GET string:
    if (isset($_GET['yearval']))
    {
        $yearval = $_GET['yearval'];
    }
    // otherwise maybe it was POSTED?
    elseif (isset($_POST['yearval']))
    {
        $yearval = $_POST['yearval'];
    }
    // otherwise choose the current year from the years.inc config
    else
    {
        global $current_year;
        echo '<h1>No year value selected; defaulting to '.$current_year.'</h1>';
        $yearval = $current_year;
    }
}


global $valid_years;
$yeardesc = '';
foreach ($valid_years as $vyear) { if ($vyear[0] == $yearval) $yeardesc = $vyear[1]; }
echo '<h3>For '.$yeardesc.':</h3>';

// display the home button:
echo '<form action="'.$_SERVER['PHP_SELF'].'" name="homeform" method="POST"><table width="100%">'.
     '<tr><td align="right" valign="bottom"><input type="Submit" name="return" value="Home"></td></tr>'.
     '</table></form>';
     
// display the recalculate button - AEC corrected to include the $yearval:
echo '<div class="recalc">'.
'<form method="post" action="view_points_formulae.php?yearval='.$yearval.'">'.
     '<input type="submit" value="Recalculate Points" name="recalc_points" />'.
     '</form>';


// if the user pressed the 'recalculate points' button:
if (post_exists('recalc_points'))
{
      // connect to the database:
      global $dbwrite;
      open_db_write();

      // MJ Dec 2008:
      // Given that points formulae are now in use, it is necessary to calculate
      // points where possible.
      /* To calculate points:
          1. A points formula other than NULL or 0 must be selected
          2. The job must have a unit (paper) associated with it
          3. The unit (paper) must exist in the studentspercourse table
          4. The unit in studentsperscourse should have a value for the number
             of students doing this unit (i.e. not NULL)

         This is relatively easily done with an inner join, where
         anything NULL falls off the back of the bus automatically:

         -- Calculate the points
         SELECT j.id as JobID, ((m.student_count * p.n_multiplier) + p.offset)*100. as CalculatedPoints
         FROM jobs_2008_09 j
         inner join point_formulae p on j.formula_ref = p.formula_id
         inner join units_2008_09 k on j.paper = k.uname
         inner join studentspercourse m on k.uname = m.coursename
         where j.formula_ref != 0
         and m.student_count is not null

         -- update the affected records:
         update jobs_2008_09 as j, point_formulae as p, units_2008_09 as k, studentspercourse as m
         set
         j.points = ((m.student_count * p.n_multiplier) + p.offset)*100.
         where j.formula_ref = p.formula_id
         and j.paper = k.uname
         and k.uname = m.coursename
         and j.formula_ref != 0
         and m.student_count is not null

      */

      // set up the query
      $pointsq = 'update jobs_'.$yearval.' as j, point_formulae_'.$yearval.' as p, units_'.$yearval.' as k, studentspercourse_'.$yearval.' as m '.
                 'set '.
                 'j.points = ((m.student_count * p.n_multiplier) + p.offset)*100. '.
                 'where j.formula_ref = p.formula_id '.
                 'and j.paper = k.uname '.
                 'and k.uname = m.coursename '.
                 'and j.formula_ref != 0 '.
                 'and m.student_count is not null; ';

       //echo "<p>$pointsq</p>";

      // run the query and look for any mysql failure
      if (($pointsres = @mysql_query($pointsq, $dbwrite)) === FALSE)
      {
        echo "<p>Points calculation update failed... ";
        echo mysql_error()."</p>";
      }
      else
      {
        echo "<p>Number of records recalculated: ".mysql_affected_rows()."</p>";
      }

      // close database connection
      close_db_write();
}

// finish off the recalculation DIV:
echo '</div>';

// create a page display config object instance
// to hold the configuration information for this page:
$pdc_obj = new PageDisplayConfig();
// and init with the correct config:
$pdc_obj->page_prefix = 'editformula';

$pdc_obj->display_col_widths = array('F_name' => 40, 'F_Math_Desc' => 20, 'n_Multiplier' => 10, 'offset' => 3);
$pdc_obj->display_col_aliases = array('F_name' => 'Formula name',
                               'F_Math_Desc' => 'Mathematical <br/>description',
                               'n_Multiplier' => 'Multiplier',
                               'offset' => 'Offset');
$pdc_obj->display_cols = array('F_name', 'F_Math_Desc', 'n_Multiplier', 'offset');
$pdc_obj->pkey_col = 'Formula_ID';
$pdc_obj->table_name = 'point_formulae_'.$yearval;
$pdc_obj->table_display_name = 'Point Formulae';
$pdc_obj->order_by_col = 'F_Name';

// Call the function that allows the user to display and edit this table:
ViewPage($pdc_obj);

// show the HTML at the bottom of the page
require('config/footer.inc');



?>

