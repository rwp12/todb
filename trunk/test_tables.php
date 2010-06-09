<HTML>
<HEAD>
 <TITLE>Test wizard page 4: MySQL tables</TITLE>
</HEAD>
<BODY>

<h1>Testing MySQL tables:</h1>

<p>If your MySQL installation has correctly set-up tables:</p>



<?php


$required_tables = array(
  'config_years' => FALSE,
  'editlocks' => FALSE,
  'jobs' => FALSE,
  'people' => FALSE,
  'point_formulae' => FALSE,
  'studentspercourse' => FALSE,
  'units' => FALSE);


    if (function_exists('mysql_query'))
    {
        // get DB connection apparatus:
        global $dbread;
        require('config/db.inc');
    
        // connect:
        open_db_read();
        
        // test query:
        $tq = 'show tables;';
        
        // run the query:
        $res = mysql_query($tq, $dbread);
        
        echo "<ul>";
        while ($row = mysql_fetch_array($res))
        {
            echo "<li />$row[0]: ";
            //$prefix = substr($row[0], 0, strpos($row[0], '_'));
            $pieces = preg_split('/(_[0-9]+_[0-9]+)/', $row[0]);
            $required_tables[$pieces[0]] = TRUE;
            //echo $pieces[0];
        }
        echo "</ul>";
        
        mysql_free_result($res);

    }
    else
    {
        echo '<p>MySQL PHP module not installed!  This is essential for TODB operation.</p>';
    }
    
    echo "<p>Checking tables...</p>";
    echo "<ul>";
    $allok = true;
    foreach ($required_tables as $rk => $rt)
    {
        if (!$rt) {
            $allok = false;
            echo "<li />Missing table: $rk"."_yyyy_yy";
        }
    }
    if ($allok) echo "<li />All tables appear to be present.";
    else echo "<li /><b>Tables are missing!</b> Please redo the 'Database Creation' steps in the Installation Guide (section 6).";
    echo "</ul>";

?>

<P><A href="test_html.html">Home</a> | Next: <A href="test_csv.php">Test CSV output directory.</a></p>

</BODY>
</HTML>
