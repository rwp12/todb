<HTML>
<HEAD>
 <TITLE>Test wizard page 2: PHP</TITLE>
</HEAD>
<BODY>

<h1>Testing PHP installation:</h1>
<p>If your PHP installation is working correctly, you should see current date and time and the decimal (floating point) value of PI displayed below:</p>

<?php

    list($sec, $usec) = microtime();
    
    echo "<p>The date and time of this request are: ".date('l jS \of F Y h:i:s A')."</p>";
    
    echo "<P>PI is ".(22/7.)."</P>";
    

?>

<P><A href="test_html.html">Home</a> |Next: <A href="test_mysql.php">Test MySQL</a></p>

</BODY>
</HTML>
