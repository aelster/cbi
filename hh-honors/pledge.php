<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI High Holy Day Honor Home</title>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationRadio.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="oneColFixCtrHdr.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="styles.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<script type="text/javascript" src="hhPledges.js"></script>
<!-- InstanceBeginEditable name="head" -->
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationRadio.js" type="text/javascript"></script>
<!-- InstanceEndEditable -->
</head>
<body onload="firstName();">
<?php
include('globals.php');
AddForm();
$hash = $_REQUEST['hash'];
DoQuery( "select member_id, honor_id from assignments where hash = '$hash'", $gDb2 );
list( $mid, $hid ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
printf( "<input type=hidden name=honor_id value=$hid>" );
printf( "<input type=hidden name=member_id value=$mid>" );

DoQuery( "select * from members where id = $mid", $gDb2 );
$member = mysql_fetch_assoc( $GLOBALS['mysql_result'] );
DoQuery( "select * from honors where id = $hid", $gDb2 );
$honor = mysql_fetch_assoc( $GLOBALS['mysql_result'] );

DoQuery( "select date from dates where id = 1", $gDb2 );
list( $td ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
$date = new DateTime( $td );

$service = $honor['service'];

switch( $service ) {
	case( 'rh1' ):
		$date->add( new DateInterval('P1D') );
		break;
	
	case( 'rh2' ):
		$date->add( new DateInterval('P2D') );
		break;
	
	case( 'kn' ):
		$date->add( new DateInterval('P9D') );
		break;
	
	case( 'yka' ):
	case( 'ykp' ):
		$date->add( new DateInterval('P10D') );
		break;
}

if( ! empty( $member['Female 1st Name' ] ) && empty( $member['Male 1st Name'] ) ) {
	$name = $member['Female 1st Name'];
} elseif( empty( $member['Female 1st Name'] ) && ! empty( $member['Male 1st Name'] ) ) {
	$name = $member['Male 1st Name' ];
} else {
	$name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
}

$str = $member['E-Mail Address'];
if( preg_match( "/,/", $str ) ) {
  $email = preg_split( "/,/", $str, NULL, PREG_SPLIT_NO_EMPTY );
} elseif( preg_match( "/;/", $str ) ) {
	$email = preg_split( "/;/", $str, NULL, PREG_SPLIT_NO_EMPTY );
} elseif( preg_match( "/ /", $str ) ) {
	$email = preg_split( "/ /", $str, NULL, PREG_SPLIT_NO_EMPTY );
} else {
  $email = [ $str ];
}

$name .= sprintf( " %s", $member['Last Name'] );
printf( "<input type=hidden name=hh-name value=\"%s\">", $name );
printf( "<input type=hidden name=hh-email value=\"%s\">", join(",",$email) );
$hstr = sprintf( "%s during the %s service on %s.", $honor['honor'], $gService[ $honor['service']], $date->format( "l, M jS, Y") );
?>
<div id="FloatPage">
<div id="page">
  <div id="AboveBar">
  	<div id="AboveBarLeft">
	  <div id="Logo">
		<a href="http://www.cbi18.org" style="background-image:none; width:auto; height:auto;">
        	<img src="assets/CBI_logo.png" alt="Congregation B&#039;nai Israel" />
        </a>
      <!-- .end Logo --></div>
   <!-- .end AboveBarLeft --></div>
   <div id="AboveBarRight">
   <!--
		<div id="paypaldonate">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="hosted_button_id" value="5N98SQ897NR92" />
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="image" src="http://www.cbi18.org/images/Donate_sm.jpg" border="0"
                 name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
    	</div>
		<div id="candlelight">
        	<a href="#topReveal"><img src="http://www.cbi18.org/images/Candle_sm.jpg" /></a>
        </div>
       -->
        <div id="textwidget">
    		<h3>A Conservative Congregation serving the <br>diverse Jewish needs of Orange County</h3>
	  	</div>

   <!-- .end AboveBarRight --></div>
   <!-- .end AboveBar --></div>
  <div id="MenuBar">
      <ul id="MenuBar1" class="MenuBarHorizontal">
          <li><a href="http://cbi18.org/">CBI Home</a></li>
      </ul>
  <!-- end .MenuBar --></div>
  <div id="content">
  <!-- InstanceBeginEditable name="Content" -->
  <div class="content">
    
    <div class="fltrt" id="rightContents">
  <strong>Name</strong>:&nbsp;<?php echo $name ?><br />
  <strong>Honor</strong>:&nbsp;<?php echo ucfirst($hstr) ?><br />
  <strong>Please check one:</strong>
    <div id="spryradio1">
      <table width="700" border="0">
        <tr>
          <td><label>
            <input type="radio" name="RadioGroup2" value="accept" id="RadioGroup2_0" />
            I accept this honor offered to me</label></td>
      <td><span class="radioRequiredMsg">Please make a selection.</span></td>
        </tr>
        <tr>
          <td><label>
            <input type="radio" name="RadioGroup2" value="decline" id="RadioGroup2_1" />
            I decline this honor offered to me</label></td>
            <td>&nbsp;</td>
        </tr>
      </table>
      </td>
      </div>
    <p><strong>Comments:</strong><br />
  <textarea name="hh-comment" cols="120" rows="2"></textarea></p>
  <div>It is customary for those receiving honors to make a donation to the shul in honor of that participatory role, usually in a multiple of 18, or Chai, the Jewish numerical symbol for life.<br />
  </div>
  <div>Please accept my contribution to CBI of $&nbsp;
  <select name="hh-amount">
	 <option value=0>-- Click Here --</option>
	 <?php
	 $price_points = [ 18, 36, 54, 72, 108, 144, 180, 270, 360, 540, 720, 1080 ];
	 foreach( $price_points as $val ) {
		printf( "<option value=%d>%d</option>", $val, $val );
	 }
	 ?>
  </select>
  </div>
  <div><br />
  </div>
  <div><strong>Payment method</strong> (select one)</div>
  <div><input name="hh-payment" type="radio" value="credit" />&nbsp;Charge my credit card on file</div>
  <div><input name="hh-payment" type="radio" value="check" />&nbsp;I will send a check to the office</div>
  <div><input name="hh-payment" type="radio" value="call" />&nbsp;Contact me about payment</div>
  <br />
  <input name="" value="Submit" type="submit" onClick="addAction('honor');"/><input name="" type="reset" />  </div>
    
    <br />
    <div id="bottom_buttons">
      </div>
    <!-- end .content -->
  </div>
  <script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var spryradio1 = new Spry.Widget.ValidationRadio("spryradio1");
  </script>
  <!-- InstanceEndEditable -->
  <!-- end .content --></div>
  <div id="footer">
    <p><img src="assets/CBI_footer.png" alt="Footer" width="971" height="194" usemap="#Map" border="0" />
      <map name="Map" id="Map">
        <area shape="circle" coords="381,80,17" href="http://www.facebook.com/cbi18" alt="Facebook" />
        <area shape="circle" coords="428,80,17" href="http://twitter.com/cbi18" alt="Twitter" />
        <area shape="circle" coords="470,79,16" href="http://www.flickr.com/photos/56463940@N05" alt="Flikr" />
        <area shape="circle" coords="517,78,16" href="http://www.youtube.com/cbi18video" alt="YouTube" />
        <area shape="rect" coords="41,57,209,86" href="http://eepurl.com/clLK" alt="Join the Email List" />
      </map>
    </p>
    <!-- end .footer --></div>
    </form>
    <!-- .end .page --></div>
    <!-- .end FloatPage --></div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
<!-- InstanceEnd --></html>
