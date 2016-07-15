<?php

// A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
// but we can do it ourself.
if ( !isset( $HTTP_RAW_POST_DATA ) ) {
	$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
}

// fix for mozBlog and other cases where '<?xml' isn't on the very first line
if ( isset($HTTP_RAW_POST_DATA) )
	$HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);

// load constants
include_once('../config.php');

// serve RSD file, if needed
if ( isset( $_GET['rsd'] ) ) {
header('Content-Type: text/xml; charset=utf-8', true);
?><?php echo '<?xml version="1.0" encoding="utf-8"?'.'>'; ?>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
  <service>
    <engineName>NoDB-Blog</engineName>
    <engineLink>http://fremycompany.com</engineLink>
    <homePageLink><?php echo blogURL; ?></homePageLink>
    <apis>
      <api name="MetaWeblog" blogID="1" preferred="true" apiLink="<?php echo blogURL; ?>/API/xmlrpc/" />
    </apis>
  </service>
</rsd>
<?php
exit;
}

// load XMLRPC functions
include_once('./IXR_Server.php');
include_once('./XMLRPC_Server.php');

// Allow for a plugin to insert a different class to handle requests.
$XMLRPC_Server = new XMLRPC_Server();

// Fire off the request
$XMLRPC_Server->serve_request();
exit;

?>