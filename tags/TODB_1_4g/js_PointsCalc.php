<?php 
// added above HTML AEC 15.4.10
require('config/config.inc');
?><HTML>
<HEAD>
 <TITLE>JS calculator form</TITLE>
 <style type="text/css">
   fieldset { font-family: arial; font-size: 11 pt; color: black; }
   .error_area { color: red }
</style>
</HEAD>
<BODY>
<?php

//require 'config/config.inc';
global $calculator_x_desc;

/* This is a simple calculator for working out points scores for Chem.  Their points
are based on the number of students who answered a question, rather than the # of
students doing the course.
Two options are presented:
1. JS calculator
2. A form submission-based calculator if JS does not work

Points calcs are something like: y = mx + c where m is a multiplier, x is the number
of students, c is the points offset (in the chem case, reward for setting the question)
and y is the number of points.


Integration with points formulae:
---------------------------------
The idea is that points formulae can be set up in the usual way (for n-students) but
the values used in the little calculator for n-answers instead.

This is a little tricky, because:
1. In CGI mode, a formula selected from the list will not be able
   to populate the input fields; the values can be set iff a formula has
   already been selected in the parent screen (this is supposed to be
   a popup window).
2. In JS mode, several pieces of information need to be collected and
   stored so that the input fields can be populated when one of the
   options is selected.
3. If a formula is selected and its values are not modified, the window
   needs to send the calculated points total and the formula id to the
   parent window so that the formula ref is stored.


*/
// INPUTS
// ::::::::::::::::::::::::::::::::
$x_caption = "Number of ".$calculator_x_desc;
$c_caption = "Points offset";
$m_caption = "Points multiplier";
$y_caption = "Points";

$yearval = $_GET['yearval']; //'2008_09';
// ::::::::::::::::::::::::::::::::


// get a list of formulae from the database:
// :::::::::::::::::::::::::::::::::::::::::
require 'config/db.inc';
global $dbread;
open_db_read();
$records = array();

// create query for points formulae
$query = "select * from point_formulae_$yearval;";
//execute the query
$res = mysql_query($query, $dbread);

$first = true;
//echo "<table>\n";
while ($line = mysql_fetch_assoc($res))
{
/*    if ($first)
    {
        echo "<tr>\n";
        foreach ($line as $lk => $lv)
        {
            echo "<th>$lk</th>";
        }
        $first = false;
        echo "</tr>\n";
    }
*/
//    echo "<tr>\n";
//    foreach ($line as $lk => $lv) echo "<td>$lv</td>";
    array_push($records, $line);

//    echo "</tr>\n";
}
//echo "</table>\n";

//free the result
mysql_free_result($res);






// Internal variables:
// :::::::::::::::::::
$points_calc_x = '';
$points_calc_c = '';
$points_calc_m = '';
$points_calc_y = '';

// categories:
$input_categories = array('x','m','c');

// error messages[m,x,c,y]:
$error_messages = array();

// captions [m,x,c,y]:
$captions = array();
$captions['x'] = $x_caption;
$captions['y'] = $y_caption;
$captions['c'] = $c_caption;
$captions['m'] = $m_caption;

// points_calc_values
$points_calc_values = array();

// posted values
$posted_values = array();
// copy posted variables into array
foreach ($input_categories as $cat) $posted_values[$cat] = $_POST['points_calc_'.$cat];

// ok to calculate - if all the terms of the calc expression are provided:
$input_ok = array();

// the formula id selected in the on-screen list
$posted_formula_id = -1;

// the name of the submit button is points_calc_calcbutton
// if this is set, the page has just been submitted, in which case send back the same
// form, but with values filled in, along with the answer:
if ($_POST['points_calc_calcbutton'])
{
    // now, if a formula was selected, the values defined by the formula should
    // be used.  The formula data is available in the $records array:
    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    if (isset($_POST['points_calc_formula']))
    {
        // get the formula ID:
        $posted_formula_id = $_POST['points_calc_formula'];
        // if it is -1 ignore:
        if ($posted_formula_id > -1)
        {
            // find the corresponding record:
            foreach ($records as $rec)
            {
                // when found, assign values to variables:
                if ((int)$rec['Formula_ID'] == (int)$posted_formula_id)
                {
                    // fetch the values from the DB and put in
                    // the 'posted values' array to be processed
                    // by the usual rules
                    $posted_values['m'] = $rec['n_Multiplier'];
                    $posted_values['c'] = $rec['offset'];
                }
            }
        }
    }


    // run through the input categories checking the inputs:
    foreach ($input_categories as $cat)
    {
       // first assume that the input was incorrect:
       $input_ok[$cat] = false;
       
       // now look to see if it's ok:
       // :::::::::::::::::::::::::::
       // was the variable submitted and is the value submitted non-trivial?
       if (isset($posted_values[$cat]) & strlen($posted_values[$cat]) > 0)
       {
            // was a numeric value entered?
            if (is_numeric($posted_values[$cat]))
            {
                // store this value to output again with the form response
                $points_calc[$cat] = $posted_values[$cat];
                // indicate that this input was ok
                $input_ok[$cat] = true;
            }
            // if not, the error message should advise the user to enter a number
            else $error_messages[$cat] = "Please enter a number for '".$captions[$cat]."'.  ";
       }
       // if no variable submitted or it was an empty string, ask the user to enter a value
       else $error_messages[$cat] = "Please enter a value for '".$captions[$cat]."'.  ";
    }
    

    
    // if all the necessary bits were defined, calculate:
    $all_ok = true;
    // run through the Input_ok array, trying to prove that 'all_ok' is false:
    foreach ($input_categories as $cat) if (!$input_ok[$cat]) $all_ok = false;
    
    // if all values were entered, calculate:
    if ($all_ok) {
        $points_calc['y'] = round($points_calc['m'] * $points_calc['x'] + $points_calc['c'], 2);
    }
    // otherwise enter a dash for 'y'
    else {
        $points_calc['y'] = '-';
    }

}

//echo the GET variables:
/*
echo "<p>Get vars:\n";
foreach ($_GET as $gk => $gv)
{
    echo "$gk, $gv<br />\n";
}
echo "-blah--</p>";*/

?>



<script language="JavaScript" type="text/javascript">

// --------------------------------------------------------------
// Things to do when the page loads...
// 1. Set formula ID to the same that might have been set
//    in the parent window
// --------------------------------------------------------------
function SetOnLoad()
{
    // get the parent window's selected formula:
    parentRef = window.opener;
    //parentRef.parentform.Formula_ref_1.getSelectedIndex;
}


// --------------------------------------------------------------
// this function is called when the 'Done' button is pressed
// and communicates information to the parent window
// --------------------------------------------------------------
function CommunicateUp()
{
    // which record in the job_popup window needs points to be calculated?
    // i.e. which points field needs to be assigned with the results of the
    // calculator?  This is specified in a GET parameter (CalcPtsElement),
    // which is inserted by PHP below:
    
    var CalcPtsElement = '<?php echo $_GET['CalcPtsElement']; ?>';
    var parentRef = window.opener;
    
    // transfer points calc answer to parent:
    //parentRef.document.popupform.elements[CalcPtsElement].value = calcform.points_calc_y.value;
    parentRef.document.forms[0].elements[CalcPtsElement].value = calcform.points_calc_y.value;

    window.close();
}


var FormulaIDsArray = [ <?php foreach ($records as $rec) echo $rec['Formula_ID'].',';  ?> null];
var FormulaMultArray = [ <?php foreach ($records as $rec) echo $rec['n_Multiplier'].', ';  ?> null];
var FormulaOffsetArray = [ <?php foreach ($records as $rec) echo $rec['offset'].', ';  ?> null];

// ----------------------------------------------------------------
// this function retrieves information about the points formula and
// inserts the formula values into the correct fields:
// ----------------------------------------------------------------
function SetInputsByFormula()
{
    //alert('SetInputsByFormula()');

    // get the selected formula value:
    // :::::::::::::::::::::::::::::::
    var FormulaValue = -1;
    var selected = calcform.points_calc_formula.selectedIndex;
    FormulaValue = calcform.points_calc_formula.options[selected].value;
    //alert('Value is ' + FormulaValue);

    // work out the JS array index for which this formula ID is:
    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    var index = -1;
    for (i=0; i < FormulaIDsArray.length; i++) {
        if (FormulaValue == FormulaIDsArray[i]) {
            index = i;
            break;
        }
    }

    // if the user has not chosen a formula (or has selected none),
    // the input boxes need to be enabled; also, no further action
    // should be taken:
    // ::::::::::::::::
    if (index==-1)
    {
        calcform.points_calc_m.disabled = false;
        calcform.points_calc_c.disabled = false;
        return;
    }

    // now get the multiplier and offset for
    // this formula:
    // :::::::::::::
    var mult = FormulaMultArray[index];
    var offset = FormulaOffsetArray[index];

    // overwrite the values in the form to these:
    // ::::::::::::::::::::::::::::::::::::::::::
    calcform.points_calc_m.value = mult;
    calcform.points_calc_c.value = offset;
    
    // and set the input fields to disabled so the
    // user knows that a formula with fixed values has
    // been selected:
    // ::::::::::::::
    calcform.points_calc_m.disabled = true;
    calcform.points_calc_c.disabled = true;
    
    // finally, calculate points:
    CalculatePoints();
}


// ------------------------------------------------
// JS code to calculate points in the editing form:
// ------------------------------------------------
function CalculatePoints()
{
    // VARIABLES ::::::::
    // ::::::::::::::::::
    // array of input value identifiers in the form
    var InputArray = ['x', 'c', 'm'];
    
    // array of captions:
    var CaptionsArray = [<?php echo "'".$captions['x']."', ";
                               echo "'".$captions['c']."', ";
                               echo "'".$captions['m']."'"; ?>];

    // are the inputs acceptable?
    var InputsOK = [false, false, false];

    // are ALL the inputs acceptable?
    var AllOK = true;
    
    // error messages:
    var ErrorMessages = ['','',''];

    // The answer (number of points)
    var pts;
    
    // CODE  ::::::::::::
    // ::::::::::::::::::

    // now iterate through each of the categories, checking inputs and
    // calculating the values
    for (i=0; i < InputArray.length; i++)
    {
        // tmpvalue represents the form element associated with x, m, and c
        tmpvalue = eval('calcform.points_calc_' + InputArray[i]);

        if (tmpvalue.value.length < 1)
        {
            // indicate that this input is not correct:
            AllOK = false;
            // show an error message
            ErrorMessages[i] = 'Please enter a value for \'' + CaptionsArray[i] + '\'';
        }
        else
        {
            if (isNaN(tmpvalue.value))
            {
                // indicate that this input is not correct:
                AllOK = false;
                // show an error message
                ErrorMessages[i] = 'Please enter a valid number for \'' + CaptionsArray[i] + '\'';

            }
        }
        
    }
    
    pts = '-';
    if (AllOK)
    {
        //var pts_dec;
        pts = parseFloat(calcform.points_calc_c.value) + parseFloat(calcform.points_calc_m.value) * parseFloat(calcform.points_calc_x.value);
        pts = Math.round(pts * 100.)/100.;
    }
    calcform.points_calc_y.value = pts;

    // set all of the error messages to empty:
    for (i=0; i < InputArray.length; i++)
    {
        // get the span object by id for the error message
        spanobj = document.getElementById('error_area_' + InputArray[i]);
        // show any errors:
        spanobj.innerHTML = ErrorMessages[i];

    }
}
</script>


<fieldset>
<legend>Calculator</legend>
<!-- onSubmit="CalculatePoints(); return false;" -->
<form name="calcform"  onSubmit="CalculatePoints(); return false;" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<fieldset class="inside">
<legend>Inputs</legend>
<?php echo $x_caption; ?>: <input size="3" type="text" name="points_calc_x" value="<?php echo $points_calc['x']; ?>" />
  <span class="error_area" id="error_area_x"><?php echo $error_messages['x']; ?></span><br />
  Formula selection: <select name="points_calc_formula" onchange="SetInputsByFormula();">
  <option value="-1">No formula selected</option>
  <?php
  foreach($records as $rec)
  {
    $sel = '';
    if ((int)$rec['Formula_ID'] == (int)$posted_formula_id) $sel = 'selected';
    echo '<option value="'.$rec['Formula_ID'].'" '.$sel.'>'.$rec['F_Name']."</option>\n";
  }
  ?>
  </select><br />

<?php echo $c_caption; ?>: <input size="3" type="text" name="points_calc_c" value="<?php echo $points_calc['c']; ?>" />
  <span class="error_area" id="error_area_c"><?php echo $error_messages['c']; ?></span><br />
<?php echo $m_caption; ?>: <input size="3" type="text" name="points_calc_m" value="<?php echo $points_calc['m']; ?>" />
  <span class="error_area" id="error_area_m"><?php echo $error_messages['m']; ?></span><br />
</fieldset>
<fieldset  class="inside">
<legend>Outputs</legend>
<?php echo $y_caption; ?>: <input type="text" name="points_calc_y" value="<?php echo $points_calc['y']; ?>"/>
  <span class="error_area" id="error_area_y"><?php echo $error_messages['y']; ?></span><br />
<input type="submit" name="points_calc_calcbutton" value="Calculate" />
<input type="submit" name="points_calc_done" value="Done" onclick="CommunicateUp(); return false;" />
</fieldset>
<!--a href="#" onclick="CalculatePoints()">Calculate</a-->

</form>
</fieldset>

</BODY>
</HTML>
