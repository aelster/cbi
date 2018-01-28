<?php

$tpath = array();
if( preg_match( "/cbi18.org/", $_SERVER['HTTP_HOST'] ) === 1 ) {
    $tpath[] = '/usr/lib/php/';
    $tpath[] = '/usr/local/lib/php';
    $tpath[] = '/home/cbi18/site/php';
    $tpath[] = '/home/cbi18/site/Swift-5.0.1';
} else {
    $tpath[] = '/usr/local/site/php';
    $tpath[] = '/usr/local/swiftmailer';
    $tpath[] = '/usr/local/fpdf';
}

$path = implode( PATH_SEPARATOR, $tpath );
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
date_default_timezone_set('America/Los_Angeles');

require_once 'lib/swift_required.php';
require_once( 'SiteLoader.php' );
SiteLoad( 'Common' );

include( 'globals.php' );
include( 'library.php' );
include( 'local_cbi_auction.php' );

$gDb = OpenDb();                # Open the MySQL database

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

if( $action == "bid" ) {
	include( "bids.php" );
	
} elseif( $action == "pledge" ) {
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
