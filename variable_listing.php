<?php

/*   Matthew Jones, May 2009
     -----------------------
     This software parses a PHP file and identifies all variables used:
*/


    $lookin = $argv[1];
    echo "Looking for variables in the following file: $lookin \n";
    
    $fh = fopen($lookin, 'rb');
    if (!$fh) die("Could not open file");
    $line_num = 0;
    $all_vars = array();
    
    while (!feof($fh))
    {
        $line_num++;
        $line = trim(fgets($fh));
        // regex to match variables:
        $regex = '/\$([A-Za-z_0-9]+)/';
        $matches = array();
        preg_match_all($regex, $line, $matches);
        $n = 1;
        if (isset($matches[$n]))
        {
            echo $line_num.": ";
            foreach ($matches[$n] as $varname)
            {
                echo "$varname; ";
                if (!isset($all_vars[$varname])) $all_vars[$varname] = 1;
                else $all_vars[$varname] += 1;
            }

        }
    }
    
    // sort the keys nicely:
    ksort($all_vars);
    // display:
    foreach ($all_vars as $onev_key => $onev_v)
    {
        $msg = '';
        /*
        if ($onev_v == 1) $msg = 'once';
        else if ($onev_v == 2) $msg = 'twice';
        else $msg = "$onev_v times";
        echo $onev_key." used $msg.\n";
        */
        echo $onev_key."\n";
    }
    

?>
