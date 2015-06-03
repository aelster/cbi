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
	 
  <div class="fltlft" id="leftDirectory">
	 <?php
	 DoQuery( "select label, date from dates" );
	 while( list( $label, $ts ) = mysql_fetch_array( $mysql_result ) ) {
		$myDate[$label] = $ts;
	 }
	 $now = time();
	 
	 if( $now >= $myDate['open'] || $gGala ) {
		DoQuery( "select * from items where status != $gStatusHidden and itemIsLive = 0 and itemPackage = 0" );
		$avail = $GLOBALS['mysql_numrows'];
		$jsx = array();
		$jsx[] = "setValue('area','bid')";
		$jsx[] = sprintf( "setValue('id','%d')", -2);
		$jsx[] = "addAction('')";
		$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		echo "<a $js>Silent Auction ($avail)</a><br>";
		
		DoQuery( "select * from items where itemIsLive = 1 and status = 0 and itemPackage = 0" );
		if( $mysql_numrows ) {
		  $jsx = array();
		  $jsx[] = "setValue('area','bid')";
		  $jsx[] = sprintf( "setValue('id','%d')", -3);
		  $jsx[] = "addAction('')";
		  $js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		  echo "<a $js>Live Auction ($mysql_numrows)</a><br>";
		}
		
		DoQuery( "select * from packages" );
		if( $mysql_numrows ) {
		  $jsx = array();
		  $jsx[] = "setValue('area','bid')";
		  $jsx[] = sprintf( "setValue('id','%d')", -8);
		  $jsx[] = "addAction('')";
		  $js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		  echo "<a $js>Packages ($mysql_numrows)</a><br>";
		}
		
		echo "<br>";
		
		foreach( $gCategories as $id => $label ) {
		  DoQuery( "select * from items where itemPackage = 0 and status != $gStatusHidden and itemCategory = $id" );
		  if( $mysql_numrows > 0) {
			  $jsx = array();
			  $jsx[] = "setValue('area','bid')";
			  $jsx[] = sprintf( "setValue('id','%d')", $id);
			  $jsx[] = "addAction('')";
			  $js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
  #		  echo "<li>$label (<a href=\"$gSourceCode\" $js>$avail</a>)</li>";
			 echo "<a $js>$label ($mysql_numrows)</a><br>";
		  }
		}
	 }
#	 echo "</li>";
	 $cid = isset( $_POST['id'] ) ? $_POST['id'] : 0;
	 ?>
  </div>
  
  <div class="fltrt" id="rightContents">
<?php
  if( $gDebug ) { DumpPostVars(); }
	 if( $now < $myDate['open'] ) {
		$open = $gGala;
		$dstr = sprintf( "%s at %s", date( "l, F jS, Y", $myDate['open'] ), date( "g:i A", $myDate['open'] ) );
	 } elseif( $now < $myDate['close'] ) {
		$open = 1;
	   $dstr = sprintf( "%s at %s", date( "l, F jS, Y", $myDate['close'] ), date( "g:i A", $myDate['close'] ) );
	 } else {
		$open = $gGala;
		$dstr = sprintf( "%s at %s", date( "l, F jS, Y", $myDate['close'] ), date( "g:i A", $myDate['close'] ) );
	 }
	 
	 if( $gPreSelected > 0 ) {
		DoQuery( "select itemCategory from items where id = $gPreSelected" );
		if( $mysql_numrows == 0 ) {
		  $cid = -1;
		} else {
		  list( $cid ) = mysql_fetch_array( $mysql_result );
		}
	 } else {
		$cid = isset( $_POST['id'] ) ? $_POST['id'] : -1;
	 }
	 if( $cid == -1 ) {
			$yyyy = date('Y', $gAuctionYear );

?>
    <p>Welcome to the <?php echo $yyyy ?> Congregation B'nai Israel Annual Gala Auction Site</p>
	 <?php
		if( $now < $myDate['open'] ) {
?>
<p><strong>THE ONLINE AUCTION IS COMING SOON! </strong></p>
<p> The online auction will be open <?php echo $dstr ?>.
<p>We appreciate all of your support of CBI's programs, of our warm community, and of our Annual Gala honoring our immediate past president Beth Elster! </p>
<?php
		} elseif( $now < $myDate['close'] ) {
?>
<p>
<strong>THE ONLINE AUCTION IS NOW OPEN!</strong><br>
<br>
The online auction will remain open through <?php echo $dstr ?>.<br>
<br>
Items that have not reached the "BUY NOW" amount by that time will be put
on display at CBI on Sunday, March 29, with the highest online bid received as the bidding starting point.
<br>
<br>We appreciate all of your support of CBI's programs, of our warm community, and of our Annual Gala honoring our immediate past president Beth Elster! </p>
<?php
		} else {
?>
<p><strong>THE ONLINE AUCTION IS NOW CLOSED</strong></p>
<p>Items that have not been sold will be put
on display at CBI on Sunday, March 29, with the highest online bid received as the bidding starting point.
Bids will continue to be accepted from 11:30 AM - 1:00 PM, and at the gala that evening from 5:00 - 5:45 PM.
Auction items range from vacation getaways to donated items guaranteed to delight and surprise.
Many are exclusive offers from our members, sharing their special talents.  </p>
<p><strong>A VERY SPECIAL THANK YOU TO OUR DONORS AND BIDDERS! </strong></p>
<p>We appreciate all of your support of CBI's programs, of our warm community, and of our Annual Gala honoring our immediate past president Beth Elster! </p>
<?php
		}
	 } elseif( $cid == -4 ) {
?>
	 <p>Thank you for supporting Congregation B'nai Israel.</p>
	 <p>You should be receiving an email confirming your latest bid.</p>
	 <p>We also encourage you to bid on additional items.</p>
<?php
	 } elseif( $cid == -5 ) {
?>
	 <p>We're terribly sorry but somebody just completed a BuyNow for this item and it is no longer available.</p>
	 <p>We also encourage you to bid on additional items.</p>
<?php
	 } elseif( $cid == -6 ) {
?>
	 <p>We're terribly sorry but somebody beat you to that price.  Please try again.</p>
	 <p>We also encourage you to bid on additional items.</p>
<?php
	 } elseif( $cid == -7 ) {
?>
	 <p>We're terribly sorry but somebody already bid a higher price.  Please try again.</p>
	 <p>We also encourage you to bid on additional items.</p>
<?php
	 } else {
		if( $cid == -2 ) {
		  DoQuery( "select * from items where status != $gStatusHidden and itemIsLive = 0 and itemPackage = 0 order by itemTitle asc" );
		  $title = "Silent Auction Items";
		} elseif( $cid == -3 ) {
		  DoQuery( "select * from items where status != $gStatusHidden and itemIsLive = 1 and itemPackage = 0 order by itemTitle asc" );
		  $title = "Live Auction Items";
		} elseif( $cid == -8 ) {
		  $title = "All Packages";
		} else {
		  DoQuery( "select * from items where status != $gStatusHidden and itemCategory = $cid order by itemTitle asc" );
		  $title = $gCategories[$cid];
		}
		
		if( $cid == -8 ) {
		  echo "<h3>$title</h3>";
		  echo "<hr>";
		  
		  $pids = array_keys( $gPackages );
		  foreach( $pids as $pid ) {
			 if( $pid == 0 ) continue;
			 
			 echo "<table class=items>";
		  
			 DoQuery( "select sum(itemValue) from items where itemPackage = $pid" );
			 $row = mysql_fetch_array( $mysql_result );
			 echo "<tr>";
			 echo "<td colspan=2><b>$pid: " . $gPackages[$pid] . sprintf( ", Total Value: \$ %s", number_format( $row[0], 2 ) ). "</td>";
			 echo "</tr>";
			 
			 DoQuery( "select itemTitle, itemDesc from items where itemPackage = $pid" );
			 while( list( $it, $id ) = mysql_fetch_array( $mysql_result ) ) {
				echo "<tr>";
				echo "<td>&nbsp;&nbsp;&bull;&nbsp;$it - $id</td>";
				echo "</tr>";
			 }
		  echo "</table>";
		  echo "<hr>";
		  }
		  
		} else {
		  $cols = ( $open ) ? 4 : 2;
		
		echo "<table class=items>";
		echo "<thead>";
		echo "<tr>";
		echo "<th colspan=$cols class=top><h3>$title</h3></th>";
		echo "</tr>";
		

		echo "<tr>";
		echo "<th class=bottom>&nbsp;</th>";
		if( $open ) {
		  echo "<th class=bottom>Current Bid</th>";
		  echo "<th class=bottom>New Bid</th>";
		  echo "<th class=bottomr>Value</th>";
		} else {
		  echo "<th class=bottom>Status</th>";
		}
		echo "</tr>";
		echo "</thead>";

		$outer = $mysql_result;
		while( $row = mysql_fetch_assoc( $outer ) ) {
		  $iid = $row['id'];
		  $sold = ( $row['status'] == $gStatusClosed );

		  echo "<tr id=\"itm_$iid\">";
		  
		  // Col #1, Title/Description
		  echo "<td>";
		  printf( "<b>%s</b><br>%s", $row['itemTitle'], $row['itemDesc']);
		  echo "</td>";
		  
		  // Col #2, Sold or Current Bid/Buy Now
		  echo "<td class=c>";
		  if( $row['itemIsLive'] == 1 ) {
			 echo "Live";
		  } elseif( $open ) {
			 if( $sold ) {
				echo "<img src=\"assets/icon_sold.png\" alt=Sold />";
			 } else {
				DoQuery( "select max(bid) from bids where itemId = $iid");
				list( $bid) = mysql_fetch_array( $mysql_result);
				printf( "\$ %s", number_format( $bid, 2 ) );
				
				if( $row['buyNowPrice'] > 0 ) {
				  echo "<br>";
				  $jsx = array();
				  $jsx[] = "setValue('area','buynow')";
				  $jsx[] = sprintf( "setValue('id','%d')", $iid);
				  $jsx[] = "addAction('bid')";
				  $js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
				  printf( "<input type=button $js value=\"Buy Now:\$ %s\">", number_format( $row['buyNowPrice'],2) );
				}
			 }
		  } else {
			 if( $sold ) {
				echo "<img src=\"assets/icon_sold.png\" alt=Sold />";
			 } else {
				echo "&nbsp;";
			 }
		  }
		  echo "</td>";
		  
		  // Col #3, New bid/Submit
		  
		  if( $open ) {
			 echo "<td class=c>";
			 if( $sold || ( $row['itemIsLive'] == 1 ) ) {
				echo "&nbsp;";
			 } else {
				$ll = max( $row['bidOpen'], $bid + $row['bidIncrement'] );
				$ul = $ll + 9*$row['bidIncrement'];
				if( $row['buyNowPrice'] > $row['bidOpen'] ) {
					$ul = min( $ul, $row['buyNowPrice'] );
				}  
				$tag = MakeTag('bid_'.$iid);
				echo "<select $tag>";
				if( $row['bidIncrement'] > 0 ) {
				  $sel = "selected";
				  for( $val=$ll; $val<$ul; $val+= $row['bidIncrement'] ) {
					 echo "<option value=$val $sel>\$ " . number_format($val,2) . "</option>";
					 $sel = "";
				  }
				}
				echo "</select>";
				echo "<br>";
				$jsx = array();
				$jsx[] = "setValue('area','bid')";
				$jsx[] = sprintf( "setValue('id','%d')", $iid);
				$jsx[] = "addAction('bid')";
				$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
				echo "<input type=button $js value=\"Submit\">";
			 }
			 echo "</td>";
		  }

		  // Col #4, Value
		  if( $open ) {
			 echo "<td class=price>";
			 if( $sold ) {
				echo "&nbsp;";
			 } else {
				if( $row['itemValue'] < 0 ) {
				  printf( "%s", "Priceless" );
				} else {
				  printf( "\$ %s", number_format( $row['itemValue'], 2 ) );
				}
			 }
			 echo "</td>";
		  }
		  echo "</tr>\n";
		  
		  echo "<tr>";
		  echo "<td class=bar></td>";
		  echo "<td class=bar2></td>";
		  if( $open ) {
			 echo "<td class=bar2></td>";
			 echo "<td class=bar2></td>";
		  }
		  echo "</tr>\n";
		}
		echo "</table>";
		}
	 }
	 if( $gPreSelected > 0 ) {
		echo "<script type=\"text/javascript\">myScroll('$gPreSelected');</script>";
	 }
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
    <!-- end .footer -->
	 </div>
    </form>
    <!-- .end .page --></div>
    <!-- .end FloatPage --></div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
<!-- InstanceEnd --></html>
