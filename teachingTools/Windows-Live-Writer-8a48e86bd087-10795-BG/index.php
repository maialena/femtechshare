<?php include_once('./API/config.php'); ?><!DOCTYPE html>
<html>
  <head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <title><?php echo blogName; ?></title>
    <link rel="stylesheet" href="./RES/blog.css">
    <link rel="EditURI" type="application/rsd+xml" href="./API/xmlrpc/?rsd=1" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="./wlwmanifest.xml" />
  </head>
  <body>
    <center>
<?php

    // Check the required arguments
    if(isset($_GET['cat']) && $_GET['cat'] != '') {
		$dir = './CATS/'.$_GET['d'];
	} else {
		$dir = './ALL';
	}
        
	// Try to open the comment directory
	if ($handle = opendir($dir)) {

		// Loop directory content
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && is_file($dir.'/'.$entry)) {

				// File url
				$url = file_get_contents($dir.'/'.$entry);

                // If the article is published
                if(file_exists(($url.'/published.txt'))) {

				    ?>
      <div class="content">
        <article>
          <h1><a href="<?php echo htmlspecialchars($url); ?>"><?php echo file_get_contents($url.'/title.txt'); ?></a></h1>
          <div><?php echo file_get_contents($url.'/short-description.txt'); ?></div>
          <div><a class="bluelink" href="<?php echo htmlspecialchars($url); ?>">Read more...</a></div>
        </article>
      </div><?php
                }
			}
		}
		closedir($handle);

	}

?>
    </center>
  </body>
</html>