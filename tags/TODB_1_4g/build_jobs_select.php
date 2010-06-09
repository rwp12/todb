<?php



// MJ: make available these arrays set in config.inc
global $all_titles, $all_prefixes, $all_column_names, $all_categories, $all_captions, $all_other_cats;
global $csvmode;


// This part determines the display state - either the user has entered some filtering
// criteria, or the system must choose some limited selection to display by default.
if (post_exists('filter') || post_exists('filter_special') ||
    post_exists('editmode') || post_exists('CSV_button'))
{

  // MJ, Dec 2008: if the user clicked on the 'Download as CSV' BUTTON (rather than
  // link), the system should indicate that output should be in CSV mode.
  // However, not the same csv mode as before!  Rather, mode = 2 indicates that a
  // file should be created and the csv data output to it, as well as the screen,
  // and the page then redirects to this.
  if (post_exists('CSV_button')) $csvmode = 2;

  // MJ, Jan 2009: The following query replaced, to support formula display, ...
  // $filter = '(jobs_'.$yearval.'.deleted = FALSE)';
  // with this:
  $filter = '(A.deleted = FALSE)';
  $tmp_filter = $filter;

  $querydesc = "";

  // MJ#: INSTR() Return the index of the first occurrence of substring.  It is
  // understood in this context to be 'true' if the index is positive (match found)
  // and false otherwise

  // ---------------------------------
  // MJ, Nov 2008:
  // SUPPORT FOR LIST OF GROUPS IN TEXTFIELD
  // ........................................
  // The code below creates the list of letters that have been
  // entered
  // ........................................

    foreach (array_keys($all_titles) as $criterion)
    {
        $querydescg = "";
        $gpnotother = '';
  
        // for the human-readable and CSV queries
        //$gpnotother = implode('', $all_categories[$criterion]);
        $title = $all_titles[$criterion];
        $prefix = $all_prefixes[$criterion];
        $column_name = $all_column_names[$criterion];
        $categories = $all_categories[$criterion];
        $captions = $all_captions[$criterion];
        $other_cats = $all_other_cats[$criterion];

        // Get the SQL filter (WHERE) string:
        //$tmp_gp_filter .= GetFilterString($column_name, $prefix, $categories, $querydesctm);
        $tmp_filter .= GetFilterString($column_name, $prefix, $categories, $querydescg);
        //echo "<!-- MJ 009:  Temp group filter is $tmp_gp_filter -->\n";

        // To display the human-readable version of the query and to facilitate the CSV
        // query (to be removed eventually (MJ, 1 December 2008)
        if (trim($querydescg) != "")
        {
	       $querydesc .= $querysep."$title is ".implode("/",explode(' ',$querydescg));
           $querysep = ", ";
        }
    }

  // *****************************************************************************************************
  if (post_exists('unalloc'))
  {
    //$filter .= " && (uname <=> NULL)";
    $tmp_filter .= " && ((A.uname <=> NULL) || (length(trim(A.uname))<1))";
    $unalloc = 'checked';
    $querydesc .= $querysep."unallocated";
    $querysep = ", ";
  }
  else $unalloc = '';

  if (post_exists('modlead'))
  {
     //$filter .= " && (INSTR(name, \"leader\") > 0) && NOT (paper IS NULL)";
     $tmp_filter .= " && (INSTR(name, \"leader\") > 0) && NOT (paper IS NULL)";
     $modlead = 'checked';
     $querydesc .= querysep."module leaders";
     $querysep = ", ";
  }
  else $modlead = '';

  $select_string = '';
  if (post_exists('showemail'))
  { 
      $showemail = 'checked'; 
      $join_string = ' LEFT JOIN people_'.$yearval.' AS C on (A.uname = C.uname)';
      $select_string = ', engid ';
  }
  else
  {
      $showemail = '';
      $join_string = ' LEFT JOIN people_'.$yearval.' AS C on (A.uname = C.uname)';
  } 

  // MJ, Jan 2009: in order to display the formulae on-screen on the jobs screen, the following query was replaced...
  // $full_query = 'SELECT jobs_'.$yearval.'.* '.$select_string.' FROM jobs_'.$yearval.$join_string.' WHERE ' . $tmp_filter . 'ORDER BY year, prgroup, paper, name';
  // with this:
  $full_query = 'SELECT A.*, B.* '.$select_string.' FROM jobs_'.$yearval.' AS A '.
                'left join point_formulae_'.$yearval.' as B on A.formula_ref = B.formula_id '.$join_string.
                //' WHERE ' . $tmp_filter . 'ORDER BY year, prgroup, paper, name';
                ' WHERE ' . $tmp_filter .' order by '. $jobordering;

}
else
{
  // * use default queries *
  // * MJ, Nov 2008:  When the user first accesses the system, the selections made by default are
  // set in this section. *

  // if a demonstration admin user is using the system, his/her access is limited
  // to seeing, modifying and adding jobs of type 'D'
  // ::::::::::::::::::::::::::::::::::::::::::::::::
  // limit these if the user is a demonstration admin user:
  $where_extra = '';
  $qd_extra = '';
  // if demo_user, add an extra limitation to the SQL where clause to prevent
  // viewing of anything other than jobs of type 'D'
  if ($is_demo_user)
  {
    // inform the user of demo user constraint
    $qd_extra = ', limited to Demonstration job type';
    // add to the where clause:
    $where_extra = ' and type = "D" ';
  }
  
  // generate the SQL query
  // MJ, Jan 2009: in order to display the formulae on-screen on the jobs screen, the following query was replaced...
  //$full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (year = 1)) ORDER BY prgroup, paper, name';
  // with this:
  $full_query = ' SELECT A.*, B.*  FROM jobs_'.$yearval.' as A '.
                ' left join point_formulae_'.$yearval.' as B on A.formula_ref = B.formula_id '.
                ' LEFT JOIN people_'.$yearval.' as C on (A.uname = C.uname)'.
                ' WHERE ((A.deleted = FALSE)) '.$where_extra.
                //' ORDER BY prgroup, paper, name';
                ' ORDER BY '.$jobordering;
  // and the query description for the user:
  $querydesc = "all jobs".$qd_extra;
  // Now choose which of the checkboxes should be set (should match the query above!):
  foreach ($all_categories as $ckey => $cats)
  {
    foreach ($cats as $each_cat)
    {
        // if a demonstration admin user is using the system, his/her access is limited
        // to seeing, modifying and adding jobs of type 'D'
        // this section ticks all checkboxes unless it is the 'job type' filter, in
        // which case only the 'D' box is ticked if this is a demo_user.
        if ($is_demo_user && ($ckey == 'type'))
        {
            if ($each_cat == 'D')
            {
                $out_var = $all_prefixes[$ckey];
                $var = GetPHPVariableName($out_var, $each_cat);
                $GLOBALS[$var] = 'checked';
            }
        }
        else
        // the standard behaviour in most cases:
        {
            $out_var = $all_prefixes[$ckey];
            $var = GetPHPVariableName($out_var, $each_cat);
            $GLOBALS[$var] = 'checked';
        }
    }
    // 'OTHER' category:
    // :::::::::::::::::
        // if a demonstration admin user is using the system, his/her access is limited
        // to seeing, modifying and adding jobs of type 'D'
        // this section ticks all 'other' checkboxes unless it is the 'job type' filter, in
        // which case the other box is not ticked.
    if (!(($ckey == 'type') && ($is_demo_user)))
    {
        $var = GetPHPOtherVariableName($all_prefixes[$ckey]);
        $GLOBALS[$var] = 'checked';
    }
  }

  $unalloc = '';
  $modlead = '';
  $showemail = '';
}

// various of the special filters produce output sorted by person, in 
// which case it's sensible and useful to provide teaching point summaries
$dopointsummary = FALSE;
$dopastyearsummary = FALSE;
$deletedjobmode = FALSE;

// to overwrite the query definition.
//
// the logic here is very similar to that for editmodestate;  if we've
// not clicked the button, but we're in that state anyway, then  
// treat it as if we have clicked the button
// NB but not if we were in that state, but had hit the filter button
$filter_special = "";

if (
     (
       (post_nomatch('filter_special', ''))
       || 
       (post_nomatch('filter_specialstate', 'nofilter_special'))
     ) 
   &&
     ( !post_exists('filter') )
   ) {

   // work out which of the special filters it was ...

   if (post_nomatch('filter_special', '')) {
      $filter_special = $_POST['filter_special'];
   } else { 
      $filter_special = $_POST['filter_specialstate'];
   }

   require('view_jobs_special.inc');
} else {
  // it's useful to know this is set so that it's easy to pre-select
  // the right item in the <select> section in the <form> below ...
  $filter_special = 'nofilter_special';
}



/*
--------------------------------------------------------------
GetFilterString
--------------------------------------------------------------
Matthew Jones, 2008-11-24
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
This function builds the SQL filter string based on the choices
the user has made in the checkboxes onscreen.  The string is
returned.  The &$querydesctm parameter keeps a record of the
filter terms for display to the user.
$prefix  - the internal/external variable name prefix, e.g. cyr in cyr_1, or jty
$categories - array of e.g. Years (1,2,3,4) or Groups (A,B,C,D,E,F,X,M)
&$querydesctm - reference to the query string for human readers
to see what the output is filtered by.
--------------------------------------------------------------
*/
function GetFilterString($column_name, $prefix, $categories, &$querydescx )
{
   // for clarity, alias prefix with column_name:
   // * maybe sometime... *
   //$column_name = $prefix;
   global $is_demo_user;  // demonstration admin users can only see 'D'
   //echo "<p>is_demo_user is $is_demo_user</p>";
   // special case for demonstration admin users:
   if ($is_demo_user)
   {
        if (strtolower($column_name) == 'type')
        {
            $varname = GetPHPVariableName($prefix, 'D');
            $$varname = 'checked';
            // give this variable global scope:
            $GLOBALS[$varname] = $$varname;
            $querydescx = 'D';
            return ' && (FALSE ' ." ||  type = 'D') ";
        }
   }

   // initialise the fragment of SQL statement where clause:
   $filter_fragment = ' ';

   // initialise the inverse of this for the 'other' option
   $not_filter_fragment = ' FALSE ';

   // make an array of the values submitted from the list select
   // textfield:
   $list_values = array();

   // if the user requested that the list be used, parse the list:
   $button_name = GetListButtonName($prefix);
   $use_list = false;
   $list_other_selected = false;
   $category_list = array();

   if (post_exists($button_name))
   {
     // work out the name of the HTML textfield (=name in POST array)
     $list_text_name = GetListTextName($prefix);
     // and retrieve its contents
     $list_text = $_POST[$list_text_name];

     // check and tokenise:
     if (strlen(trim($list_text)) > 0)
     {
        // indicate that this list should be used:
        $use_list = true;
        // tokenise (break into individual characters or numbers:
        $category_list = TokeniseList($list_text, $list_other_selected);
     }
   }
   
   // MJ, debug: write out category list:
   /*
   echo "<p>Categories from list: <br />\n";
   foreach ($category_list as $listedcat)
   {
        echo $listedcat.", ";
   }
   echo "</p>  \n"; */

   // for all categories (not OTHER), loop through the categories defining the
   // php-local variables and giving them values of 'checked' if necessary, as
   // well as building the SQL statement.
   foreach ($categories as $cat_key => $category)
   {
      $varname = GetPHPVariableName($prefix, $category);
      // indicate that the variable is checked and add to SQL filter list,
      // if the variable has been posted AND the list was NOT used,
      // or if the variable exists in the list AND the list should be used.
      if (   (post_exists(GetFORMVariableName($prefix, $category)) and (!$use_list))
          or (in_array(strtoupper($category), $category_list) and ($use_list))
         )
      {
         // create SQL statement fragment
         $filter_fragment .= " || (INSTR($column_name, '$category')) ";
         // record that the user checked this variable on-screen, by creating a
         // variable with that name and assigning it the value of 'checked'.
         // this is used later in the HTML output. Note: added explicitly
         // to the globals array anyway.
         $$varname = 'checked';
         //echo "\n -- $varname is checked -- \n";
         // update the filter criteria list
         $querydescx .= ' '.$category;
      }
      // otherwise, the user did not select this and it should not be
      // checked onscreen in the result screen.
      else $$varname = '';

      // give this variable global scope:
      $GLOBALS[$varname] = $$varname;

      // create the inverse of 'select all' for 'other' category:
      $not_filter_fragment .= " || (INSTR($column_name, '$category')) ";
   }

   // Complete the process by addressing the 'OTHER' variable:
   $other_varname = GetFORMOtherVariableName($prefix);
   $php_other_var = GetPHPOtherVariableName($prefix);

   // if 'other' was checked, add this to the SQL string:
   if ((post_exists($other_varname) and (!$use_list)) or (($list_other_selected) and ($use_list)))
   {
      // create the inverse SQL statement
      $filter_fragment .= " || NOT($not_filter_fragment) || ($column_name IS NULL)";
      $$php_other_var = 'checked';
      $querydescx .= ' ?';
   }
   else
   {
     $$php_other_var ='';
   }

   // give this variable global scope:
   $GLOBALS[$php_other_var] = $$php_other_var;

   // complete the SQL statement:
   $filter_fragment .= ")";

   // and prepend the conjunction:
   $filter_fragment = ' && (FALSE ' . $filter_fragment;

   // finally, return the fragment of SQL statement:
   return $filter_fragment;
}
// END:  GetFilterString
// --------------------------------------------------------------


/*
GetCheckBoxString
--------------------------------------------------------------
Matthew Jones, 2008-11-24
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
This function generates the HTML code that displays a set of
checkboxes that the user can manipulate.
The HTML variables defined here interact with the SQL
statements in GetFilterString
$title  - the title for this set of checkboxes, e.g. Year, Group, Job Type
$prefix - the internal/external variable name prefix, e.g. cyr in cyr_1, or jty
$categories - array of e.g. Years (1,2,3,4) or Groups (A,B,C,D,E,F,X,M)
$captions - the names of the categories e.g. Groups A --> Thermo and Fluids, B --> etc
$other_cats - e.g. 'Other groups', 'Other Years', etc
--------------------------------------------------------------
*/
function GetCheckboxString($title, $prefix, $categories, $captions, $other_cats)
{
    static $even;
    // switch colours for display
    $even = (!$even);

    $HTMLstring = '';
    if ($even) $HTMLstring = '<div class="control_area_even">';
    else $HTMLstring = '<div class="control_area_odd">';
    
    $HTMLstring .= '<div class="leftbit"><table name="'.$title.'"><tr>';
    
    // selection list for display in textfield:
    $select_list_text = '';
    // and the delimeter to separate the categories:
    $select_list_delim = ' ';

    // run through categories, generating HTML checkboxes
    // in a table
    $HTMLstring .= '<td><b>'.$title.':</b></td>'."\n";
    
    foreach ($categories as $cat_key => $category)
    {
        $caption = $captions[$cat_key];
        $varname = GetPHPVariableName($prefix, $category);
        $form_cat_var = GetFORMVariableName($prefix, $category);
        //global $$varname;
        
        $tooltip = "Tick this to display jobs in/of $title '$caption', when Standard Filter is clicked; these jobs will contain '$category' in the $title column.";

        $HTMLstring .=
             '<td><input title="'.$tooltip.'" type="Checkbox" name="'.
             $form_cat_var.
             '" '.$GLOBALS[$varname].'> '.
             $caption.' ['.$category.'] &nbsp;</td>'."\n";
             
        // build list of checked (selected) categories:
        if ($GLOBALS[$varname] == 'checked') $select_list_text .= $category.$select_list_delim;
    }

    // generate HTML for 'Other' category
    $form_other_var = GetFORMOtherVariableName($prefix);
    $php_other_var = GetPHPOtherVariableName($prefix);
    $HTMLstring .=
             '<td><input type="Checkbox" name="'.
             $form_other_var.'" '.$GLOBALS[$php_other_var].' >'.
             $other_cats.'&nbsp; </td>'."\n";

    // finish off the table and left-floated div
    $HTMLstring .= '</tr></table></div>';


    // now set up the table for the list select controls
    $HTMLstring .= '<div class="rightbit"><table name="'.$title.'_rightbit"><tr>';
    // build list of checked (selected) categories:
    if ($GLOBALS[$php_other_var] == 'checked') $select_list_text .= 'OTHER';
    
    // build the textfield and button:
    // base the size of the textfield on the length of the categories array:
    $text_field_size = count($categories) + 7;
    $HTMLstring .= "<td class=\"chooselist\"><input type=\"text\" size=\"".
         $text_field_size."\" value=\"$select_list_text\" name=\"".
         GetListTextName($prefix).
         "\" /><input type=\"submit\" value=\"OK\" name=\"".
         GetListButtonName($prefix).
         "\"></td>\n";

    // finish off the table
    $HTMLstring .= '</tr></table></div>';
    // finish off the DIV
    $HTMLstring .= '</div>';
    
    return $HTMLstring;
}
// END:  GetCheckBoxString
// -----------------------------------------------------------

/*
GetPHPVariableName()
--------------------------------------------------------------
Matthew Jones, Nov 2008
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
This function constructs the internal PHP variable names for
those variables that have an equivalent in the HTML form (i.e.
in the POST array).  It is good form to have these different
for security reasons.
--------------------------------------------------------------
*/
function GetPHPVariableName($prefix, $category)
{
    $varname = $prefix.$category.'_new';
    return $varname;
}

function GetPHPOtherVariableName($prefix)
{
    $varname = $prefix.'_other_new';
    return $varname;
}


// construct the name of the button in the HTML
// form pressed when the user selects the list rather
// than choosing from the individual checkboxes
function GetListButtonName($prefix)
{
    $varname = $prefix.'_list_btn';
    return $varname;
}

function GetListTextName($prefix)
{
    $varname = $prefix.'_list_text';
    return $varname;
}
/*
END: GetPHPVariableName()
--------------------------------------------------------------
*/

/*
GetFORMVariableName()
--------------------------------------------------------------
Matthew Jones, Nov 2008
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
This function constructs the HTML Form variable names for
those variables that have an equivalent in the PHP code (i.e.
in the POST array).  It is good form to have these different
for security reasons.
--------------------------------------------------------------
*/
function GetFORMVariableName($prefix, $category)
{
    $varname = $prefix.'_'.$category.'_new';
    return $varname;
}

function GetFORMOtherVariableName($prefix)
{
    $varname = $prefix.'_other_new';
    return $varname;
}
/*
END: GetFORMVariableName()
--------------------------------------------------------------
*/


/*
TokeniseList($list_text)
--------------------------------------------------------------
Matthew Jones, Nov 2008
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
This function breaks a list in the form 'A, B, C' into 'A  B  C'
and then into 'A', 'B', 'C'.  Any non-alphanumeric character
can be used as a delimiter.
If 'OTHER' was entered, this is a special case and is
$list_other_selected is set to 'true' if it is found.
--------------------------------------------------------------
*/
function TokeniseList($list_text, &$list_other_selected)
{
  // assume that 'other' is not selected
  $list_other_selected = false;

  // array of tokens:
  $list_letters = array();
  // make list uppercase:
  $list_text = strtoupper(trim($list_text));
  
  // find and replace anything not alphabetic with a space:
    $list_length = strlen($list_text);
    // loop through the characters in the string searching for
    // delimeters; replace with a space
    for ($str_i = 0; $str_i < $list_length; $str_i++)
    {
        // if the character at is not alphanumeric, replace with space
        if (!(($list_text{$str_i} >= 'A') and ($list_text{$str_i} <= 'Z') or
            ($list_text{$str_i} >= '1') and ($list_text{$str_i} <= '9')))
            {  $list_text{$str_i} = ' '; }
    }
  // ---
  
  // break string into array of letters
  $tmp_list = explode(' ', $list_text);

  // run through the list ommitting any blanks from consecutive
  // delimiter characters and excepting for 'OTHER'
  foreach ($tmp_list as $tl)
  {
     if (strlen($tl)>0)
     {
       // if the 'OTHER' value was entered, flag this
       if ($tl == 'OTHER') $list_other_selected = true;
       // otherwise, add to array of letters selected
       else $list_letters[] = $tl;
     }
  }
  
  return $list_letters;
}

/*
// show contents of $GLOBALS array:
    echo "<!-- GLOBALS array:  \n";
    foreach ($GLOBALS as $gkey => $gval)
    {
          if (strpos($gkey, 'cyr') > -1) echo "$gkey := $gval \n";
    }
    echo " END GLOBALS array:  -->\n";
*/



?>
