<?php  

    include_once('../config.php');

    // Check the required arguments
    if(isset($_GET['d']) && $_GET['d'] != '' && isset($_POST['author']) && $_POST['author'] != '' && isset($_POST['message'])) {
        $dir = $_GET['d']; $dir=trim($dir,'/'); $dir=substr($dir,strlen(blogURL)-strlen(siteURL));
        $firstEntry = TRUE;

        // Check security features
        if ((strpos($dir,'.') === FALSE)) {

            // Try to open the comment directory
            $commentDir = '../../'.$dir;
            if (file_exists($commentDir)) {

                // create the comments directory, if needed
                $commentDir = $commentDir.'/comments/';
                mkdir($commentDir);

                // create the file
                date_default_timezone_set("Europe/Paris");
                $date = date('y-m-d-H-i-s');
                $dateStr = date(DATE_RFC822);

                $random = rand(100,999);
                $fileName = $date.'-'.$random.'.txt';
                file_put_contents($commentDir.$fileName,$_POST['author'].' on '.$dateStr."\n".$_POST['message']);

                header('Location: '.$_GET['d'].'?comment-posted=true');
                exit;
            }
        
        }        
    }

    header('Location: '.$_GET['d'].'?comment-posted=false');
    exit;


?>