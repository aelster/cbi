<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI Spiritual Pledge</title>
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
  <input type=hidden name=from id=from value=spiritual />
  <h2>5774 High Holy Day Appeal </h2>
  <?php
DoQuery( "select pledgeIds, pledgeOther from pledges where pledgeType = $PledgeTypeSpiritual" );
$num = $GLOBALS['mysql_numrows'];
$mitzvot = 0;
while( list( $ids, $other ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
	if( ! empty( $ids ) ) {
		$tmp = preg_split( '/,/', $ids );
		$mitzvot += count($tmp);
	}
	if( ! empty( $other ) ) {
		$mitzvot++;
	}
}
	
echo "<hr>";
echo "<div class=to_date>";
echo "<table><tr><td>";
echo "<p class=num>$num</p>";
echo "<p>Individuals</p>";
echo "</td><td>";
echo "<p class=num>$mitzvot</p>";
echo "<p>Pledged mitzvot to date<br>See below for (counts)</p>";
echo "</td></tr></table>";
echo "</div>";
echo "<hr>";
  ?>
  <p>I pledge that, during the coming year, I will fulfill the mitzvah/mitzvot which I am choosing below:</p>
    <div class=spirit>
        <div class=spiritLeft>
        <?php
echo "<table class=spiritTable>";
echo "<tr><th>Torah</th></tr>";
DoQuery( "select id, description from spiritual where spiritualType = $SpiritualTorah" );
while( list( $id, $desc ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
	$freq = empty( $gSpiritIDstats[$id] ) ? 0 : $gSpiritIDstats[$id];
	printf( "<tr><td><input type=checkbox name=spirit id=spirit_%d onClick=\"makeActive('spirit');\">(%d) %s</td></tr>",
	 $id, $freq, $desc );
}
echo "</table>";

echo "<table class=spiritTable>";
echo "<tr><th>Avodah</th></tr>";
DoQuery( "select id, description from spiritual where spiritualType = $SpiritualAvodah" );
while( list( $id, $desc ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
	$freq = empty( $gSpiritIDstats[$id] ) ? 0 : $gSpiritIDstats[$id];
	printf( "<tr><td><input type=checkbox name=spirit id=spirit_%d onClick=\"makeActive('spirit');\">(%d) %s</td></tr>",
	 $id, $freq, $desc );
}
echo "</table>";
			?>
      	<table class="spiritTable">
            <tr><th>Other</th></tr>
            <tr><td>
                <input type=checkbox name=spirit id=other onClick="clearSpiritOther();makeActive('spirit');"/>
<?php
$id = 0;
$freq = empty( $gSpiritIDstats[$id] ) ? 0 : $gSpiritIDstats[$id];
echo "(" . $freq . ")";
?>
        		<input type=text size=50 name=other_desc id=spiritOther onkeyup="makeActive('spirit');" value="Check box and enter description" />
			</td></tr>
		</table>
        </div> <!-- end spiritLeft -->
    	<div class=spiritRight>
<?php
echo "<table class=spiritTable>";
echo "<tr><th>Gemilut Chasadim</th></tr>";
DoQuery( "select id, description from spiritual where spiritualType = $SpiritualGemilut" );
while( list( $id, $desc ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
	$freq = empty( $gSpiritIDstats[$id] ) ? 0 : $gSpiritIDstats[$id];

	printf( "<tr><td><input type=checkbox name=spirit id=spirit_%d onClick=\"makeActive('spirit');\">(%d) %s</td></tr>",
	 $id, $freq, 		$desc );
}
echo "</table>";
?>
		</div> <!-- end spiritRight -->
    </div> <!-- end spirit -->
    <div id="bottom_buttons">
      <input type=button class=buttonNotOk id=spiritNow onclick="spiritFields();addAction('pledge_now');" value="Pledge Now" disabled />
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
