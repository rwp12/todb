<script type="text/javascript">
<!--

window.name='<?php echo "$windowid"; ?>'

var editpersonwindow = ''
var addpersonwindow = ''

function AddMyPerson() 
{
    if (!addpersonwindow.closed && addpersonwindow.location) {
       addpersonwindow.location.href = 'addperson.php?windowid=<?php echo "$windowid"; ?>'
    } else {
       addpersonwindow = open('addperson.php?windowid=<?php echo "$windowid"; ?>', 'update', 'scrollbars=yes, height=130')
       if (!addpersonwindow.opener) addpersonwindow.opener = self
    }
    if (window.focus) { addpersonwindow.focus() }
    return false;
}

var selected = { numlines:0, 
<?php
$separator = '';
foreach ($personitems as $personitem) {
   echo $separator.$personitem.':[""]';
   $separator = ', ';
} 
echo $separator.'deleted:[""]';
echo ' }';
?>

function SelectLine(
<?php
$separator = '';
foreach ($personitems as $personitem) {
   echo $separator.$personitem;
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
foreach ($personitems as $personitem) {
   echo "selected.".$personitem."[selected.numlines] = ".$personitem."\n";
}

if ($filter_special == 'filter_deleted') {
   echo "selected.".deleted."[selected.numlines] = 1";
}  
?> 
     }
     if (!editpersonwindow.closed && editpersonwindow.location) {
        editpersonwindow.location.href = 'person_popup.php?windowid=<?php echo "$windowid"; ?>'
     } else {
        editpersonwindow = open('person_popup.php?windowid=<?php echo "$windowid"; ?>', 'update', 'scrollbars=yes,height=150')
        if (!editpersonwindow.opener) editpersonwindow.opener = self
     }
     if (window.focus) { editpersonwindow.focus() }
     return false;
}


//-->
</script>
