<?php

require('config/config.inc');
require('useful.inc');
require('config/db.inc');

// get yearval from $_GET
$yearval = false;
$yearval = $_GET['yearval'];
//echo "\n<!-- Yearval is $yearval -->\n";
if (!$yearval) $yearval = '2008_09';

$form_list = GetFormulaeList($yearval);



// run a query to get the list of names from the people database:
open_db_read();
$namelist = array();
$people_q = 'select uname from people_'.$yearval.';';
$res = mysql_query($people_q, $dbread);
while ($res_arr = mysql_fetch_array($res))
{
  $namelist[] = $res_arr[0];
}
$all_unames = implode('#', $namelist);
close_db_read();

?>

<html>
<head>
<title>add job</title>
<!--  MJ, Jan 2009
      Javascript for supporting the drop-down menus.
      This uses JQuery Autocomplete technology.  See:    http://docs.jquery.com/Plugins/Autocomplete
-->
  <!-- JQuery base: -->
  <script src="./ac_files/lib/jquery.js"></script>
  <!-- the CSS for the drop-down menu -->
  <link rel="stylesheet" href="./ac_files/jquery.autocomplete.css" type="text/css" />
  <!-- autocomplete javascript -->
  <script type="text/javascript" src="./ac_files/jquery.autocomplete.js"></script>
  <script>
  $(document).ready(function(){
    var data = "<?php echo $all_unames ?>".split("#");
$("#id_uname").autocomplete(data);
  });
  </script>





<script type="text/javascript">
<!--

<?php
 // this provides the hoover_form_state() and view_jobs_fix_state() functions
 require('popup_form_state.inc');
?>


function OpenCalcWindow(fieldname)
{
    //alert('opening window...');
    window.open("js_PointsCalc.php?yearval=<?php echo $yearval; ?>&CalcPtsElement="+fieldname, "CalculatorWindow", "width=650,height=300,toolbar=no,resizable=yes");
}

var doclose

function cancelme()
{
   self.close()
}  

function possibly_close()
{
   if (doclose == 'yes') {
      setTimeout('self.close()', 250); 
   } else {
//      self.location.href = 'addjob.php'
   }
}

//-->
</script>

</head>
<body>

<script type="text/javascript">
<!--
   this.document.write('<form method=post action="')
   this.document.write(this.opener.location.href)
   this.document.write('" target="<?php echo "$windowid"; ?>"')
   this.document.writeln(' onsubmit="possibly_close()">')
   //alert(this.opener.location.href);
//-->
</script>

<table rules="groups">
<thead><tr>
<?php
require('config/jobs.inc');
foreach ($updateableadminjobcolshdr as $jobitem) {
   echo "<th>".$jobitem."</th>\n";
}
?>
</tr><thead>

<tbody><tr>
<?php
// MJ: for some reason this was originally javascript writing HTML.
//  I have changed to ordinary HTML to support the autocompletion of
// names:

$jobcol = 0;
foreach ($updateableadminjobcols as $jobitem)
{ 
   $fieldwidth=$adminjobcolwidths[$jobcol++];
   // MJ, Dec 2008: this is a hack to show a drop-down list of point formulae
   if (strtolower($jobitem) == 'formula_ref')
   {
     echo "<td><select name='$jobitem'>\n";
     echo "<option value=''>Select</option>\n";
     foreach ($form_list as $flv)
     {
        echo "<option value='$flv[0]'>$flv[1] ($flv[2])</option>\n";
     }
     echo "</select><td>\n";
   }
   else
   {
     echo "<td><input type='text' size='$fieldwidth' id='id_$jobitem' name='$jobitem' value='' />";
     if (($jobitem == 'points') && ($show_points_calculator))
     {
        echo '<a href="js_PointsCalc.php?yearval='.$yearval.
        '&CalcPtsElement=id_'.$jobitem.'" target="blank" onclick="OpenCalcWindow(\'id_'.$jobitem.'\'); return false;">'.
        '<img border="0" src="images/Calculator-1-16x16.png" width="16" height="16" alt="Points Calculator" /></a>';
     }
     echo "</td>\n";
   }
}
?>
</tr>
</table>
<input type="hidden" name="windowid" value="<?php echo "$windowid"; ?>">
<input type="submit" name="JobAdd" value="Apply and clear" 
    onclick="javascript:doclose='yes'">&nbsp;
<input type="submit" name="JobAdd" value="Apply and retain" 
    onclick="javascript:doclose='no'">&nbsp
<input type="button" name="cancel" value="Cancel" onclick="cancelme()">

<script type=text/javascript>
<!--

hoover_form_state()
view_jobs_state_fix()

//-->
</script>

</form>




</body></html>
