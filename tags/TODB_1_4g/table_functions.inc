<?php
// ------------------------------------------------------------------------------------------------------------------------------------
// Generic Function for displaying a table of information (should replace job_table, people_table, etc)
// Matthew Jones, 2009-05-13
// ------------------------------------------------------------------------------------------------------------------------------------
if (!function_exists('ShowGeneralTable')) {     //AEC Nov 09 Line added for Ujobs to work (Engineering only)
                                         //  p num       I/O?   V=Variable, C=constant/config
function ShowGeneralTable($isadminuser,       // 1        I     V
                          $adminwantstoedit,  // 2        I     V
                          $firstheader,       // 3        I     V
                          $admin_col_hdrs,    // 4        I     C
                          $result,            // 5        I     V
                          $admin_cols,        // 6        I     C
                          $items,             // 7        I     C
                          $showemail,         // 8        I     V
                          $cols_hdrs,         // 9        I     C
                          $csvmode,           // 10       I     V
                          $csv_file_handle,   // 11       I     V
                          $cols,              // 12       I     C
                          $button_cols,       // 13       I     V(C?)
                          $show_quotas,       // 14       I     V
                          &$linecount,        // 15       O     V
                          &$points_sum,       // 16       O     V
                          &$quota_sum)        // 17       O     V
{
    // $cols_hdrs == $jobcolshdr, from jobs.inc / people.inc / units.inc
    // $admin_cols == $adminjobcols, from jobs.inc / people.inc / units.inc
    // $admin_col_hdrs == $adminjobcolshdr, from jobs.inc / people.inc / units.inc
    // $cols == $jobcols, from jobs.inc / people.inc / units.inc
    // $items == job_items, from jobs.inc / people.inc / units.inc
    // $button_cols: associative array of column names and their respective HTML element names, to be represented as columns
    //               rather than ordinary text fields.
    //               e.g. $button_cols['uname'] = 'name_button';
    // $isadminuser: true or false if the currently logged-in user is an admin user
    // $adminwantstoedit: true or false if 'Edit' button has been pressed and is in 'edit' state
    // $result: MySQL resultset to be displayed in tabular form onscreen
    //
    
    
    // Get the column comments from the information_schema database:
    // =============================================================
    // This is to display 'tooltips' with explanations of headings:
    global $tablename, $yearval, $database_name, $db_metadata;
    $column_comments = array();
    $tt_query = "select column_name, column_comment from columns where table_name = '".$tablename."_".$yearval."' and table_schema = '$database_name';";
    $tt_res = mysql_query($tt_query, $db_metadata);
    if (!$tt_res) error_log("Error getting column comments: table: $tablename; year: $yearval; schema: $database_name;Query is:# $tt_query #".mysql_error($db_metadata));
    else
    {
        while ($row = mysql_fetch_array($tt_res))
        {
            $column_comments[$row[0]] = $row[1];
        }
        mysql_free_result($tt_res);
          
          // Add in comments for columns derived not stored AEC 25 03 10
          $column_comments[othergroups] = "Subject Groups relating to jobs in this Unit that are not included in the Subj Group entry";
          $column_comments[sum] = "Total points from jobs allocated to this person";
    }
    // =============================================================
    
    

    // Based on whether the system is in edit mode, and then in CSV or HTML output mode,
    // choose what to do:
    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    if (($isadminuser) && ($adminwantstoedit))
    {
        // EDIT MODE: table header
        // :::::::::::::::::::::::
        if ($firstheader == 1)
        {
            // header tags
            echo "<thead><tr>\n";
            // run through the list of column header names, printing the heading
            //foreach ($admin_col_hdrs as $item) echo "<th>$item</th>";
            foreach ($admin_col_hdrs as $key => $item) echo "<th title='".$column_comments[$admin_cols[$key]]."'>$item</th>";
            // finish off the table
            echo "</tr></thead>\n";
        }


        // EDIT MODE: table body
        // :::::::::::::::::::::

        // start by echoing the start of the table
        echo "<tbody>";
        // write editable entry records:
        // these have HTML links that trigger a javascript function that loads an editing window.
        $linecount = 0;
        $quota_sum = 0;
        $points_sum = 0;
        
        while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            // we're storing points*100 to avoid having to change the column type 8-(
            if (isset($line['points'])) $line['points'] /= 100;

            // call the per-person count function. From job_table.inc
            //if (function_exists('per_person_count')) per_person_count($line, array_search('points', $admin_job_cols)+1);
            if (function_exists('per_person_count')) per_person_count($line, array_search('points', $admin_cols)+1);

            // start the output row
            echo "<tr>\n";
            // run through the columns, generating the edit links:
            $points_sum = 0;
            foreach ($admin_cols as $ad_item) {
                editable_entry($line, $ad_item, $items, $showemail);
                // if the column is 'sum', accumulate it (from people_table)
                if ($item == 'sum') {
                    $points_sum = $points_sum + $line[$item];
       	            $line[$item] = (int)$line[$item];
                }
                // MJ, 2009-05-11:  only calculate quotas if instructed to consider quotas:
                if ($show_quotas) {
                    if ($item == 'quota') $quota_sum = $quota_sum + $line[$item];
                }
            }
            echo "</tr>\n";
            $linecount++;
        }


        if ($linecount == 0) {
            echo "<tr><td colspan='".count($admin_col_hdrs)."'>No Records Found</td></tr>\n";
        }
        
        // and flush the last person's totals
        if (function_exists('per_person_count')) per_person_count($line, count($admin_cols));
    }
    else
    {
          // NOT ($isadminuser and $adminwantstoedit)
        // MJ, December 2008: Added support for output to 'static' CSV file,
        // but keeping the existing system in place:

        // CSV OUTPUT:
        // :::::::::::
        if (($csvmode == 1) or ($csvmode==2))
        {
            $separator = "";
            $headings_array = $cols_hdrs;
              //AEC use admin cols for admin user
              if ($isadminuser)
                 $headings_array = $admin_col_hdrs;
                 
            foreach ($headings_array as $item)
            {
                if (($item == 'Person') && ($showemail))
                {
                    if ($csvmode==1) echo "$separator$item";
                    else fwrite($csv_file_handle, "$separator$item");
                    $separator = ", ";
                    if ($csvmode==1) echo $separator."email";
                    else fwrite($csv_file_handle, $separator."email");
                }
                else
                {
                    if ($csvmode==1)  echo "$separator$item";
                    else fwrite($csv_file_handle,"$separator$item");
                }
                $separator = ", ";
            }
            if ($csvmode==1) echo "\n";
            else fwrite($csv_file_handle,"\n");
        }
        else
        // NOT CSV mode - this is standard output to the screen:
        // :::::::::::::::::::::::::::::::::::::::::::::::::::::
        {
            /*

            ///////////////////////////////////////////////////////////////////
            // Echo another header row to display the 'order by' radio buttons:
            ///////////////////////////////////////////////////////////////////
            if ($firstheader == 1)
            {
                echo "<thead><tr>";
            }
            else
            {
                echo "<tbody><tr>";
                echo "<!-- MJ #016: firstheader is false! -->";
            }

            /*
            // get the first element as the 'order by' variable if none is selected:
            if (!isset($chosen_ordering))
            {
                $chosen_ordering = $jobcolshdr[0];
            }

            foreach ($jobcolshdr as $item)
            {
                // allow the user to indicate which fields to order by in Jobs page:
                $order_string = '<br /><input type="radio" name="orderjobsby" value="'.$item.'" />Order by';
                echo "<th class='ut_extra'>$order_string</th>";
            }
            */


            // ------------------------------------------
            if ($firstheader == 1)
            {
                // start table header
                echo "<thead><tr>\n";

               // MJ, 2009-07-09: differentiate between admin and ordinary users:
               if ($isadminuser)
               {
                 $fields_array = $admin_cols;
                 $headings_array = $admin_col_hdrs;
               }
               else
               {
                 $fields_array = $cols;
                 $headings_array = $cols_hdrs;
               }

                //$headings_array = $cols_hdrs;
                // run through the list of column header names, printing the heading
                foreach ($headings_array as $key => $item) echo "<th title='".$column_comments[$fields_array[$key]]."'>$item</th>";
                // finish off the table header
                echo "</tr></thead>\n";
            }

            echo "<tbody>";
        }

        $linecount = 0;
        $quota_sum = 0;
        $points_sum = 0;
        
        // MJ, 2009-07-09: differentiate between admin and ordinary users:
        if ($isadminuser) $columns_array = $admin_cols;
        else $columns_array = $cols;
        
        while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            // we're storing points*100 to avoid having to change the column type 8-(
            $line['points'] /= 100;
            // AEC pass $columns_array not $cols in here to get right alignment
            if (function_exists('per_person_count')) per_person_count($line, count($columns_array));
            if (($csvmode != 1) && ($csvmode != 2))
            {
                echo "<tr>\n";
            }

            // iterate through each column for this row, outputting the data either as
            // HTML table cells or as CSV fields:
            foreach ($columns_array as $item)
            {
                // make buttons if necessary:
                if (isset($button_cols[$item]) && ($csvmode != 1) && ($csvmode  != 2))
                {
                    echo "<td><input type=\"Submit\" name=\"".$button_cols[$item]."\" value=\"".$line[$item]."\"></td>\n";
                }
                else
                // if a button was not generated for this column, display as usual:
                {
                    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
                    // there are various 'special cases' for display.
                    // So far, these do not clash and so each of the
                    // special cases is accommodated here:
                    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
                    // decorate according to patterns of asterisks in job name:
                    $decor = decorate($line, $item, $csvmode);
                    
                    // make points and hours columns right-aligned
                      //if (($item == 'hours') || ($item == 'points'))
                      // AEC Sum too
                      if (($item == 'hours') || ($item == 'points')|| ($item == 'sum'))
                      
                    {
                        // numberstyle aligns right (at the time of writing!)
                        $align=" class=\"numberstyle\"";
                    }
                    else  $align="";
                    

                    // if the column is called 'sum', keep a running total:
                    if ($item == 'sum')
                    {
                        $points_sum = $points_sum + $line[$item];
                        $line[$item] = (int)$line[$item];
                        //echo "<p>Points sum is $points_sum.</p>";
         	        }


                    // if the column is 'quota', and quotas are considered, accumulate quota
                    // total.
                    if ($show_quotas) {
                        // MJ, 2009-05-11:  only calculate quotas if instructed to consider quotas:
                        if ($item == 'quota') $quota_sum = $quota_sum + $line[$item];
                    }


                    // if the item is uname and showemail is true, include the 'engid' column
                    if (($showemail == 'checked') && ($item == 'uname'))
                    {
                        // MJ: neither $cvsout nor $csvout are ever defined, so this is never true.  Will perhaps
                        // correct sometime if necessary.
                        if ($cvsout) $engid = ', '.str_replace(',', ';', $line['engid']);
                        else  $engid = ', '.$line['engid'];
                    }
                    else $engid = '';
                    
                    // Units: if item is running or global, show radic and chi characters
                      // (which are meant to be ticks and crosses - AEC only if not in CSV mode
                    if (($csvmode != 1) && ($csvmode  != 2) )
                    {
	                    if (($item == 'running') || ($item == 'global'))
	                    {
	                        if ($line[$item] == 1) $line[$item] = '&radic;';
	                        else $line[$item] = '&chi;';
	                    }
                    }
                    

                    // Again, differentiate between old and new style CSV
	                if (($csvmode == 1) or ($csvmode == 2))
                    {
                        $str = $decor[0].str_replace(',', ';', $line[$item]).$engid.$decor[1];
                        // old-style CSV
                        if ($csvmode == 1) echo $str;
                        // new CSV approach
                        else fwrite($csv_file_handle, $str);
                    }
                    else
                    {
                        // MJ#: this is where the standard HTML screen output rows are generated
                        echo "<td".$align.">".$decor[0].$line[$item].$engid.$decor[1]." </td>\n";
                    }
                 }
            }
            
            if (($csvmode == 1) or ($csvmode == 2))
            {
                if ($csvmode == 1) echo "\n";
                else fwrite($csv_file_handle, "\n");
            }
            else
            {
                echo "</tr>\n";
            }
            $linecount++;
        }
        if (($linecount == 0) && ($csvmode != 1) && ($csvmode != 2))
        {
            echo "<tr><td colspan=\"".count($columns_array)."\">No Records Found</td></tr>\n";
        }
        // and flush the last person's totals
        if (function_exists('per_person_count')) per_person_count($line, count($columns_array));
    }
}
}




// ------------------------------------------------------------------------------------------------------------------------------------
// Functions common to the old job_table, people_table, unit_table?
// Matthew Jones, 2009-05-13
// ------------------------------------------------------------------------------------------------------------------------------------
if (!function_exists('editable_entry'))
{
    function editable_entry($line, $item, $array_items, $showemail)
    {
      $separator = "'";

      // right-align (=apply the CSS 'numberstyle' to) the hours and points columns
      //if (($item == 'hours') || ($item == 'points')) $align=" class=\"numberstyle\"";
      if (($item == 'hours') || ($item == 'points')|| ($item == 'sum')) $align=" class=\"numberstyle\"";
      
      else $align="";

      // there is no email column, so pull out email address if this option was specified
      if (($showemail == 'checked') && ($item == 'uname')) $engid = ', '.$line['engid'];
      else $engid = '';
      
      // AEC Just show integer bit of sum
      if ($item == 'sum') $line[$item] = (int)$line[$item];
                      
      // AC 9.11.09 ticks and crosses copied to edit mode
      //Units: if item is running or global, show radic and chi characters
      // (which are meant to be ticks and crosses
      if (($item == 'running') || ($item == 'global'))
      {
          if ($line[$item] == 1) $line[$item] = '&radic;';
          else $line[$item] = '&chi;';
      }

      // start the table column
      echo "<td".$align.">\n";
      
      // start the a tag, javascript function call with parameter string:
      $href = "<a href=\"javascript:void(0)\" onclick=\"SelectLine(";
      
      // iterate through array assigning values to javascript parameter string
      foreach ($array_items as $aitem)
      {
         $href .= $separator.addcslashes($line[$aitem], "',()\n\r");
	     $separator = "', '";
      }
      
      // apply any decoration (bold, etc)
      $decor = decorate($line, $item, $csvmode);

      // complete the a/js string, table and output them
      $href .= "')\">".$decor[0].$line[$item].$engid.$decor[1]."</a></td>\n";
      echo $href;
    }
}




///////////////////////////////////////////////////////////////////////////////////////////////
// this function determines if particular entries get highlighted,
// boldified, italicised, or whatever, based on column and sometimes
// on content
// --------------------------------
// Function decorate()
// --------------------------------
if (!function_exists('decorate'))
{
    function decorate ($line, $item, $csvmode)
    {
        //global $csvmode;
        // MJ 20090310 added from orig JBS job_table
        global $do_buttons_in_job_table;

        // if a CSV file is to be generated, the only 'decoration' required is a comma
        // MJ: what if there is a comma in $line[$item]?  It appears to be handled somewhere...
        //     (replaced with a ';').
        if (($csvmode == 1) or  ($csvmode == 2)) return array("", ",");

        // if it's the name of the job, and it starts with a '*', then boldify
        if (($item == "name") && (strncmp('*', $line[$item], 1) == 0))
        {
            $decor = "<b>";
            $enddecor = "</b>";
        }
        // if it's the term, then italicise
        elseif ($item == "term")
        {
            $decor = "<i>";  $enddecor = "</i>";
        }
        // otherwise apply no decoration
        else
        {
            $decor = ""; $enddecor = "";
        }


        // MJ 20090310: added from orig JBS job_table
        // not really used but here in case JBS want these back
        //////////////////////////////////////////
        ////////// makes buttons
        //////////////////////////////////////////
        if ( (($item == 'paper') || ($item == 'course')) && ($do_buttons_in_job_table == 'TRUE'))
        {
  	         $decor = '<form method="post" action="view_related_jobs.php?yearval='.$yearval.'">'.
  		              '<input type="hidden" name="show_related_thing" value="'.$item.'">'.
  		              '<input type="submit" name="show_related_items_button" value="';
  	         $enddecor = '"></form>';
        }
        //////////////////////////////////////////
        //////////////////////////////////////////


        return array ($decor, $enddecor);
    }
}
// END decorate
// --------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////////////////////
// --------------------------------
// Function per_person_count()
// --------------------------------
if (!function_exists('per_person_count')) {
function per_person_count($line, $numcols) {
   global $dopointsummary;
   global $dopastyearsummary;
   global $currentuser;
   global $currenttotal;
   global $curyrindex;
   global $valid_years;
   global $dbread;
   global $csvmode;
   global $yearval;
   global $csv_file_handle;
   global $show_quotas;
   global $isadminuser;
   
   //echo "<!-- valid_years[0][1] is ".$valid_years[0][1]." ~~ -->";

   if (! $dopointsummary) {
      return TRUE;
   }
   
   if (($currentuser != "") && ($currentuser != $line['uname'])) {
      // we've changed users, and we're not at the first user, so we
      // should output a summary.
      if (($csvmode != 1) && ($csvmode != 2)) {

         echo "<tr>";

         // we want to print a summary line, including past years' data
         // so we do the current total in the last column[*], and the past
         // years' data right-aligned in a colspan covering all the
         // other columns
         //
         // [*] AEC - assume points is right most column
         echo "<td align=right style=\"border-bottom: solid 1px;\" colspan=".
              ($numcols-1)."><i>";
      }
      
      /*if ($dopastyearsummary) echo "Past year summary";
      else echo "No past year summ.";*/
      //echo "Current year index: $curyrindex";
      
      if ($dopastyearsummary)
      {
         for ($i = 0; $i<$curyrindex; $i++)
         {
            $scorequery = 'SELECT SUM(points) FROM jobs_'.
                           $valid_years[$i][0].
                           ' WHERE uname=\''.$currentuser.'\' and deleted = 0;';
            //echo "Score q: $scorequery";

	        $scoreresult = mysql_query($scorequery, $dbread);
            //echo "res $scoreresult";
	        $thisscore = mysql_result($scoreresult, 0);
	        
            if ($thisscore == FALSE) {
               $thisscore = 0;
            }
	        # have to divide by 100, because of change to the way we
            # store points
            $thisscore /= 100;

            // output - in CSV format
            if (($csvmode == 1) or ($csvmode == 2))
            {
              $str = $valid_years[$i][1].", ".$thisscore."\n";
              if ($csvmode == 1) echo $str;
              else fwrite($csv_file_handle, $str);
            }
            // or output to the screen:
            else
            {
              echo "(".$valid_years[$i][1]." : ".$thisscore.")&nbsp;&nbsp; ";
            }
         }
      }

      //////////////////////////////////////////////////////////////////////////////////////
      // MJ 20090310: some bits added from the JBS job table so that the quotas are displayed
      if ($show_quotas)
      {
        $quota_query = 'SELECT quota FROM people_'.$yearval.' WHERE uname=\''.$currentuser.'\'';
        $quota_current_person_result = mysql_query($quota_query, $dbread);
	    $quota_current_person = mysql_result($quota_current_person_result, 0);
	    $diff = $currenttotal - $quota_current_person;
	    if ($diff ==0) {$colour = '#000000';}
        if ($diff >0) {$colour = '#0000d9';}
        if ($diff <0) {$colour = '#f00000';}
      }
      //////////////////////////////////////////////////////////////////////////////////////

      if (($csvmode == 1) or ($csvmode == 2))
      {
        $str = $yearval.", ".$currenttotal."\n";
        // MJ 20090310: some bits added from the JBS job table so that the quotas are displayed
        if ($show_quotas) $str .= $quota_current_person."\n".$diff."\n";
        // if csvmode==1, output to screen:
        if ($csvmode == 1) echo $str;
        // otherwise output to file:
        else fwrite($csv_file_handle, $str);
      }
      else
      {
         // MJ 20090310: some bits added from the JBS job table so that the quotas are displayed
         if ($show_quotas) echo "<br>&nbsp;&nbsp;Quota : <br>Difference : </i></td>";
         echo "<td align=right style=\"border-top: solid 1px; border-bottom: solid 1px;\"><b>$currenttotal</b>\n";
                       
         // MJ 20090310: some bits added from the JBS job table so that the quotas are displayed
         if ($show_quotas) echo '<br />'.$quota_current_person.'<br /><span style="color: '.$colour.'">'.$diff.'</span><br />';
         // AC make sure we finish the table nicely:
         echo "</td></tr>";
      }


      $currenttotal = 0;
   }
   $currenttotal += $line['points'];
   $currentuser = $line['uname'];
   return TRUE;
}
}
// END per_person_count
// --------------------------------
///////////////////////////////////////////////////////////////////////////////////////////////










?>

