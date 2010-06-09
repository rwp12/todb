<?php


/* Matthew Jones, April 2009
   mrj35@cam.ac.uk
   =========================
   
   This is meant to:
   -------------------------------------------------------------------------------------
   1. Read a series of files, looking for all required includes, and displays the heirarchy of includes.
   2. It can also recursively search these for a particular included file, in order to show which files include it.
   -------------------------------------------------------------------------------------

   Usage:
   -------------------------------------------------------------------------------------
   php requirements.php filelist.txt [searchfor]
   - where filelist.txt contains a list of files to search (one per line)
   - and searchfor is the name of a required file, to show where it is used [optional];
     if filename contains spaces, enclose in double-quotes (on WinXP at least)
   -------------------------------------------------------------------------------------
   
   Notes:
   -------------------------------------------------------------------------------------
   - if you want to play with the regular expression uses to identify require lines
     (e.g. to include 'include' lines, or ignore comment lines) please change
     the variable '$regex' below.
   -------------------------------------------------------------------------------------
*/

// :::::::::::::::::::::::::::::::::::::::::::::
$regex = '/.*require.*[\'|"](.*)[\'|"].*/';
// :::::::::::::::::::::::::::::::::::::::::::::


// Instructions:
// -------------
if ($argc < 2)
{
    echo "Usage: php requirements.php filelist.txt [searchfor]\n...where filelist.txt contains a list of files to search (one per line)\n".
         "...and searchfor is the name of a required file, to show where it is used [optional] - if filename contains spaces, enclose in double-quotes (on WinXP at least).";
    die();
}


$allfiles_filename = $argv[1];
echo "Reading filelist from $allfiles_filename\n";
$allfilenames = array();
$ffh = fopen($allfiles_filename, 'rb');
while (!feof($ffh))
{
    $allfilenames[] = trim(fgets($ffh));
}

// find and echo the tree of includes/requires
$allfiles = array();
foreach ($allfilenames as $filename)
{
    echo "$filename\n";
    $thisreftreenode = new FileRefTreeNode($filename);
    $first_allfiles = array();
    FindRequires($filename, $first_allfiles, 1);
    foreach ($first_allfiles as $sf)
    {
        $thisreftreenode->AddInclude($sf);
    }
    array_push($allfiles, $thisreftreenode);
    
}


// -------------------------------------------------------
// now search for something:
//var_dump($allfiles);


// if the user entered the second (optional) parameter:
if ($argc > 2 )
{
    // find the filename, excluding any apostrophes etc:
    $param = $argv[2];

    $search = $param;
    $first = true;
    foreach ($allfiles as $af)
    {
        $tmp = $af->filename;
        if ($af->SearchIncludes($search, 0))
        {
            if ($first) {
                $first = false;
                echo "The file '$search' is required by the following files:\n";
            }
            echo "$tmp";
            echo "\n";
        }
    }
}





////////////////////////////////////////////////////////////////////////
function FindRequires($fname, &$filelist, $level)
{
    global $regex;
    // open the file:
    if (file_exists($fname))
    {
        $fh = fopen($fname, 'rb');
        if (!$fh) return;
    }
    else return;
    
    
    while (!feof($fh))
    {
        // read a line:
        $line = fgets($fh);
        // regex to find require:
        $matches = array();
        if (preg_match_all($regex, $line, $matches))
        //if (preg_match_all('/^((?!\/\/).)*require.*[\'|"](.*)[\'|"]*$/', $line, $matches))
        {
            foreach ($matches[1] as $file)
            {
                for ($i=0; $i<$level; $i++) echo "-> ";
                echo "$file \n";
                $thisreftreenode = new FileRefTreeNode($file);

                if ($level > 100)
                {
                    echo "SAFETY NET!";
                    return;
                }
                // the recursive bit:
                $subfilelist = array();
                FindRequires($file, $subfilelist, $level+1);
                foreach ($subfilelist as $sf)
                {
                    $thisreftreenode->AddInclude($sf);
                }
                array_push($filelist, $thisreftreenode);
                //echo "\n";
            }
        }
    }
    
    fclose($fh);
}




class FileRefTreeNode
{
    var $filename;
    var $includes = array();
    var $isincludedby = array();
    
    function FileRefTreeNode($fn)
    {
        $this->filename = trim($fn);
    }
    
    // stores a reference to another one of these (FileRefTreeNode objects)
    // that this includes
    function AddInclude(&$node)
    {
        array_push($this->includes, $node);
    }
    
    // stores a ref to files that include this
    function AddIncludedBy(&$node)
    {
        array_push($this->isincludedby, $node);
    }
    
    // search includes:
    function SearchIncludes($filename, $level)
    {
        //echo "Searching $this->filename for $filename...";
        // the natural end of recursion, if the file is found
        if (($filename == $this->filename) /*&& ($level > -1)*/)
        {
            //echo "[Found]\n";
            return true;
        }
        
        // if there are no more sub-includes and there has been no match, return false (not found)
        $children = count($this->includes);
        if ($children < 1)
        {
            //echo "[Not found]\n";
            return false;
        }
        
        // no match has been found, but files are included, so carry on searching:
        foreach ($this->includes as $include)
        {
            if ($include->SearchIncludes($filename, $level+1)) return true;
        }
        
        // otherwise return false
        return false;
    }

}



?>
