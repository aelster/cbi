<?php

require_once( 'includes/config.php');

$gLF = "\n";

LocalInit();

$sa=0;
$saveAction[$sa++] = $gAction . "[$gDebug]";

$gArea = ( isset($_POST['area']) ? $_POST['area'] : "" );
$gFunc = ( isset($_POST['func']) ? $_POST['func'] : "" );

if( $gAction == 'Download') {
    $area = $_POST['area'];
    if ($area == "spiritual") {
        ExcelSpiritual();
    } elseif ($area == "items") {
        ExcelItems();
    } elseif ($area == "gabbai") {
        ExcelGabbai();
    } elseif ($area == "donations") {
        ExcelMoney();
    }
}
if($user->is_logged_in()) {
    UserManager('load', $_SESSION['userid'] );
}

$saveAction[$sa++] = $gAction . "[$gDebug]";
WriteHeader();
$saveAction[$sa++] = $gAction . "[$gDebug]";
WriteBody();
$saveAction[$sa++] = $gAction . "[$gDebug]";
AddForm();
$saveAction[$sa++] = $gAction . "[$gDebug]";

if ($gDebug) {
    DumpPostVars(sprintf("Begin Phase #1 (pre-html)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea));
}
switch ($gAction) {
    case( 'Back' ):
    case( 'Logout' ):
        break;

    case( 'New' ):
        if ($gFrom == "UserReleaseNotes") {
            $gAction = "Update";
        }
        break;
  
    case 'Reset':
        if( array_key_exists('password',$_POST) ) {
            UserManager('reset');
        }
        break;
        
    case 'Special':
        SpecialCode();
        $gAction = "Main";
        break;
    
    default:
        if ($gFrom == "UserFeatures") {
            $gAction = "Update";
        } elseif ($gFrom == "UserManager") {
            $gAction = "Update";
        } elseif ($gFrom == "UserReleaseNotes") {
            $gAction = "Update";
        } elseif (empty($gAction)) {
            $gAction = "Start";
        } else {
            Logger( '** No action taken for Phase #1 **' );
        }
        break;
}

if( $gDebug & $gDebugWindow ) {
    echo "<script type='text/javascript'>";
    echo "debug_disabled=0;";
    echo "clearDebugWindow();";
    echo "createDebugWindow();";
    echo "debug('---start of run ---')";
    echo "</script>";
}

if ($gDebug) {
   for( $i = 0; $i < $sa; $i++ ) {
       Logger( "gAction[$i]: " . $saveAction[$i] );
   }
   DumpPostVars(sprintf( "Begin Phase #2 (perform updates)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

switch ($gAction) {
    case 'Back':
        if ($gFrom == "EditItem") {
            $gAction = 'Main';
            $_POST['area'] = 'items';
        } elseif( $gFrom == "MembersEdit" ) {
            MembersDisplay();
            exit;
            $gAction = 'Main';
            $gFunc = "members";
        } else {
            $gAction = 'Welcome';
            $gFunc = "";
        }
        break;

    case( 'Continue' ):
        $gAction = "Start";
        break;

    case( 'forgot' ):
        if( $gArea == 'check') {
            UserManager('forgot');
            $gAction = 'Start';
        }
        break;
        
    case( 'Login' ):
        UserManager('verify');
        break;

    case( 'Mail'):
        if ($gFunc == "validate") {
            MailValidate();
            $gAction = "Mail";
        }

        if ($gFunc == "all") {
            MailAssignments($gFunc);
            $gAction = "Mail";
        }

        if ($gFunc == "unsent") {
            MailAssignments($gFunc);
            $gAction = "Mail";
        }

        if ($gFunc == "noresponse") {
            MailAssignments($gFunc);
            $gAction = "Mail";
        }

        if ($gFunc == "remind-rosh") {
            MailAssignments($gFunc);
            $gAction = "Mail";
        }

        if ($gFunc == "remind-yom") {
            MailAssignments($gFunc);
            $gAction = "Mail";
        }

        break;

    case( 'Main' ):
        if ($gFunc == "backup") {
            Logger( "About to perform backup ...");
            exec("perl /home/cbi18/bin/hh_honors_backup.pl", $out);
            print_r( $out );
        }

        if ($gFunc == "members") {
            MembersDisplay();
            exit;
        }

        if ($gFunc == "bozo-mode") {
            $stmt = DoQuery("select `ival` from dates where `label` = 'bozo'");
            list( $val ) = $stmt->fetch(PDO::FETCH_NUM);
            $val = 1 - $val;
            DoQuery("update dates set ival = $val where label = 'bozo'");
            $gTrace = $val;
            $gDebug = $val;
        }

        if ($gFunc == "log") {
            LogfileDisplay();
            $gAction = 'Done';
        }

        if ($gFunc == "build-memb") {
            BuildMembers();
            $gAction = 'Main';
        }

        if ($gFunc == "comp-memb") {
            CompareMembers();
            $gAction = 'Done';
        }

        if ($gFunc == "responses") {
            Responses();
            $gAction = "Mail";
        }

        break;

    case( 'Update' ):
        if ($gFrom == "Assign") {
            if ($gFunc == "add") {
                AssignAdd();
                $gAction = "Assign";
            } elseif ($gFunc == "del") {
                AssignDel();
                $gAction = "Assign";
            } elseif ($gFunc == "mail") {
                MailAssignment();
                $gAction = "Assign";
            } elseif ($gFunc == "mails") {
                MailAssignments();
                $gAction = "Assign";
            } elseif ($gFunc == "manual") {
                SendConfirmation();
                $gAction = "Assign";
            }
        } elseif ($gFrom == "DisplayDates") {
            DateUpdate();
            $gAction = 'Main';
            $_POST['area'] = 'dates';
        } elseif( $gFrom == "LogfileDisplay" ) {
            if( $gFunc == "log-reset" ) { 
                LogfileReset ();
                LogfileDisplay();
                $gAction = "Done";
            }
        } elseif ($gFrom == "MailDisplay") {
            MailUpdate();
            $gAction = 'Main';
            $_POST['area'] = 'mail';
        } elseif ($gFrom == "MembersDisplay") {
            MembersUpdate();
            MembersDisplay();
            exit;
        } elseif( $gFrom == "MembersEdit") {
            MembersUpdate();
            MembersEdit();
            exit;
    
        } elseif ($gFrom == "DisplayFinancial") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == "DisplaySpiritual") {
            PledgeUpdate();
            $gAction = 'Main';
        } elseif ($gFrom == 'DisplayMain') {
            if ($area == 'reset') {
                DoQuery("start transaction");
                DoQuery("update items set status = 0 where status = 1");
                DoQuery("commit");
            }
            $gAction = 'Main';
        } elseif ($gFrom == "HonorsEdit") {
            HonorsUpdate();
            $gAction = "Edit";
            $area = "honors";
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
        } elseif( $gFrom == "MyDebug" ) {
            MyDebug();
            $gAction = 'Debug';
            $gFunc = 'display';
        } else {
            UserManager('update');
            $gAction = 'Welcome';
        }
        break;
}

if ($gDebug) {
    DumpPostVars(sprintf( "Begin Phase #3 (display)> gAction: [%s], gFunc: [%s], gArea: [%s]", $gAction, $gFunc, $gArea ));
}

$vect = $args = array();

$vect['Assign'] = 'Assign';
$vect['Debug'] = 'MyDebug';
$vect['Edit'] = 'EditManager';
$vect['Honors'] = 'HonorsEdit';
$vect['Inactive'] = 'UserManager';
$vect['Login'] = 'UserManager';
$vect['Logout'] = 'UserManager';
$vect['Mail'] = 'MailDisplay';
$vect['Main'] = 'DisplayMain';
$vect['New'] = 'UserManager';
$vect['Resend'] = 'UserManager';
$vect['Reset'] = 'UserManager';
$vect['Start'] = 'UserManager';
$vect['Welcome'] = 'DisplayMain';
$vect['forgot'] = 'UserManager';

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
    $fn = $vect[$gAction];
    $arg = array_key_exists($gAction, $args) ? $args[$gAction] : [];
    switch (count($arg)) {
        case( 0 ):
            $fn();
            break;

        case( 1 ):
            $fn($arg[0]);
            break;

        case( 2 ):
            $fn($arg[0], $arg[1]);
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

WriteFooter();