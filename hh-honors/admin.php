<?php

require_once( 'includes/config.php');

$gLF = "\n";

LocalInit();

$gArea = ( isset($_POST['area']) ? $_POST['area'] : "" );
$gFunc = ( isset($_POST['func']) ? $_POST['func'] : "" );

if ($gDebug) {
    DumpPostVars(sprintf("Begin Phase #1 (pre-html)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea));
}

//-----------------------------------------------------------------------------
// Main Program
//
// Initial actions:
//		Start 		==>	Before authentication
//		Login			==> Verify the user password
//		Welcome		==> After successful authentication
//-----------------------------------------------------------------------------
//

$gFunc = ( isset($_POST['func']) ? $_POST['func'] : "" );

switch ($gAction) {
    case( 'Back' ):
    case( 'Logout' ):
        break;

    case( 'New' ):
        if ($gFrom == "UserReleaseNotes") {
            $gAction = "Update";
        }
        break;

    case( 'Download'):
        LocalInit();
        $area = $_POST['area'];
        if ($area == "spiritual") {
            ExcelSpiritual();
        } elseif ($area == "items") {
            ExcelItems();
        }
        break;

    default:
        if ($gFrom == "UserFeatures") {
            $gAction = "Update";
        }
        if ($gFrom == "UserManager") {
            $gAction = "Update";
        }
        if ($gFrom == "UserReleaseNotes") {
            $gAction = "Update";
        }
        if (empty($gAction)) {
            $gAction = "Start";
        }
        break;
}

if ($user->is_logged_in()) {
    UserManager('load', $_SESSION['userid']);
}
WriteHeader();

if ($gDebug & $gDebugWindow) {
    echo "<script type='text/javascript'>";
    echo "debug_disabled=0;";
    echo "clearDebugWindow();";
    echo "createDebugWindow();";
    echo "debug('---start of run ---')";
    echo "</script>";
}

WriteBody();
AddForm();

if ($gDebug) {
    DumpPostVars(sprintf("Begin Phase #2 (perform updates)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea));
}

$area = ( isset($_POST["area"]) ) ? $_POST["area"] : "";

switch ($gAction) {
    case 'Back':
        if ($gFrom == "EditItem") {
            $gAction = 'Main';
            $_POST['area'] = 'items';
        } elseif ($gFrom == "ShowBids") {
            $gAction = 'Main';
            $_POST['area'] = 'bidders';
        } else {
            $gAction = 'Welcome';
        }
        break;

    case( 'Continue' ):
        $gAction = "Start";
        break;

    case( 'Login' ):
        UserManager('verify');
        break;

    case( 'Main' ):
        if ($gFunc == "backup") {
            exec("perl /home/cbi18/site/my_backup.pl > /home/cbi18/backup_sql/manual.log", $out);
        }
        break;

    case( 'Update' ):
        if ($gFrom == "DisplayDates") {
            DateUpdate();
            $gAction = 'Main';
            $_POST['area'] = 'dates';
        } elseif ($gFrom == "DisplayMail") {
            MailUpdate();
            $gAction = 'Main';
            $_POST['area'] = 'mail';
        } elseif ($gFrom == "DisplayFinancial") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == "DisplaySpiritual") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == 'DisplayMain') {
            if ($area == 'reset') {
                DoQuery("start transaction");
                DoQuery("truncate bids");
                DoQuery("truncate bidders");
                DoQuery("update items set status = 0 where status = 1");
                DoQuery("commit");
            }
            $gAction = 'Main';
        } elseif ($gFrom == "UserManagerPassword") {
            UserManager('update');
            $gAction = 'Start';
        } elseif ($gFrom == "UserManagerPrivileges") {
            UserManager('update');
            $gAction = 'Main';
            $gFunc = 'privileges';
        } elseif ($gFrom == 'Users') {
            UserManager('update');
            $gAction = "Main";
            $gFunc = 'users';
        } elseif ($gFrom == 'PledgeEdit') {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == "EditItem") {
            UpdateItem();
            $gAction = 'Main';
            $_POST['area'] = 'items';
        } elseif ($gFrom == "DisplayItems") {
            UpdateItem();
            $gAction = 'Main';
            $_POST['area'] = 'items';
        } elseif ($gFrom == "DisplayCategories") {
            UpdateCategories();
            $gAction = 'Main';
            $_POST['area'] = 'categories';
        } else {
            UserManager('update');
            $gAction = 'Welcome';
        }
        break;
}

$_POST['action'] = $gAction;
$_POST['func'] = $gFunc;

if ($gDebug) {
    DumpPostVars(sprintf( "Begin Phase #3 (display)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

$vect = $args = array();

$vect['Edit'] = 'EditManager';
$vect['Inactive'] = 'UserManager';
$vect['Login'] = 'UserManager';
$vect['Logout'] = 'UserManager';
$vect['Main'] = 'DisplayMain';
$vect['Resend'] = 'UserManager';
$vect['Start'] = 'UserManager';
$vect['Welcome'] = 'DisplayMain';

$args['Inactive'] = array('inactive');
$args['Login'] = array('verify');
$args['Logout'] = array('logout');
$args['Resend'] = array('resend');
$args['Start'] = array('login');

echo "<div class=center>";

if (!empty($vect[$gAction])) {
    $gFunc = $vect[$gAction];
    $arg = array_key_exists($gAction, $args) ? $args[$gAction] : [];
    switch (count($arg)) {
        case( 0 ):
            $gFunc();
            break;

        case( 1 ):
            $gFunc($arg[0]);
            break;

        case( 2 ):
            $gFunc($arg[0], $arg[1]);
            break;
    }
} else {
    switch ($gAction) {
        case( 'Done' ):
            break;

        case( 'Reset Password' ):
            UserManager('reset');
            SessionStuff('logout');
            break;

        default:
            echo "action: $gAction<br>";
            echo "I'm sorry but something unexpected occurred.  Please send all details<br>";
            echo "of what you were doing and any error messages to $gSupport<br>";
            echo "<input type=submit name=action value=Back>";
    }
}

echo "</div>";

echo "</form>";
echo "</body>";
echo "</html>";
?>