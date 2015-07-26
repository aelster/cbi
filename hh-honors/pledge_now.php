<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI Pledge Info Page</title>
<!-- InstanceEndEditable -->
<link href="oneColFixCtrHdr.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="styles.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<script type="text/javascript" src="hhPledges.js"></script>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body onload="firstName();">
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
  <form action="" method="post" id="form1">
  <input type=hidden name=action id=action />
  <input type=hidden name=amount id=amount />
  <input type=hidden name=fields id=fields />
  <div id="MenuBar">
      <ul id="MenuBar1" class="MenuBarHorizontal">
          <li><a href="http://cbi18.org/">CBI Home</a></li>
      </ul>
  <!-- end .MenuBar --></div>
  <div id="content">
  <!-- InstanceBeginEditable name="Content" -->
  <?php
/*  	    <tr>
		  <td>
		  	<input type=radio disabled>
			<input type="image" src="assets/pp_secure_213wx37h.gif" border="0" disabled
                 name="paypal" id="paypal" alt="PayPal - The safer, easier way to pay online!" onClick="paypal();addAction('paypal');">
				 ** Not yet available **
	      </td>
		</tr>
*/
  if( $gFrom == "financial" ) {
	  $tag = "Financial Pledge";
	  $radio = 1;
  } else if( $gFrom == "spiritual" ) {
	  $tag = "Spiritual Pledge";
	  $radio = 0;
  }
  printf( "<input type=hidden name=from id=from value=\"%s\">", $gFrom );
  foreach( array( 'firstName', 'lastName', 'phone', 'email' ) as $fld ) {
	  printf( "<input type=hidden name=\"%s\">\n", $fld );
  }
  echo "<script type='text/javascript'>\n";
  printf( "var radio_required = %d;\n", $radio );
  printf( "var pledge_amount = '%.2f';\n", $_POST['amount'] );
  printf( "var gFrom = '%s';\n", $gFrom );
  printf( "var pledgeIds = new Array();\n" );
  printf( "var pledgeOther = '';\n" );
  echo "</script>\n";
  ?>
  <div class="content">
  <h2>5774 High Holy Day Appeal<br /><?php echo $tag ?></h2>
  <?php
  if( $gFrom == "financial" ) {
	  $amount = $_POST['amount'];
	  printf( "<p>Thank you for your generous pledge of \$ %s.</p>", number_format($amount, 2 ) );
  } else if( $gFrom == 'spiritual' ) {
	  echo "<div class=spirit_detail>";
	  printf( "Thank you for your pledge to:<br>" );
	  $items = preg_split( '/\|/', $_POST['fields'] );
	  echo "<ul>\n";
	  foreach( $items as $item ) {
		  if( preg_match( '/^spirit_/', $item ) ) {
			  list( $na, $id ) = preg_split( '/_/', $item );
			  $desc = $gSpiritIDtoDesc[$id];
			  printf( "<script type='text/javascript'>pledgeIds.push('id_' + %d);</script>\n", $id );
		  } else {
			  $desc = CleanString( $_POST['other_desc'] );
			  if( empty( $desc ) ) continue;
			  printf( "<script type='text/javascript'>pledgeOther = '%s';</script>\n", $desc );
		  }
		  echo "<li>$desc</li>\n";
	  }
	  echo "</ul>";
	  echo "</div><br>";
  }
  
  $fn = isset( $_SESSION['firstName'] ) ? $_SESSION['firstName'] : "";
  $ln = isset( $_SESSION['lastName'] ) ? $_SESSION['lastName'] : "";
  $pn = isset( $_SESSION['phone'] ) ? $_SESSION['phone'] : "";
  $em = isset( $_SESSION['email'] ) ? $_SESSION['email'] : "";

  echo <<<END
      <table>
        <tr>
          <td>*&nbsp;First Name</td>
          <td><input type="text" name="paynow" id="firstName" value="$fn" onchange="makeActive('paynow');" onkeyup="makeActive('paynow');" size=60 /></td>
        </tr>
        <tr>
          <td width="130">*&nbsp;Last Name</td>
          <td width="442"><input type="text" name="paynow" id="lastName" value="$ln" onchange="makeActive('paynow');" onkeyup="makeActive('paynow');" size=60 /></td>
        </tr>
        <tr>
          <td>*&nbsp;Phone #</td>
          <td><input type="text" name="paynow" id="phone" value="$pn" onchange="makeActive('paynow');" onkeyup="makeActive('paynow');" size=60 /></td>
        </tr>
        <tr>
          <td>*&nbsp;E-mail Address</td>
          <td><input type="text" name="paynow" id="email" value="$em" onchange="makeActive('paynow');" onkeyup="makeActive('paynow');" size=60 /></td>
        </tr>
END;
if( $gFrom == 'financial' ) {
		  echo <<<END
  <tr>
	<td>*&nbsp;Payment (select one)</td>
	<td>
      <table width="600">
        <tr>
          <td><label>
            <input type="radio" name="paynow" value=$PaymentCredit onClick="makeActive('paynow');" />
            Charge my credit card on file</label></td>
        </tr>
        <tr>
          <td><label>
            <input type="radio" name="paynow" value=$PaymentCheck onClick="makeActive('paynow');" />
            I will send a check to the office</label></td>
        </tr>
        <tr>
          <td><label>
            <input type="radio" name="paynow" value=$PaymentCall onClick="makeActive('paynow');" />
            Contact me about payment</label></td>
        </tr>
      </table>
	</td>
  </tr>
END;
}
?>
	</table>
      <h2>
      <input type=button onclick="payNow();addAction('paynow');" class=pledgeBtn id=paynow value=Submit disabled/>
      </h2>
      <p class="left"><em class=left>*</em>&nbsp;Required fields</p>
  </div>

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
