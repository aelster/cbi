<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CBI High Holy Day Pledge Home</title>
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
          <li><a onclick="addAction('pledge');">Pledge Home</a></li>
          <?php if( $site_enabled ) { ?>
          <li><a onclick="addAction('financial');">Financial Pledge</a></li>
          <li><a onclick="addAction('spiritual');">Spiritual Pledge</a></li>
          <?php } ?>
      </ul>
  <!-- end .MenuBar --></div>
  <div id="content">
  <!-- InstanceBeginEditable name="Content" -->
  <div class="content">
<p>Welcome to the CBI annual High Holy Day Appeal web page. Due to the success of the online appeal last year I was able to present the “State of the Congregation”, but did not have to ask for donations during the Kol Nidre service.  If we meet our goal again this year we will not have to interrupt the sanctity of our service to ask for money. Please consider making both financial and spiritual pledges as we renew ourselves in the New Year.</p>
<p>Please click the arrow below to view the 3-minute video 5775 High Holy Day Appeal.</p>
<p>My wish is that each of us will contribute financially as generously as we can, and spiritually in a way that is personally meaningful. I am hoping to stand before our congregation during the High Holy Days and be able to say that 100% of our incredible community contributed and participated. Every pledge matters and makes a difference. Please contact the CBI office with any questions. Thank you for your support.  I wish you all a year of health, happiness, prosperity and growth.</p>
	<p>
      L'Shana Tova,<br />
      Beth Elster<br />
      CBI President
      </p>  
      <div id="video">
	<embed src="assets/Pledge-Video-5775.mp4" width="320" class="fltrt" id=video height="216" autostart="false"></embed>
      </div>
      <br />
    <div id="bottom_buttons">
<?php
if( $site_enabled ) {
?>
  <input class=buttonOk type=button onClick="addAction('financial');" value="Make a Financial Pledge" />
  <input class=buttonOk type=button onClick="addAction('spiritual');" value="Make a Spiritual Pledge" />
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
