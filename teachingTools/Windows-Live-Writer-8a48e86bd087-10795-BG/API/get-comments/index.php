<?php header('Content-Type: application/json'); header("Pragma: public"); header("Cache-Control: maxage=60"); ?>[<?php

    include_once('../config.php');

    // Check the required arguments
    if(isset($_GET['d']) && $_GET['d'] != '') {
        $dir = $_GET['d']; $dir=trim($dir,'/'); $dir=substr($dir,strlen(blogURL)-strlen(siteURL));
        $firstEntry = TRUE;

        // Check security features
        if ((strpos($dir,'.') === FALSE)) {

            // Try to open the comment directory
            $commentDir = '../../'.$dir.'/comments/';
            if ($handle = opendir($commentDir)) {

                // Loop directory content
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {

                        // Check that the entry is a .txt file
                        $fileExt = strstr($entry,'.txt');
                        if($fileExt=='.txt') {

                            // Comma-separator
                            if($firstEntry) { $firstEntry=FALSE; }
                            else { echo ','; }

                            // File url
                            echo('"'.$entry.'"');

                        }
                    }
                }
                closedir($handle);

            }
        
        }        
    }


?>]