<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI Financial Pledge</title>
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
  <input type=hidden name=from id=from value=financial />
  <h2>5774 High Holy Day Appeal </h2>
  <?php
DoQuery( "select sum(amount), count(pledgeType) from pledges where pledgeType = $PledgeTypeFinancial" );
list( $total,$num ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
DoQuery( "select amount from pledges where pledgeType = $PledgeTypeFinGoal" );
list( $goal ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
	
echo "<hr>";
echo "<div class=to_date>";
echo "<table><tr><td>";
echo "<p class=num>$num</p>";
echo "<p>Donors to date</p>";
echo "</td><td>";
echo "<p class=num>$ " . number_format( $total, 0 ) . "</p>";
echo "<p>Pledged of $ " . number_format( $goal, 0 ) . " goal</p>";
echo "</td></tr></table>";
echo "</div>";
echo "<hr>";
  ?>
  <p>I pledge the following amount:</p>
  <div>
  <?php
  DoQuery( "select multiplier from financial order by multiplier asc" );
  $num_per_row = $GLOBALS['mysql_numrows'] / 3;
  $i = 0;
  while( list( $mult ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
	  if( $i % $num_per_row == 0 ) {
		  echo "<table class=pledge>\n";
	  }
	  $amount = $mult * 18;
	  printf( "<tr><td><input type=radio name=Pledges value=%d onClick=\"makeActive('pledges');\">\$%s (%d x Chai)</td></tr>\n",
	  	$amount, number_format($amount,0), $mult );
	  $i++;
	  if( $i % $num_per_row == 0 ) {
		  echo "</table>\n";
	  }
  }
  ?>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <div>
    <table class=pledge_other>
    <tr>
        <td><input type="radio" name="Pledges" value=other onClick="makeActive('pledges');" />Other: <input type=text name=PledgeOther id=pledgeOther onkeyup="makeActive('pledges');" /></td>
      </tr>
      </table>
	</div>
    <div id="bottom_buttons">
      <input type=button class=buttonNotOk id=pledgeNow onclick="setAmount();addAction('pledge_now');" value="Pledge Now" disabled/>
    </div>
      </form>
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
