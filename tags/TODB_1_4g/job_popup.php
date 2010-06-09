<?php 
// added above HTML AEC 15.4.10
require('config/config.inc');
?>
<html>
<head>
<title>update job</title>
<script type=text/javascript>
<!--

<?php
//require('config/config.inc');
require('useful.inc');
require('config/db.inc');
// this provides the hoover_form_state() and view_jobs_fix_state() functions
require('popup_form_state.inc');

global $show_points_calculator;
?>

function cancelme()
{
   this.opener.selected.numlines=0
   self.close()
}

<?php
require('scroll_java.inc');
?>

//-->
</script>



<!-- ::::::::::::::::::::::::::::::::::::::::: -->
<?php
  global $dbread;


  // get yearval from $_GET
  $yearval = false;
  $yearval = $_GET['yearval'];
  //echo "\n<!-- Yearval is $yearval -->\n";
  if (!$yearval) $yearval = '2008_09';

  // AUTOCOMPLETION in edit screen:
  // ------------------------------
  // run a query to get the list of names from the people database, for the autocompletion:
  if(!isset($dbread)) open_db_read();
  $namelist = array();
  $people_q = 'select uname from people_'.$yearval.';';
  $res = mysql_query($people_q, $dbread);
  while ($res_arr = mysql_fetch_array($res))
  {
    $namelist[] = $res_arr[0];
  }
  $all_unames = implode('#', $namelist);
  
  // Get the list of jobs types from the configuration, for the autocompletion:
  global $all_categories;
  $all_job_types = implode(' ', $all_categories['type']);

  

  
  close_db_read();

?>

<!--  MJ, Jan 2009
      Javascript for supporting the drop-down menus.
      This uses JQuery Autocomplete technology.  See:  http://docs.jquery.com/Plugins/Autocomplete
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
    
    for (i=1; i<= window.opener.selected.numlines; i++)
    {
        $("#id_uname_" + i).autocomplete(data);
    }

  });
  
  var lastTime = 0;
  
  function EndBits()
  {
    setTimeout('self.close()', 250);
  }
  

  </script>
<!-- ::::::::::::::::::::::::::::::::::::::::: -->

</head>
<body>

<script type=text/javascript>
<!--
   this.document.write('<form method="post" name="popupform" action="')
   this.document.write(this.opener.location.href)
   this.document.write('" target="<?php echo "$windowid"; ?>"')
   this.document.writeln(' onsubmit="javascript:EndBits();">')
//-->
</script>

<table rules=groups>
<thead><tr>
<th><font size=-1>Del?</font></th>
<th>duplicate?</th>
<?php
require('config/jobs.inc');

    global $updateableadminjobcolshdr;
foreach ($updateableadminjobcolshdr as $jobitem) {
   echo "<th>".$jobitem."</th>\n";
}
?>

<?php

// MJ: get the list of formulae from the DB, once:
$form_list = GetFormulaeList($yearval);

?>

</tr><thead><tbody>
<script type=text/javascript>
<!--

function OpenCalcWindow(fieldname)
{
    //alert('opening window...');
    window.open("js_PointsCalc.php?yearval=<?php echo $yearval; ?>&CalcPtsElement=points_"+fieldname, "CalculatorWindow", "width=650,height=300,toolbar=no,resizable=yes");
}

/*function SetAutocompleteHere(rownum)
{
    //alert('Trying to do autocomplete on field ' + "#id_uname_"+rownum);
    //var idname = "#id_uname_" + rownum;
    //$(idname).autocomplete(data);
    alert ('OK');

} */



var idlist
var separator
idlist=''
separator=''
for (i=1; i<= this.opener.selected.numlines; i++) {
   this.document.write('<tr>')
   this.document.write('<td><input type="checkbox" name="deleted_')
   this.document.write(this.opener.selected.id[i])
   if (this.opener.selected.deleted[i] == 1) {
      this.document.write('" checked=true')
   } else {
      this.document.write('"')
   }
   this.document.write(' value="Delete line"></td>')

   // the following includes a 'make duplicate' button, so that a job can be copied
   this.document.write('<td><input type="checkbox" name="duplicate" value="duplicate" </td>')
   // end of make duplicate button

<?php
$jobcol = 0;
foreach ($updateableadminjobcols as $jobitem) {
   $fieldwidth=$adminjobcolwidths[$jobcol++];
   
   // the formula_ref column needs to be displayed a selected option on a
   // drop-down combo box, so hence the slightly messy code below
   if (strtoupper($jobitem) == 'FORMULA_REF')
   {
     // create table col and start combobox (select box)
     echo "this.document.write('<td><select ')\n";
     //echo "this.document.write('".$fieldwidth."\" name=\"".$jobitem."_')\n";
     // AEC replaced by line above echo "this.document.write(' name=\"".$jobitem."_')\n";
     echo "this.document.write(' name=\"".$jobitem."_')\n";
     echo "this.document.write(this.opener.selected.id[i])\n";
     echo "this.document.write('\" >')\n";

     // add a blank value to the beginning of the array (meant to match a NULL formula ref)
     array_unshift($form_list, array('', 'No Formula', ''));

     // run through the list of items, displaying them as
     // combobox options
     foreach ($form_list as $flist_val)
     {
       echo "b = ".$flist_val[0]."\n";
       echo "this.document.write('<option value=\"')\n";
       echo "this.document.write('".$flist_val[0]."\"  ')\n";
       echo "if (this.opener.selected.".$jobitem."[i] == '".$flist_val[0]."') this.document.write('selected')\n";
       echo "this.document.write('>')\n";

       echo "this.document.write('".$flist_val[1]." [".$flist_val[2]."]')\n";
       echo "this.document.write('</option>')\n";
     }
     // finish off select box
     echo "this.document.write('</select></td>')\n";
   }
   else
   {
     echo "this.document.write('<td><input type=text  id=\"id_".$jobitem."_'+ i +'\"  size=\"')\n";
     echo "this.document.write('".$fieldwidth."\" name=\"".$jobitem."_')\n";
     echo "this.document.write(this.opener.selected.id[i])\n";
     echo "this.document.write('\" value=\"')\n";
     echo "this.document.write(this.opener.selected.".$jobitem."[i] + '\"');\n";

     // if the field is uname (person), set the autocomplete list to use this element:
     //if (strtoupper($jobitem) == 'UNAME') echo "this.document.write(' onclick=\"SetAutocompleteHere('+i+');\" ');\n";
     echo "this.document.write(' />";
     if (($jobitem == 'points') && ($show_points_calculator)) echo '<a href="js_PointsCalc.php?yearval='.$yearval.'&CalcPtsElement=points_\'+this.opener.selected.id[i]+\'" target="blank" onclick="OpenCalcWindow(\'+this.opener.selected.id[i]+\'); return false;"><img border="0" src="images/Calculator-1-16x16.png" width="16" height="16" alt="Points Calculator" /></a>';
     echo"</td>')\n";
   }
}
?>
   //this.document.write('<td><a href="/todb/app/js_PointsCalc.php?CalcPtsElement=points_'+this.opener.selected.id[i]+'" target="blank" onclick="OpenCalcWindow('+this.opener.selected.id[i]+'); return false;"><img border="0" src="Calculator-1-32x32.png" width="32" height="32" /></a></td>');
   this.document.write('</tr>')
   idlist = idlist+separator+this.opener.selected.id[i]
   separator = '+'
}
this.document.write('<input type="hidden" name="idlist" value="')
this.document.write(idlist)
this.document.writeln('">')
this.document.write('<input type="hidden" name="origxscroll" value="')
this.document.write(getxscroll(this.opener.window))
this.document.writeln('">')
this.document.write('<input type="hidden" name="origyscroll" value="')
this.document.write(getyscroll(this.opener.window))
this.document.writeln('">')

//-->
</script>
</tbody>
</table>
<input type="hidden" name="windowid" value="<?php echo "$windowid"; ?>">
<p><input type="submit" name="JobEdit" value="Apply">&nbsp;
<input type="button" name="cancel" value="Cancel" onclick="cancelme()"></p>

<?php

    echo "<p>All job types: $all_job_types</p>";

?>


<script type=text/javascript>
<!--

hoover_form_state()
view_jobs_state_fix()

//-->
</script>

</form>

</body></html>

<?php
require('resize_popup.inc')
?>
