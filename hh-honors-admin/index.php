<?php
require_once( 'includes/config.php');
checkForDownloads();
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
# $gDebugWindow | $gDebugErrorLog;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <script type="text/javascript">var debug_disabled = 1;</script>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $gSiteName ?></title>
        <?php addHtmlHeader(); ?>
    </head>
    <?php $gPhase = 1; Phase1(); # Phase1 is for pre-output actions that would interfere with PDF production ?>
    <?php $gPhase = 2; Phase2(); # Phase2 is for making updates to the database ?>
    <body>
    <?php $gPhase = 3; Phase3(); # Phase3 is for display ?>
    </body>
</html>