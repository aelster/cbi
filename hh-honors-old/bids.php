<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI Auction Home</title>
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
<?php
LocalInit();
AddForm();
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
	 <?php
	 if( $gDebug ) { DumpPostVars(); }
	 $iid = $_POST['id'];
	 DoQuery( "select * from items where id = $iid" );
	 $row = mysql_fetch_assoc( $mysql_result );
	 
	 $area = $_POST['area'];
	 if( $area == "bid" ) {
		$price = $_POST['bid_' . $iid ];
		$notify = 1;
	 } else {
		$price = $row['buyNowPrice'];
		$notify = 0;
	 }

	 ?>
	 <h3>Thank you for bidding on the following item in the 2014 CBI Gala Auction</h3>
	 <?php
	 $tag = MakeTag('bid_amount');
	 echo "<input type=hidden $tag>";
	 
	 echo "<table>";
	 echo "<tr><th>Item</th><td>" . $row['itemTitle'] . "</td></tr>";
	 echo "<tr><th>Desc</th><td>" . $row['itemDesc'] . "</td></tr>";
	 echo "<tr><th>Bid</th><td>\$ " . number_format( $price, 2 ) . "</td></tr>";
	 echo "</table>";

	 echo "<br><br>";
	 
	 $js = "onchange=\"makeActive('confirm');\" onkeyup=\"makeActive('confirm');\"";
	 echo "<b>Please enter the following information to confirm your bid:</b>";
  	 echo "<table>";
		echo "<tr>";
		  $tag = MakeTag( 'bidder_email');
		  echo "<th>* E-mail</th>";
		  echo "<td><input type=text $tag size=80 $js></td>";
		echo "</tr>\n";
		echo "<tr>";
		  $tag = MakeTag( 'bidder_first');
		  echo "<th>* First Name</th>";
		  echo "<td><input type=text $tag size=80 $js></td>";
		echo "</tr>";
		echo "<tr>";
		  $tag = MakeTag( 'bidder_last');
		  echo "<th>* Last Name</th>";
		  echo "<td><input type=text $tag size=80 $js></td>";
		echo "</tr>";
		if( $notify ) {
		  echo "<tr>";
			$tag = MakeTag('bidder_notify');
		  echo "<td colspan=2 class=c>If this box <input type=checkbox $tag value=1 checked> is checked, you will be notified by e-mail if outbid.</td>";
		  echo "</tr>";
		} else {
		  echo "<tr>";
		  $tag = MakeTag('bidder_phone');
		  echo "<th>* Phone</th>";
		  echo "<td><input type=text $tag size=80 $js></td>";
		  echo "</tr>";
		  
		}
		
		echo "<tr>";
		echo "<td colspan=2 class=c>";
		$jsx = array();
		$jsx[] = "setValue('area','bid')";
		$jsx[] = sprintf( "setValue('id','%d')", $row['itemCategory'] );
		$jsx[] = "addAction('')";
		$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		echo "<input type=button $js value=Cancel>";
		
		$jsx = array();
		$jsx[] = "setValue('area','confirm')";
		$jsx[] = sprintf( "setValue('id','%d')", $iid);
		$jsx[] = sprintf( "setValue('bid_amount','%s')", $price);
		$jsx[] = "addAction('confirm')";
		$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		echo "<input type=button id=confirm $js value=Confirm disabled>";
		echo "</td>";
		echo "</tr>";
	 echo "</table>";
	 echo "<p>* Required fields</p>";
	 ?>
  </div>
 
      <br />
    <div id="bottom_buttons">
<?php
if( $site_enabled ) {
?>
<?php
} else {
  echo "The pledge site is currently disabled.  Please check back in a few minutes";  
}
?>
    </div>
  <!-- end .content -->
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
