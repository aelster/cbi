<?php

function AddForm() {
	include( 'globals.php' );
	echo "<form name=fMain id=fMain method=post action=\"$gSourceCode\">";
	
	$hidden = array( 'action', 'area', 'fields', 'func', 'from', 'id' );
	foreach( $hidden as $var ) {
		$tag = MakeTag($var);
		echo "<input type=hidden $tag>";
	}
}

function BidAdd() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$item_id = $_POST['id'];
	$from = $_POST['from'];

	$tmp = preg_split( '/,/', $_POST['fields'] );  // This is what was touched
	$keys = array_unique( $tmp );
	
	$email = $_POST['bidder_email'];
	
	$qx = array();
	DoQuery( "select * from bidders where email = '$email'");
	if( $mysql_numrows ) {
		$row = mysql_fetch_assoc( $mysql_result );
		$bidder_id = $row['id'];
		$get_new_hash = empty( $row['hash'] );
		if( isset( $_POST['bidder_phone'] ) ) {
			$qx[] = sprintf( "phone = '%d'", preg_replace("/[^0-9]/", "", $_POST['bidder_phone'] ) );
			$query = sprintf( "update bidders set %s where id = $bidder_id", join(',',$qx) );
			DoQuery( $query );
		}
	} else {
		$get_new_hash = 1;
		$qx[] = sprintf( "first = '%s'", CleanString($_POST['bidder_first']) );
		$qx[] = sprintf( "last = '%s'", CleanString($_POST['bidder_last']) );
		$qx[] = sprintf( "email = '%s'", CleanString($_POST['bidder_email']) );
		if( isset( $_POST['bidder_phone'] ) ) {
			$qx[] = sprintf( "phone = '%d'", preg_replace("/[^0-9]/", "", $_POST['bidder_phone'] ) );
		}
		$query = sprintf( "insert into bidders set %s", join(',',$qx) );
		DoQuery( $query );
		$bidder_id = $mysql_last_id;
	}
	
	DoQuery( "start transaction" );
	while( $get_new_hash ) {
		$random_hash = substr(md5(uniqid(rand(), true)), 8, 8); // 6 characters long
		DoQuery( "select * from bidders where hash = '$random_hash'" );
		if( ! $mysql_numrows ) {
			DoQuery( "update bidders set hash = '$random_hash' where id = $bidder_id" );
			$get_new_hash = 0;
		}
	}
	DoQuery( "commit" );

	$bid_amount = $_POST['bid_amount'];
#
# *** Start of transaction
#
	DoQuery( "start transaction" );
	
	DoQuery( "select * from items where id = $item_id" );
	$item = mysql_fetch_assoc( $mysql_result );
	
	DoQuery( "select * from bids where itemId = $item_id order by bid desc limit 1" );
	$top_bid = mysql_fetch_assoc( $mysql_result );
	
	if( $gStatus[ $item['status'] ] == 'Closed' ) {  # Oops, we've missed this item altogether
		$_POST['id'] = -5;
		DoQuery( "rollback" );
		
	} elseif( $bid_amount == $top_bid['bid'] ) {  # Oh well, insufficient bid, try again
		$_POST['id'] = -6;
		DoQuery( "rollback" );
		
	} elseif( $bid_amount < $top_bid['bid'] ) {  # Oh well, insufficient bid, try again
		if( $top_bid['bid'] < $item['buyNowPrice'] ) {
			$_POST['id'] = -7;
		} else {
			$_POST['id'] = -5;
		}
		DoQuery( "rollback" );
		
	} else {  # We have a winner	
		if( $top_bid['notify'] ) {
			$type = ( $bid_amount == $item['buyNowPrice'] ) ? $gSendOldBought : $gSendOld;
			SendConfirmation( $top_bid['bidderId'], $item_id, $top_bid['id'], $type );
		}

		$qx = array();
		$qx[] = sprintf( "itemId = %d", $item_id );
		$qx[] = sprintf( "bidderId = %d", $bidder_id );
		$qx[] = sprintf( "bid = %s", $_POST['bid_amount'] );
		if( array_key_exists( 'bidder_notify', $_POST ) ) {
			$qx[] = sprintf( "notify = %d", $_POST['bidder_notify'] );
		}
		$query = sprintf( "insert into bids set %s", join( ',', $qx ) );
		DoQuery( $query );
		$bid_id = $mysql_last_id;
	
		if( $bid_amount == $item['buyNowPrice'] ) {
			SendConfirmation( $bidder_id, $item_id, $bid_id, $gSendBought );
			DoQuery( "update items set status = 1 where id = $item_id" );
		} else {
			SendConfirmation( $bidder_id, $item_id, $bid_id, $gSendTop );
		}
		DoQuery( "commit" );
		$_POST['id'] = -4;
	}
	
	if( $gTrace ) array_pop( $gFunction );
}


function CleanString ($data) {
	$data = trim($data);
	$data = addslashes($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data, ENT_QUOTES );
	return $data;
}

function DateUpdate() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$id = $_POST['id'];
	$from = $_POST['from'];

	$tmp = preg_split( '/,/', $_POST['fields'] );  // This is what was touched
	$keys = array_unique( $tmp );
	
	if( $func == "update" ) {
		$qx = array();
		foreach( $keys as $id ) {
			$qx[] = sprintf( "date = '%d'", strtotime( $_POST['date_'.$id] ) );
			if( $id == 0 ) {
				$query = sprintf( "insert into dates set %s", join( ',', $qx ) );
			} else {
				$query = sprintf( "update dates set %s where id = %d", join( ',', $qx ), $id );
			}
			DoQuery( $query );
		}

	} elseif( $func == "delete" ) {
		$query = sprintf( "delete from dates where id = %d", $keys[0] );
			DoQuery( $query );
	}
	
	if( $gTrace ) array_pop( $gFunction );
}

function DisplayBidders() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	echo "<input type=button onclick=\"setValue('area','bidders');setValue('func','Back');addAction('Main');\" value=Refresh>";
	
	echo "<ul>";
	echo "<li>Click on a blue header to sort, click again to reverse the sort</li>";
	echo "</ul>";
	
	echo "<table class=sortable>";
	
	echo "<tr>";
	echo "<th>Last</th>";
	echo "<th>First</th>";
	echo "<th>E-mail</th>";
	echo "<th>Phone</th>";
	echo "<th># bids</th>";
	echo "</tr>\n";

	DoQuery( "select * from bidders order by last asc, first asc" );
	$outer = $mysql_result;
	while( $row = mysql_fetch_assoc( $outer ) ) {
		echo "<tr>";
		printf( "<td>%s</td>", $row['last'] );
		printf( "<td>%s</td>", $row['first'] );
		printf( "<td>%s</td>", $row['email'] );
		printf( "<td>%s</td>", FormatPhone( $row['phone'] ) );
		$query = sprintf( "select * from bids where bidderId = %d", $row['id'] );
		DoQuery( $query );
		$jsx = array();
		$jsx[] = "setValue('area','showbids')";
		$jsx[] = sprintf( "setValue('id','%s')", $row['hash']);
		$jsx[] = "addAction('Main')";
		$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
		echo "<td class=c $js>";
		printf( "%d", $mysql_numrows );
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function DisplayCategories() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	$tag = MakeTag('update');
	$jsx = array();
	$jsx[] = "setValue('area','$area')";
	$jsx[] = "setValue('from','DisplayCategories')";
	$jsx[] = "setValue('func','update')";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=Update $tag $js>";
	
	echo "<table class=sortable>";
	
	echo "<tr>";
	echo "<th>#</th>";
	echo "<th>Label</th>";
	echo "<th># of Items</th>";
	echo "<th>Action</th>";
	echo "</tr>\n";

	$i=0;
	foreach( $gCategories as $id => $label ) {
		$i++;
		$tag = MakeTag("cat_$id");

		echo "<tr>";
		echo "<td>$i</td>";
		echo "<td><input type=text $tag value=\"$label\" onChange=\"addField('$id');toggleBgRed('update');\"></td>";
		DoQuery( "select count(id) from items where itemCategory = '$id'" );
		list($num) = mysql_fetch_array( $GLOBALS['mysql_result'] );
		echo "<td class=c>$num</td>";
		echo "<td class=c>";

		if( $num == 0 ) {
			$jsx = array();
			$jsx[] = "setValue('area','category')";
			$jsx[] = "setValue('from','DisplayCategories')";
			$jsx[] = "addField('$id')";
			$jsx[] = "setValue('func','delete')";
			$txt = sprintf( "Are you sure you want to delete the category $label?" );
			$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Del $tag $js>";
		} else {
			echo "&nbsp;";
		}
		
		echo "</td>";
		echo "</tr>";
	}
	
	$i++;
	$id=0;
	$label="";
	$tag = MakeTag("cat_$id");

	echo "<tr>";
	echo "<td>$i</td>";
	echo "<td><input type=text $tag value=\"$label\" onChange=\"toggleBgRed('add_$id');\"></td>";
	DoQuery( "select count(id) from items where itemCategory = '$id'" );
	list($num) = mysql_fetch_array( $GLOBALS['mysql_result'] );
	echo "<td class=c>$num</td>";
	echo "<td class=c>";

	$tag = MakeTag("add_$id");
	$jsx = array();
	$jsx[] = "setValue('area','category')";
	$jsx[] = "setValue('from','DisplayCategories')";
	$jsx[] = "addField('$id')";
	$jsx[] = "setValue('func','add')";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=Add $tag $js>";
	echo "</td>";
	echo "</tr>";
	
	echo "</table>";

	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function DisplayDates() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	$tag = MakeTag('update');
	$jsx = array();
	$jsx[] = "setValue('area','$area')";
	$jsx[] = "setValue('from','DisplayDates')";
	$jsx[] = "setValue('func','update')";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=Update $tag $js>";
	
	printf( "<h3>Current date: %s</h3>", date( "D M jS, Y, g:i A" ) );
	echo "<table class=sortable>";
	
	echo "<tr>";
	echo "<th>Label</th>";
	echo "<th>Weekday</th>";
	echo "<th>Date</th>";
	echo "</tr>\n";

	DoQuery( "select * from dates order by `date` asc" );
	while( $row = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		$id = $row['id'];
		$label = $row['label'];
		$date = $row['date'];
		if( ! ( $label == 'open' || $label == 'close' ) ) continue;
		
		echo "<tr>";
		$jsx = array();
		$jsx[] = "setValue('from','DisplayDates')";
		$jsx[] = "addField('$id')";
		$jsx[] = "toggleBgRed('update')";
		$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );

		printf( "<td>%s</td>", $label );

		printf( "<td class=c>%s</td>", date( "l", $date ) );

		$tag = MakeTag('date_'.$id);
		printf( "<td><input $tag $js size=30 value=\"%s\"></td>", date( "M jS, Y, g:i A", $date ) );

		echo "</tr>\n";
	}

	echo "</table>\n";
	echo "</div>\n";

	if( $gTrace ) array_pop( $gFunction );
}

function DisplayFinancial() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$ts = time() + $time_offset;
	$today = date('j-M-Y', $ts );
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	$ok_to_edit = UserManager( 'authorized', 'office' );
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";

	$jsx = array();
	$jsx[] = "setValue('area','financial')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Refresh>";
	
	$jsx = array();
	$jsx[] = "setValue('area','financial')";
	$jsx[] = "addAction('Download')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Download>";
	
	$jsx = array();
	$jsx[] = "setValue('area','spiritual')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Spiritual>";

	echo "<br>";
	echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

	DoQuery( "select sum(amount) from pledges where pledgeType = $PledgeTypeFinancial" );
	list( $total ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
	
	DoQuery( "select amount from pledges where pledgeType = $PledgeTypeFinGoal" );
	list( $goal ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
#	DoQuery( "select * from pledges where pledgeType = $PledgeTypeFinancial order by amount desc, lastName asc" );
	DoQuery( "select * from pledges where pledgeType = $PledgeTypeFinancial order by timestamp desc" );
	$num_pledges = $GLOBALS['mysql_numrows'];
	echo "<ul>";
	echo "<li>The columns are sortable by clicking on their header</li>";
	$x = $total * 100.0 / $goal;
	printf( "<li>%d pledges: \$ %s ( %d %% of \$ %s goal)</li>",
			 $num_pledges, number_format( $total ), intval($x), number_format( $goal ) );
	echo "<li><span class=today>Highlighted pledges were made today ($today)</span></li>";
	echo "</ul>";
	echo "<div class=CommonV2>";
	echo "<table class=sortable>";
	echo "<tr>";
	echo "  <th>#</th>";
	echo "  <th class=\"sorttable_numeric\">Amount</th>";
	echo "  <th>Donor</th>";
	echo "  <th>Phone</th>";
	echo "  <th>Method</th>";
	echo "  <th>Date/Time</th>";
	if( $ok_to_edit ) {
		echo "<th>Action</th>";
	}
	echo "</tr>";
	
	$methods = array( $PaymentCredit => 'Credit', $PaymentCheck => 'Check', $PaymentCall => 'Call' );
	
	$lf = "\n";
	$i = 0;
	while( $rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		foreach( $rec as $key => $val ) {
			$$key = $val;
		}
		$i++;
		$ts = strtotime( $timestamp ) + $time_offset;
		$dmy = date('j-M-Y',$ts);
		$hl = ( $today == $dmy ) ? "class=today" : "";
		echo "<tr>$lf";
		printf( "<td $hl>%d</td>$lf", $i );
		printf( "<td $hl style=\"text-align:right;\">\$ %s</td>$lf", number_format( $amount, 2 ) );
		printf( "<td $hl>%s %s</td>$lf", $lastName, $firstName );
		printf( "<td $hl>%s</td>$lf", FormatPhone( $phone) );
		printf( "<td $hl class=c>%s</td>$lf", $methods[ $paymentMethod ] );
#		printf( "<td $hl>%s</td>$lf", $tdate->format( 'j-M-Y h:i A') );
		printf( "<td sorttable_customkey=$ts $hl>%s</td>$lf", date( 'j-M-Y h:i A', $ts ) );
		if( $ok_to_edit ) {
			echo "<td $hl>$lf";
			
			$jsx = array();
			$jsx[] = "setValue('area','$area')";
			$jsx[] = sprintf( "setValue('id','%d')", $id);
			$jsx[] = "addAction('Edit')";
			$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Edit $js>$lf";
			
			$jsx = array();
			$jsx[] = "setValue('area','$area')";
			$jsx[] = "setValue('from','DisplayFinancial')";
			$jsx[] = "setValue('func','delete')";
			$jsx[] = sprintf( "setValue('id','%d')", $id);
			$txt = sprintf( "Are you sure you want to delete %s %s's donation for \$ %s?",
								$firstName, $lastName, number_format($amount,2));
			$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
			$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Delete $js>$lf";
			
			$jsx = array();
			$jsx[] = "setValue('area','$area')";
			$jsx[] = "setValue('from','DisplayFinancial')";
			$jsx[] = "setValue('func','mail')";
			$jsx[] = sprintf( "setValue('id','%d')", $id);
			$txt = sprintf( "Are you sure you want to resend the confirmation for  %s %s's donation of \$ %s\\nmade on %s?",
								$firstName, $lastName, number_format($amount,2), date('j-M-Y h:i A', $ts) );
			$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
			$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Mail $js>$lf";
			
			echo "</td>$lf";
		}
		echo "</tr>$lf";
	}
	echo "</table>$lf";
	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function DisplayItems() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\"><br>";
	echo "<input type=button value=Refresh onclick=\"setValue('area', 'items');addAction('Main');\">";

	$jsx = array();
	$jsx[] = "setValue('area','item')";
	$jsx[] = "setValue('id',0)";
	$jsx[] = "addAction('Edit')";
	$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=New $js>";
	
	$jsx = array();
	$jsx[] = "setValue('area','items')";
	$jsx[] = "addAction('Download')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Download>";

	DoQuery( "select count(id) from items" );
	list( $num ) = mysql_fetch_array( $GLOBALS['mysql_result'] );
	echo "<h2>There are $num items in the database</h2>";
	
	echo "<ul>";
	echo "<li>Click on a blue header to sort, click again to reverse the sort</li>";
	echo "</ul>";
	
	echo "<table class=sortable>";
	
	echo "<tr>";
	echo "<th>Id</th>";
	echo "<th>Auction</th>";
	echo "<th>Status</th>";
	echo "<th>Category</th>";
	echo "<th>Type</th>";
	echo "<th>Title</th>";
	echo "<th>Description</th>";
	echo "<th>Misc</th>";
	echo "</tr>";

	$i=0;
	foreach( $gCategories as $cid => $label ) {
		DoQuery( "select * from items where itemCategory = '$cid' order by `itemTitle` asc" );
		$outer = $GLOBALS['mysql_result'];
		while( $row = mysql_fetch_assoc( $outer ) ) {
			$i++;
			$iid = $row['id'];
			echo "<tr>";
			echo "<td class=c>$iid</td>";
			$val = ( $row['itemAuction'] == 0 ) ? "Silent" : "Live";
			echo "<td class=c>$val</td>";
			printf( "<td class=c>%s</td>", $gStatus[ $row['status'] ] );
			echo "<td>" . $gCategories[$cid] . "</td>";
			echo "<td>" . $row['itemType'] . "</td>";
			echo "<td>" . $row['itemTitle'] . "</td>";
			echo "<td class=desc>" . $row['itemDesc'] . "</td>";
	
			echo "<td class=c>";
			$jsx = array();
			$jsx[] = "setValue('area','item')";
			$jsx[] = sprintf( "setValue('id','%d')", $iid);
			$jsx[] = "addAction('Edit')";
			$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Edit $js>";	

			$jsx = array();
			$jsx[] = "setValue('area','category')";
			$jsx[] = "setValue('from','DisplayItems')";
			$jsx[] = "setValue('id','$iid')";
			$jsx[] = "setValue('func','delete')";
			$txt = sprintf( "Are you sure you want to delete this item?" );
			$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button value=Del $js>";

			echo "</td>";
			echo "</tr>\n";
		}
	}
	
	echo "</table>";
	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function DisplayMail() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";

	echo "<table>";	
	DoQuery( "select date from dates where label = 'mail'" );
	list( $val ) = mysql_fetch_array( $mysql_result );
	echo "<tr>";
	echo "<th>Mail Confirmations</th>";
	if( $val ) {
		echo "<td class=cok>Enabled</td>";
		$new = 0;
	} else {
		echo "<td class=cbad>Disabled</td>";
		$new = 1;
	}
	$tag = MakeTag('update');
	$jsx = array();
	$jsx[] = "setValue('area','$area')";
	$jsx[] = "setValue('from','DisplayMail')";
	$jsx[] = "setValue('func','update')";
	$jsx[] = "addField($new)";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<td><input type=button value=Toggle $tag $js></td>";
	
	echo "</tr>";

	echo "</table>";
	if( $gTrace ) array_pop( $gFunction );
}

function DisplayMain() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$area = $_POST['area'];
	
	if( $func == 'hash' ) {
		HashAdd();
		$func = 'xxx';
	}

	if( $area == 'bidders' ) {
		DisplayBidders();
	
	} elseif( $area == 'categories' ) {
		DisplayCategories();
	
	} elseif( $area == 'dates' ) {
		DisplayDates();
	
	} elseif( $area == 'financial' ) {
		DisplayFinancial();
		
	} elseif( $area == 'items' ) {
		DisplayItems();
	
	} elseif( $area == 'mail' ) {
		DisplayMail();
	
	} elseif( $area == 'showbids' ) {
		ShowBids();
		
	} elseif( $area == 'spiritual' ) {
		DisplaySpiritual();
		
	} elseif( $area == 'topbids' ) {
		DisplayTopBids();
		
	} elseif( $func == 'users' ) {
		UserManager( 'control' );
		
	} elseif( $func == 'privileges' ) {
		UserManager( 'privileges' );
		
	} elseif( $func == 'source' ) {
		SourceDisplay();
		
	} else {
		printf( "User: %s<br>", $GLOBALS['gUserName'] );
		if( UserManager( 'authorized', 'control' ) ) {
			echo "<div class=control>";
			echo "<h3>Control User Features</h3>";
			echo "<input type=button onclick=\"setValue('func','source');addAction('Main');\" value=\"Source\">";
			echo "<input type=button onclick=\"setValue('func','backup');addAction('Main');\" value=\"Backup\">";
			echo "<input type=button onclick=\"setValue('area','mail');addAction('Main');\" value=\"Mail\">";
			echo "<input type=button onclick=\"setValue('func','users');addAction('Main');\" value=Users>";
			echo "<input type=button onclick=\"setValue('func','privileges');addAction('Main');\" value=Privileges>";
			echo "<input type=button onclick=\"setValue('func','hash');addAction('Main');\" value=\"Add Hashes\">";

			$jsx = array();
			$jsx[] = "setValue('area','reset')";
			$jsx[] = "setValue('from','DisplayMain')";
			$jsx[] = "myConfirm('Are you sure you want to delete all bidders and bids?')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value='Reset Bids'>";

			echo "</div>";
		}
		
		if( UserManager( 'authorized', 'admin' ) ) {
			echo "<div class=admin>";
			echo "<h3>Admin User Features</h3>";

			$jsx = array();
			$jsx[] = "setValue('area','categories')";
			$jsx[] = "addAction('Main')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value=Categories>";

			$jsx = array();
			$jsx[] = "setValue('area','dates')";
			$jsx[] = "addAction('Main')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value=Dates>";

			$jsx = array();
			$jsx[] = "setValue('area','items')";
			$jsx[] = "addAction('Main')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value=Items>";

			echo "</div>";
			echo "<br>";
		}
		
		if( UserManager( 'authorized', 'office' ) ) {
			echo "<div class=office>";
			echo "<h3>User Features</h3>";
			
			echo "<input type=button onclick=\"setValue('func','Back');addAction('Main');\" value=Refresh>";

			$jsx = array();
			$jsx[] = "setValue('area','bidders')";
			$jsx[] = "addAction('Main')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value=Bidders>";

			$jsx = array();
			$jsx[] = "setValue('area','topbids')";
			$jsx[] = "addAction('Main')";
			$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
			echo "<input type=button $js value=\"Top Bids\">";

			echo "<ul>";
			
			DoQuery( "select distinct email from bidders" );
			printf( "<li># of bidders: %d</li>", $mysql_numrows );
		
			DoQuery( "select count(id) from bids" );
			list( $num ) = mysql_fetch_array( $mysql_result );
			printf( "<li># of bids: %d</li>", $num );
			
			DoQuery( "select distinct itemId from bids" );
			printf( "<li># of items with bids: %d</li>", $mysql_numrows );
		
			$v1 = array();
			DoQuery( "select id from items" );
			while( list( $id ) = mysql_fetch_array( $mysql_result ) ) {
				$v1[] = $id;
			}
			$total = 0;
			foreach( $v1 as $itemId ) {
				DoQuery( "select max( bid ) from bids where itemId = $itemId" );
				list( $bid ) = mysql_fetch_array( $mysql_result );
				$total += $bid;
			}
			printf( "<li>Sum of winning bids: \$ %s</li>", number_format( $total, 2 ) );

			echo "</ul>";

			echo "</div>";
			echo "<br>";
		}
		
		echo "<br>";
		echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";
	}
	
	if( $gTrace ) array_pop( $gFunction );
}

function DisplaySpiritual() {
	include( 'globals.php');
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	$ts = time() + $time_offset;
	$today = date('j-M-Y', $ts );

	$area = $_POST['area'];
	$func = __FUNCTION__;
	
	$ok_to_edit = UserManager( 'authorized', 'office' );

	echo "<div class=CommonV2>";	
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	
	$jsx = array();
	$jsx[] = "setValue('area','spiritual')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Refresh>";
	
	$jsx = array();
	$jsx[] = "setValue('area','spiritual')";
	$jsx[] = "addAction('Download')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Download>";
	
	$jsx = array();
	$jsx[] = "setValue('area','financial')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Financial>";
	
	echo "<br>";
	echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

	echo "<ul><li>The columns are sortable by clicking on their header</li></ul>";
	
	$lf = "\n";
	echo "<table class=sortable>$lf";
	echo "<tr>$lf";
	echo "  <th>#</th>$lf";
	echo "  <th>Category</th>$lf";
	echo "  <th class=mitzvah>Mitzvah</th>$lf";
	echo "  <th>Name</th>$lf";
	echo "  <th>Phone</th>$lf";
	echo "  <th>E-mail</th>$lf";
	if( $ok_to_edit ) {
		echo "<th>Action</th>";
	}
	echo "</tr>$lf";
	
	DoQuery( "select * from pledges where pledgeType = $PledgeTypeSpiritual order by lastName asc, firstName asc" );
	$i = 0;
	while( $rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		foreach( $rec as $key => $val ) {
			$$key = $val;
		}
		$ts = strtotime( $timestamp ) + $time_offset;
		$dmy = date('j-M-Y',$ts);
		$hl = ( $today == $dmy ) ? "class=today" : "";
		$tmp = preg_split( '/,/', $pledgeIds, NULL, PREG_SPLIT_NO_EMPTY );
		$phone = FormatPhone( $phone );
		$mail = 1;
		if( count( $tmp ) ) {
			foreach( $tmp as $pid ) {
				$desc = $gSpiritIDtoDesc[$pid];
				switch( $gSpiritIDtoType[$pid] ) {
					case( $SpiritualTorah ):
						$type = "Torah";
						break;
					case( $SpiritualAvodah ):
						$type = "Avodah";
						break;
					case( $SpiritualGemilut ):
						$type = "Gemilut";
						break;
				}
				$i++;
				echo "<tr>$lf";
				echo "  <td $hl>$i</td>$lf";
				echo "  <td $hl>$type</td>$lf";
				echo "  <td $hl class=mitzvah>" . $desc . "</td>$lf";
				printf( "  <td $hl>%s, %s</td>$lf", $lastName, $firstName );
				echo "  <td $hl>$phone</td>$lf";
				echo "  <td $hl>" . $email . "</td>$lf";
				if( $ok_to_edit ) {
					echo "<td $hl>$lf";
					$name = sprintf( "%s %s", $firstName, $lastName );
					$jsx = array();
					$jsx[] = "setValue('area','$area')";
					$jsx[] = "setValue('from','DisplaySpiritual')";
					$jsx[] = "setValue('func','delete')";
					$jsx[] = sprintf( "setValue('id','%d')", $id);
					$jsx[] = "addField('$pid')";
					$txt = sprintf( "Are you sure you want to delete %s's pledge to %s?",
										$name, $desc );
					$str = CVT_Str_to_Overlib($txt);
					$jsx[] = sprintf( "myConfirm('%s')", $str );
					$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
					echo "<input type=button value=Delete $js>$lf";
					
					if( $mail ) {
						$jsx = array();
						$jsx[] = "setValue('area','$area')";
						$jsx[] = "setValue('from','DisplaySpiritual')";
						$jsx[] = "setValue('func','mail')";
						$jsx[] = sprintf( "setValue('id','%d')", $id);
						$txt = sprintf( "Are you sure you want to resend the confirmation for %s's donation made on %s?",
											$name, date('j-M-Y h:i A', $ts) );
						$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
						$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
						echo "<input type=button value=Mail $js>$lf";
						$mail = 0;
					}					
					echo "</td>$lf";
				}
				echo "</tr>$lf";
			}
		}
		if( ! empty( $rec['pledgeOther'] ) ) {
			$desc = $rec['pledgeOther'];
			$pid = 0;
			$i++;
			echo "<tr>$lf";
			echo "  <td $hl>$i</td>$lf";
			echo "  <td $hl>Other</td>$lf";
			echo "  <td $hl class=mitzvah>$desc</td>$lf";
			printf( "  <td $hl>%s, %s</td>$lf", $lastName, $firstName );
			echo "  <td $hl>$phone</td>$lf";
			echo "  <td $hl>" . $email . "</td>$lf";
			if( $ok_to_edit ) {
				echo "<td $hl>$lf";
				$name = sprintf( "%s %s", $firstName, $lastName );
				$jsx = array();
				$jsx[] = "setValue('area','$area')";
				$jsx[] = "setValue('from','DisplaySpiritual')";
				$jsx[] = "setValue('func','delete')";
				$jsx[] = sprintf( "setValue('id','%d')", $id);
				$jsx[] = "addField('$pid')";
				$txt = "Are you sure you want to delete ${name}'s other pledge?";
				$str = CVT_Str_to_Overlib($txt);
				$jsx[] = sprintf( "myConfirm('%s')", $str );
				$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
				echo "<input type=button value=Delete $js>$lf";
				
				if( $mail ) {
					$jsx = array();
					$jsx[] = "setValue('area','$area')";
					$jsx[] = "setValue('from','DisplaySpiritual')";
					$jsx[] = "setValue('func','mail')";
					$jsx[] = sprintf( "setValue('id','%d')", $id);
					$txt = sprintf( "Are you sure you want to resend the confirmation for %s\'s donation made on %s?",
										$name, date('j-M-Y h:i A', $ts) );
					$jsx[] = sprintf( "myConfirm('%s')", CVT_Str_to_Overlib($txt) );
					$js = sprintf( "onclick=\"%s\"", join(';',$jsx) );
					echo "<input type=button value=Mail $js>$lf";
					$mail = 0;
				}					
				echo "</td>$lf";
			}
			echo "</tr>$lf";
		}
	}

	echo "</table>$lf";
	echo "</div>$lf";
		
	if( $gTrace ) array_pop( $gFunction );
}

function DisplaySpiritualXXX() {
	include( 'globals.php');
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}

	$func = $_POST['func'];
	
	echo "<div class=CommonV2>";
	
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	
	$jsx = array();
	$jsx[] = "setValue('area','spiritual')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Refresh>";
	
	$jsx = array();
	$jsx[] = "setValue('area','financial')";
	$jsx[] = "addAction('Main')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button $js value=Financial>";
	
	echo "<br>";
	echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

	DoQuery( "select * from pledges where pledgeType = $PledgeTypeSpiritual" );
	$hist = array();
	$other = array();
	$people = array();
	
	foreach( $gSpiritIDtoDesc as $id => $desc ) {
		$hist[$id] = 0;
	}
	
	while( $rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		$str = FormatPhone( $rec['phone'] );
		$tmp = preg_split( '/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY );
		if( count( $tmp ) ) {
			foreach( $tmp as $id ) {
				$hist[$id]++;
				$people[$id][] = sprintf( "%s, %s: %s", $rec['lastName'], $rec['firstName'], $str );
			}
		}
		if( ! empty( $rec['pledgeOther'] ) ) {
			$desc = $rec['pledgeOther'];
			if( empty( $other[ $desc ] ) ) {
				$other[$desc] = 0;
				$other[$desc]++;
				$people[$desc][] = sprintf( "%s, %s: %s",  $rec['lastName'], $rec['firstName'], $str );
			}
		}
	}

	arsort( $hist );
	
	echo "<ul><li>The columns are sortable by clicking on their header</li></ul>";
	echo "<table class=sortable>";
	echo "<tr>";
	echo "  <th class=mitzvah>Mitzvah</th>";
	echo "  <th># Selected</th>";
	echo "</tr>";
	
	foreach( $hist as $id => $count ) {
		if( empty( $count ) ) continue;
		echo "<tr>";
		printf( "<td class=mitzvah>%s</td>", $gSpiritIDtoDesc[$id] );
		echo "<td class=c>";
		$tag = number_format($count,0);
		$str = join( '<br>', $people[$id] );
		$cap = $gSpiritIDtoDesc[$id];
		echo <<<END
<a href="javascript:void(0);"
onmouseover="return overlib('$str', WIDTH, 300, CAPTION, '$cap')"
onmouseout="return nd();">$tag
</a>
END;
		echo "</td>";
		echo "</tr>";
	}
	foreach( $other as $desc => $count ) {
		echo "<tr>";
		printf( "<td class=mitzvah>%s (other)</td>", $desc );
		echo "<td class=c>";
		$tag = number_format($count,0);
		$str = $people[$desc][0];
		$cap = 'caption';
		echo <<<END
<a href="javascript:void(0);"
onmouseover="return overlib('$str', WIDTH, 300, CAPTION, '$cap')"
onmouseout="return nd();">$tag
</a>
END;
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
		
	if( $gTrace ) array_pop( $gFunction );
}

function DisplayTopBids() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	$func = $_POST['func'];
	
	$items = array();
	DoQuery( "select * from items order by itemTitle asc" );
	while( $row = mysql_fetch_assoc( $mysql_result ) ) {
		$id = $row['id'];
		$items[$id] = $row;
	}
	
	$bidders = array();
	DoQuery( "select * from bidders" );
	while( $row = mysql_fetch_assoc( $mysql_result ) ) {
		$id = $row['id'];
		$bidders[$id] = $row;
	}
	
	$with_bids = array();
	DoQuery( "select distinct itemId from bids order by bid desc" );
	while( list( $iid ) = mysql_fetch_array( $mysql_result ) ) {
		$with_bids[] = $iid;
	}
?>
<style>
.CommonV2 th.right {
	background-color: #addfff;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: right;
}
.CommonV2 td.left {
	background-color: #ffffff;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: left;
}
.CommonV2 td.sold_left {
	background-color: #ff0;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: left;
}
.CommonV2 td.sold_c {
	background-color: #ff0;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: center;
}
.CommonV2 td.right {
	background-color: #ffffff;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: right;
}
.CommonV2 td.sold_right {
	background-color: #ff0;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: right;
}
</style>
<?php
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	echo "<input type=button onclick=\"setValue('area','topbids');setValue('func','Back');addAction('Main');\" value=Refresh>";

	echo "<ul>";
	echo "<li>Click on a blue header to sort, click again to reverse the sort</li>";
	echo "<li><span style=\"background-color: #ff0;\">Items in yellow have been purchased.</span></li>";
	echo "</ul>";
	
	echo "<table class=sortable>";
	
	echo "<tr>";
	echo "<th>Id</th>";
	echo "<th>Title</th>";
	echo "<th>Bidder</th>";
	echo "<th class=\"sorttable_numeric\">Top Bid</th>";
	echo "<th># Bids</th>";
	echo "</tr>\n";

	$sum = 0;
	$tot_bids = 0;
	foreach( $with_bids as $x => $iid ) {
		DoQuery( "select * from bids where itemId = $iid order by bid desc" );
		$row = mysql_fetch_assoc( $mysql_result );
		$sold = ( $items[$iid]['status'] == $gStatusClosed ) ? "sold_" : "";
		
		printf( "<td class=${sold}c>%d</td>", $iid );
		
		printf( "<td class=${sold}left>%s</td>", $items[$iid]['itemTitle'] );
		
		$bidder_id = $row['bidderId'];
		printf( "<td class=${sold}left>%s, %s</td>", $bidders[$bidder_id]['last'], $bidders[$bidder_id]['first'] );
		
		printf( "<td class=${sold}right>\$ %s</td>", number_format( $row['bid'], 2 ) );
		
		printf( "<td class=${sold}c>%d</td>", $mysql_numrows );
		$tot_bids += $mysql_numrows;
		echo "</tr>\n";
		$sum += $row['bid'];
	}
	echo "<tfoot>";
	echo "<tr>";
	echo "<th colspan=3 class=right>Total</th>";
	printf( "<th class=right>\$ %s</th>", number_format( $sum, 2 ) );
	printf( "<th>%d</th>", $tot_bids );
	echo "</tr>";
	echo "</tfoot>";
	echo "</table>";
	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function EditItem() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$iid = $_POST['id'];
	$area = $_POST['area'];
	$func = __FUNCTION__;
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";

	$tag = MakeTag('update');
	$jsx = array();
	$jsx[] = "setValue('area','$area')";
	$jsx[] = "setValue('from','EditItem')";
	$jsx[] = "setValue('id','$iid')";
	$jsx[] = "setValue('func','update')";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=Update $tag $js>";

	echo "<table>";
	
	echo "<tr>";
	echo "<th>Fields</th>";
	echo "<th>Value</th>";
	echo "</tr>";

	DoQuery( "show fields from items" );
	$fields = array();
	while( $row = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		$label = $row['Field'];
		if( $label == "id" ) continue;
		$fields[] = $row['Field'];
	}
	
	DoQuery( "select * from items where id = '$iid'" );
	$row = mysql_fetch_assoc( $GLOBALS['mysql_result'] );
	
	foreach( $fields as $fld ) {
		$tag = MakeTag('fld_'.$fld);
		echo "<tr>";
		echo "<th>$fld</th>";
		if( $fld == "itemCategory" ) {
			$cid = $row[$fld];
			echo "<td>";
			$jsx = array();
			$jsx[] = "setValue('from','EditItem')";
			$jsx[] = "addField('$fld')";
			$jsx[] = "toggleBgRed('update')";
			$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );
			echo "<select $tag $js>";
			if( $cid == 0 ) {
				echo "<option value=0 selected>-- Click Here --</option>";
			}
			foreach( $gCategories as $id => $label ) {
				$selected = ( $id == $cid ) ? "selected" : "";
				echo "<option value=$id $selected>$label</option>";
			}
			echo "</select></td>";
		} elseif( $fld == "itemAuction" ) {
			$live = $row[$fld];
			echo "<td>";
			$jsx = array();
			$jsx[] = "setValue('from','EditItem')";
			$jsx[] = "addField('$fld')";
			$jsx[] = "toggleBgRed('update')";
			$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );
			echo "<select $tag $js>";
			if( $live == -1 ) {
				echo "<option value=0 selected>-- Click Here --</option>";
			}
			$tlive = array( 0 => "Silent", 1 => "Live" );
			foreach( $tlive as $val => $label ) {
				$selected = ( $val == $live ) ? "selected" : "";
				echo "<option value=$val $selected>$label</option>";
			}
			echo "</select></td>";
		} elseif( $fld == "status" ) {
			$status = $row[$fld];
			echo "<td>";
			$jsx = array();
			$jsx[] = "setValue('from','EditItem')";
			$jsx[] = "addField('$fld')";
			$jsx[] = "toggleBgRed('update')";
			$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );
			echo "<select $tag $js>";
			foreach( $gStatus as $val => $label ) {
				$selected = ( $val == $status ) ? "selected" : "";
				echo "<option value=$val $selected>$label</option>";
			}
			echo "</select></td>";
		} else {
			$val = $row[$fld];
			$jsx = array();
			$jsx[] = "setValue('from','EditItem')";
			$jsx[] = "addField('$fld')";
			$jsx[] = "toggleBgRed('update')";
			$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );
			echo "<td><input type=text size=100 $tag $js value=\"$val\"></td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";

	if( $gTrace ) array_pop( $gFunction );
}

function EditManager() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$area = $_POST['area'];
	
	if( $area == 'category' ) {
		EditCategory();
		
	} elseif( $area == 'item' ) {
		EditItem();
	}
	
	if( $gTrace ) array_pop( $gFunction );
}

function ExcelItems() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	$ts = time() + $time_offset;
	$str = date( 'Ymj', $ts );
	header("Content-type: application/csv");
	header("Content-Disposition: attachment;Filename=CBI-Auction-Items-$str.csv");

	$body = array();
	DoQuery( "show fields from items" );
	$fields = array();
	$types = array();
	while( $row = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		$label = $row['Field'];
		$fields[] = '"' . $row['Field'] . '"';
		$types[$label] = $row['Type'];
	}
	
	$body[] = join( ',',$fields );
	
	DoQuery( "select * from items order by id asc" );
	$outer = $mysql_result;
	while( $row = mysql_fetch_assoc( $outer ) ) {
		$id = $row['id'];
		DoQuery( "select max(bid) from bids where itemId = $id");
		list( $bid) = mysql_fetch_array( $mysql_result);
		$line = array();
		foreach( $row as $x => $fld ) {
			if( $x == "itemCategory" ) {
				$fld = $gCategories[$fld];
			} elseif( $x == "itemAuction" ) {
				$fld = ($fld == 0 ) ? "Silent" : "Live";
			} elseif( $x == 'status' ) {
				$fld = $gStatus[$fld];
			} elseif( $x == 'bidCurrent') {
				$fld = $bid;
			}
			if( $types[$x] == "float" ) {
				$line[] = '"$ ' . number_format( $fld,2) . '"';

			} else {
				$line[] = '"' . $fld . '"';
			}
		}
		$body[] = join(',',$line);
	}

	echo join("\n", $body );
	exit;
	
	if( $gTrace ) array_pop( $gFunction );	
}

function ExcelSpiritual() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	$ts = time() + $time_offset;
	$str = date( 'Ymj', $ts );
	header("Content-type: application/csv");
	header("Content-Disposition: attachment;Filename=CBI-HH-Spiritual-Pledges-$str.csv");

	$body = array();
	$line = array( '"#"','"Time"','"Category"','"Mitzvah"', '"Name"', '"Phone"', '"E-Mail"' );
	$body[] = join( ',',$line );
	
	DoQuery( "select * from pledges where pledgeType = $PledgeTypeSpiritual order by lastName asc, firstName asc" );
	$i = 0;
	while( $rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] ) ) {
		$tmp = preg_split( '/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY );
		$name = sprintf( "%s, %s", $rec['lastName'], $rec['firstName'] );
		$ts = strtotime($rec['timestamp']) + $time_offset;
		$time = date( 'j-M-Y h:i A', $ts );
		$phone = FormatPhone( $rec['phone'] );
		if( count( $tmp ) ) {
			foreach( $tmp as $id ) {
				switch( $gSpiritIDtoType[$id] ) {
					case( $SpiritualTorah ):
						$type = "Torah";
						break;
					case( $SpiritualAvodah ):
						$type = "Avodah";
						break;
					case( $SpiritualGemilut ):
						$type = "Gemilut";
						break;
				}
				$ts = $rec['timestamp'] + $time_offset;
				$i++;
				$line = array();
				$line[] = $i;
				$line[] = '"' . $time . '"';
				$line[] = '"' . $type . '"';
				$line[] = '"' . $gSpiritIDtoDesc[$id] . '"';
				$line[] = '"' . $name . '"';
				$line[] = '"' . $phone . '"';
				$line[] = '"' . $rec['email'] . '"';
				$body[] = join(',',$line);
			}
		}
		if( ! empty( $rec['pledgeOther'] ) ) {
			$desc = $rec['pledgeOther'];
			$type = "Other";
			$i++;
			$line = array();
			$line[] = $i;
			$line[] = '"' . $time . '"';
			$line[] = '"' . $type . '"';
			$line[] = '"' . $desc . '"';
			$line[] = '"' . $name . '"';
			$line[] = '"' . $phone . '"';
			$line[] = '"' . $rec['email'] . '"';
			$body[] = join(',',$line);
		}
	}

	echo join("\n", $body );
	exit;
	
	if( $gTrace ) array_pop( $gFunction );	
}

function HashAdd() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	DoQuery( "select id, hash from bidders where hash = ''" );
	if( $mysql_numrows ) {
		$outer = $mysql_result;
		while( list( $bidder_id, $hash ) = mysql_fetch_array( $outer ) ) {
			if( empty( $hash ) ) {
				DoQuery( "start transaction" );
				$get_new_hash = 1;
				while( $get_new_hash ) {
					$random_hash = substr(md5(uniqid(rand(), true)), 8, 6); // 6 characters long
					DoQuery( "select * from bidders where hash = '$random_hash'" );
					if( ! $mysql_numrows ) {
						DoQuery( "update bidders set hash = '$random_hash' where id = $bidder_id" );
						$get_new_hash = 0;
					}
				}
			}
			DoQuery( "commit" );
		}
	}
	if( $gTrace ) array_pop( $gFunction );	
}

function LocalInit() {
	include( 'globals.php' );
	
	$gDebug = 0;
	$gTrace = 0;
	$x = isset( $_REQUEST['bozo'] ) ? 1 : 0;
	if( $x ) {
		$gDebug = $x;
		$gTrace = $x;
	}

	$gFrom = array_key_exists( 'from', $_POST ) ? $_POST['from'] : '';
	$gFunction = array();
	$gSourceCode = $_SERVER['REQUEST_URI'];
	$gPreSelected = ( preg_match( '/id=(\d+)/', $gSourceCode, $matches ) ) ? $matches[1] : 0;
	if( $gPreSelected > 0 ) {
		$tmp = preg_match( '/(.+)\?(.+)/', $gSourceCode, $matches );
		$gSourceCode = $matches[1];
	}
#============
	DoQuery( "set transaction isolation level serializable" );

	$gCategories = array();
	$gCategories[0] = '__Unassigned';
	DoQuery( "select id, label from categories order by label" );
	while( list( $id, $label ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
		$gCategories[$id] = $label;
	}
	asort( $gCategories );
	
	foreach( array( 'open', 'close', 'mail' ) as $label ) {
		DoQuery( "select * from dates where label = '$label'" );
		if( $mysql_numrows == 0 ) {
			$val = ( $label == 'mail' ) ? 0 : time();
			DoQuery( "insert into dates set label = '$label', date = $val");
		}
	}
	
#============
	$date_server = new DateTime( '2000-01-01' );
	$date_calif = new DateTime( '2000-01-01', new DateTimeZone('America/Los_Angeles'));
	$time_offset = $date_server->format('U') - $date_calif->format('U');
}

function MailUpdate() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$val = $_POST['fields'];
	DoQuery( "update dates set date = $val where label = 'mail'" );
	
	if( $gTrace ) array_pop( $gFunction );
}

function PayPal() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	foreach( array('amount','email','firstName','lastName','phone') as $fld ) {
		$$fld = $_POST[$fld];
	}
	
	echo <<<END
<form name=form_paypal action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="amount" value="$amount">
	<input type="hidden" name="email" value="$email">
	<input type="hidden" name="first_name" value="$firstName">
	<input type="hidden" name="last_name" value="$lastName">
	<input type="hidden" name="phone" value="$phone">
	<input type="image" src="http://www.cbi18.org/images/Donate_sm.jpg" border="0"
		  name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<script type="text/javascript">
form_paypal.submit();
</script>
END;

	if( $gTrace ) array_pop( $gFunction );
}
	
function PledgeEdit() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$id = $_POST['id'];
	$area = $_POST['area'];
	DoQuery( "select * from pledges where id = '$id'" );
	$rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] );
	
	echo "<input type=button value=Back onclick=\"setValue('from', 'PledgeEdit');addAction('Back');\">";
	
	$tag = MakeTag('update');
	$jsx = array();
	$jsx[] = "setValue('area','$area')";
	$jsx[] = "setValue('from','PledgeEdit')";
	$jsx[] = "setValue('id','$id')";
	$jsx[] = "setValue('func','update')";
	$jsx[] = "addAction('Update')";
	$js = sprintf( "onClick=\"%s\"", join(';',$jsx) );
	echo "<input type=button value=Update $tag $js>";

	echo "<div class=CommonV2>";
	
	if( $area == 'financial' ) {
		echo "<table>";
		echo "<tr><th>Field</th><th class=val>Value</th></tr>";
		$fields = array( 'firstName' => 'First Name',
							 'lastName' => 'Last Name',
							 'phone' => 'Phone',
							 'email' => 'E-mail' );
		foreach( $fields as $key => $label  ) {
			echo "<tr>";
			echo "<td>$label</td>";
			$jsx = array();
			$jsx[] = "setValue('area','$area')";
			$jsx[] = "setValue('from','PledgeEdit')";
			$jsx[] = "addField('$key')";
			$jsx[] = "toggleBgRed('update')";
			$js = sprintf( "onKeyDown=\"%s\"", join(';',$jsx) );
			printf( "<td><input type=text size=50 name=%s value=\"%s\" $js></td>", $key, $rec[$key] );
			echo "</tr>";
		}
		$jsx = array();
		$jsx[] = "setValue('area','$area')";
		$jsx[] = "setValue('from','PledgeEdit')";
		$jsx[] = "addField('amount')";
		$jsx[] = "toggleBgRed('update')";
		$js = sprintf( "onKeyDown=\"%s\"", join(';',$jsx) );
		echo "<tr>";
		echo "<td>Amount</td>";
		printf( "<td><input type=text size=50 name=amount value=\"\$ %s\" $js></td>",
				number_format( $rec['amount'], 2 ) );
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>Payment Method</td>";
		$tag = MakeTag( 'paymentMethod');
		$types = array( $PaymentCredit => 'Credit', $PaymentCheck => 'Check', $PaymentCall => 'Call' );
		$jsx = array();
		$jsx[] = "setValue('area','$area')";
		$jsx[] = "setValue('from','PledgeEdit')";
		$jsx[] = "addField('paymentMethod')";
		$jsx[] = "toggleBgRed('update')";
		$js = sprintf( "onChange=\"%s\"", join(';',$jsx) );
		echo "<td><select $tag $js>";
		foreach( $types as $val => $label ) {
			$selected = ( $val == $rec['paymentMethod'] ) ? "selected" : "";
			echo "<option value=$val $selected>$label</option>";
		}
		echo "</select></td>";
		echo "</tr>";
		echo "</table>";
	}
	
	echo "</div>";
	
	if( $gTrace ) array_pop( $gFunction );
}

function PledgeStore() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$args = array();
	$tmp = preg_split( '/\|/', $_POST['fields'] );
	foreach( $tmp as $nvp ) {
		list( $name, $value ) = preg_split( '/=/', $nvp );
		$_SESSION[$name] = $value;
		if( $name == 'pledgeIds' ) {
			$pledgeIds = array();
			$tmp2 = preg_split( '/,/', $value );
			foreach( $tmp2 as $xx ) {
				list( $key, $id ) = preg_split( '/_/', $xx );
				if( $key == "id" ) {
					$pledgeIds[] = $id;
				}
			}
			$args[] = sprintf( "pledgeIds = '%s'", join(',', $pledgeIds ) );
			 
		} elseif( $name == "phone" ) {
			$args[] = "phone = " . preg_replace("/[^0-9]/", "", $value);
	
		} else {
			$args[] = sprintf( "%s = '%s'", $name, addslashes($value) );
		}
	}
	
	if( $gFrom == 'financial' ) {
		$args[] = "pledgeType = $PledgeTypeFinancial";
		$args[] = sprintf( "paymentMethod = '%d'", $_POST['paynow'] );
	
	} else {
		$args[] = "pledgeType = $PledgeTypeSpiritual";
	}
	
	$query = "insert into pledges set " . join( ',', $args );
	DoQuery( $query );
	$id = mysql_insert_id();

	if( $gTrace ) array_pop( $gFunction );
	return $id;
}

function PledgeUpdate() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$id = $_POST['id'];
	$from = $_POST['from'];
	
	if( $func == 'update' ) {
		$tmp = preg_split( '/,/', $_POST['fields'] );  // This is what was touched
		$keys = array_unique( $tmp );
		$qx = array();
		foreach( $keys as $key ) {
			$val = CleanString($_POST[$key]);
			if( $key == 'phone' ) {
				$val = preg_replace("/[^0-9]/", "", $val );
			} elseif( $key == 'amount' ) {
				$val = preg_replace( "/[\$,]/", "", $val );
			}
			$qx[] = sprintf( "`%s` = '%s'", $key, $val );
		}
		$query = sprintf( "update pledges set %s where id = $id", join( ',', $qx ) );
		DoQuery( $query );
		
	} elseif( $func == 'delete' ) {
		if( $from == "DisplayFinancial" ) {
			$query = sprintf( "delete from pledges where id = $id" );
			DoQuery( $query );
		} elseif( $from == "DisplaySpiritual" ) {
			$pid = $_POST['fields'];
			DoQuery( "select * from pledges where id = $id" );
			$rec = mysql_fetch_assoc( $GLOBALS['mysql_result'] );
			$pids = preg_split( '/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY );
			$other = $rec['pledgeOther'];
			
			if( $pid ) { // Delete a defined pledge
				if( count( $pids ) == 1 ) {  // This was the only defined pledgeId
					if( $other == "" ) { // And I have no "other" pledge
						$query = "delete from pledges where id = $id";
					} else {
						foreach( $pids as $key => $value ) {
							if( $pid == $value ) {
								unset( $pids[$key] );
							}
						}
						$query = sprintf( "update pledges set pledgeIds = '%s' where id = $id", join(',', $pids ) );
					}
				} else {
					foreach( $pids as $key => $value ) {
						if( $pid == $value ) {
							unset( $pids[$key] );
						}
					}
					$query = sprintf( "update pledges set pledgeIds = '%s' where id = $id", join(',', $pids ) );
				}
			} else { // I'm deleting an "other" pledge
				if( count( $pids ) == 0 ) { // Nothing left, delete the pledge
					$query = "delete from pledges where id = $id";
				} else {
					$query = "update pledges set pledgeOther = '' where id = $id";
				}
			}
			DoQuery( $query );
		}

	} elseif( $func == 'mail' ) {
		SendConfirmation($id);
	}
	
	if( $gTrace ) array_pop( $gFunction );
}

function	SendConfirmation() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	$subject = "5775 CBI High Holy Day Honor";
	
	$message = Swift_Message::newInstance($subject);
	
	$firstName = $_POST['hh-name'];
	$lastName = "";
	$email = $_POST['hh-email'];

	$html = $text = array();
	$cid = $message->embed(Swift_Image::fromPath('assets/CBI_ner_tamid.png'));

	$html[] = "<html><head></head><body>";
	$html[] = '<img src="' . $cid . '" alt="Image" />';
	
	$html[] = "Congregation B'nai Israel";
	$text[] = "Congregation B'nai Israel";
	
	$html[] = "";
	$text[] = "";
	
	$html[] = sprintf( "Dear %s,", $firstName );
	$text[] = sprintf( "Dear %s,", $firstName );

	$html[] = "";
	$text[] = "";
	
	$button = $_POST['RadioGroup2'];
	if( $button == 'accept' ) {
		$html[] = sprintf( "Thank you for allowing us to honor you." );
		$text[] = sprintf( "Thank you for allowing us to honor you." );
		
	} elseif( $button == 'decline' ) {
		$html[] = sprintf( "Thank you for letting us know you are declining your honor." );
		$text[] = sprintf( "Thank you for letting us know you are declining your honor." );

	}

	$amount = $_POST['hh-amount'];
	if( ! empty( $amount ) ) {
		$html[] = sprintf( "  In addition, thank you for your generous donation of \$ %s.", number_format( $amount, 2 ) );
		$text[] = sprintf( "  In addition, thank you for your generous donation of \$ %s.", number_format( $amount, 2 ) );

		$html[] = "";
		$text[] = "";
	
		$payment = $_POST['hh-payment'];
		if( $payment == 'credit' ) {
			$html[] = sprintf( "  We will be charging your credit card on file." );
			$text[] = sprintf( "  We will be charging your credit card on file." );
		} elseif( $payment == 'check' ) {
			$html[] = sprintf( "  We will be expecting your check in the next few days." );
			$text[] = sprintf( "  We will be expecting your check in the next few days." );
		} elseif( $payment == 'call' ) {
			$html[] = sprintf( "  We will be contacting you to arrange for payment." );
			$text[] = sprintf( "  We will be contacting you to arrange for payment." );
		}
	}
		
	$html[] = "";
	$text[] = "";
	
	$comment = $_POST['hh-comment'];
	if( ! empty( $comment ) ) {
		$html[] = sprintf( "We appreciate your following comments:" );
		$text[] = sprintf( "We appreciate your following comments:" );
		$html[] = "";
		$text[] = "";
		$html[] = $comment;
		$text[] = $comment;
		
	}
	
	$message->setTo( array( $email => "$firstName" ) );
	$message->setFrom(array('cbi18@cbi18.org' => 'CBI'));
	$message->setBcc(array(
		'cbi18@cbi18.org' => 'Debbie Hebron',
		'bertil@askelid.com' => 'Bertil Askelid',
		'bethelster1@gmail.com' => 'Beth Elster',
		'andy.elster@gmail.com' => 'Andy Elster'
	) );

	$message
	->setBody( join('<br>',$html), 'text/html' )
	->addPart( join('\n',$text), 'text/plain' )
	;

	MyMail($message);

	if( $gTrace ) array_pop( $gFunction );
}

function ShowBids() {
	include( 'globals.php' );
	$func = __FUNCTION__;
	if( $gTrace ) {
		$gFunction[] = $func;
		Logger();
	}

	$hash = $_POST['id'];	
	DoQuery( "select * from bidders where hash = '$hash'" );
	$bidder = mysql_fetch_assoc( $mysql_result );
	
	echo "<div class=CommonV2>";
	echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";
	$jsx = array();
	$jsx[] = "setValue('area','showbids')";
	$jsx[] = "setValue('func','Back')";
	$jsx[] = "setValue('id','$hash')";
	$jsx[] = "addAction('Main')";
	$js = join(';',$jsx);
	echo "<input type=button onclick=\"$js\" value=Refresh>";

	$hash = $_POST['id'];	
	DoQuery( "select * from bidders where hash = '$hash'" );
	$bidder = mysql_fetch_assoc( $mysql_result );
?>
<style>
.CommonV2 td.right {
	background-color: #ffffff;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: right;
}
.CommonV2 td.sold {
	background-color: #ff0;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: center;
}
.CommonV2 td.low {
	background-color: #ffffff;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: center;
}
.CommonV2 td.top {
	background-color: #0f0;
	border: 1px solid #000000;
	padding: 0px 5px 0px 5px;
	text-align: center;
}
</style>
<?php
	printf( "<h3>Bids for %s %s</h3>", $bidder['first'], $bidder['last'] );
	
	echo "<table class=sortable>";
	echo "<tr>";
	echo "<th>#</th>";
	echo "<th>Title</th>";
	echo "<th># Bids</th>";
	echo "<th>My Top</th>";
	echo "<th>Current Bid</th>";
	echo "<th>Status</th>";
	echo "</th>";
	
	$bidderId = $bidder['id'];
	DoQuery( "select distinct itemId from bids where bidderId = '$bidderId'" );
	$my_items = array();
	while( list( $iid ) = mysql_fetch_array( $mysql_result ) ) {
		$my_items[] = $iid;
	}
	foreach( $my_items as $iid ) {
		echo "<tr>";
		echo "<td class=c>$iid</td>";
		DoQuery( "select * from items where id = '$iid'" );
		$item = mysql_fetch_assoc( $mysql_result );
		$title = $item['itemTitle'];
		echo "<td>$title</td>";

		DoQuery( "select bid from bids where itemId = $iid and bidderId = $bidderId order by bid desc" );
		printf( "<td class=c>%d</td>", $mysql_numrows );

		list( $my_bid ) = mysql_fetch_array( $mysql_result );
		printf( "<td class=right>\$ %s</td>", number_format( $my_bid, 2 ) );

		DoQuery( "select max(bid) from bids where itemId = $iid" );
		list( $top_bid ) = mysql_fetch_array( $mysql_result );
		printf( "<td class=right>\$ %s</td>", number_format( $top_bid, 2 ) );

		if( $item['status'] == $gStatusClosed ) {
			$stat = "Sold";
			$c="class=sold";
		} elseif( $my_bid < $top_bid ) {
			$stat = "Too Low";
			$c="class=low";
		} else {
			$stat = "Top for now";
			$c="class=top";
		}
		echo "<td $c>$stat</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "</div>";
	
	if( $gTrace ) array_pop( $gFunction );
}
	
function UpdateCategories() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$tmp2 = preg_split( '/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY );
	$tmp = array_unique( $tmp2 );
	
	if( $func == "update" ) {
		foreach( $tmp as $cid ) {
			$label = $_POST["cat_$cid"];
			DoQuery( "update categories set label = '$label' where id = '$cid'" );
		}
		
	} elseif( $func == "delete" ) {
		foreach( $tmp as $cid ) {
			DoQuery( "delete from categories where id = '$cid'" );
		}
		
	} elseif( $func == "add" ) {
		$label = $_POST["cat_0"];
		DoQuery( "insert into categories set label = '$label'");
	}
	
	$gCategories = array();
	DoQuery( "select id, label from categories order by label" );
	while( list( $id, $label ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
		$gCategories[$id] = $label;
	}
	asort( $gCategories );

	$gAction = "Edit";
	$_POST['area'] = 'category';
	if( $gTrace ) array_pop( $gFunction );
}

function UpdateItem() {
	include( 'globals.php' );
	if( $gTrace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	$func = $_POST['func'];
	$iid = $_POST['id'];
	
	if( $func == "delete" ) {
		DoQuery( "delete from items where id = '$iid'" );
		
	} else {
		$tmp2 = preg_split( '/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY );
		$tmp = array_unique( $tmp2 );
		if( count( $tmp ) ) {
			$mods = array();
			foreach( $tmp as $fld ) {
				$mods[] = sprintf( "`%s` = '%s'", $fld, CleanString( $_POST['fld_' . $fld]) );
			}
			if( $iid > 0 ) {
				$query = sprintf( "update items set %s where id = '%s'", join( ',', $mods ), $iid );
			} else {
				$query = sprintf( "insert into items set %s", join( ',', $mods ) );
			}
			DoQuery( $query );

		}
	}
	
	$gAction = "Edit";
	$_POST['area'] = 'item';
	$_POST['id'] = $iid;
	if( $gTrace ) array_pop( $gFunction );
}

function WriteHeader() {
	include( 'globals.php' );
	
	echo "<html>$gLF";
	echo "<head>$gLF";
	
	$styles = array();
	$styles[] = "/css/CommonV2.css";
	$styles[] = "admin.css";
	
	foreach( $styles as $style ) {
		printf( "<link href=\"%s\" rel=\"stylesheet\" type=\"text/css\" />$gLF", $style );
	}

	$scripts = array();
	$scripts[] = "/scripts/overlib/overlib.js";
	$scripts[] = "/scripts/overlib/overlib_hideform.js";
	$scripts[] = "/scripts/commonv2.js";
	$scripts[] = "/scripts/sha256.js";
	$scripts[] = "/scripts/sorttable.js";
	$scripts[] = "hhPledges.js";
	
	foreach( $scripts as $script ) {
		printf( "<script type=\"text/javascript\" src=\"%s\"></script>$gLF", $script );
	}
	echo "</head>$gLF";
	
	echo "<body>$gLF";
	AddOverlib();
}
?>
