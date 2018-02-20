<?php
require_once( 'includes/config.php' );

WriteHeader();
LocalInit();
if( $gDebug & $gDebugWindow ) {
    echo "<script type='text/javascript'>";
    echo "debug_disabled=0;";
    echo "clearDebugWindow();";
    echo "createDebugWindow();";
    echo "debug('---start of run ---')";
    echo "</script>";
}

if (!$gAction)
    $gAction = "pledge";

if ($gAction == "confirm") {
    if ($gDebug)
        DumpPostVars();
    BidAdd();
    $gAction = "pledge";
}

if ($gAction == "bid") {
    include( "bids.php" );
} elseif ($gAction == "pledge") {
    include( "pledge.php" );
} elseif ($gAction == "pledge_now") {
    include( "pledge_now.php" );
} elseif ($gAction == "financial") {
    include( "financial.php" );
} elseif ($gAction == "spiritual") {
    include( "spiritual.php" );
} elseif ($gAction == "paynow") {
    $id = PledgeStore();
    SendConfirmation($id);
    include( "thank_you.php" );
} elseif ($gAction == "paypal") {
    PayPal();
} else {
    echo "uh-oh, not sure what to do with action: [$gAction]<br>";
}
?>
</body>
</html>