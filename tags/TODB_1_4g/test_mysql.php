<HTML>
<HEAD>
 <TITLE>Test wizard page 3: MySQL/PHP installation</TITLE>
</HEAD>
<BODY>

<h1>Testing MySQL/PHP installation:</h1>

<p>Note: TODB uses the PHP MySQL libraries, <i>not</i> the MySQLi libraries.</p>

<p>If your MySQL installation is working correctly with PHP, you should see current date and the decimal (floating point) value of PI displayed below:</p>



<?php

    if (function_exists('mysql_query'))
    {
        // get DB connection apparatus:
        global $dbread;
        require('config/db.inc');
    
        // connect:
        open_db_read();
        
        // test query:
        $tq = 'select pi(), CURDATE();';
        
        // run the query:
        $res = mysql_query($tq, $dbread);
        
        while ($row = mysql_fetch_array($res))
        {
            echo "<p>PI is $row[0] and the current date is $row[1].</p>";
        }
        
        mysql_free_result($res);

    }
    else
    {
        echo '<p>MySQL PHP module not installed!  This is essential for TODB operation.</p>';
    }

?>

<P><A href="test_html.html">Home</a> |Next: <A href="test_tables.php">Test database setup (tables).</a></p>

</BODY>
</HTML>
