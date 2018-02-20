<?php

require_once( 'includes/config.php' );

$gLF = "\n";

//-----------------------------------------------------------------------------
// Main Program
//
// Initial actions:
//		Start 		==>	Before authentication
//		Login			==> Verify the user password
//		Welcome		==> After successful authentication
//-----------------------------------------------------------------------------
//

LocalInit();
$gArea = ( isset($_POST['area']) ? $_POST['area'] : "" );
$gFunc = ( isset($_POST['func']) ? $_POST['func'] : "" );

if ($gDebug) {
    DumpPostVars(sprintf( "Begin Phase #1> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

switch ($gAction) {
    case 'Back':
    case 'Logout' :
        continue;

    case 'New':
        if ($gFrom == "UserReleaseNotes") {
            $gAction = "Update";
        }
        break;

    case 'Download':
        if ($gArea == "spiritual") {
            ExcelSpiritual();
        } elseif ($gArea == "items") {
            ExcelItems();
        }
        break;

    case 'Reset':
        if( array_key_exists('password',$_POST) ) {
            UserManager('reset');
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
WriteBody();
AddForm();

if ($gDebug) {
    DumpPostVars(sprintf( "Begin Phase #2> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

switch ($gAction) {
    case 'Back':
        if ($gFrom == "EditItem") {
            $gAction = 'Main';
            $gArea = 'items';
        } elseif ($gFrom == "ShowBids") {
            $gAction = 'Main';
            $gArea = 'bidders';
        } elseif ($gFrom == 'source') {
            $gAction = 'Main';
        } elseif( $gFrom == 'UserManagerNew' ) {
            $gAction = 'Login';
        } else {
            $gAction = 'Welcome';
            $gFunc = "";
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
            exec("perl /home/cbi18/site/my_backup.pl auction > /home/cbi18/backup_sql/manual.log", $out);
        } elseif( $gFunc == "debug" ) {
            $gAction = 'Debug';
        }
        break;

    case( 'New' ):
        if( $gArea == 'verify' ) {
            UserManager('new');
            $gAction = 'Done';
        }
        break;
        
    case 'Update':
        if ($gFrom == "DisplayDates") {
            DateUpdate();
            $gAction = 'Main';
            $gArea = 'dates';
        } elseif ($gFrom == "DisplayMail") {
            MailUpdate();
            $gAction = 'Main';
            $gArea = 'mail';
        } elseif ($gFrom == "DisplayFinancial") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == "DisplaySpiritual") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == 'DisplayMain') {
            if ($gArea == 'reset') {
                DoQuery("start transaction");
                DoQuery("truncate bids");
                DoQuery("truncate bidders");
                DoQuery("update items set status = 0 where status = 1");
                DoQuery("commit");
            }
            $gAction = 'Main';
        } elseif ($gFrom == "UserManagerPassword") {
            UserManager('update');
            $gAction = 'Main';
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
            $gArea = 'items';
        } elseif ($gFrom == "DisplayItems") {
            UpdateItem();
            $gAction = 'Main';
            $gArea = 'items';
        } elseif ($gFrom == "DisplayCategories") {
            UpdateCategories();
            $gAction = 'Main';
            $gArea = 'categories';
        } elseif ($gFrom == "DisplayPackages") {
            UpdatePackages();
            $gAction = 'Main';
            $gArea = 'packages';
        } elseif( $gFrom == "MyDebug" ) {
            MyDebug();
            $gAction = 'Debug';
            $gFunc = 'display';
        } else {
            UserManager('update');
            $gAction = 'Welcome';
        }
        break;

    case 'activate':
        UserManager('activate');
        break;
    
    case 'forgot':
        if ($gArea == 'check') {
            UserManager('forgot');
            $gAction = 'Start';
        }
        break;

    case 'verify':
        if ($user->is_logged_in()) {
            $gAction = 'Main';
        } else {
            UserVerify();
        }
        break;
}

if ($gDebug) {
    DumpPostVars(sprintf( "Begin Phase #3> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

$vect = $args = array();

$vect['Debug'] = 'MyDebug';
$vect['Edit'] = 'EditManager';
$vect['Inactive'] = 'UserManager';
$vect['Login'] = 'UserManager';
$vect['Logout'] = 'UserManager';
$vect['Main'] = 'DisplayMain';
$vect['New'] = 'UserManager';
$vect['Resend'] = 'UserManager';
$vect['Reset'] = 'UserManager';
$vect['Start'] = 'UserManager';
$vect['Welcome'] = 'DisplayMain';
$vect['forgot'] = 'UserManager';
$vect['verify'] = 'LoginMain';

$args['Inactive'] = array('inactive');
$args['Login'] = array('verify');
$args['Logout'] = array('logout');
$args['New'] = ['new'];
$args['Resend'] = array('resend');
$args['Reset'] = array('reset');
$args['Start'] = array('login');
$args['forgot'] = array('forgot');

echo "<div class=center>";

if (!empty($vect[$gAction])) {
    $func = $vect[$gAction];
    $arg = array_key_exists($gAction, $args) ? $args[$gAction] : NULL;
    switch (count($arg)) {
        case( 0 ):
            $func();
            break;

        case( 1 ):
            $func($arg[0]);
            break;

        case( 2 ):
            $func($arg[0], $arg[1]);
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