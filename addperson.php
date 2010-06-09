<?php 
// added above HTML AEC 15.4.10
require('config/config.inc');
?>
<html>
<head>
<title>add person</title>
<script type=text/javascript>
<!--

<?php 
//require('config/config.inc');
// this provides the hoover_form_state() and view_people_fix_state() functions
require('popup_form_state.inc');
?>

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
//      self.location.href = 'addperson.php'
   }
}

//-->
</script>
</head>
<body>

<script type=text/javascript>
<!--
   this.document.write('<form method=post action="')
   this.document.write(this.opener.location.href)
   this.document.write('" target="<?php echo "$windowid"; ?>"')
   this.document.writeln(' onsubmit="possibly_close()">')
//-->
</script>
<table rules=groups>
<thead><tr>
<?php
require('config/people.inc');
foreach ($updateableadminpersoncolshdr as $personitem) {
   echo "<th>".$personitem."</th>\n";
}
?>
</tr><thead><tbody><tr>
<script type=text/javascript>
<!-- 
<?php
$personcol = 0;
foreach ($updateableadminpersoncols as $personitem) { 
   $fieldwidth=$adminpersoncolwidths[$personcol++];
   echo "this.document.write('<td><input type=text size=\"')\n";
   echo "this.document.write('".$fieldwidth."\" name=\"".$personitem."')\n";
   echo "this.document.write('\" value=\"\"></td>')\n";
}
?>
//-->
</script>
</tr>
</table>
<input type="hidden" name="windowid" value="<?php echo "$windowid"; ?>">
<input type="submit" name="PersonAdd" value="Apply and clear" 
    onclick="javascript:doclose='yes'">&nbsp;
<input type="submit" name="PersonAdd" value="Apply and retain" 
    onclick="javascript:doclose='no'">&nbsp
<input type="button" name="cancel" value="Cancel" onclick="cancelme()">

<script type=text/javascript>
<!--

hoover_form_state()
view_people_state_fix()

//-->
</script>

</form>

</body></html>
