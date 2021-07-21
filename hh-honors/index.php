<?php
require_once( 'includes/config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <script type="text/javascript">var debug_disabled = 1; var initCalled = 0;</script>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $gSiteName ?></title>
        <?php addHtmlHeader(); ?>
    </head>
    <body>
        <?php Phase1(1); # Phase1 is for pre-output actions that would interfere with PDF production ?>
        <?php Phase2(2); # Phase2 is for making updates to the database ?>
        <?php Phase3(3); # Phase3 is for display ?>
        <script type="text/javascript">setValue('userId',<?php echo $gUserId?>);</script>
    </body>
</html>