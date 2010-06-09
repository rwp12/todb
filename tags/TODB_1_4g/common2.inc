<?php

// We do much the same thing in both view_people.php and view_jobs.php
// Just have to make sure that $tablename and $tablething are pre-set!
// (see comment at beginning of common1.inc)

$uctablename=ucfirst($tablename);

// and present any informational messages we needed to:
echo $GLOBALS['inform_message'];

// TEMPORARY - useful for debugging the hideousness of the form submission
//$debug = TRUE;
$debug = FALSE;

if (array_key_exists('REMOTE_USER', $_SERVER) &&
    ($_SERVER['REMOTE_USER'] == 'jpmg') && $debug)
{
  foreach (array_keys($_POST) as $key)
  {
    $value = $_POST[$key];
    echo "<p>$key : $value</p>";
  }
  echo "<p>window_id = $windowid</p>";
}

// END

?>
<!-- was 'searchform' - MJ, 2009-05-08 -->
<form name="mainform" method="post" action="view_<?php echo $tablename; ?>.php?yearval=<?php echo $yearval; ?>">

<?php

// propagate the window_id
echo "<input type=hidden name=\"windowid\" value=\"".$windowid."\">\n";
// and the yearval
echo "<input type=hidden name=\"yearval\" value=\"".$yearval."\">\n";

?>
<table cellspacing="0" border="0" width="100%">
<tr>
<td><b>View <?php echo $uctablename; ?></b></td>

<?php
if ($isadminuser)
{
   if ($adminwantstoedit)
   {
     ?>
     <td align=right>
     <input type="Submit" name="editmode" value="Finish Edit">
     <input type=hidden name="editmodestate" value="Edit"></td>
     <td align=left>
     <?php
     if ($deletedjobmode)
     {
       ?>
       <button type="submit" name="PurgeDeleted" value="PurgeDeleted<?php echo $uctablename; ?>">Purge Deleted <?php echo $uctablename;?></button></td>
       <?php
     }
     else
     {
       ?>
       <button type="button" onclick="javascript:AddMy<?php echo $tablething; ?>()" name="Add<?php echo $tablething; ?>" value="Add New <?php echo $tablething; ?>">Add New <?php echo $tablething; ?></button></td>
       <?php
     }
   }
   else
   {
     // check if we were prevented from editing by a lock
     if ($forceunlockbutton)
     {
       ?>
       <td align=right>
       <input type="Submit" name="editmode" value="Edit">
       <input type=hidden name="editmodestate" value="Finish Edit"></td>
       <td align=left>
       <input type="Submit" name="forceunlock" value="Force Unlock"></td>
       <?php
     }
     else
     {
       if (1)
       {
         ?>
         <td><input type="Submit" name="editmode" value="Edit">
         <input type=hidden name="editmodestate" value="Finish Edit">
         </td>
         <?php
       }
     }
   }
}

?>
<td align="right" valign="bottom"><input type="Submit" name="return" value="Home"></td>
</tr>
</table>
<?php

// Allow non-admin user to view as normal user
if ($usertestmode && $isadminuser)
{
	echo"<p>View screen as a normal user ?:
	<input type='Checkbox' 
	title='Check this if you wish to see how a normal user views this screen, then click the appropriate action button.  Clicking the action button again will take you back to the Administrator Screen' 
	name='viewasuser'  ></p>";
}
?>

