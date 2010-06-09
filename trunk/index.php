<?php
// This program is distributed under the terms of the GNU General Public License
// see COPYING file for more details
//
// Copyright 2010, University of Cambridge

require('config/config.inc');
require('config/header.inc');
require('config/db.inc');
require('config/years.inc');
require('auth.inc');
require('config/top.inc');



echo "<div class=welcometext>$index_welcome";

require('config/index.inc');

if ($isadminuser)
{
    ShowGenericOpBox();
    echo "<p><a href='view_new_year.php'><b>Admin:</b> Modify year configurations</a></p>";
}

echo "</div>";

if ($usertestmode && $isadminuser)
{
	ViewAsUserOption();
}




require('config/footer.inc');

// ----------------------------------------------------------------------------------------------------
// Put automatically-generated links to allow different system functions (view people, view jobs, etc)
// to be applied to any of the current list of available years.  This gets round the limitation that
// when users create new years from the web interface, the links to these years are not automatically
// generated
// (and to do so would counter the efforts to make the index page easily modifyable)
// ----------------------------------------------------------------------------------------------------
function ShowGenericOpBox()
{
    global $valid_years;
    global $generic_operations, $generic_operation_names;
    
    $operations = $generic_operations;
    $operation_names = $generic_operation_names;

    echo "<div class='opsbyyear'>";
    echo "<fieldset title='Select operations (view people, jobs, etc) by year'>";
    echo "<legend>Operations by year</legend>";
    echo "<form name='ShowOpForm' action='ViewGeneric.php' method='POST'>";
    // select list of operations:
    // ::::::::::::::::::::::::::
    echo "<label for='GenericOperation'>Select operation:";
    echo "<select id='GenericOperation' name='GenericOperation'>";
    foreach ($operations as $opk => $opv)
    {
        echo "<option value='$opv'>".$operation_names[$opk]."</option>";
    }
    echo "</select></label>";
    echo "<br />";
    // select list of years:
    // :::::::::::::::::::::
    echo "<label for='GenericYear'>Select year:";
    echo "<select id='GenericYear' name='GenericYear'>";
    foreach ($valid_years as $yrv)
    {
        // pick out yearval (2008_09) and description from valid_years array:
        echo "<option value='".$yrv[0]."'>".$yrv[1]."</option>";
    }
    echo "</select></label>";
    echo "<br />";
    // ok button
    echo "<input type='submit' name='GenericOK' value='OK' />";

    echo "</fieldset>";
    echo "</form></div>";
}
// ----------------------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------------------
// This added to allow an Admin user to view this screen as a 'normal user'
// ----------------------------------------------------------------------------------------------------
function ViewAsUserOption()
{
    echo "<div class='viewasuser'>";

    echo "<form name='ViewAsUserForm' action='index.php' method='POST'>";
    echo "<label for='viewasuser'>View this screen as a normal user:";
    echo "<input type='submit' title='Click this if you wish to see how a normal user views this screen.  To return to Administrator view, click on one of the view links, then HOME to come back again' 
    	  name='viewasuser' value='OK' />";
    echo "<input type=hidden name='viewasuser' value='True'></td>"; 
    echo "</form></div>";
}
// ----------------------------------------------------------------------------------------------------



?>
