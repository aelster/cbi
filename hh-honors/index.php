<?php
require_once( 'includes/config.php');
WriteHeader();

$gLF = "\n";

LocalInit();
$gArea = ( isset($_POST['area']) ? $_POST['area'] : "" );
$gFunc = ( isset($_POST['func']) ? $_POST['func'] : "" );

if ($gDebug) {
    DumpPostVars(sprintf("Begin Phase #1 (pre-html)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea));
}

#========================================================================
WriteHeader();
LocalInit();

if ($gAction == "confirm") {
    if ($gDebug)
        DumpPostVars();
    BidAdd();
    $gAction = $action = "pledge";
}

if ($gAction == 'honor') {
    SendConfirmation();
    include( "ThankYou.html" );
} elseif ($gAction == "pledge") {
    include( "pledge.php" );
} else {
    include 'pledge.php';
}
?>
</body>
</html>
