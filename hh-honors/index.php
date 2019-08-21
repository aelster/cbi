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
#$_REQUEST['hash'] = 'f77b7c';
#========================================================================
WriteHeader();
LocalInit();
$gDebug = 1;


if( array_key_exists('hash', $_REQUEST) ) {
    $hash = $_REQUEST['hash'];
    if( empty($gAction) ) $gAction = "pledge";
} else {
    $gAction = "exit";
}
echo "gAction = $gAction<br>";
        
$stmt = DoQuery("select * from assignments where hash = '$hash' and active = 1");
if ($gPDO_num_rows == 0) {
    $gAction = "pledge";
}
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
    #include 'pledge.php';
}
?>
</body>
</html>
