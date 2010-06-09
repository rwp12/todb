<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');

require('auth.inc');
//require('config/jobs.inc');
//require('config/people.inc');
require('locks.inc');

require('common1.inc');

// start the output process here
require('config/header.inc');
require('config/top.inc');

// we set $tablename to be the table to be edited before common1.inc ,
// but here it's the name of the script to be submitted to ...
$tablename = 'new_year';
require('common2.inc');

require('config/new_years.inc');


///  TODO: autogenerate the index page links from the year list.

$info = "<h1>Manage Years facility</h1>";
$info .= "<p>Use this facility to :<ul>
             <li />Prepare the system for the <b>next academic year</b>
             <li /><b>Create past years</b> to be populated with archived data.
             <li />Change the '<b>flux status</b>' of a year's data
             <li/><b>Remove a year</b> from the system
             <li/>Set the '<b>current year</b>'
             </ul>
             This system works by copying the table definitions and
             some data from an existing year.  You can choose which columns of data are carried across by
             editing the file <pre>config/new_years.inc</pre>  You can also choose the name and
             description of the year to add.</p>";


// instruct form to fill in submitted values
$descripvalue = '';
if (isset($_POST['new_year_descrip'])) $descripvalue = $_POST['new_year_descrip'];
$newyearvalue = '';
if (isset($_POST['new_year_value'])) $newyearvalue = $_POST['new_year_value'];
$from_yearval = '';
if (isset($_POST['yearslist'])) $from_yearval = addslashes($_POST['yearslist']);


if ($isadminuser)
{

    // --------------------------------------------------------------------------------------------------------------
    $showinputs = true;
    $showdonebutton = false;
    
    // arrays of mysql statements for creating new tables and inserting data:
    $create_queries = array();
    $insert_queries = array();
    // respond to things the user has set here
    // ---------------------------------------
    // DONE BUTTON WAS PRESSED
    if (isset($_POST['okbutton']))
    {
        // not strictly necessary, but nice to do things explicitly
        $showinputs = true;
        $showdonebutton = false;
    }
    // REQUEST TO SET CURRENT YEAR
    elseif (isset($_POST['curryear_button']) && isset($_POST['yearslist']))
    {
        open_db_write();
        $cyear_q = "update config_years set yearval = '$from_yearval' where description = 'current_year';";
        echo "<p>Setting current year to $from_yearval: <pre>$cyear_q</pre>";
        // run the query
        mysql_query($cyear_q, $dbwrite);

        $showinputs = false;
        close_db_write();
        $showdonebutton = true;

    }
    // REQUEST TO TOGGLE FLUX STATUS of a YEAR
    // :::::::::::::::::::::::::::::::::::::::
    elseif (isset($_POST['fluxstat_button']) && isset($_POST['yearslist']))
    {
        open_db_write();
        $flux_stat_q = "update config_years set notinflux = not(notinflux) where yearval = '$from_yearval' and description != 'current_year';";
        echo "<p>Toggling flux status for year <b>$from_yearval</b>:<pre>$flux_stat_q</pre></p>";
        // run the query
        mysql_query($flux_stat_q, $dbwrite);

        $showinputs = false;
        close_db_write();
        $showdonebutton = true;
    }
    // REQUEST FOR DELETION is here - did the user request that a year be deleted?
    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    elseif (isset($_POST['delyear_button']) && isset($_POST['yearslist']))
    {
        echo "<P>The user requested that ".$_POST['yearslist']." be deleted!</P>";
        echo "<P>The current year is $current_year</p>";
        if ($current_year == trim($_POST['yearslist']))
        {
            echo "<p><b>Warning:</b>You are attempting to delete the current year.  Please set another year to be the current year, and then try again.</p>";
            $showinputs = false;
            $showdonebutton = true;
        }
        else
        {
            echo "<p>The following tables will be deleted: </p><ul>";
            // get the list of tables:
            //$from_yearval = addslashes($_POST['yearslist']);
            $tlist_q = "select table_name from information_schema.tables where table_schema = '$database_name' and table_name like '%$from_yearval';";
            $tlist_res= mysql_query($tlist_q, $db_metadata);
            $count = 0;
            while ($row = mysql_fetch_array($tlist_res))
            {
                $count++;
                // md5 hashes of the table names are used as variable names:
                // AEC check each file by default
                echo "<li /><input type='checkbox' name='deltable".md5($row[0])."' checked='true'>$row[0]";
            }
            mysql_free_result($tlist_res);
            if ($count >0)
            {
            	// AEC check each file by default so tell them to uncheck if needed.
                echo "</ul><p>Please uncheck any tables that you do not want deleted and then click the Definitely Delete button.  To cancel and go back to main screen, click 'Done'.</p>";
                echo "<input type='submit' value='Definitely delete these years!' name='def_del_button' />\n";
                // save the yearslist variable so that the delete will work:
                echo "<input type='hidden' value=".$_POST['yearslist']." name='yearslist' />";
            }
            else
            {
                echo "<li />No tables were found to delete!</ul>";
            }
        
            $showinputs = false;
            $showdonebutton = true;
        }
    }
    // CONFIRM DELETION and ACTUALLY DELETE here:
    // ::::::::::::::::::::::::::::::::::::::::::
    elseif ( isset($_POST['def_del_button']) && isset($_POST['yearslist']))
    {
        // delete the records the user has selected.
        // the records are in the form deltable_28a34e69237490
        // we cannot decode the table name from the hash, but we can test them against the hashes.
        // if there are no differences, go ahead and delete!
        
        // final blunt check that current year is not deleted:
        //echo "<p>Current year is $current_year and from_yearval is $from_yearval</p>";
        if ($current_year == trim($from_yearval)) die("Cannot delete current year!");
        
        // prevent the last remaining year from being deleted:
        $safety_q = 'select count(*) from config_years where description != "current_year";';
        $safety_res = mysql_query($safety_q, $dbread);
        $row = mysql_fetch_array($safety_res);
        if (isset($row[0]))
        {
            if ($row[0] < 2) die("Cannot delete the last remaining year!  Copy the year first and then attempt to delete.");
        }
        
        //die("safety net");
        
        $deletelist = array();
        $candidatelist = array();
        $tobedeleted = array();
        
        $delete_tables = true;
        
        // get the submitted list:
        foreach ($_POST as $pkey =>$pvar) {
            if (substr($pkey, 0, 8) == 'deltable') {
                array_push($deletelist, $pkey);
            }
        }
        
        $tlist_q = "select table_name from information_schema.tables where table_schema = '$database_name' and table_name like '%$from_yearval';";
        $tlist_res= mysql_query($tlist_q, $db_metadata);
        while ($row = mysql_fetch_array($tlist_res))
        {
            // search array of submitted values for the md5 of this table:
            foreach ($deletelist as $delitem)
            {
                if ($delitem == 'deltable'.md5($row[0]))
                {
                    array_push($candidatelist, $row[0]);
                }
            }
        }
        mysql_free_result($tlist_res);
        
        // delete the tables:
        // ::::::::::::::::::
        open_db_write();
        echo "<ul>";
        foreach ($candidatelist as $cand)
        {
            // delete the tables:
            // ::::::::::::::::::
            echo "<li />Deleting: <b>$cand</b>...";
            $del_q = "drop table $cand;";
            echo "<pre>$del_q</pre>";
            mysql_query($del_q, $dbwrite);
            if (mysql_error($dbwrite)) echo mysql_error($dbwrite);
        }
        echo "</ul>";
        
        // and finally, remove the entry for this year from the config_years table:
        $years_del_q = "delete from config_years where yearval = '$from_yearval';";
        echo "<p>Removing the following year entry from config_years: $from_yearval - <pre>$years_del_q</pre></p>";
        mysql_query($years_del_q, $dbwrite);
        
        close_db_write();
        
        $showinputs = false;
        $showdonebutton = true;
        
    }
    // CREATE NEW TABLES here
    // ::::::::::::::::::::::
    // check that all necessary fields were entered
    elseif (isset($_POST['yearslist']) && isset($_POST['new_year_descrip']) && isset($_POST['new_year_value']))
    {
        // check that they are significant:
        if ((strlen(trim($_POST['new_year_descrip'])) > 0) && (strlen(trim($_POST['new_year_value'])) > 0))
        {
            // $showinputs = false; - MJ# undo
            $new_yeardesc = addslashes($_POST['new_year_descrip']);
            $new_yearval = addslashes($_POST['new_year_value']);
        
            // copying from:
            $from_yearval = addslashes($_POST['yearslist']);
            
            // is there a table name clash?
            $is_clash = false;
            
            // now find all tables with FROM yearval:
            $tlist_q = "select table_name from tables where table_schema = '$database_name' and table_name like '%$from_yearval';";
            //echo "<p>Query is $tlist_q</p>";
            // run query:
            $tlist_res= mysql_query($tlist_q, $db_metadata);
            if ($tlist_res)
            {
                echo "<p>Tables copied: <ul>";
                $count = 0;
                while ($row = mysql_fetch_array($tlist_res))
                {
                    $count++;
                    
                    // get the old (COPY FROM) table name:
                    $t_name = $row[0];
                    
                    // create queries:
                    // :::::::::::::::
                    // get the new table name
                    $ntable_name = str_replace($from_yearval, $new_yearval, $t_name);

                    // table creation SQL:
                    $n_query = "create table $ntable_name like $t_name;";

                    // table data copying SQL:
                    // :::::::::::::::::::::::
                    // Data copying is directed by the columns specified
                    //  in new_years.inc in the config directory
                    // :::::::::::::::::::::::
                    // get the table prefix name:
                    $table_prefix_underscore = str_replace($from_yearval, '', $t_name);
                    $table_prefix  = substr($table_prefix_underscore, 0, strlen($table_prefix_underscore)-1);
                    $column_list = implode(', ', $copy_columns[$table_prefix]);
                    $n_copy_query = "insert into $ntable_name($column_list) (select $column_list from $t_name );";

                    //check that the table does not exist before hand -
                    // if it does exist, there is a danger that the insert will repeat all the records
                    // in the table.
                    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
                    $exist_q = "select count(*) from information_schema.tables where table_schema = '$database_name' and table_name = '$ntable_name';";
                                        
                    $res_exist = mysql_query($exist_q, $db_metadata);
                    $num_exist = GetArrayValue(mysql_fetch_array($res_exist), 0);
                    mysql_free_result($res_exist);

                    // if it does not exist, queue for execution:
                    // ::::::::::::::::::::::::::::::::::::::::::::::::::::
                    if ($num_exist < 1)
                    {
                        // queue queries to create table:
                        array_push($create_queries, $n_query);
                        // ... and those to copy data:
                        array_push($insert_queries, $n_copy_query);
                    }
                    else
                    {
                        // flag the clash
                        $is_clash = true;
                        // warn the user if the table existed and the queries were not run:
                        echo "<li />The table that you attempted to create (<b>$ntable_name</b>) already exists!  Please enter a different new year value.";
                    }

                }
                
                if ($count < 1)
                {
                    $showinputs = true;
                    echo "<li /><h2>ERROR: Sorry, no tables for year '$from_yearval' were found.  Please try another year!</h2>";
                }
                else
                {
                    if (!$is_clash)
                    {
                        open_db_write();
                        foreach ($create_queries as $cq_key => $cq_value)
                        {
                            echo "<li />Creating table... ";
                            mysql_query($cq_value, $dbwrite);
                            
                            
                            if (mysql_error($dbwrite)) echo mysql_error($dbwrite).'<br /><pre>'.$cq_value.'</pre>';
                            echo "Inserting data...";
                            mysql_query($insert_queries[$cq_key], $dbwrite);
                            if (mysql_error($dbwrite)) echo mysql_error($dbwrite).'<br /><pre>'.$insert_queries[$cq_key].'</pre>';
                        }

                        $descripvalue = '';
                        $newyearvalue = '';
                        $showinputs = false;
                        $showdonebutton = true;
                        
                        // now, update config_years table:
                        $new_year_conf_q = "insert into config_years(yearval, description) values('$new_yearval', '$new_yeardesc'); ";
                        mysql_query($new_year_conf_q, $dbwrite);
                        close_db_write();
                    }
                }
                echo "</ul></p>";
                
                // report the name clash:
                if ($is_clash)
                {
                    echo "<p>There were table name clashes.  Please try a different year value in below</p>";
                    $showinputs = true;
                }

            }
        }
        else $showinputs = true;
        
    }

    // --------------------------------------------------------------------------------------------------------------
    // SHOW THE INPUT SCREEN HERE
    // ::::::::::::::::::::::::::

    if ($showinputs)
    {
        // allow user to make choices here:
        // --------------------------------
        // Information for the user:
        echo $info;

        // show fieldset:
        echo "<p><fieldset>";
        echo "<legend>Select Year and Operation</legend>";

        // Which year's data must the system use to create the new year?
        echo "<label title='Which year data must the system use to create the new year?'>Please enter the year you wish to copy FROM:<br />";

        // read years.inc to get available years:
        echo "<select name='yearslist'>";
        $yearcount = 0;
        foreach ($valid_years as $vkey => $vyear)
        {
            $checked = '';
            if ($from_yearval == $vyear[0]) $checked = 'selected';
            $influx = '';
            if ($vyear[2] == '0') $influx .= ' [in FLUX]';
            if ($vyear[0] == $current_year) $influx .= '[CURRENT]';
            echo "<option value='$vyear[0]' $checked>$vyear[1] (yearval = $vyear[0])$influx</option>";
            $yearcount++;
        }
        echo "</select></label>\n";
        
        
        // now find a list of 5 years before and after the lowest and highest years in valid_years to offer
        // as new year options; also offer any missing ones:
        $lowest_year = $valid_years[0][0];
        $highest_year = $valid_years[count($valid_years)-1][0];
        $lowest_numeric = (int) GetArrayValue(explode('_', $lowest_year), 0);
        $low_range_start = $lowest_numeric - 5;
        //$low_range_end   = $lowest_numeric - 1;
        $highest_numeric = (int) GetArrayValue(explode('_', $highest_year), 0);
        //$high_range_start = $highest_numeric + 1;
        $high_range_end   = $highest_numeric + 5;
        
        echo "<p>";
        
        // show delete button
        $del_disabled = '';
        $extra_comment = '';
        if ($yearcount < 2)
        {
            $del_disabled = 'disabled';
            $extra_comment = 'Cannot delete year if there is only one year!  ';
        }
        echo "<input $del_disabled type='submit' value='Delete selected year' name='delyear_button' title='$extra_comment Click here to delete the year you have selected in the \"FROM\" list above.'/>\n";
        
        // show the 'toggle flux status' button
        echo "<input type='submit' value='Toggle flux status' name='fluxstat_button'  title='Click here to toggle the flux year status of the year you have selected in the \"FROM\" list above.'/>\n";
        
        // show button to allow user to set the 'current year'
        echo "<input type='submit' value='Set current year' name='curryear_button'  title='Click here to set the year you have selected in the \"FROM\" list above as the \"current year\".'/>\n";
        
        echo "</p>";
        
        echo "</fieldset></p>";



        echo "<p><fieldset><legend>Enter new year details</legend>";
        echo "<p><label>Please enter the NEW year description, as above (e.g. 'Oct 2010 - 2011'):<br />  <input type='text' size='15' name='new_year_descrip' value='$descripvalue' /></label></p>\n";
        echo "<p><label>Please enter the NEW year value, as above (e.g. '2010_11'):<br />  <select name='new_year_value'>";
        for ($i = $low_range_start; $i < $high_range_end; $i++)
        {
            // construct temp year:
            $endbit = substr($i, 2, 2)+1;
            if (strlen($endbit) == 1) $endbit = '0'.$endbit;
            $tmp_yv = $i.'_'.$endbit;
            // is this temp year in valid_years?
            $display_year = true;
            foreach ($valid_years as $vyr)
            {
                if (is_numeric(array_search($tmp_yv, $vyr))) $display_year = false;
            }
            if ($display_year)
            {
                $checked = '';
                if ($tmp_yv == $newyearvalue) $checked = 'selected';
                echo "<option value='$tmp_yv' ".$checked.">$tmp_yv</option>\n";
            }
        }
        echo "</select></label></p>\n";

        // create the 'Ok' button:
        echo "<input type='submit' value='Create new year' name='addyear_button' title='Click here to create a new year by copying table definitions from the year selected in the \"FROM\" list above, named according to what you have entered in the \"NEW\" fields.'/>\n";

        
        echo "</fieldset></p>";

        //end form
        echo "</form>\n";
    }
    
    // CREATE THE 'Done' button here:
    // ::::::::::::::::::::::::::::
    if ($showdonebutton)
    {
        echo "<input type='submit' name='okbutton' value = 'Done' />";
    }
}
else
{
    echo '<p>You do not have permission to create new years.  Sorry. </p>';
}


require('config/footer.inc');



?>

