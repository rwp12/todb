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


// display the home button:
echo '<form action="'.$_SERVER['PHP_SELF'].'" name="homeform" method="POST"><table width="100%">'.
     '<tr><td align="right" valign="bottom"><input type="Submit" name="return" value="Home"></td></tr>'.
     '</table></form>';
     
     
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

// create a page display config object instance
// to hold the configuration information for this page:
$pdc_obj = new PageDisplayConfig();
// and init with the correct config:
$pdc_obj->page_prefix = 'editstudnum';

$pdc_obj->display_col_widths = array('coursename' => 40, 'student_count' => 20);
$pdc_obj->display_col_aliases = array('coursename' => 'Name of Course/Unit',
                               'student_count' => 'Count of students');
$pdc_obj->display_cols = array('coursename', 'student_count');
$pdc_obj->pkey_col = 'ID';
$pdc_obj->table_name = 'studentspercourse_'.$yearval;
$pdc_obj->table_display_name = 'Student numbers per course(unit)';


// the user can paste in tab/whitespace-separated list:
// states: none and 'saveallsnums':
$page_op = 'none';
echo '<div style="float:right; width: 50%">';
// if the 'submit_allsnums' button was pressed:
if (post_exists('submit_allsnums')) $page_op = 'pleasesave';
if (!$isadminuser) $page_op = 'none';
switch ($page_op)
{
    case 'none':
    echo '<p>Larger quantities of student count data can be entered into the text area below; the software expects '.
             'two space- or tab-separated columns of data pasted from Word/Excel.  Tip: if you are copying &amp; pasting'.
             ' from Word/Excel, please paste into Notepad first and then copy that before pasting.  This will ensure that '.
             ' the data loses any formatting which might interfere with the data entry process.  Any existing Units will be '.
             ' updated; new units will be added.'.
         '</p>';
    // show a form with a textarea:
    ShowStudentCountForm();
    break;
    
    case 'pleasesave':
    SaveSCData();
    // show a form with a textarea:
    ShowStudentCountForm();

    break;
}
echo '</div>';


//  Alternatively:
// Call the function that allows the user to display and edit this table:

ViewPage($pdc_obj);




// show the HTML at the bottom of the page
require('config/footer.inc');


function ShowStudentCountForm()
{
    global $yearval;
    echo '<form name="bigstudentcountform" action="'.$_SERVER['PHP_SELF'].'?yearval='.$yearval.'" method="POST" >'."\n";
    echo '<textarea rows="10" cols="40" name="biglotofstudents">&nbsp;</textarea>'."\n";
    echo '<br />';
    echo '<input type="submit" name="submit_allsnums" value="Save List"/>'."\n";
    echo '</form>'."\n";
}


function SaveSCData()
{
    global $yearval;
    // get a list of all the existing unit/course codes in the table:
    open_db_read();
    $courses = array();
    $q = 'select coursename from studentspercourse_'.$yearval.';';
    $res = mysql_query($q);
    while ($row = mysql_fetch_array($res))
    {
       $key = $row[0];
       $courses[$key] = 1;
    }
    close_db_read();
    
    open_db_write();
    global $dbwrite;
    
    echo '<pre>';


    // get POSTed submission:
    $all_data = &$_POST['biglotofstudents'];
    
    // break up by line:
    $lines = explode("\n", $all_data);
    
    // FOLLOWING LINES NOT necessary, as the tabs are replaced with spaces anyway.
    // choose delimiter:
    // it's probably going to be a tab, or a space; either way, run through the lines, trimming them
    // and counting the number of spaces or tabs:
    /*$tab_count = 0;
    $space_count = 0;
    $delim = ' ';
    foreach ($lines as $line)
    {
        $line = trim($line);
        $tab_count = substr_count($line, "\t");
        $space_count = substr_count($line, " ");
    }
    if ($tab_count > $space_count) $delim = "\t";
    */
    // run through the lines separating the fields:
    foreach ($lines as $line)
    {
        // replace any non-alphanumeric chars with spaces:
        $line = strtoupper($line);
        $len = strlen($line);
        
        // replace any underscores with empty strings:
        $line = str_replace ( '_', '', $line);
        
        for ($i = 0; $i < $len; $i++)
        {
          // if the character at is not alphanumeric, replace with space
          if (!(($line{$i} >= 'A') and ($line{$i} <= 'Z') or
            ($line{$i} >= '0') and ($line{$i} <= '9')))
            {  $line{$i} = ' '; }
        }

        // break string into array of letters
        $tmp_list = explode(' ', $line);

        // run through the list omitting any blanks from consecutive
        // delimiter characters
        foreach ($tmp_list as $tl)
        {
          if (strlen(trim($tl))>0)
          {
            // add to array of words
            $words[] = $tl;
          }
        }

        // now create a query to insert this into the DB:
        // first echo:
        /*
        echo '<p>';
        foreach ($words as $word)
        {
            echo $word;
            echo ' ['.mysql_real_escape_string($word, $dbwrite).'], ';
        }
        echo "</p>\n";
        */
        
        // update if the course is already in the DB; insert new if not:
        $key = $words[0];
        $q = '';
        if (strlen(trim($key)) > 0 and strlen(trim($words[1])) >0)
        {
          if (isset($courses[$key]))
          {
              $q = 'update studentspercourse_'.$yearval.' set student_count = '.mysql_real_escape_string($words[1], $dbwrite).' where coursename = \''.mysql_real_escape_string($key, $dbwrite).'\';';
          }
          else
          {
              $q = 'insert into studentspercourse_'.$yearval.'(coursename, student_count) values(\''.mysql_real_escape_string($words[0], $dbwrite).'\', '.mysql_real_escape_string($words[1], $dbwrite).'); ';
          }
          echo $q;
          
          if (mysql_query($q, $dbwrite) === true)
          {
            echo '...<span style="color: green">[OK]</span>'."\n";
          }
          else
          {
            echo '...<span style="color: red">[FAILED]</span>'."\n";

            // __FUNCTION__ returns the currently-called function name.
            // * May not work with older versions of PHP *
            error_log('Problem with MySQL insert/update in '.__FUNCTION__.': '.mysql_error($dbwrite)."\n");
          }
          
        }

        $words = null;
        $tmp_list = null;
    }
    
    echo '</pre>';
    
    close_db_write();
}


?>

