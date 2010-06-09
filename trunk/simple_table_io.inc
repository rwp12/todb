<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////
/* SIMPLE TABLE IO
   MJ, 23 Dec 2008
   
   These functions allow the programmer to display a table for view/editing in a simple HTML
   kind of way, by configuring a PageDisplayConfig object, and passing it as a parameter to
   the ViewPage function.
   
 */
///////////////////////////////////////////////////////////////////////////////////////////////////////


function ViewPage($pdc_obj)
{
  /* MJ, December 2008:
   ----------------------------------------------------------------------------------------------------
     This page allows the user to display the points_formulae table to facilitate editing.
   ----------------------------------------------------------------------------------------------------
   The page operates in 4 modes, identified by the state variable page_operation:

   - default (display)  | NULL ('none'), 'view'
     -------------------+----------------------
   - view in edit mode  | 'wanttoedit'
     -------------------+----------------------
   - show blank recs    | 'addnew'
     for adding new     |
     -------------------+----------------------
   - save changes       | 'saveedit'

  */

  // $pdc_obj is the configuration object with the table name, field names etc

  global $isadminuser;

  $page_prefix = $pdc_obj->page_prefix;

  // switch statement to decide on what the page should do:
  if (post_exists($page_prefix.'_edit')) $page_operation = 'wanttoedit';
  elseif (post_exists($page_prefix.'_addnew')) $page_operation = 'addnew';
  elseif (post_exists($page_prefix.'_save')) $page_operation = 'saveedit';
  elseif (post_exists($page_prefix.'_view')) $page_operation = 'view';
  else $page_operation = 'none';
  if (!$isadminuser) $page_operation = 'none';

  //echo '<p>Page operation is '.$page_operation.'</p>';

  switch ($page_operation)
  {
      // default or viewing page:
      // -----------------------------------------------------------------------------------------------
      // case 'none' and 'view' have the same outcome:
      case 'none':
      case 'view':
        // show the page
        echo '<H1>View '.$pdc_obj->table_display_name.'</h1>';
        ShowPage($pdc_obj, 1);
        break;
      break;
      // add new page:
      // -----------------------------------------------------------------------------------------------
      case 'addnew':
        // show the page
        echo '<H1>Add new records to '.$pdc_obj->table_display_name.' table</h1>';
        ShowPage($pdc_obj, 3);
      break;

      // view page in edit mode:
      // -----------------------------------------------------------------------------------------------
      case 'wanttoedit':
        // show page in editmode;
        echo '<H1>Editing '.$pdc_obj->table_display_name.' table</h1>';
        ShowPage($pdc_obj, 2);
      break;
      // edit existing page:
      // -----------------------------------------------------------------------------------------------
      case 'saveedit':
        // save any new records:
        SaveNewRecords($pdc_obj);

        // delete any marked records:
        DeleteMarkedRecords($pdc_obj);

        // update any edited records:
        UpdateEditedRecords($pdc_obj);

        // show the page
        echo '<H1>Records saved; viewing '.$pdc_obj->table_display_name.' table</h1>';

        // show the records
        ShowPage($pdc_obj, 1);
      break;


  }

  // close connection to DB:
  close_db_read();
}

// END of ViewPage()
// -----------------------------------------------------------------------------------------------------







// -----------------------------------------------------------------------------------------------------
// ShowPage()
// ----------
// This function displays the table on-screen
// MJ, December 2008
// editmode == 1: Show the page as a table
// editmode == 2: Show the page as an editable form
// editmode == 3: Show the page as a table with empty editable records for adding new
// -----------------------------------------------------------------------------------------------------
function ShowPage($pdc_obj, $editmode)
{
  global $isadminuser;

  // config:
  ///////////////////////////////////////////////////////////
  $page_prefix = $pdc_obj->page_prefix;

  $display_col_widths = $pdc_obj->display_col_widths;
  $display_col_aliases = $pdc_obj->display_col_aliases;
  $display_cols = $pdc_obj->display_cols;
  $pkey_col = $pdc_obj->pkey_col;
  $table_name = $pdc_obj->table_name;
  $order_by_col = $pdc_obj->order_by_col;
  ///////////////////////////////////////////////////////////

  // create SQL queries
  $s_cols = $pkey_col.', '.implode(', ', $display_cols);
  $order_str = (strlen($order_by_col) > 0) ? " order by $order_by_col;" : ";";
  $query = 'SELECT '.$s_cols.' from '.$table_name.$order_str;
  //echo "<!-- Query:\n $query  \n--> ";


  // run the queries:
  global $dbread;
  open_db_read();
  $result_set = mysql_query($query);

  // display as a form if the user wants to edit and
  // is allowed to
  $allowed_to_edit = $isadminuser;
  $show_edit_app = false;
  if ($allowed_to_edit) $show_edit_app = true;

  // show the <FORM>:
  if ($show_edit_app) $HTML .= '<form name="'.$page_prefix.'_form" method="POST" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';

  // Generate the table
  /////////////////////
  $HTML .= '<table border="1">';

    // table heading:
    $HTML .= "<thead>\n<tr>\n";
    foreach ($display_cols as $th) {  $HTML .= '<th>'.$display_col_aliases[$th].'</th>';  }
    if ($show_edit_app) $HTML .= '<th>Delete?</th>';
    $HTML .= "\n</tr>\n</thead>\n";

    // table body:
    $HTML .= "<tbody>\n";
    while ($row = mysql_fetch_array($result_set, MYSQL_ASSOC))
    {
      $HTML .= '<tr>';
      foreach ($display_cols as $col)
      {
        // if editmode is 1, show plain HTML
        $item = $row[$col];
        if (strlen($item) < 1) $item = '&nbsp;';
        if (($editmode == 1) or ($editmode == 3)) $HTML .= '<td>'.$item."</td>\n";
        // if editmode is 2, show editable HTML fields:
        elseif ($editmode == 2)
        {
          $HTML .= '<td><input type="text" '.
                   'name="'.$page_prefix.'_'.$col.'_'.$row[$pkey_col].'" '.
                   'size="'.$display_col_widths[$col].'" '.
                   'value="'.$row[$col].'" '.
                   'onChange="'.$page_prefix.'_edswitch_'.$row[$pkey_col].'.value = \'ON\'"'.'" /> '.
                   "</td>\n";
        }
      }
      if ($show_edit_app) $HTML .= '<td><input type="checkbox" value="off" name="'.$page_prefix.'_delete_'.$row[$pkey_col].'" /></td>';
      // create hidden edit switch variable for this record:
      if ($show_edit_app && ($editmode == 2)) $HTML .= '<input type="hidden" name="'.$page_prefix.'_edswitch_'.$row[$pkey_col].'" value="OFF" />';

      $HTML .= "</tr>\n";

    }

    // show some (editable) blank recs at the bottom if the user is adding new
    // records:
    if ($editmode == 3)
    {
      // count the number of cols:
      $count = count($display_cols);
      // show a line indicating new ones:
      $HTML .= '<tr><td colspan="'.$count.'"><b>Add new records below:</b></td></tr>';

      if ($editmode == 3)
      {
        // add 3 new blank records:
        for ($i=1; $i<4; $i++)
        {
          // start table row:
          $HTML .= '<tr>';
          // for each of the columns, generate a blank input field:
          foreach ($display_cols as $col)
          {
            $HTML .= '<td><input type="text" '.
                     'name="'.$page_prefix.'_'.$col.'_new_'.$i.'" '.
                     'size="'.$display_col_widths[$col].'" '.
                     'value="">'.
                     "</td>\n";
          }
          $HTML .= '</tr>';
        }
      }
    }

    $HTML .= "</tbody>\n";

  // finish table:
  $HTML .= '</table>';
  ////////////////////////


  // close the resultset:
  mysql_free_result($result_set);


  // Show the 'view in edit mode' button if the user wants to edit and
  // is allowed to, and if not already in edit mode:
  $HTML .= '<input type="submit" value="View" name="'.$page_prefix.'_view" />';
  if ($show_edit_app) $HTML .= '<input type="submit" value="Edit" name="'.$page_prefix.'_edit" />';
  //if ($show_edit_app) $HTML .= '<input type="hidden" name="page_operation" value="wanttoedit" />';
  // show 'Add new' button, subject to the same criteria:
  if ($show_edit_app) $HTML .= '<input type="submit" value="Add new" name="'.$page_prefix.'_addnew" />';
  // show 'save' button
  if ($show_edit_app) $HTML .= '<input type="submit" value="Save Changes" name="'.$page_prefix.'_save" />';
  if ($show_edit_app) $HTML .= '</form>';
  // show the HTML:
  echo $HTML;
}
// END of ShowPage()
// -----------------------------------------------------------------------------------------------------





// -----------------------------------------------------------------------------------------------------
// SaveNewRecords()
// MJ, Dec 2008
// save any new records submitted
// -----------------------------------------------------------------------------------------------------
function SaveNewRecords($pdc_obj)
{
   $page_prefix = $pdc_obj->page_prefix;

   // open the database for writing:
   global $dbwrite;

   // config
   $table_name = $pdc_obj->table_name;

   open_db_write();

   // new record items array
   $new_items = array();

   // search the post array for any sign of new records:
   foreach ($_POST as $pkey => $pvalue)
   {
     if ((strpos($pkey, $page_prefix.'_') > -1) && (strpos($pkey, '_new_') > -1))
     {
        // get the index of the '_new_' to find out the index of the new record
        $npos = strpos(trim($pkey), '_new_');
        $index = trim(substr(trim($pkey), $npos+5));

        // get the column name:
        $cpos = strpos(trim($pkey), $page_prefix.'_');
        $colname = mysql_real_escape_string(substr($pkey, $cpos+12, ($npos - ($cpos+12))), $dbwrite);

        //$pvalue = RemoveNonAlphaNumeric($pvalue);
        //$pvalue = str_replace ( ' ', '', $pvalue);

        // store the new records in an associative 2D array
        if (strlen(trim($pvalue)) > 0) $new_items[$index][$colname] = "'".mysql_real_escape_string(trim($pvalue), $dbwrite)."'";

     }
   }

   // now, create insert queries based on these:
   //$queries = array();
   $q_count = 0;
   foreach ($new_items as $new_record)
   {
     // construct queries
     $q = 'insert into '.$table_name.'(';
     $qcols = implode(',', array_keys($new_record));
     $qvals = implode(',', array_values($new_record));
     $q .= "$qcols) values($qvals);";
     
     //echo "\n<p><b>Insert:</b> $q</p>\n";

     // execute the queries:
     $res = mysql_query($q, $dbwrite);
     if ($res === true) $q_count++;
     else
     {
        // __FUNCTION__ returns the currently-called function name.
        // * May not work with older versions of PHP *
        echo '<p>Error! ['.__FUNCTION__.'] - '.mysql_error($dbwrite)."</p>\n";
        error_log('Problem with MySQL insert in '.__FUNCTION__.': '.mysql_error($dbwrite)."\n");
     }
   }

   // one of my pet peeves - zero records, one record, many records...
   $plural = (($q_count > 1) or ($q_count == 0)) ? "s" : "";
   echo "<p>$q_count record".$plural." added</p>\n";

   // close the write connection to the DB
   close_db_write();
}
// END of SaveNewRecords()
// -----------------------------------------------------------------------------------------------------




// -----------------------------------------------------------------------------------------------------
// DeleteMarkedRecords()
// MJ, Dec 2008
// delete any records marked with the checkboxes
// -----------------------------------------------------------------------------------------------------
function DeleteMarkedRecords($pdc_obj)
{
   // config:
   $page_prefix = $pdc_obj->page_prefix;

   // open the database for writing:
   global $dbwrite;
   open_db_write();

   // config
   // name of table
   $table_name = $pdc_obj->table_name;
   // the primary key column name
   $pkey_col = $pdc_obj->pkey_col;

   $items_to_delete = array();

      // search the post array for any sign of new records:
   foreach ($_POST as $pkey => $pvalue)
   {
     if ((strpos($pkey, $page_prefix.'_') > -1) && (strpos($pkey, '_delete_') > -1))
     {
        // get the index of the '_delete_' to find out the index of the deleted record
        $npos = strpos(trim($pkey), '_delete_');
        $index = trim(substr(trim($pkey), $npos+8));

        // add to list of items to delete
        $items_to_delete[] = mysql_real_escape_string($index, $dbwrite);
     }
   }

   // create query:
   $items = "'".implode("', '", $items_to_delete)."'";
   $changed_count = 0;
   if (strlen(trim($items)) > 2)
   {
     $q = 'delete from '.$table_name.' where '.$pkey_col.' in ('.$items.');';
     //echo "<p><b>Delete:</b> $q; </p>";
     mysql_query($q, $dbwrite);
     
     $changed_count = mysql_affected_rows($dbwrite);
   }
   // one of my pet peeves - zero records, one record, many records...
   $plural = (($changed_count > 1) or ($changed_count == 0)) ? "s" : "";
   echo "<p>$changed_count record".$plural." deleted</p>\n";


   // close the write connection to the DB
   close_db_write();
}
// END of DeleteMarkedRecords()
// -----------------------------------------------------------------------------------------------------




// -----------------------------------------------------------------------------------------------------
// UpdateEditedRecords()
// MJ, Dec 2008
// update any records changed...
// -----------------------------------------------------------------------------------------------------
function UpdateEditedRecords($pdc_obj)
{
   // how to figure out if they've been changed?
   // it is better to have only the changed records updated, because it is inefficient to be
   // transferring whole tables to and fro via HTML forms, and also prone to errors, hacking,
   // etc.  I think there is probably a better chance of 'losing' everything if the
   // connection becomes corrupted or something if the whole table is transferred every time.
   // The answer:
   // 1. Use JavaScript to change the state of a hidden variable in the page from 'off' to 'on'
   // if the corresponding textfield changed.
   // 2. Only update those records that are 'on' for their edited switches.

   // config
   // page prefix
   $page_prefix = $pdc_obj->page_prefix;
   // name of table
   $table_name = $pdc_obj->table_name;
   // primary key column name
   $pkey_col = $pdc_obj->pkey_col;

   // open the database for writing:
   global $dbwrite;
   open_db_write();


   // find all the edited records ($page_prefix_edswitch_* == 'ON')
   // and store them in the $edited_recs array
   $edited_recs = array();
   foreach ($_POST as $pkey => $pvalue)
   {
      if ($pvalue == 'ON')
      {
        if (strpos($pkey, $page_prefix.'_edswitch_') > -1)
        {
            $pos = strpos($pkey, $page_prefix.'_edswitch_');
            //echo "<p>Index should be: ".substr($pkey, $pos+strlen($page_prefix)+10)."</p>";
            $index_value = substr($pkey, $pos+strlen($page_prefix)+10);
            //echo "<p>Index is $int_index</p>";
            $edited_recs[$index_value] = array();
        }
      }
   }


   // new record items array
   $update_items = array();

   // search the post array for any sign of edited records:
   foreach ($_POST as $postkey => $pvalue)
   {
     if ((strpos($postkey, $page_prefix.'_') > -1) and (strpos($postkey, 'edswitch') === false))
     {
        // find the substring from the last underscore
        $postkey = trim($postkey);
        $lastbit = strrchr($postkey, '_');

        $npos = strpos($postkey, $lastbit);
        $index = substr($lastbit, 1);
        //echo "<P>Update index is $index</p>";

        $index_value = $index;

        // if this index value (primary key value of table) appears in the $edited_recs
        // array, it means that this datum should be stored for entry into the database
        // (i.e. there are potentially many fields associated with one primary key value,
        // and many fields associated with a single record).  The use of the 2D-array
        // allows storage of many pieces of data for each primary key value
        if (isset($edited_recs[$index_value]))
        {

          //echo "<P>Index is set: $index</p>";
          // get the column name:
          // :::::::::::::::::::::
          // get position of primary key colname in postkey
          $cpos = strpos($postkey, $page_prefix.'_');
          $st_length = strlen($page_prefix);
          $colname = substr($postkey, $cpos+$st_length+1, ($npos - ($st_length+1))) ;

          // show:
          //echo '<p> Index is '.$index.', colname is '.$colname.' and pvalue is '.$pvalue.'</p>';
          $index_value = mysql_real_escape_string($index_value, $dbwrite);
          
          //$pvalue = RemoveNonAlphaNumeric($pvalue);
          //$pvalue = str_replace( ' ', '', $pvalue);
          
          // store the new records in an associative 2D array
          if (strlen(trim($pvalue)) > 0) $update_items[$index_value][$colname] = "'".mysql_real_escape_string($pvalue, $dbwrite)."'";
          //echo '<P>pvalue is "'.$pvalue.'" and escaped is "'.mysql_real_escape_string("'".$pvalue."'", $dbwrite).'"</p>';
          //if (strlen(trim($pvalue)) > 0) $update_items[$index_value][$colname] = "'".$pvalue."'";
        }

     }
   }

   // now we have an array (update_items), with each element containing a record that has values that need
   // updating.  The element index is the value of the primary key column:

   // now, create update queries based on these:
   $queries = array();

   foreach ($update_items as $new_key => $new_record)
   {
     // construct queries
     $q = 'update '.$table_name.' set ';

     //echo "<P>New key is $new_key</p>";
     
     // trim the index value (probably unnecessary)
     $index_val = trim($new_key);

     $setset = array();
     foreach ($new_record as $nr_k => $nr_v)
     {
        // remove any spaces or underscores:
        //$nr_v = str_replace ( ' ', '', $nr_v);
        $nr_v = str_replace ( '_', '', $nr_v);
        // add to array of set x = y pairs for query
        $setset[] = "$nr_k = $nr_v";
     }
     $q .= implode(', ', $setset);
     $q .= " where $pkey_col='$index_val';";

     // display the queries:
     //echo "<p>$q</p>";

     // store query in array:
     $queries[] = $q;
   }

   // Finally, execute the update queries:
   $changed_count = 0;
   foreach ($queries as $q)
   {
     mysql_query($q, $dbwrite);
     $changed_count += mysql_affected_rows($dbwrite);
   }
   
   // one of my pet peeves - zero records, one record, many records...
   $plural = (($changed_count > 1) or ($changed_count == 0)) ? "s" : "";
   echo "<p>$changed_count record".$plural." updated</p>\n";

   // close the write connection to the DB
   close_db_write();

}
// END of UpdateEditedRecords()
// -----------------------------------------------------------------------------------------------------

function RemoveNonAlphaNumeric($line)
{
    //$line = strtoupper($line);
    $len = strlen($line);

    // replace any underscores with empty strings:
    $line = str_replace ( '_', '', $line);

    /*for ($i = 0; $i < $len; $i++)
    {
      // if the character at is not alphanumeric, replace with space
      if (!((strtoupper($line{$i}) >= 'A') and (strtoupper($line{$i}) <= 'Z') or
            ($line{$i} >= '0') and ($line{$i} <= '9') or ))
      {  $line{$i} = ' '; }
    }*/
    return $line;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
// -----------------------------------------------------------------------------------------------------
// CLASS PageDisplayConfig
// This class allows the construction of a simple data-storage class for neater function calls.
// The data stores configuration values.
// -----------------------------------------------------------------------------------------------------
class PageDisplayConfig
{
   // 2-D array containing field names referencing column widths for display
   var $display_col_widths;

   // easy human-readable names for display, corresponding with db table field names
   // 2D array, in the form 'DBFieldName1' => 'Field Name Alias 1', ...
   var $display_col_aliases;

   // 1D array of field names
   var $display_cols;

   // the primary key column name of the table
   var $pkey_col;

   // the name of the table
   var $table_name;

   // table name, for human readers (e.g. 'Point Formulae' instead of 'point_formulae'
   var $table_display_name;

   // the variable name to use as the POST variable name prefix for this page:
   var $page_prefix;
   
   // the column name to use for ordering the standard select query:
   var $order_by_col;

   // constructor - does nothing
   function PageDisplayConfig()
   {
   }
}

?>

