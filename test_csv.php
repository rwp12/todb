<HTML>
<HEAD>
 <TITLE>Test wizard page 2: PHP</TITLE>
</HEAD>
<BODY>

<h1>Testing CSV directory:</h1>
<p>The static_csv directory needs to be writeable.  This test checks that this is the case.</p>

<?php
    $time = Time();
    $fh = fopen('./static_csv/test'.$time.'.txt', 'wb');
    if ($fh)
    {
        $teststring = 'Testing the static_csv directory...';
        fputs($fh, $teststring);

        fclose($fh);
        $fho = fopen('./static_csv/test'.$time.'.txt', 'rb');
        $cont = fgets($fho);
        echo "<P>$cont</P>";
        if ($cont == $teststring) echo "<p>Your static csv directory appears to be writeable!</p.";
        fclose($fho);
    }
    else
    {
        echo "<p>Could not write to './static_csv/test'.$time.'.txt' ";
    }
    

?>

<P><A href="test_html.html">Home</a> |Next: <A href="test_js.html">Test JavaScript</a></p>

</BODY>
</HTML>
