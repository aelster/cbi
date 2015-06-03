<?php
require_once 'lib/swift_required.php';
require_once( 'SiteLoader.php' );
SiteLoad( 'Common' );

include( 'globals.php' );
include( 'library.php' );
include( 'local_cbi.php' );

$gDb = OpenDb();                # Open the MySQL database

LocalInit();
$ok = session_start();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>CBI HH Pledges</title>
<script type="text/javascript" src="hhPledges.js"></script>
</head>
<body>
<?php

if( $gDebug ) {
	$tmp = array_keys( $_POST );
	sort( $tmp );
	foreach( $tmp as $key ) {
		printf( "_POST['%s'] = %s<br>", $key, $_POST[$key] );
	}
	$tmp = array_keys( $_SESSION );
	sort( $tmp );
	echo "SessionStart: $ok<br>";
	foreach( $tmp as $key ) {
		printf( "_SESSION['%s'] = '%s'<br>", $key, $_SESSION[$key] );
	}
}

$action = array_key_exists( "action", $_POST ) ? $_POST[ "action" ]  : "";
if( ! $action ) $action = "pledge";
$gFrom = array_key_exists( "from", $_POST ) ? $_POST[ "from" ]  : "";

$gAction = $action;

if( $action == "pledge" ) {
	include( "pledge.php" );
	
} elseif( $action == "pledge_now" ) {
	include( "pledge_now.php" );

} elseif( $action == "financial" ) {
	include( "financial.php" );

} elseif( $action == "spiritual" ) {
	include( "spiritual.php" );

} elseif( $action == "paynow" ) {
	$id = PledgeStore();
	SendConfirmation( $id );
	include( "thank_you.php" );
	
} elseif( $action == "paypal" ) {
	PayPal();
	
} else {
	echo "uh-oh, not sure what to do with action: [$action]<br>";
}
?>
</body>
</html>
