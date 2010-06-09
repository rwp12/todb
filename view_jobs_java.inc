<!-- *********************  Javascript for popup editing of jobs *************** -->
<script language="Javascript">
<!--


window.name='<?php echo "$windowid"; ?>'

var editjobwindow = ''
var addjobwindow = ''

function AddMyJob() 
{
    if (!addjobwindow.closed && addjobwindow.location)
    {
         addjobwindow.location.href = 'addjob.php?windowid=<?php echo "$windowid&yearval=$yearval"; ?>'
    }
    else
    {
       addjobwindow = open('addjob.php?windowid=<?php echo "$windowid&yearval=$yearval"; ?>', 'update', 'scrollbars=yes, height=250, resizable')
       if (!addjobwindow.opener) addjobwindow.opener = self
    }
    if (window.focus) { addjobwindow.focus() }
    return false;
}


<?php
echo 'var selected = { numlines:0,';
$separator = '';
foreach ($jobitems as $jobitem) {
   echo $separator.$jobitem.':[""]';
   $separator = ', ';
} 
echo $separator.'deleted:[""]';
echo " }\n";
?>


<!-- the SelectLine() function -->
<?php
echo 'function SelectLine(';
$separator = '';
foreach ($jobitems as $jobitem) {
   echo $separator.$jobitem;
   $separator = ', ';
}
echo ")\n";
?>
 {
     var duplicateline = 0

     for (i=1; i<=selected.numlines; i++)
     {
        if (id == selected.id[i])
        {
           duplicateline = 1
        }
     }

     if (duplicateline == 0)
     { 
        selected.numlines++
     
<?php
foreach ($jobitems as $jobitem) {
   echo "        selected.".$jobitem."[selected.numlines] = ".$jobitem."\n";
}

if ($filter_special == 'filter_deleted') {
   echo "        selected.deleted[selected.numlines] = 1";
}  
?> 
     }
     if (!editjobwindow.closed && editjobwindow.location)
     {
        editjobwindow.location.href = 'job_popup.php?windowid=<?php echo "$windowid"; ?>&yearval=<?php echo $yearval; ?>'
     }
     else
     {
        editjobwindow = open('job_popup.php?windowid=<?php echo "$windowid"; ?>&yearval=<?php echo $yearval; ?>', 'update', 'scrollbars=yes,height=150')
        if (!editjobwindow.opener) editjobwindow.opener = self
     }
     if (window.focus) { editjobwindow.focus() }
     return false;
}


//-->
</script>
<!-- *********************  END Javascript for popup editing of jobs *************** -->
