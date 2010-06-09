<script type="text/javascript">
<!--

window.name='<?php echo "$windowid"; ?>'

var editunitwindow = ''
var addunitwindow = ''

function AddMyUnit() 
{
    if (!addunitwindow.closed && addunitwindow.location) {
       addunitwindow.location.href = 'addunit.php?windowid=<?php echo "$windowid"; ?>'
    } else {
       addunitwindow = open('addunit.php?windowid=<?php echo "$windowid"; ?>', 'update', 'scrollbars=yes, height=130')
       if (!addunitwindow.opener) addunitwindow.opener = self
    }
    if (window.focus) { addunitwindow.focus() }
    return false;
}

var selected = { numlines:0, 
<?php
$separator = '';
foreach ($unititems as $unititem) {
   echo $separator.$unititem.':[""]';
   $separator = ', ';
} 
echo $separator.'deleted:[""]';
echo ' }';
?>

function SelectLine(
<?php
$separator = '';
foreach ($unititems as $unititem) {
   echo $separator.$unititem;
   $separator = ', ';
}
?>
) {

     var duplicateline = 0

     for (i=1; i<=selected.numlines; i++) {
        if (id == selected.id[i]) {
           duplicateline = 1
        }
     }

     if (duplicateline == 0) { 
        selected.numlines++
     
<?php
foreach ($unititems as $unititem) {
   echo "selected.".$unititem."[selected.numlines] = ".$unititem."\n";
}

if ($filter_special == 'filter_deleted') {
   echo "selected.".deleted."[selected.numlines] = 1";
}  
?> 
     }
     if (!editunitwindow.closed && editunitwindow.location) {
        editunitwindow.location.href = 'unit_popup.php?windowid=<?php echo "$windowid"; ?>'
     } else {
        editunitwindow = open('unit_popup.php?windowid=<?php echo "$windowid"; ?>', 'update', 'scrollbars=yes,height=150')
        if (!editunitwindow.opener) editunitwindow.opener = self
     }
     if (window.focus) { editunitwindow.focus() }
     return false;
}


//-->
</script>
