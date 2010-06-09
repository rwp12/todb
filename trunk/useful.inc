<?php
function post_exists($key) {
   return (array_key_exists($key, $_POST));
}

function post_match($key, $match) {
   return (array_key_exists($key, $_POST) &&
           ($_POST[$key] == $match));
}

function post_nomatch($key, $match) {
   return (array_key_exists($key, $_POST) &&
           ($_POST[$key] != $match));
}

function post_ncmp($key, $match, $len) {
   return (array_key_exists($key, $_POST) &&
           (!strncmp($_POST[$key], $match, $len)));
}

function get_exists($key) {
   return (array_key_exists($key, $_GET));
}

function qfix($dat) {
  if ($dat == '')
    return 'NULL';
  else
#    return '"' . trim(strtr($dat, '"', "'")) . '"';
     return "'" . trim(addslashes($dat)) . "'";
}
/*
 * function crs2eng
 *      given a crsid it will attempt to convert it to an engid if no engid is found in the current users table will return crsid
 *      both crsid and eng id exist in th eflux users lookup array - but not the editors array!
 *      a temporary move towards using crsids (Raven) as the identifier for a user
 *      once references to engid have been removed from the application we shoul dbe able to remove this function and its call (in auth.inc)
 */

function crs2eng($crsid) {
        global $current_year, $dbread;
        $closedb=0;
        $ret=$crsid;
        if(!isset($dbread)){
                $closedb=1;
                open_db_read();
        }

        if(!isset($current_year))
                 die('crs2eng: unable to map crsid to eng id current_year not set: years.inc must be included before this function is called ');


        $res=mysql_query('SELECT engid FROM people_'.$current_year.' WHERE crsid = "'.$crsid.'"' , $dbread) or die('crs2eng: query failed: ' . mysql_error());
        if (isset($res))
                if ( $resset = mysql_fetch_array($res,MYSQL_ASSOC) )
                        $ret=$resset['engid'];

        if ( $closedb )
                close_db_read();

        return $ret;
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ShowTable:  This function takes an SQL query and
// shows an HTML table of it.
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function ShowTable($conn, $query, $classname)
{
    // execute query
    $result = mysql_query($query,$conn);

    // start the output table:
    echo "<table class=\"$classname\">\n";

    // loop through results:
    $heading = true;
    while ($row = mysql_fetch_assoc($result))
    {

        // show the heading row:
        if ($heading)
        {
            // start table header section
            echo "<thead>\n";
            // display headings:
            $headings = array_keys($row);
            echo "<tr>\n";
            foreach ($headings as $head) echo "<th>".htmlspecialchars($head)."</th>\n";
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
        foreach ($values as $value)  echo "<td>".htmlspecialchars($value)."</td>\n";
        echo "</tr>\n";
    }
    // complete table body and table:
    echo "</tbody>\n</table>\n";

    // free the result set:
    mysql_free_result($result);

    // finished!
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// A function to echo to the screen with a line return
// at the end (~ to writeln in pascal, println in Java)
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function echoln($text)
{
    echo $text."\n";
}



// MJ, December 2008
// get a list of points formulae from the database, in order to display
// a select-box list onscreen:
function GetFormulaeList($yearval)
{
    // SQL query
    $q = 'select Formula_ID, F_name, F_math_desc from point_formulae_'.$yearval.';';

    // connect to the DB
    global $dbread;
    // if(!isset($dbread)) AEC this gives a warning and stops things working
    open_db_read();

    // run the query
    $qres_formula =mysql_query($q, $dbread);

    // populate list:
    $form_list = array();
    while ($row = mysql_fetch_array($qres_formula))
    {
      $form_list[] = $row;
    }

    // close db connection
    close_db_read();

    // return:
    return $form_list;
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// A function to display a CSV button, and also
// respond if it was pressed.
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function InsertCSVApparatus()
{
    // where the CSV files are stored, on the local filesystem and
    // as a URL
    global $csv_output_path;
    global $csv_url_path;
    global $csvmode;
    global $csv_file_handle;
    global $csv_url;
    global $csv_file_only;
    global $tablename;

    // MJ: if the CSV option was selected (i.e. the user has just clicked the
    // 'Download as CSV' button, include the redirect to the CSV file:
    if(post_exists('CSV_button'))
    {
        // names of CSV-related files and URL
        $csv_filename = '';
        $csv_file_only = '';
        $csv_url = '';

        // create a local filename that is unique (using time):
        list($usec, $sec) = explode(' ', microtime());
        $time_in_sec = (float) (((float) $sec) + ((float) $usec));
        // construct the filename:
        $csv_file_only = $tablename.'_'.$time_in_sec.'.csv';
        $csv_filename = $csv_output_path.$csv_file_only;
        $csv_url = $csv_url_path.$csv_file_only;
        
        // open the csv file for writing:
        $csv_file_handle = fopen($csv_filename, 'wb');
        if (!$csv_file_handle)
        {
            error_log("Could not open CSV file handle.  Trying to open --$csv_filename--");
        }
        
        // set csvmode so that output goes to file rather than the screen:
        $csvmode = 2;
    }
    else
    {
      // AEC Don't show CSV button if in Edit mode - funny things happen if you press this button in edit mode
      if (!post_match('editmode', "Edit"))
      // output a 'Download CSV' button:
      echo '<input title="Click here to download a CSV file of the data displayed (based on the filtering criteria above)" type="submit" value="Download as CSV" name="CSV_button" />';
    }
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// close the CSV file, if it is likely to be open,
// and echo a link to download the file:
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function CloseCSVFile()
{
    global $csv_file_only;
    global $csv_url;
    global $csvmode;
    global $csv_file_handle;
    
    if ($csvmode == 2)
    {
        // close file:
        @fclose($csv_file_handle);
        // show link:
        echo '<p>Right-click on this link and select \'Save target/link as...\': <a href="'.$csv_url.'" target="_blank">'.$csv_file_only.'</a></p>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Take a mysql resultset and write it to the
// specified file handle
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function WriteQueryToCSVFile($resultset, $csv_file_handle)
{
    //echo "<p>Csvfilehandle is $csv_file_handle and resultset is $resultset</p>";

    $process_heading = true;
    $flush_every = 1;
    while ($row = mysql_fetch_assoc($resultset))
    {
        // write the headings:
        if ($process_heading) { fputcsv($csv_file_handle, array_keys($row)); $process_heading = false; };
        
        // write the data:
        fputcsv($csv_file_handle, array_values($row));
        
        // flush the buffer every 10 lines:
        $flush_every++;
        if (($flush_every % 10) == 0) fflush($csv_file_handle);
    }
    // flush buffer at the end as well, just for good measure!
    fflush($csv_file_handle);
    
    // resultset not freed, in case calling page wants to use it as well.
    // BUT: rewind:
    mysql_data_seek($resultset, 0);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// FPUTCSV
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// This function provides the same functionality as
// the one built into PHP 5+
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if (!function_exists('fputcsv'))
{
    function fputcsv($fh, $row)
    {
        $output_array = array();
        // run through the array looking for commas
        foreach ($row as $field)
        {
            $field = trim($field);
            // if a comma is found, enclose the field in double quotes
            if (strpos($field, ',')>-1) $field = '"'.$field.'"';
            // add to output array
            array_push($output_array, $field);
        }
        // glue the output array together with commas
        $output_rec = implode(',', $output_array)."\n";
        // write to the output stream provided:
        fputs($fh, $output_rec);
    }
}

// in PHP, if you have a function that returns an array:
// e.g. GetValues()
// ... and you want to access the third element, you can't do:
// $x = GetValues()[3];
// instead you must do:
// 1. $values = array();
// 2. $values = GetValues();
// 3. $x = values[3];
// with this function, you can do:
// $x = GetArrayValue(GetValues(), 3);
function GetArrayValue($array_arr, $element)
{
    return $array_arr[$element];
}


?>
