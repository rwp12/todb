<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('config/people.inc');
require('locks.inc');


/*

TODO: MJ, 20090417

* The list of job codes acceptable and considered by the query should be drawn from config or jobs.inc,
not hard-coded.


*/


$yearval = $_REQUEST["yearval"];

$tablename = 'faculty_summary';
$tablething = 'faculty_summary';

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

// start the output process here
require('config/header.inc');
// header.inc finishes just before the </head>, so we can stick javascript
// in here ...
require('view_jobs_java.inc');
// and finish off with the </head> and the top of page decoration here ...
require('config/top.inc');

require('common2.inc');

////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////
// As the subjectgroup table has been removed, it is necessary to insert the names of the divisions
// manually.  This is easiest done as follows:
/////////////////////////////////////////////////////////////////////////////////////////////////
// list of divisions=>division names
/*
global $division_longnames;
// the query string fragment:
$div_lookup = '';
// the number of closing brackets to add (this avoids recursion)
$brackets = 0;
foreach ($division_longnames as $dvl_key => $dvl_val)
{
    $div_lookup .= "IF(p.division='$dvl_key', '$dvl_val', ";
    $brackets++;
}
// if there is no division match, call it 'other'
//$div_lookup .= "'Other'";
$div_lookup .= "IF (trim(p.division) = '' or p.division is NULL, 'Other', p.division)";
// now add the closing brackets:
for ($i=0; $i<$brackets; $i++)
{
    $div_lookup .= ')';
}                                 */

// Which produces something like:
// ::::::::::::::::::::::::::::::
/*IF(p.division='B', 'Business and Management Economics',
    IF(p.division='F', 'Finance and Accounting',
       IF(p.division='H', 'Human Resources Organisation',
          IF(p.division='M', 'Management Science',
             IF(p.division='O', 'Operations and IT',
                IF(p.division='S', 'Strategy and Management',
                   IF(p.division='T', 'Joint Appointments CUED',
                      IF(p.division='U', 'Outsourced',
                         IF(p.division='V', 'Postdocs & PhDs & TA & RA ', 'Other')))))))))
*/
// I apologise for this.  I admit that it is a bit of a compromise.
// On the good side, notice that no recursion was necessary?  Isn't that impressive?  No?  Ok...
/////////////////////////////////////////////////////////////////////////////////////////////////

// COLUMNS TO DISPLAY
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Build an array to store the tooltips for each heading
$titles = array(  'Unique name of person',
                  'Hours spent lecturing',
                  'Hours spent in practicals',
				  'Hours spent in seminars',
				  'Hours spent in supervision',
                  'TOTAL hours spent on Teaching (Lecturing, Practicals, Seminars and Supervisions)',
                  'Points from lecturing jobs',
                  'Points from practical jobs',
				  'Points fromseminar jobs',
				  'Points from supervision jobs',
                  'TOTAL Points from jobs spent on Teaching (Lecturing, Practicals, Seminars and Supervisions)',
			      'Count of Examining jobs',
                  'Count of Administration jobs',
                  'Count of Other jobs'
 
                  );
                         
// This is the list of columns to select, as well as what will be used in the order by section:
$select_order = array(   'Person'        ,
                         '`Lectures (hrs)`',
                         '`Practicals (hrs)`'    ,
                         '`Seminars (hrs)`',
 						 '`Supervision (hrs)`',
						 '`TOTAL Teaching (hrs)`'     ,
                         '`Lectures (points)`',
                         '`Practicals (points)`'    ,
                         '`Seminars (points)`',
 						 '`Supervision (points)`',
						 '`TOTAL Teaching (points)`'     ,
 						 '`Examining (count)`' ,
                         '`Admin (count)`',
						 '`Other (count)`'
                             
                         );                         

// it would be nice to be able to filter the records by category, as is done in Excel.
// the problem is, any values that are derived by an aggregate function (e.g. sum()) cannot be put in the where
// clause.  So, only where-permissable values can be filterable.
// This array is the list of column numbers that can be filtered.
$show_filter_cols = array(0);

// what to select?
$what_to_select = array('p.uname',
                        'ifnull(round(subqueryL.hours, 1), 0)',
						'ifnull(round(subqueryP.hours, 1), 0)',
						'ifnull(round(subqueryS.hours, 1), 0)',
						'ifnull(round(subqueryV.hours, 1), 0)',
                        'ifnull(round(totals.hours, 1), 0)',
 	 					'ifnull(round(subqueryL.points, 0), 0)',
						'ifnull(round(subqueryP.points, 0), 0)',
						'ifnull(round(subqueryS.points, 0), 0)',
						'ifnull(round(subqueryV.points, 0), 0)',
						'ifnull(round(totals.points, 0), 0)',
						'ifnull(round(subqueryE.numjobs, 0), 0)',
						'ifnull(round(subqueryA.numjobs, 0), 0)',
						'ifnull(round(subqueryO.numjobs, 0), 0)'

                       );                       

// build the FROM clause

// grouping job type as follows:
// L - Lectures
// C - JC, PBL, Discuss
// P - Projects
// S - Safety
// E - Examining
// A - Administration
// O - Outreach
// W - Web-based Teaching

           
$all_from = " people_$yearval as p ". /*inner joins follow "*/
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval where type in ('L','P','S','V') and deleted = false group by uname) as totals on p.uname = totals.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='L' and deleted = false group by uname) as subqueryL on totals.uname = subqueryL.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='P' and deleted = false group by uname) as subqueryP on totals.uname = subqueryP.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='S' and deleted = false group by uname) as subqueryS on totals.uname = subqueryS.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='V' and deleted = false group by uname) as subqueryV on totals.uname = subqueryV.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='E' and deleted = false group by uname) as subqueryE on totals.uname = subqueryE.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type='A' and deleted = false group by uname) as subqueryA on totals.uname = subqueryA.uname  ".
            " left join (select uname, sum(hours) as hours,  count(*) as numjobs, sum(points)/100 as points from jobs_$yearval  where type not in ('L','P','S','V', 'E', 'A') and deleted = false group by uname) as subqueryO on totals.uname = subqueryO.uname  ";
         

//$all_group_by = " totals.uname ";
$all_group_by = "";

// Show the table:
echo "<h3>Workload allocations by person</h3>";
echo "<P>\n";

ShowOrderableTable($dbread, $display_select_arr, $all_from, $all_group_by, $what_to_select, $select_order, $show_filter_cols, "unit_table", $titles);
echo "</P>\n";

//echo "<P>juhgfuifuig</p>";

echoln('</form>');

////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////



close_db_read();

require('config/footer.inc');



// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// CreateGoogleChart:  This function takes name=>values and
// draws a bar chart, parity is the 100% value as a
// fraction of the width of the graph (i.e. 100% / maximum)
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

function CreateGoogleChart($names, $values, $parity)
{
    // now encode for Google Charts:
    //chart type:
    // bhs = horisontal bar chart

    $cht = 'cht=bhs';
    // data:
    $chd = 'chd=t:'.implode(',', $values);
    // chart size:
    $chs = 'chs=100x300';
    // url:

    $url = 'http://chart.apis.google.com/chart?';

    // labels:
    $chxt = 'chxt=y';
    // names need to be in reverse order (??)
    $chl='chxl=0:|'.implode('|', array_reverse($names));
    $chbh ='chbh=a,2,2';
    
    // 100% line:
    $parity = round($parity, 1);
    $p_end = round($parity, 1) + 0.01;
    //$chm='chm=r,FF0000,0,'.$parity.','.$p_end.'';
    $chm='chm=r,FF0000,0,0.0,'.$p_end.'|r,AABBFF,0,'.$p_end.',1.0';
    // colour:
    $chco = "chco=0000FF";


    $options = array($cht, $chs, $chd, $chxt, $chl, $chbh, $chm, $chco);
    $chartref = $url.implode('&amp;', $options);

    // build the URL:
    //$chartref = $url.'&amp;'.$chxt.'&amp;'.$chs.'&amp;'.$chl.'&amp;'.$chd.'&amp;'.$cht;
    echoln("<!-- See Google Charts API:\n http://code.google.com/apis/chart/ \n $chartref  -->");

    // insert into page:
    echoln('<img border="1" src="'.$chartref.'" alt="Allocations vs quotas" />');
}




// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ShowTable:  This function takes an SQL query and
// shows an HTML table of it.
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ShowOrderableTable()
// --------------------
// $from - the SQL from clause
// $what_to_select - array of select expressions
// $order - array of select aliases (same index as $what_to_select)
// $show_filter_cols - array of field indices in result table that show be displayed as filterable (those that do not have an
//                     aggregate function in the select expression)
function ShowOrderableTable($conn, $display_select_arr, $from, $groupby, $what_to_select, $order, $show_filter_cols, $classname, $tooltips)
{
    global $yearval;
    
    $csvmode = 0;
    
    // create the select expression as an array of 'expr as alias' strings;
    $display_select_arr = array();
    $count = count($order);
    for ($index = 0; $index < $count; $index++)
    {
        $display_select_arr[$index] = $what_to_select[$index]." as ".$order[$index];
    }
    
    // these are then merged into a single select expression:
    $display_select = implode(', ', $display_select_arr);

    // ORDER
    // --------------------------------------------
    // was a post submission of OrderByChoice made?
    $sel_order = 0;
    if (isset($_POST['OrderByChoice']))
    {
        $sel_order = mysql_escape_string($_POST['OrderByChoice']);
    }
    
    // MJ, Dec 2008: if the user clicked on the 'Download as CSV' BUTTON (rather than
    // link), the system should indicate that output should be in CSV mode.
    // However, not the same csv mode as before!  Rather, mode = 2 indicates that a
    // file should be created and the csv data output to it, as well as the screen,
    // and the page then redirects to this.
    if (post_exists('CSV_button')) $csvmode = 2;


    // FILTER BY:
    // --------------------------------------------
    /*  MJ 20090302:  this works as follows:
        1. If the user selects a value to filter by, at least one variable in the form FilterBy_x will be
           present.  x is the column number in the result set corresponding with field chosen for filtering by.
           The value of FilterBy_x will be the row number of the distinct value (filter by) list for that field.
        2. The POST array is searched for matching variables.
        3. Distinct values for each field are queried from the DB
        4. FilterBy_x = y: y's meaning z is looked up in the distinct value array  distinct[x][y] => z;
        5. FilterBy z's are collected into a where clause for the SQL statement.
    */
    $filterby_post = array();
    
    $filter_selected = 0;

    foreach ($_POST as $post_k => $post_v)
    {
        // search for post variables with 'FilterBy_' in the name
        $pos = strpos($post_k, 'FilterBy_');

        // if found, store in the filterbypost array
        if (($pos > -1) && ($post_v != '-1'))
        {
            // if one is found, assign to filterby array:
            $key = str_replace('FilterBy_', '', $post_k);
            $filterby_post[$key] = $post_v;
        }
    }
    // filtering needs to go in the 'where' clause:
    $where = ' true ';
    $first_distinct_sets = GetDistinctSets($conn, $display_select_arr, $from, $where, $groupby, $order);
    
    // iterate through the filter posts to construct where clause:
    $wheres = array();
    foreach ($filterby_post as $f_key => $f_val)
    {
        // $wheres[] .= ' '. $order[$f_key].' = "'.$first_distinct_sets[$f_key][$f_val].'"';
        $wheres[] .= ' '. $what_to_select[$f_key].' = "'.$first_distinct_sets[$f_key][$f_val].'"';
    }
    
    // put together the where clause:
    $where_new = ' '.implode(' and ', $wheres).' ';

    if (count($wheres) > 0) $where = html_entity_decode($where_new);

    // ------------------------------------------------
    // CGI stuff:
    // ------------------------------------------------
    echoln('<input type="submit" value="Update" />');
    // generate the CSV button:
    InsertCSVApparatus();
    // ------------------------------------------------
    // DB stuff
    // ------------------------------------------------
    $groupby_clause = '';
    if (strlen(trim($groupby))> 0) $groupby_clause = ' group by '.$groupby; 
    // construct main display query:
    $query = 'select '.$display_select.' from '.$from.' where '.$where.$groupby_clause.' order by '.$order[$sel_order].';';
    //$query .= $order[$sel_order].';';
    //echo "<P>Query is $query</p>\n";
    
    // construct queries for getting distinct sets for the filter-by menus:
    //$distinct_sets = GetDistinctSets($conn, $from, $where, $groupby, $order);
    $distinct_sets = $first_distinct_sets;
    
    // execute query
    $result = mysql_query($query,$conn);
    
    if ($csvmode ==2)
    {
        global $csv_file_handle;
        WriteQueryToCSVFile($result, $csv_file_handle);
        CloseCSVFile();
        echo "<p>Please click <b>UPDATE</b> to return to the on-screen output.</p>";
        return;
    }

    // start the output table:
    echoln("<table class=\"$classname\">\n");
    

    // loop through results:
    $heading = true;
    $rowcount = 0;
    while ($row = mysql_fetch_assoc($result))
    {

        // show the heading row:
        if ($heading)
        {
            // start table header section
            echo "<thead>\n";
            // display headings:
            $headings = array_keys($row);

            // show line of headings:
            echo "<tr>\n";
            $n=0;
            foreach ($headings as $head)
            {
                // column name
                // -----------
                  echo "<th title='$tooltips[$n]'>".htmlspecialchars($head);
                  echo "\n</th>";
                  $n++;
            }
            echo "</tr>\n";
            
            // show line of order by and filter by apparatii:
            echo "<tr>";
            $oi = 0;
            foreach ($headings as $head)
            {
                echo "<th class='ut_extra'>\n";
                  
                // the Order By? radio button:
                // ---------------------------
                  echo '<input type="radio" name="OrderByChoice" value="'.$oi.'" ';
                  if ($oi == $sel_order) echo ' checked';
                  echo '/>Order by?';
                // Filter By drop down:
                // --------------------
                  // only show this column to be filterable if allowed:
                  if (in_array($oi, $show_filter_cols))
                  {
                    echo '<br />Filter by:<br /> <select name="FilterBy_'.$oi.'">';
                    // list of things to filter by (the distinct values contained in this column)
                    // in $distinct_sets
                    echo '<option value="-1">Show all</option>';
                    // counter for options:
                    $di = 0;
                    foreach ($distinct_sets[$oi] as $d_item)
                    {
                        // if this was chosen as a filter criteria set to 'selected':
                        $ch = '';

                        if (isset($filterby_post[$oi]))
                        {
                            // This is the right column.
                            if ($di == $filterby_post[$oi]) $ch = 'selected';
                        }
                    
                        echo '<option value="'.$di.'" '.$ch.'>'.$d_item.'</option>';
                        $di++;
                    }
                    echo '</select>'."\n";
                  }
                // end of heading
                // --------------
                  echo"</th>\n";
                  
                $oi++;
            }
            echo "</tr>\n";
            $heading = false;
            // complete table header section
            echo "</thead>\n";

            // start the table body section
            echo "<tbody>";
        }

        // show the data:
        $values = array_values($row);
        echo "<tr>\n";
        
    	$col_n = 0;
        foreach ($values as $value)
        {
            $shade = (($rowcount % 2) != 0) ? 'class="shaded"' : '';
            // AEC Make numbers right hand justified - would be nice to use table level col alignment but this does not work in Firefox
            $align = "align='left'";
            if ($col_n > 0) {$align = "align='right'";}
            
            echo "<td $align $shade>".htmlspecialchars($value)."</td>\n";
            
            $col_n++;
        }
        
        $rowcount++;
        echo "</tr>\n";
    }
    // complete table body and table:
    echo "</tbody>\n</table>\n";
    
    // show an error if there were no records:
    if ($oi == 0)
    {
        echo '<p>No records were returned.  Press the UPDATE button to return to a full listing.  ';
        echo mysql_error($conn);
        echo '; Query:<br/>'.$query.'</p>';
    }

    // free the result set:
    mysql_free_result($result);

    // finished!
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~





// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//  function GetDistinctSets()
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function GetDistinctSets($conn, $display_select_arr, $from, $where, $groupby, $order)
{
    // construct queries for getting distinct sets for the filter-by menus:
    $distinct_sets = array();
    $oi = 0;
    $groupby_clause = '';
    if (strlen(trim($groupby))> 0) $groupby_clause = ' group by '.$groupby; 
    
    foreach ($order as $key => $field)
    {
        // query:
        $where = 'true';
        $q_f = 'select distinct '.$display_select_arr[$key].' from '.$from.' where '.$where.$groupby_clause.' order by '.$field.';';
        //echo "<p>Running query:\n<br />$q_f</p>";
        // execute:
        $r_f = mysql_query($q_f,$conn);

        // create tmp array:
        $tmp_arr = array();

        // run through results assigning to array:
        while ($row = mysql_fetch_array($r_f, MYSQL_NUM))
        {
            $tmp_arr[] = htmlspecialchars($row[0]);
        }
        
        //echo 'Temp array has '.count($tmp_arr).' elements.';
        //echo '</p>';

        // create array entry:
        $distinct_sets[$oi] = $tmp_arr;

        // clean up
        $tmp_arr = null;
        mysql_free_result($r_f);
        $r_f = null;

        // increment counter
        $oi++;

    }
    
    return $distinct_sets;
}
?>







