<?php
require_once 'lib/swift_required.php';
require_once( 'SiteLoader.php' );
SiteLoad( 'Common' );

include( 'globals.php' );
include( 'library.php' );

include( 'local_cbi_honors.php' );
$gDb = OpenDb();

SessionStuff('start');
WriteHeader();
LocalInit();

if( $gDebug ) {
	$tmp = array_keys( $_POST );
	sort( $tmp );
	foreach( $tmp as $key ) {
		printf( "_POST['%s'] = %s<br>", $key, $_POST[$key] );
	}
	$tmp = array_keys( $_SESSION );
	sort( $tmp );
	foreach( $tmp as $key ) {
		printf( "_SESSION['%s'] = '%s'<br>", $key, $_SESSION[$key] );
	}
}

$action = array_key_exists( "action", $_POST ) ? $_POST[ "action" ]  : "";
if( ! $action ) $action = "pledge";
$gFrom = array_key_exists( "from", $_POST ) ? $_POST[ "from" ]  : "";

$gAction = $action;

if( $action == "confirm" ) {
	if( $gDebug ) DumpPostVars();
	BidAdd();
	$gAction = $action = "pledge";
}

if( $action == 'honor' ) {
	SendConfirmation();
	include( "ThankYou.html" );

} elseif( $action == "pledge" ) {
	include( "pledge.php" );
	
} else {
	echo "uh-oh, not sure what to do with action: [$action]<br>";
}
?>
</body>
</html>
