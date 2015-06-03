<?php
require_once 'lib/swift_required.php';
require_once( 'SiteLoader.php' );
SiteLoad( 'Common' );

include( 'globals.php' );
include( 'library.php' );
include( 'local_cbi.php' );

$gDb = OpenDb();                # Open the MySQL database

//-----------------------------------------------------------------------------
// Main Program
//
// Initial actions:
//		Start 		==>	Before authentication
//		Login			==> Verify the user password
//		Welcome		==> After successful authentication
//-----------------------------------------------------------------------------
//

$gAction = ( isset( $_POST[ "action" ] ) ) ? $_POST[ "action" ] : "";
$gFrom = ( isset( $_POST[ 'from' ] ) ? $_POST[ 'from' ] : "" );
$func = ( isset( $_POST['func'] ) ? $_POST['func'] : "" );

switch( $gAction )
{
	case( 'Back' ):
	case( 'Logout' ):
		continue;
		
	case( 'New' ):
		if( $gFrom == "UserReleaseNotes" ) { $gAction = "Update"; }
		break;
	
	case( 'Download'):
		LocalInit();
		$area = $_POST['area'];
		if( $area == "spiritual" ) {
			ExcelSpiritual();
		} elseif( $area == "financial" ) {
			ExcelFinancial();
		}
		break;
	
	default:
		if( $gFrom == "UserFeatures" ) { $gAction = "Update"; }
		if( $gFrom == "UserManager" ) { $gAction = "Update"; }
		if( $gFrom == "UserReleaseNotes" ) { $gAction = "Update"; }
		if( empty( $gAction ) ) { $gAction = "Start"; }
		break;
}

SessionStuff('start');

WriteHeader();
AddForm();

LocalInit();

if( $gDebug ) { DumpPostVars( "After SessionStuff(start): gAction=[$gAction]" ); }

$area = ( isset( $_POST[ "area" ] ) ) ? $_POST[ "area" ] : "";

switch( $gAction ) {
   case 'Back':
      $gAction = 'Welcome';
      $func = "";
      break;
   
   case( 'Continue' ):
      $gAction = "Start";
      break;

   case( 'Login' ):
      UserManager('verify');
      break;

   case( 'Main' ):
		$func = $_POST['func'];
		if( $func == "backup" ) {
			exec( "perl /home/cbi18/site/my_backup.pl", $out );
		}
		break;
	
	case( 'Reminders' ):
		SendReminders();
		$gAction = 'Main';
		break;
	
	case( 'Update' ):
      if( $gFrom == "DisplayFinancial" ) {
         PledgeUpdate();
         $gAction = 'Main';
         
		} elseif( $gFrom == "DisplaySpiritual" ) {
         PledgeUpdate();
         $gAction = 'Main';
         
      } elseif( $gFrom == 'DisplayGoal' ) {
         GoalUpdate();
         $gAction = 'Main';

      } elseif( $gFrom == 'DisplayMain' ) {
         if( $area == 'reset' ) {
            $query = "delete from pledges where pledgeType = $PledgeTypeFinancial or pledgeType = $PledgeTypeSpiritual";
            DoQuery( $query );
         }
         $gAction = 'Main';
         
      } elseif( $gFrom == "UserManagerPassword" ) {
         UserManager('update');
         $gAction = 'Start';
         
      } elseif( $gFrom == "UserManagerPrivileges" ) {
         UserManager('update');
         $gAction = 'Main';
         $func = 'privileges';
   
      } elseif( $gFrom == 'Users' ) {
         UserManager('update');
         $gAction = "Main";
         $func = 'users';

      } elseif( $gFrom == 'PledgeEdit' ) {
         PledgeUpdate();
         $gAction = 'Main';

      } else {
         UserManager( 'update' );
         $gAction = 'Welcome';
      }
      break;
}

$_POST['action'] = $gAction;
$_POST['func'] = $func;

if( $gDebug ) { DumpPostVars( "After Login/Logout:  gAction=[$gAction]" ); }

$vect = $args = array();

$vect['Edit'] = 'PledgeEdit';
$vect['Inactive'] = 'UserManager';
$vect['Login']	= 'UserManager';
$vect['Logout'] = 'UserManager';
$vect['Main'] = 'DisplayMain';
$vect['Resend'] = 'UserManager';
$vect['Start'] = 'UserManager';
$vect['Welcome'] = 'DisplayMain';

$args['Inactive'] = array('inactive');
$args['Login'] = array( 'verify' );
$args['Logout'] = array( 'logout' );
$args['Resend'] = array( 'resend' );
$args['Start'] = array('login');

echo "<div class=center>";

if( ! empty( $vect[ $gAction ] ) ) {
	$func = $vect[ $gAction ];
	$arg = array_key_exists( $gAction, $args ) ? $args[ $gAction ] : NULL;
	switch( count( $arg ) ) {
		case( 0 ):
			$func();
			break;
		
		case( 1 ):
			$func( $arg[0] );
			break;
		
		case( 2 ):
			$func( $arg[0], $arg[1] );
			break;
	}
} else {
	switch( $gAction )
	{
		case( 'Done' ):
			break;
		
		case( 'Reset Password' ):
			UserManager( 'reset' );
			SessionStuff( 'logout' );
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