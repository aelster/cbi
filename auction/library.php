<?php

function AddForm() {
    include( 'globals.php' );
    echo "<form name=fMain id=fMain method=post action=\"" . DIR . "$gSourceCode\">";

    $hidden = array('action', 'area', 'fields', 'func', 'from', 'key', 'id');
    foreach ($hidden as $var) {
        $tag = MakeTag($var);
        echo "<input type=hidden $tag>";
    }
}

function BidAdd() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $item_id = $_POST['id'];
    $from = $_POST['from'];

    $tmp = preg_split('/,/', $_POST['fields']);  // This is what was touched
    $keys = array_unique($tmp);

    $email = $_POST['bidder_email'];

    $qx = array();
    $stmt = DoQuery("select * from bidders where email = '$email'");
    if ($gPDO_num_rows) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $bidder_id = $row['id'];
        $get_new_hash = empty($row['hash']);
        if (isset($_POST['bidder_phone'])) {
            $qx[] = sprintf("phone = '%d'", preg_replace("/[^0-9]/", "", $_POST['bidder_phone']));
            $query = sprintf("update bidders set %s where id = $bidder_id", join(',', $qx));
            DoQuery($query);
        }
    } else {
        $get_new_hash = 1;
        $qx[] = sprintf("first = '%s'", CleanString($_POST['bidder_first']));
        $qx[] = sprintf("last = '%s'", CleanString($_POST['bidder_last']));
        $qx[] = sprintf("email = '%s'", CleanString($_POST['bidder_email']));
        if (isset($_POST['bidder_phone'])) {
            $qx[] = sprintf("phone = '%d'", preg_replace("/[^0-9]/", "", $_POST['bidder_phone']));
        }
        $query = sprintf("insert into bidders set %s", join(',', $qx));
        $stmt = DoQuery($query);
        $bidder_id = $gPDO_lastInserID;
    }

    DoQuery("start transaction");
    while ($get_new_hash) {
        $random_hash = substr(md5(uniqid(rand(), true)), 8, 8); // 6 characters long
        DoQuery("select * from bidders where hash = '$random_hash'");
        if (!$gPDO_num_rows) {
            DoQuery("update bidders set hash = '$random_hash' where id = $bidder_id");
            $get_new_hash = 0;
        }
    }
    DoQuery("commit");

    $bid_amount = $_POST['bid_amount'];
#
# *** Start of transaction
#
    DoQuery("start transaction");

    $stmt = DoQuery("select * from items where id = $item_id");
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = DoQuery("select * from bids where itemId = $item_id order by bid desc limit 1");
    $top_bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gStatus[$item['status']] == 'Closed') {  # Oops, we've missed this item altogether
        $_POST['id'] = -5;
        DoQuery("rollback");
    } elseif ($bid_amount == $top_bid['bid']) {  # Oh well, insufficient bid, try again
        $_POST['id'] = -6;
        DoQuery("rollback");
    } elseif ($bid_amount < $top_bid['bid']) {  # Oh well, insufficient bid, try again
        if ($top_bid['bid'] < $item['buyNowPrice']) {
            $_POST['id'] = -7;
        } else {
            $_POST['id'] = -5;
        }
        DoQuery("rollback");
    } else {  # We have a winner	
        if ($top_bid['notify']) {
            $type = ( $bid_amount == $item['buyNowPrice'] ) ? $gSendOldBought : $gSendOld;
            SendConfirmation($top_bid['bidderId'], $item_id, $top_bid['id'], $type);
        }

        $qx = array();
        $qx[] = sprintf("itemId = %d", $item_id);
        $qx[] = sprintf("bidderId = %d", $bidder_id);
        $qx[] = sprintf("bid = %s", $_POST['bid_amount']);
        if (array_key_exists('bidder_notify', $_POST)) {
            $qx[] = sprintf("notify = %d", $_POST['bidder_notify']);
        }
        $query = sprintf("insert into bids set %s", join(',', $qx));
        DoQuery($query);
        $bid_id = $gPDO_lastInsertID;

        if ($bid_amount == $item['buyNowPrice']) {
            SendConfirmation($bidder_id, $item_id, $bid_id, $gSendBought);
            DoQuery("update items set status = 1 where id = $item_id");
        } else {
            SendConfirmation($bidder_id, $item_id, $bid_id, $gSendTop);
        }
        DoQuery("commit");
        $_POST['id'] = -4;
    }

    if ($gTrace)
        array_pop($gFunction);
}

function CleanString($data) {
    $data = trim($data);
    $data = addslashes($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES);
    return $data;
}

function DateUpdate() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $from = $_POST['from'];

    $tmp = preg_split('/,/', $_POST['fields']);  // This is what was touched
    $keys = array_unique($tmp);

    if ($gFunc == "update") {
        $qx = array();
        foreach ($keys as $id) {
            $qx[] = sprintf("date = '%d'", strtotime($_POST['date_' . $id]));
            if ($id == 0) {
                $query = sprintf("insert into dates set %s", join(',', $qx));
            } else {
                $query = sprintf("update dates set %s where id = %d", join(',', $qx), $id);
            }
            DoQuery($query);
        }
    } elseif ($gFunc == "delete") {
        $query = sprintf("delete from dates where id = %d", $keys[0]);
        DoQuery($query);
    }

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayBidders() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
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

    $outer = DoQuery("select * from bidders order by last asc, first asc");
    while ($row = $outer->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        printf("<td>%s</td>", $row['last']);
        printf("<td>%s</td>", $row['first']);
        printf("<td>%s</td>", $row['email']);
        printf("<td>%s</td>", FormatPhone($row['phone']));
        $query = sprintf("select * from bids where bidderId = %d", $row['id']);
        $stmt = DoQuery($query);
        $jsx = array();
        $jsx[] = "setValue('area','showbids')";
        $jsx[] = sprintf("setValue('id','%s')", $row['hash']);
        $jsx[] = "addAction('Main')";
        $js = sprintf("onclick=\"%s\"", join(';', $jsx));
        echo "<td class=c $js>";
        echo $stmt->rowCount();
        echo "</td>";
        echo "</tr>\n";
    }
    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayCategories() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if (!array_key_exists(0, $gCategories)) {
        $gCategories[0] = '__Unassigned';
    }

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','DisplayCategories')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<table class=sortable>";

    echo "<tr>";
    echo "<th>Label</th>";
    echo "<th># of Items</th>";
    echo "<th>Action</th>";
    echo "</tr>\n";

    asort($gCategories);
    foreach ($gCategories as $id => $label) {
        $tag = MakeTag("cat_$id");

        echo "<tr>";
        echo "<td><input type=text $tag value=\"$label\" onChange=\"addField('$id');toggleBgRed('update');\"></td>";
        $stmt = DoQuery("select count(id) from items where itemCategory = '$id'");
        list($num) = $stmt->fetch(PDO::FETCH_BOTH);
        echo "<td class=c>$num</td>";
        echo "<td class=c>";

        if ($id == 0) {
            echo "&nbsp;";
        } else {
            $jsx = array();
            $jsx[] = "setValue('area','category')";
            $jsx[] = "setValue('from','DisplayCategories')";
            $jsx[] = "addField('$id')";
            $jsx[] = "setValue('func','delete')";
            $txt = sprintf("Are you sure you want to delete the category $label?");
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Del $tag $js>";
        }
        echo "</td>";
        echo "</tr>";
    }

    $id = 0;
    $label = "";
    $tag = MakeTag("cat_$id");

    echo "<tr>";
    echo "<td><input type=text $tag value=\"$label\" onChange=\"toggleBgRed('add_$id');\"></td>";
    echo "<td class=c>&nbsp;</td>";
    echo "<td class=c>";

    $tag = MakeTag("add_$id");
    $jsx = array();
    $jsx[] = "setValue('area','category')";
    $jsx[] = "setValue('from','DisplayCategories')";
    $jsx[] = "addField('$id')";
    $jsx[] = "setValue('func','add')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Add $tag $js>";
    echo "</td>";
    echo "</tr>";

    echo "</table>";

    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayDates() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','DisplayDates')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    printf("<h3>Current date: %s</h3>", date("D M jS, Y, g:i A"));
    echo "<table class=sortable>";

    echo "<tr>";
    echo "<th>Label</th>";
    echo "<th>Weekday</th>";
    echo "<th>Date</th>";
    echo "</tr>\n";

    $stmt = DoQuery("select * from dates order by `date` asc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $label = $row['label'];
        $date = $row['date'];
        if (!( $label == 'open' || $label == 'close' || $label == "auction" ))
            continue;

        echo "<tr>";
        $jsx = array();
        $jsx[] = "setValue('from','DisplayDates')";
        $jsx[] = "addField('$id')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));

        printf("<td>%s</td>", $label);

        printf("<td class=c>%s</td>", date("l", $date));

        $tag = MakeTag('date_' . $id);
        printf("<td><input $tag $js size=30 value=\"%s\"></td>", date("M jS, Y, g:i A", $date));

        echo "</tr>\n";
    }

    echo "</table>\n";
    echo "</div>\n";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayFinancial() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $ts = time() + $time_offset;
    $today = date('j-M-Y', $ts);

    $ok_to_edit = UserManager('authorized', 'office');

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    $jsx = array();
    $jsx[] = "setValue('area','financial')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Refresh>";

    $jsx = array();
    $jsx[] = "setValue('area','financial')";
    $jsx[] = "addAction('Download')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Download>";

    $jsx = array();
    $jsx[] = "setValue('area','spiritual')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Spiritual>";

    echo "<br>";
    echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

    DoQuery("select sum(amount) from pledges where pledgeType = $PledgeTypeFinancial");
    list( $total ) = mysql_fetch_array($GLOBALS['mysql_result']);

    DoQuery("select amount from pledges where pledgeType = $PledgeTypeFinGoal");
    list( $goal ) = mysql_fetch_array($GLOBALS['mysql_result']);
#	DoQuery( "select * from pledges where pledgeType = $PledgeTypeFinancial order by amount desc, lastName asc" );
    DoQuery("select * from pledges where pledgeType = $PledgeTypeFinancial order by timestamp desc");
    $num_pledges = $GLOBALS['mysql_numrows'];
    echo "<ul>";
    echo "<li>The columns are sortable by clicking on their header</li>";
    $x = $total * 100.0 / $goal;
    printf("<li>%d pledges: \$ %s ( %d %% of \$ %s goal)</li>", $num_pledges, number_format($total), intval($x), number_format($goal));
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
    if ($ok_to_edit) {
        echo "<th>Action</th>";
    }
    echo "</tr>";

    $methods = array($PaymentCredit => 'Credit', $PaymentCheck => 'Check', $PaymentCall => 'Call');

    $lf = "\n";
    $i = 0;
    while ($rec = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
        foreach ($rec as $key => $val) {
            $$key = $val;
        }
        $i++;
        $ts = strtotime($timestamp) + $time_offset;
        $dmy = date('j-M-Y', $ts);
        $hl = ( $today == $dmy ) ? "class=today" : "";
        echo "<tr>$lf";
        printf("<td $hl>%d</td>$lf", $i);
        printf("<td $hl style=\"text-align:right;\">\$ %s</td>$lf", number_format($amount, 2));
        printf("<td $hl>%s %s</td>$lf", $lastName, $firstName);
        printf("<td $hl>%s</td>$lf", FormatPhone($phone));
        printf("<td $hl class=c>%s</td>$lf", $methods[$paymentMethod]);
#		printf( "<td $hl>%s</td>$lf", $tdate->format( 'j-M-Y h:i A') );
        printf("<td sorttable_customkey=$ts $hl>%s</td>$lf", date('j-M-Y h:i A', $ts));
        if ($ok_to_edit) {
            echo "<td $hl>$lf";

            $jsx = array();
            $jsx[] = "setValue('area','$gArea')";
            $jsx[] = sprintf("setValue('id','%d')", $id);
            $jsx[] = "addAction('Edit')";
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Edit $js>$lf";

            $jsx = array();
            $jsx[] = "setValue('area','$gArea')";
            $jsx[] = "setValue('from','DisplayFinancial')";
            $jsx[] = "setValue('func','delete')";
            $jsx[] = sprintf("setValue('id','%d')", $id);
            $txt = sprintf("Are you sure you want to delete %s %s's donation for \$ %s?", $firstName, $lastName, number_format($amount, 2));
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Delete $js>$lf";

            $jsx = array();
            $jsx[] = "setValue('area','$gArea')";
            $jsx[] = "setValue('from','DisplayFinancial')";
            $jsx[] = "setValue('func','mail')";
            $jsx[] = sprintf("setValue('id','%d')", $id);
            $txt = sprintf("Are you sure you want to resend the confirmation for  %s %s's donation of \$ %s\\nmade on %s?", $firstName, $lastName, number_format($amount, 2), date('j-M-Y h:i A', $ts));
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Mail $js>$lf";

            echo "</td>$lf";
        }
        echo "</tr>$lf";
    }
    echo "</table>$lf";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayItems() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\"><br>";
    echo "<input type=button value=Refresh onclick=\"setValue('area', 'items');addAction('Main');\">";

    $jsx = array();
    $jsx[] = "setValue('area','item')";
    $jsx[] = "setValue('id',0)";
    $jsx[] = "addAction('Edit')";
    $js = sprintf("onclick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=New $js>";

    $jsx = array();
    $jsx[] = "setValue('area','items')";
    $jsx[] = "addAction('Download')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Download>";

    $stmt = DoQuery("select count(id) from items");
    list( $num ) = $stmt->fetch(PDO::FETCH_BOTH);
    echo "<h2>There are $num items in the database</h2>";

    echo "<ul>";
    echo "<li>Click on a blue header to sort, click again to reverse the sort</li>";
    echo "</ul>";

    echo "<table class=sortable>";

    echo "<tr>";
    echo "<th>Id</th>";
    echo "<th>Package</th>";
    echo "<th>Auction</th>";
    echo "<th>Status</th>";
    echo "<th>Category</th>";
    echo "<th>Type</th>";
    echo "<th>Title</th>";
    echo "<th>Description</th>";
    echo "<th>Misc</th>";
    echo "</tr>";

    $i = 0;
    $outer = DoQuery("select * from items order by `itemTitle` asc");
    while ($row = $outer->fetch(PDO::FETCH_BOTH)) {
        $i++;
        $iid = $row['id'];
        $pid = $row['itemPackage'];
        $cid = $row['itemCategory'];
        echo "<tr>";
        echo "<td class=c>$iid</td>";
        echo "<td>" . $gPackages[$pid] . "</td>";
        $val = ( $row['itemIsLive'] == 1 ) ? "Live" : "Silent";
        echo "<td class=c>$val</td>";
        printf("<td class=c>%s</td>", $gStatus[$row['status']]);
        echo "<td>" . $gCategories[$cid] . "</td>";
        $val = ( $row['itemIsCert'] == 1 ) ? "Certificate" : "Item";
        echo "<td class=c>$val</td>";
        echo "<td>" . $row['itemTitle'] . "</td>";
        echo "<td class=desc>" . $row['itemDesc'] . "</td>";

        echo "<td class=c>";
        $jsx = array();
        $jsx[] = "setValue('area','item')";
        $jsx[] = sprintf("setValue('id','%d')", $iid);
        $jsx[] = "addAction('Edit')";
        $js = sprintf("onclick=\"%s\"", join(';', $jsx));
        echo "<input type=button value=Edit $js>";

        $jsx = array();
        $jsx[] = "setValue('area','category')";
        $jsx[] = "setValue('from','DisplayItems')";
        $jsx[] = "setValue('id','$iid')";
        $jsx[] = "setValue('func','delete')";
        $txt = sprintf("Are you sure you want to delete this item?");
        $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
        $js = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo "<input type=button value=Del $js>";

        echo "</td>";
        echo "</tr>\n";
    }

    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayMail() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    
    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    echo "<table>";
    DoQuery("select date from dates where label = 'mail'");
    list( $val ) = mysql_fetch_array($mysql_result);
    echo "<tr>";
    echo "<th>Mail Confirmations</th>";
    if ($val) {
        echo "<td class=cok>Enabled</td>";
        $new = 0;
    } else {
        echo "<td class=cbad>Disabled</td>";
        $new = 1;
    }
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','DisplayMail')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addField($new)";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<td><input type=button value=Toggle $tag $js></td>";

    echo "</tr>";

    echo "</table>";
    if ($gTrace)
        array_pop($gFunction);
}

function DisplayMain() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if ($gFunc == 'hash') {
        HashAdd();
        $gFunc = 'xxx';
    }

    if ($gArea == 'bidders') {
        DisplayBidders();
    } elseif ($gArea == 'categories') {
        DisplayCategories();
    } elseif ($gArea == 'dates') {
        DisplayDates();
    } elseif ($gArea == 'financial') {
        DisplayFinancial();
    } elseif ($gArea == 'items') {
        DisplayItems();
    } elseif ($gArea == 'mail') {
        DisplayMail();
    } elseif ($gArea == 'packages') {
        DisplayPackages();
    } elseif ($gArea == 'showbids') {
        ShowBids();
    } elseif ($gArea == 'spiritual') {
        DisplaySpiritual();
    } elseif ($gArea == 'topbids') {
        DisplayTopBids();
    } elseif ($gFunc == 'users') {
        UserManager('control');
    } elseif ($gFunc == 'privileges') {
        UserManager('privileges');
    } elseif ($gFunc == 'source') {
        SourceDisplay();
    } else {
        printf("User: %s<br>", $gUserName);
        if( $_SESSION['level'] >= $gAccessNameToLevel['control'] ) {
            echo "<div class=control>";
            echo "<h3>Control User Features";
            if (! UserManager('authorized', 'control')) {
                echo " - Disabled</h3>";
            } else {
                echo "</h3>";
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
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value='Reset Bids'>";

                echo "<input type=button onclick=\"setValue('func','display');addAction('Debug');\" value=\"Debug\">";
            }
            echo "</div>";
        }

        if( $_SESSION['level'] >= $gAccessNameToLevel['admin'] ) {
            echo "<div class=admin>";
            echo "<h3>Admin User Features";
            if (!UserManager('authorized', 'admin')) {
                echo " - Disabled</h3>";
            } else {
                echo "</h3>";

                $jsx = array();
                $jsx[] = "setValue('area','categories')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Categories>";

                $jsx = array();
                $jsx[] = "setValue('area','packages')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Packages>";

                $jsx = array();
                $jsx[] = "setValue('area','dates')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Dates>";

                $jsx = array();
                $jsx[] = "setValue('area','items')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Items>";

                $jsx = array();
                $jsx[] = "setValue('area','bypass')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Bypass>";
                }
            echo "</div>";
            echo "<br>";
        }

        if( $_SESSION['level'] >= $gAccessNameToLevel['office'] ) {
            echo "<div class=office>";
            echo "<h3>User Features";
            if (!UserManager('authorized', 'office')) {
                echo " - Disabled</h3>";
            } else {
                echo "</h3>";

                echo "<input type=button onclick=\"setValue('func','Back');addAction('Main');\" value=Refresh>";

                $jsx = array();
                $jsx[] = "setValue('area','bidders')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=Bidders>";

                $jsx = array();
                $jsx[] = "setValue('area','topbids')";
                $jsx[] = "addAction('Main')";
                $js = sprintf("onClick=\"%s\"", join(';', $jsx));
                echo "<input type=button $js value=\"Top Bids\">";

                echo "<ul>";

                DoQuery("select id from items");
                printf("<li># of items: %d</li>", $gPDO_num_rows);

                DoQuery("select distinct email from bidders");
                printf("<li># of bidders: %d</li>", $gPDO_num_rows);

                $stmt = DoQuery("select count(id) from bids");
                list( $num ) = $stmt->fetch(PDO::FETCH_BOTH);
                printf("<li># of bids: %d</li>", $num);

                DoQuery("select distinct itemId from bids");
                printf("<li># of items with bids: %d</li>", $gPDO_num_rows);

                $v1 = array();
                $stmt = DoQuery("select id from items");
                while (list( $id ) = $stmt->fetch(PDO::FETCH_BOTH)) {
                    $v1[] = $id;
                }
                $total = 0;
                foreach ($v1 as $itemId) {
                    $stmt = DoQuery("select max( bid ) from bids where itemId = $itemId");
                    list( $bid ) = $stmt->fetch(PDO::FETCH_BOTH);
                    $total += $bid;
                }
                printf("<li>Sum of winning bids: \$ %s</li>", number_format($total, 2));

                echo "</ul>";
            }
            echo "</div>";
            echo "<br>";
        }

        echo "<br>";
        echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";
    }

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayPackages() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if (!array_key_exists(0, $gCategories)) {
        $gPackages[0] = '__Unassigned';
    }

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','DisplayPackages')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<table class=sortable>";

    echo "<tr>";
    echo "<th>Label</th>";
    echo "<th># of Items</th>";
    echo "<th>Action</th>";
    echo "</tr>\n";

    foreach ($gPackages as $id => $label) {
        $tag = MakeTag("pkg_$id");

        echo "<tr>";
        $opt = ( $id == 0 ) ? "disabled" : "";
        echo "<td><input type=text $tag $opt size=40 value=\"$label\" onChange=\"addField('$id');toggleBgRed('update');\"></td>";
        $stmt = DoQuery("select count(id) from items where itemPackage = '$id'");
        list($num) = $stmt->fetch(PDO::FETCH_BOTH);
        echo "<td class=c>$num</td>";
        echo "<td class=c>";

        if ($id == 0) {
            echo "&nbsp;";
        } else {
            $jsx = array();
            $jsx[] = "setValue('area','package')";
            $jsx[] = "setValue('from','DisplayPackages')";
            $jsx[] = "addField('$id')";
            $jsx[] = "setValue('func','delete')";
            $txt = sprintf("Are you sure you want to delete the package $label?");
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Del $tag $js>";
        }

        echo "</td>";
        echo "</tr>";
    }

    $id = 0;
    $label = "-- Enter new package name here --";
    $tag = MakeTag("pkg_$id");

    echo "<tr>";
    echo "<td><input type=text $tag size=40 value=\"$label\" onclick=\"this.select();\" onChange=\"toggleBgRed('add_$id');\"></td>";
    echo "<td class=c>&nbsp;</td>";
    echo "<td class=c>";

    $tag = MakeTag("add_$id");
    $jsx = array();
    $jsx[] = "setValue('area','package')";
    $jsx[] = "setValue('from','DisplayPackages')";
    $jsx[] = "addField('$id')";
    $jsx[] = "setValue('func','add')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Add $tag $js>";
    echo "</td>";
    echo "</tr>";

    echo "</table>";

    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function DisplaySpiritual() {
    include( 'globals.php');
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $ts = time() + $time_offset;
    $today = date('j-M-Y', $ts);

    $gFunc = __FUNCTION__;

    $ok_to_edit = UserManager('authorized', 'office');

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    $jsx = array();
    $jsx[] = "setValue('area','spiritual')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Refresh>";

    $jsx = array();
    $jsx[] = "setValue('area','spiritual')";
    $jsx[] = "addAction('Download')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Download>";

    $jsx = array();
    $jsx[] = "setValue('area','financial')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
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
    if ($ok_to_edit) {
        echo "<th>Action</th>";
    }
    echo "</tr>$lf";

    DoQuery("select * from pledges where pledgeType = $PledgeTypeSpiritual order by lastName asc, firstName asc");
    $i = 0;
    while ($rec = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
        foreach ($rec as $key => $val) {
            $$key = $val;
        }
        $ts = strtotime($timestamp) + $time_offset;
        $dmy = date('j-M-Y', $ts);
        $hl = ( $today == $dmy ) ? "class=today" : "";
        $tmp = preg_split('/,/', $pledgeIds, NULL, PREG_SPLIT_NO_EMPTY);
        $phone = FormatPhone($phone);
        $mail = 1;
        if (count($tmp)) {
            foreach ($tmp as $pid) {
                $desc = $gSpiritIDtoDesc[$pid];
                switch ($gSpiritIDtoType[$pid]) {
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
                printf("  <td $hl>%s, %s</td>$lf", $lastName, $firstName);
                echo "  <td $hl>$phone</td>$lf";
                echo "  <td $hl>" . $email . "</td>$lf";
                if ($ok_to_edit) {
                    echo "<td $hl>$lf";
                    $name = sprintf("%s %s", $firstName, $lastName);
                    $jsx = array();
                    $jsx[] = "setValue('area','$gArea')";
                    $jsx[] = "setValue('from','DisplaySpiritual')";
                    $jsx[] = "setValue('func','delete')";
                    $jsx[] = sprintf("setValue('id','%d')", $id);
                    $jsx[] = "addField('$pid')";
                    $txt = sprintf("Are you sure you want to delete %s's pledge to %s?", $name, $desc);
                    $str = CVT_Str_to_Overlib($txt);
                    $jsx[] = sprintf("myConfirm('%s')", $str);
                    $js = sprintf("onclick=\"%s\"", join(';', $jsx));
                    echo "<input type=button value=Delete $js>$lf";

                    if ($mail) {
                        $jsx = array();
                        $jsx[] = "setValue('area','$gArea')";
                        $jsx[] = "setValue('from','DisplaySpiritual')";
                        $jsx[] = "setValue('func','mail')";
                        $jsx[] = sprintf("setValue('id','%d')", $id);
                        $txt = sprintf("Are you sure you want to resend the confirmation for %s's donation made on %s?", $name, date('j-M-Y h:i A', $ts));
                        $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
                        $js = sprintf("onclick=\"%s\"", join(';', $jsx));
                        echo "<input type=button value=Mail $js>$lf";
                        $mail = 0;
                    }
                    echo "</td>$lf";
                }
                echo "</tr>$lf";
            }
        }
        if (!empty($rec['pledgeOther'])) {
            $desc = $rec['pledgeOther'];
            $pid = 0;
            $i++;
            echo "<tr>$lf";
            echo "  <td $hl>$i</td>$lf";
            echo "  <td $hl>Other</td>$lf";
            echo "  <td $hl class=mitzvah>$desc</td>$lf";
            printf("  <td $hl>%s, %s</td>$lf", $lastName, $firstName);
            echo "  <td $hl>$phone</td>$lf";
            echo "  <td $hl>" . $email . "</td>$lf";
            if ($ok_to_edit) {
                echo "<td $hl>$lf";
                $name = sprintf("%s %s", $firstName, $lastName);
                $jsx = array();
                $jsx[] = "setValue('area','$gArea')";
                $jsx[] = "setValue('from','DisplaySpiritual')";
                $jsx[] = "setValue('func','delete')";
                $jsx[] = sprintf("setValue('id','%d')", $id);
                $jsx[] = "addField('$pid')";
                $txt = "Are you sure you want to delete ${name}'s other pledge?";
                $str = CVT_Str_to_Overlib($txt);
                $jsx[] = sprintf("myConfirm('%s')", $str);
                $js = sprintf("onclick=\"%s\"", join(';', $jsx));
                echo "<input type=button value=Delete $js>$lf";

                if ($mail) {
                    $jsx = array();
                    $jsx[] = "setValue('area','$gArea')";
                    $jsx[] = "setValue('from','DisplaySpiritual')";
                    $jsx[] = "setValue('func','mail')";
                    $jsx[] = sprintf("setValue('id','%d')", $id);
                    $txt = sprintf("Are you sure you want to resend the confirmation for %s\'s donation made on %s?", $name, date('j-M-Y h:i A', $ts));
                    $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
                    $js = sprintf("onclick=\"%s\"", join(';', $jsx));
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

    if ($gTrace)
        array_pop($gFunction);
}

function DisplaySpiritualXXX() {
    include( 'globals.php');
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=CommonV2>";

    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    $jsx = array();
    $jsx[] = "setValue('area','spiritual')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Refresh>";

    $jsx = array();
    $jsx[] = "setValue('area','financial')";
    $jsx[] = "addAction('Main')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button $js value=Financial>";

    echo "<br>";
    echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

    DoQuery("select * from pledges where pledgeType = $PledgeTypeSpiritual");
    $hist = array();
    $other = array();
    $people = array();

    foreach ($gSpiritIDtoDesc as $id => $desc) {
        $hist[$id] = 0;
    }

    while ($rec = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
        $str = FormatPhone($rec['phone']);
        $tmp = preg_split('/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY);
        if (count($tmp)) {
            foreach ($tmp as $id) {
                $hist[$id] ++;
                $people[$id][] = sprintf("%s, %s: %s", $rec['lastName'], $rec['firstName'], $str);
            }
        }
        if (!empty($rec['pledgeOther'])) {
            $desc = $rec['pledgeOther'];
            if (empty($other[$desc])) {
                $other[$desc] = 0;
                $other[$desc] ++;
                $people[$desc][] = sprintf("%s, %s: %s", $rec['lastName'], $rec['firstName'], $str);
            }
        }
    }

    arsort($hist);

    echo "<ul><li>The columns are sortable by clicking on their header</li></ul>";
    echo "<table class=sortable>";
    echo "<tr>";
    echo "  <th class=mitzvah>Mitzvah</th>";
    echo "  <th># Selected</th>";
    echo "</tr>";

    foreach ($hist as $id => $count) {
        if (empty($count))
            continue;
        echo "<tr>";
        printf("<td class=mitzvah>%s</td>", $gSpiritIDtoDesc[$id]);
        echo "<td class=c>";
        $tag = number_format($count, 0);
        $str = join('<br>', $people[$id]);
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
    foreach ($other as $desc => $count) {
        echo "<tr>";
        printf("<td class=mitzvah>%s (other)</td>", $desc);
        echo "<td class=c>";
        $tag = number_format($count, 0);
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

    if ($gTrace)
        array_pop($gFunction);
}

function DisplayTopBids() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $items = array();
    $stmt = DoQuery("select * from items order by itemTitle asc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $items[$id] = $row;
    }

    $bidders = array();
    $stmt = DoQuery("select * from bidders");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $bidders[$id] = $row;
    }

    $with_bids = array();
    $stmt = DoQuery("select distinct itemId from bids order by bid desc");
    while (list( $iid ) = $stmt->fetch(PDO::FETCH_NUM)) {
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
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
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
    foreach ($with_bids as $x => $iid) {
        $stmt = DoQuery("select * from bids where itemId = $iid order by bid desc");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sold = ( $items[$iid]['status'] == $gStatusClosed ) ? "sold_" : "";

        printf("<td class=${sold}c>%d</td>", $iid);

        printf("<td class=${sold}left>%s</td>", $items[$iid]['itemTitle']);

        $bidder_id = $row['bidderId'];
        printf("<td class=${sold}left>%s, %s</td>", $bidders[$bidder_id]['last'], $bidders[$bidder_id]['first']);

        printf("<td class=${sold}right>\$ %s</td>", number_format($row['bid'], 2));

        printf("<td class=${sold}c>%d</td>", $gPDO_num_rows);
        $tot_bids += $gPDO_num_rows;
        echo "</tr>\n";
        $sum += $row['bid'];
    }
    echo "<tfoot>";
    echo "<tr>";
    echo "<th colspan=3 class=right>Total</th>";
    printf("<th class=right>\$ %s</th>", number_format($sum, 2));
    printf("<th>%d</th>", $tot_bids);
    echo "</tr>";
    echo "</tfoot>";
    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function EditItem() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $iid = $_POST['id'];
    $gFunc = __FUNCTION__;
    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','EditItem')";
    $jsx[] = "setValue('id','$iid')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<table>";

    echo "<tr>";
    echo "<th>Fields</th>";
    echo "<th>Value</th>";
    echo "</tr>";

    $stmt = DoQuery("show fields from items");
    $fields = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $label = $row['Field'];
        if ($label == "id")
            continue;
        $fields[] = $row['Field'];
    }

    $stmt = DoQuery("select * from items where id = '$iid'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach ($fields as $fld) {
        $tag = MakeTag('fld_' . $fld);
        echo "<tr>";
        $hdr = str_replace('item', '', $fld);
        echo "<th>$hdr</th>";
        if ($fld == "itemCategory") {
            $cid = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<select $tag $js>";
            if ($cid == 0) {
                echo "<option value=0 selected>-- Click Here --</option>";
            }
            foreach ($gCategories as $id => $label) {
                $selected = ( $id == $cid ) ? "selected" : "";
                echo "<option value=$id $selected>$label</option>";
            }
            echo "</select></td>";
        } elseif ($fld == "itemPackage") {
            $pid = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<select $tag $js>";
            if ($cid == 0) {
                echo "<option value=0 selected>-- Click Here --</option>";
            }
            foreach ($gPackages as $id => $label) {
                $selected = ( $id == $pid ) ? "selected" : "";
                echo "<option value=$id $selected>$label</option>";
            }
            echo "</select></td>";
        } elseif ($fld == "itemIsLive") {
            $live = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            if ($live) {
                echo "<input type=radio $tag value=1 checked>Live";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<input type=radio $tag $js value=0>Silent";
            } else {
                echo "<input type=radio $tag $js value=1>Live";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<input type=radio $tag value=0 checked>Silent";
            }
            echo "</td>";
        } elseif ($fld == "itemIsCert") {
            $live = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            if ($live) {
                echo "<input type=radio $tag $js value=0>Item";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<input type=radio $tag value=1 checked>Certificate";
            } else {
                echo "<input type=radio $tag value=0 checked>Item";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<input type=radio $tag $js value=1>Certificate";
            }
            echo "</td>";
        } elseif ($fld == "status") {
            $status = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<select $tag $js>";
            foreach ($gStatus as $val => $label) {
                $selected = ( $val == $status ) ? "selected" : "";
                echo "<option value=$val $selected>$label</option>";
            }
            echo "</select></td>";
        } elseif ($fld == "itemDesc" || $fld == "notes") {
            $val = $row[$fld];
            $status = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<textarea $tag $js cols=101>";
            echo "$val";
            echo "</textarea></td>";
        } else {
            $val = $row[$fld];
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<td><input type=text size=100 $tag $js value=\"$val\"></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function EditManager() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if ($gArea == 'category') {
        EditCategory();
    } elseif ($gArea == 'item') {
        EditItem();
    }

    if ($gTrace)
        array_pop($gFunction);
}

function ExcelItems() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-Auction-Items-$str.csv");

    $body = array();
    DoQuery("show fields from items");
    $fields = array();
    $types = array();
    while ($row = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
        $label = $row['Field'];
        $fields[] = '"' . $row['Field'] . '"';
        $types[$label] = $row['Type'];
    }

    $body[] = join(',', $fields);

    $outer = DoQuery("select * from items order by id asc");
    while ($row = $outer->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $inner = DoQuery("select max(bid) from bids where itemId = $id");
        list( $bid) = $inner->fetch(PDO::FETCH_NUM);
        $line = array();
        foreach ($row as $x => $fld) {
            if ($x == "itemCategory") {
                $fld = $gCategories[$fld];
            } elseif ($x == "itemIsLive") {
                $fld = ($fld) ? "Live" : "Silent";
            } elseif ($x == 'status') {
                $fld = $gStatus[$fld];
            } elseif ($x == 'bidCurrent') {
                $fld = $bid;
            }
            if ($types[$x] == "float") {
                $line[] = '"$ ' . number_format($fld, 2) . '"';
            } else {
                $line[] = '"' . $fld . '"';
            }
        }
        $body[] = join(',', $line);
    }

    echo join("\n", $body);
    exit;

    if ($gTrace)
        array_pop($gFunction);
}

function ExcelSpiritual() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-HH-Spiritual-Pledges-$str.csv");

    $body = array();
    $line = array('"#"', '"Time"', '"Category"', '"Mitzvah"', '"Name"', '"Phone"', '"E-Mail"');
    $body[] = join(',', $line);

    DoQuery("select * from pledges where pledgeType = $PledgeTypeSpiritual order by lastName asc, firstName asc");
    $i = 0;
    while ($rec = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
        $tmp = preg_split('/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY);
        $name = sprintf("%s, %s", $rec['lastName'], $rec['firstName']);
        $ts = strtotime($rec['timestamp']) + $time_offset;
        $time = date('j-M-Y h:i A', $ts);
        $phone = FormatPhone($rec['phone']);
        if (count($tmp)) {
            foreach ($tmp as $id) {
                switch ($gSpiritIDtoType[$id]) {
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
                $body[] = join(',', $line);
            }
        }
        if (!empty($rec['pledgeOther'])) {
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
            $body[] = join(',', $line);
        }
    }

    echo join("\n", $body);
    exit;

    if ($gTrace)
        array_pop($gFunction);
}

function HashAdd() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    DoQuery("select id, hash from bidders where hash is NULL");
    if ($gPDO_num_rows) {
        $outer = $mysql_result;
        while (list( $bidder_id, $hash ) = mysql_fetch_array($outer)) {
            if (empty($hash)) {
                DoQuery("start transaction");
                $get_new_hash = 1;
                while ($get_new_hash) {
                    $random_hash = substr(md5(uniqid(rand(), true)), 8, 6); // 6 characters long
                    DoQuery("select * from bidders where hash = '$random_hash'");
                    if (!$gPDO_num_rows) {
                        DoQuery("update bidders set hash = '$random_hash' where id = $bidder_id");
                        $get_new_hash = 0;
                    }
                }
            }
            DoQuery("commit");
        }
    }
    if ($gTrace)
        array_pop($gFunction);
}

function LocalInit() {
    include( 'globals.php' );
    $open_bypass = "bjbaim";

    $gGala = preg_match("/$open_bypass/", $_SERVER['QUERY_STRING']);

    if( $gUserId > 0 ) {
        $stmt = DoQuery("select debug from users where userid = $gUserId");
        list($val) = $stmt->fetch(PDO::FETCH_NUM);
    } else {
        $val = 0;
    }
    $gDebug = $gTrace = $_SESSION['debug'] = $val;

    $force = 0;
    if($force && preg_match('/syzygy/', $_SERVER['QUERY_STRING'])) {
        $stmt = DoQuery('show tables');
        while (list($table) = $stmt->fetch(PDO::FETCH_NUM)) {
            DoQuery("truncate $table");
        }
    }

    if (array_key_exists('action', $_POST)) {
        $gAction = $_POST['action'];
    } else {
        $tmp = preg_split("/&/", $_SERVER['QUERY_STRING'], NULL, PREG_SPLIT_NO_EMPTY);
        foreach ($tmp as $str) {
            if (preg_match('/=/', $str)) {
                list( $key, $val ) = preg_split("/=/", $str, NULL, PREG_SPLIT_NO_EMPTY);
                if ($key == 'action') {
                    $gAction = $val;
                } elseif ($key == 'key') {
                    $gResetKey = $val;
                }
            } else {
                if ($tmp == 'bozo') {
                    $gDebug = 1;
                    $_SESSION['debug'] = 1;
                }
            }
        }
    }

    $gFrom = array_key_exists('from', $_POST) ? $_POST['from'] : '';
    $gSourceCode = $_SERVER['SCRIPT_NAME'];
    
    // id=(nn) allows quick access to a prior bidder for an item
      $gPreSelected = ( preg_match( '/id=(\d+)/', $_SERVER['QUERY_STRING'], $matches ) ) ? $matches[1] : 0;
      
      if( $gPreSelected > 0 ) {
      $tmp = preg_match( '/(.+)\?(.+)/', $_SERVER['QUERY_STRING'], $matches );
      }

    if (preg_match("/^\//", $gSourceCode)) {
        $gSourceCode = substr($gSourceCode, 1);
    }
    
    if( $gGala ) {
        if( preg_match( '/\?/', $gSourceCode ) ) {
            $gSourceCode .= "&" . $open_bypass;
        } else {
            $gSourceCode .= "?" . $open_bypass;
        }
    }

    $gFunction = array();

    $date_server = new DateTime('2000-01-01');
    $date_calif = new DateTime('2000-01-01', new DateTimeZone('America/Los_Angeles'));
    $time_offset = $date_server->format('U') - $date_calif->format('U');

#	DoQuery( "set transaction isolation level serializable" );

    $gAccessNameToLevel = array();
    $gAccessNameEnabled = array();
    $gAccessLevels = array();
    $gCategories = array();
    $gPackages = array();

    $gCategories[0] = '__Unassigned';
    $gPackages[0] = '__Unassigned';

    $stmt = DoQuery('select id, label from categories order by label');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gCategories[$row['id']] = $row['label'];
    }
    asort($gCategories);

    $stmt = DoQuery('select id, label from packages order by label');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gPackages[$row['id']] = $row['label'];
    }

    foreach (array('open', 'close', 'mail', 'auction') as $label) {
        $stmt = DoQuery('select date from dates where label = :label', [':label' => $label]);
        if ($stmt->rowCount() == 0) {
            $val = ( $label == 'mail' ) ? 0 : time()+ 60*60*24*30; //Add one month today
            DoQuery('insert into dates set label = :label, date = :date', [':label' => $label, ':date' => $val]);
            $gAuctionYear = time(); //Add one month today
        } elseif ($label == 'close') {
            list( $gAuctionYear ) = $stmt->fetch(PDO::FETCH_BOTH);
        }
    }

    $stmt = DoQuery('select * from privileges order by level desc');
    if ($stmt->rowCount() == 0) {
        $query = "insert into privileges set name = :name, level = :level, enabled = :enabled";
        DoQuery($query, [':name' => 'control', ':level' => 500, ':enabled' => 1]);
        DoQuery($query, [':name' => 'admin', ':level' => 400, ':enabled' => 0]);
        DoQuery($query, [':name' => 'office', ':level' => 300, ':enabled' => 0]);
        $stmt = DoQuery('select * from privileges order by level desc');
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gAccessNameToId[$row['name']] = $row['id'];
        $gAccessNameToLevel[$row['name']] = $row['level'];
        $gAccessNameEnabled[$row['name']] = $row['enabled'];
        $gAccessLevelEnabled[$row['level']] = $row['enabled'];
        $gAccessLevels[] = $row['name'];
    }

    $yyyy = date('Y', $gAuctionYear);
    $gTitle = $yyyy . " " . SITETITLE;
}

function LoginMain() {
    include 'globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    ?>
    <div class="container">

        <div class="row">

            <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
                <h2>Please Login</h2>
                <p><input type=button value="Forgot your password?" tabindex="99"
    <?php
    $jsx = array();
    $jsx[] = "setValue('area','display')";
    $jsx[] = "addAction('forgot')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo $js . ">";
    ?> 
                </p>
                <hr>

                    <?php
                    //check for any errors
                    if (isset($gError)) {
                        foreach ($gError as $error) {
                            echo '<p class="bg-danger">' . $error . '</p>';
                        }
                    }

                    if (isset($_GET['action'])) {

                        //check the action
                        switch ($_GET['action']) {
                            case 'active':
                                echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                                break;
                            case 'reset':
                                echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
                                break;
                            case 'resetAccount':
                                echo "<h2 class='bg-success'>Password changed, you may now login.</h2>";
                                break;
                        }
                    }
                    ?>

                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php
                if (isset($gError)) {
                    echo htmlspecialchars($_POST['username'], ENT_QUOTES);
                }
                ?>" tabindex="1">
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="2">
                </div>


                <hr>
                <div class="row">
                    <div class="col-xs-6 col-md-6">
                        <input type=button value="Login"
    <?php
    $jsx = array();
    $jsx[] = "addAction('verify')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo $js
    ?> 
                               class="btn btn-primary btn-block btn-lg" tabindex="5">
                    </div>
                </div>
            </div>
        </div>



    </div>

    <?php
}

function MailUpdate() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $val = $_POST['fields'];
    DoQuery("update dates set date = $val where label = 'mail'");

    if ($gTrace)
        array_pop($gFunction);
}

function PayPal() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    foreach (array('amount', 'email', 'firstName', 'lastName', 'phone') as $fld) {
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

    if ($gTrace)
        array_pop($gFunction);
}

function PledgeEdit() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    DoQuery("select * from pledges where id = '$id'");
    $rec = mysql_fetch_assoc($GLOBALS['mysql_result']);

    echo "<input type=button value=Back onclick=\"setValue('from', 'PledgeEdit');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$gArea')";
    $jsx[] = "setValue('from','PledgeEdit')";
    $jsx[] = "setValue('id','$id')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<div class=CommonV2>";

    if ($gArea == 'financial') {
        echo "<table>";
        echo "<tr><th>Field</th><th class=val>Value</th></tr>";
        $fields = array('firstName' => 'First Name',
            'lastName' => 'Last Name',
            'phone' => 'Phone',
            'email' => 'E-mail');
        foreach ($fields as $key => $label) {
            echo "<tr>";
            echo "<td>$label</td>";
            $jsx = array();
            $jsx[] = "setValue('area','$gArea')";
            $jsx[] = "setValue('from','PledgeEdit')";
            $jsx[] = "addField('$key')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onKeyDown=\"%s\"", join(';', $jsx));
            printf("<td><input type=text size=50 name=%s value=\"%s\" $js></td>", $key, $rec[$key]);
            echo "</tr>";
        }
        $jsx = array();
        $jsx[] = "setValue('area','$gArea')";
        $jsx[] = "setValue('from','PledgeEdit')";
        $jsx[] = "addField('amount')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onKeyDown=\"%s\"", join(';', $jsx));
        echo "<tr>";
        echo "<td>Amount</td>";
        printf("<td><input type=text size=50 name=amount value=\"\$ %s\" $js></td>", number_format($rec['amount'], 2));
        echo "</tr>";

        echo "<tr>";
        echo "<td>Payment Method</td>";
        $tag = MakeTag('paymentMethod');
        $types = array($PaymentCredit => 'Credit', $PaymentCheck => 'Check', $PaymentCall => 'Call');
        $jsx = array();
        $jsx[] = "setValue('area','$gArea')";
        $jsx[] = "setValue('from','PledgeEdit')";
        $jsx[] = "addField('paymentMethod')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        echo "<td><select $tag $js>";
        foreach ($types as $val => $label) {
            $selected = ( $val == $rec['paymentMethod'] ) ? "selected" : "";
            echo "<option value=$val $selected>$label</option>";
        }
        echo "</select></td>";
        echo "</tr>";
        echo "</table>";
    }

    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function PledgeStore() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $args = array();
    $tmp = preg_split('/\|/', $_POST['fields']);
    foreach ($tmp as $nvp) {
        list( $name, $value ) = preg_split('/=/', $nvp);
        $_SESSION[$name] = $value;
        if ($name == 'pledgeIds') {
            $pledgeIds = array();
            $tmp2 = preg_split('/,/', $value);
            foreach ($tmp2 as $xx) {
                list( $key, $id ) = preg_split('/_/', $xx);
                if ($key == "id") {
                    $pledgeIds[] = $id;
                }
            }
            $args[] = sprintf("pledgeIds = '%s'", join(',', $pledgeIds));
        } elseif ($name == "phone") {
            $args[] = "phone = " . preg_replace("/[^0-9]/", "", $value);
        } else {
            $args[] = sprintf("%s = '%s'", $name, addslashes($value));
        }
    }

    if ($gFrom == 'financial') {
        $args[] = "pledgeType = $PledgeTypeFinancial";
        $args[] = sprintf("paymentMethod = '%d'", $_POST['paynow']);
    } else {
        $args[] = "pledgeType = $PledgeTypeSpiritual";
    }

    $query = "insert into pledges set " . join(',', $args);
    DoQuery($query);
    $id = mysql_insert_id();

    if ($gTrace)
        array_pop($gFunction);
    return $id;
}

function PledgeUpdate() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $from = $_POST['from'];

    if ($gFunc == 'update') {
        $tmp = preg_split('/,/', $_POST['fields']);  // This is what was touched
        $keys = array_unique($tmp);
        $qx = array();
        foreach ($keys as $key) {
            $val = CleanString($_POST[$key]);
            if ($key == 'phone') {
                $val = preg_replace("/[^0-9]/", "", $val);
            } elseif ($key == 'amount') {
                $val = preg_replace("/[\$,]/", "", $val);
            }
            $qx[] = sprintf("`%s` = '%s'", $key, $val);
        }
        $query = sprintf("update pledges set %s where id = $id", join(',', $qx));
        DoQuery($query);
    } elseif ($gFunc == 'delete') {
        if ($from == "DisplayFinancial") {
            $query = sprintf("delete from pledges where id = $id");
            DoQuery($query);
        } elseif ($from == "DisplaySpiritual") {
            $pid = $_POST['fields'];
            DoQuery("select * from pledges where id = $id");
            $rec = mysql_fetch_assoc($GLOBALS['mysql_result']);
            $pids = preg_split('/,/', $rec['pledgeIds'], NULL, PREG_SPLIT_NO_EMPTY);
            $other = $rec['pledgeOther'];

            if ($pid) { // Delete a defined pledge
                if (count($pids) == 1) {  // This was the only defined pledgeId
                    if ($other == "") { // And I have no "other" pledge
                        $query = "delete from pledges where id = $id";
                    } else {
                        foreach ($pids as $key => $value) {
                            if ($pid == $value) {
                                unset($pids[$key]);
                            }
                        }
                        $query = sprintf("update pledges set pledgeIds = '%s' where id = $id", join(',', $pids));
                    }
                } else {
                    foreach ($pids as $key => $value) {
                        if ($pid == $value) {
                            unset($pids[$key]);
                        }
                    }
                    $query = sprintf("update pledges set pledgeIds = '%s' where id = $id", join(',', $pids));
                }
            } else { // I'm deleting an "other" pledge
                if (count($pids) == 0) { // Nothing left, delete the pledge
                    $query = "delete from pledges where id = $id";
                } else {
                    $query = "update pledges set pledgeOther = '' where id = $id";
                }
            }
            DoQuery($query);
        }
    } elseif ($gFunc == 'mail') {
        SendConfirmation($id);
    }

    if ($gTrace)
        array_pop($gFunction);
}

function SendConfirmation($bidder_id, $item_id, $bid_id, $send_type) {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = sprintf( "%s(%d,%d,%d,%d)", __FUNCTION__, $bidder_id, $item_id, $bid_id, $send_type);
        Logger();
    }

    $bidder = DoQuery("select * from bidders where id = $bidder_id")->fetch(PDO::FETCH_ASSOC);
    $item = DoQuery("select * from items where id = $item_id")->fetch(PDO::FETCH_ASSOC);
    $bid = DoQuery("select * from bids where id = $bid_id")->fetch(PDO::FETCH_ASSOC);

    switch ($send_type) {
        case ( $gSendOld ):
            $subject = "CBI Auction: You've been outbid";
            break;

        case( $gSendTop ):
            $subject = "CBI Auction: Bid Confirmation";
            break;

        case( $gSendBought ):
            $subject = "CBI Auction: Purchase Confirmation";
            break;

        case ( $gSendOldBought ):
            $subject = "CBI Auction: You've been outbid";
            break;
    }
    $message = Swift_Message::newInstance($subject);

    $firstName = $bidder['first'];
    $lastName = $bidder['last'];
    $email = $bidder['email'];

    $html = $text = array();
    $cid = $message->embed(Swift_Image::fromPath('assets/CBI_ner_tamid.png'));

    $html[] = "<html><head></head><body>";
    $html[] = '<img src="' . $cid . '" alt="Image" />';

    $html[] = "Congregation B'nai Israel";
    $text[] = "Congregation B'nai Israel";

    $yyyy = date('Y', $gAuctionYear);
    $html[] = "$yyyy CBI Annual Gala Auction";
    $text[] = "$yyyy CBI Annual Gala Auction";

    $html[] = "";
    $text[] = "";

    $html[] = sprintf("Dear %s %s,", $firstName, $lastName);
    $text[] = sprintf("Dear %s %s,", $firstName, $lastName);

    $html[] = "";
    $text[] = "";

    if ($send_type == $gSendTop) {
        $html[] = sprintf("You now have the top bid of \$ %s for the following item:", number_format($bid['bid'], 2));
        $text[] = sprintf("You now have the top bid of \$ %s for the following item:", number_format($bid['bid'], 2));
    } elseif ($send_type == $gSendOld) {
        $html[] = sprintf("You have been replaced as the top bidder for the following item:");
        $text[] = sprintf("You have been replaced as the top bidder for the following item:");
    } elseif ($send_type == $gSendBought) {
        $html[] = sprintf("Your have just purchased the following item with a bid of \$ %s:", number_format($bid['bid'], 2));
        $text[] = sprintf("Your have just purchased the following item with a bid of \$ %s:", number_format($bid['bid'], 2));
    } elseif ($send_type == $gSendOldBought) {
        $html[] = sprintf("We're sorry, somebody else just purchased the following item:");
        $text[] = sprintf("We're sorry, somebody else just purchased the following item:");
    }

    $html[] = "";
    $text[] = "";

    $html[] = sprintf("&nbsp;&nbsp;&nbsp;%s", $item['itemTitle']);
    $text[] = sprintf("   %s", $item['itemTitle']);

    $html[] = "";
    $text[] = "";

    if ($send_type == $gSendBought) {
        $html[] = "Please contact Helene Coulter at hcoulter@cbi18.org or call 714-730-9693 to arrange payment and pickup of your item.";
        $text[] = "Please contact Helene Coulter at hcoulter@cbi18.org or call 714-730-9693 to arrange payment and pickup of your item.";

        $html[] = "";
        $text[] = "";
    }

    if ($send_type == $gSendOld) {
        $html[] = "Click here to view the current bid: " . DIR . $gSourceCode . "?id=$item_id";
        $text[] = "Click here to view the current bid: " . DIR . $gSourceCode . "?id=$item_id";

        $html[] = "";
        $text[] = "";
    }

    $html[] = "Click here to go to the auction site: " . DIR . $gSourceCode;
    $text[] = "Click here to go to the auction site: " . DIR . $gSourceCode;

    $html[] = "";
    $text[] = "";

    $html[] = "The CBI Annual Gala Auction Committee thanks you for your support";
    $text[] = "The CBI Annual Gala Auction Committee thanks you for your support";

    $message->setTo(array($email => "$firstName $lastName"));
    $message->setFrom(array('cbi18@cbi18.org' => 'CBI'));
    if ($send_type == $gSendBought) {
        $message->setCc(array(
            'hcoulter@cbi18.org' => 'Helene Coulter'
        ));
    }
    $message->setBcc(array(
        'bsjadler@cox.net' => 'Beth Adler'
    ));

    $message
            ->setBody(join('<br>', $html), 'text/html')
            ->addPart(join('\n', $text), 'text/plain')
    ;

    MyMail($message);

    if ($gTrace)
        array_pop($gFunction);
}

function ShowBids() {
    include( 'globals.php' );
    $gFunc = __FUNCTION__;
    if ($gTrace) {
        $gFunction[] = $gFunc;
        Logger();
    }

    $hash = $_POST['id'];
    DoQuery("select * from bidders where hash = '$hash'");
    $bidder = mysql_fetch_assoc($mysql_result);

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $jsx = array();
    $jsx[] = "setValue('area','showbids')";
    $jsx[] = "setValue('func','Back')";
    $jsx[] = "setValue('id','$hash')";
    $jsx[] = "addAction('Main')";
    $js = join(';', $jsx);
    echo "<input type=button onclick=\"$js\" value=Refresh>";

    $hash = $_POST['id'];
    DoQuery("select * from bidders where hash = '$hash'");
    $bidder = mysql_fetch_assoc($mysql_result);
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
    printf("<h3>Bids for %s %s</h3>", $bidder['first'], $bidder['last']);

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
    DoQuery("select distinct itemId from bids where bidderId = '$bidderId'");
    $my_items = array();
    while (list( $iid ) = mysql_fetch_array($mysql_result)) {
        $my_items[] = $iid;
    }
    foreach ($my_items as $iid) {
        echo "<tr>";
        echo "<td class=c>$iid</td>";
        DoQuery("select * from items where id = '$iid'");
        $item = mysql_fetch_assoc($mysql_result);
        $title = $item['itemTitle'];
        echo "<td>$title</td>";

        DoQuery("select bid from bids where itemId = $iid and bidderId = $bidderId order by bid desc");
        printf("<td class=c>%d</td>", $gPDO_num_rows);

        list( $my_bid ) = mysql_fetch_array($mysql_result);
        printf("<td class=right>\$ %s</td>", number_format($my_bid, 2));

        DoQuery("select max(bid) from bids where itemId = $iid");
        list( $top_bid ) = mysql_fetch_array($mysql_result);
        printf("<td class=right>\$ %s</td>", number_format($top_bid, 2));

        if ($item['status'] == $gStatusClosed) {
            $stat = "Sold";
            $c = "class=sold";
        } elseif ($my_bid < $top_bid) {
            $stat = "Too Low";
            $c = "class=low";
        } else {
            $stat = "Top for now";
            $c = "class=top";
        }
        echo "<td $c>$stat</td>";
        echo "</tr>\n";
    }
    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function UpdateCategories() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $tmp2 = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
    $tmp = array_unique($tmp2);

    if ($gFunc == "update") {
        foreach ($tmp as $cid) {
            $label = $_POST["cat_$cid"];
            DoQuery("update categories set label = '$label' where id = '$cid'");
        }
    } elseif ($gFunc == "delete") {
        foreach ($tmp as $cid) {
            DoQuery("delete from categories where id = '$cid'");
        }
    } elseif ($gFunc == "add") {
        $label = $_POST["cat_0"];
        $stmt = DoQuery("select * from categories where label = '$label'");
        if (! $stmt->rowCount() ) {
            DoQuery("insert into categories set label = '$label'");
        }
    }

    $gCategories = array();
    $stmt = DoQuery("select id, label from categories order by label");
    while (list( $id, $label ) = $stmt->fetch(PDO::FETCH_BOTH)) {
        $gCategories[$id] = $label;
    }
    asort($gCategories);

    $gAction = "Edit";
    $_POST['area'] = 'category';
    if ($gTrace)
        array_pop($gFunction);
}

function UpdateItem() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $iid = $_POST['id'];

    if ($gFunc == "delete") {
        DoQuery("delete from items where id = '$iid'");
    } else {
        $tmp2 = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
        $tmp = array_unique($tmp2);
        if (count($tmp)) {
            $mods = array();
            foreach ($tmp as $fld) {
                $mods[] = sprintf("`%s` = '%s'", $fld, CleanString($_POST['fld_' . $fld]));
            }
            if ($iid > 0) {
                $query = sprintf("update items set %s where id = '%s'", join(',', $mods), $iid);
            } else {
                $query = sprintf("insert into items set %s", join(',', $mods));
            }
            DoQuery($query);
        }
    }

    $gAction = "Edit";
    $_POST['area'] = 'item';
    $_POST['id'] = $iid;
    if ($gTrace)
        array_pop($gFunction);
}

function UpdatePackages() {
    include( 'globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $tmp2 = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
    $tmp = array_unique($tmp2);

    if ($gFunc == "update") {
        foreach ($tmp as $pid) {
            $label = $_POST["pkg_$pid"];
            DoQuery("update packages set label = '$label' where id = '$pid'");
        }
    } elseif ($gFunc == "delete") {
        foreach ($tmp as $pid) {
            DoQuery("delete from packages where id = '$pid'");
        }
    } elseif ($gFunc == "add") {
        $label = CleanString($_POST["pkg_0"]);
        $stmt = DoQuery("select * from packages where label = '$label'");
        if (! $stmt->rowCount() ) {
            DoQuery("insert into packages set label = '$label'");
        }
    }
    /*
      $gPackages = array();
      $gPackages[0] = '__Unassigned';
      DoQuery( "select id, label from packages order by label" );
      while( list( $id, $label ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
      $gPackages[$id] = $label;
      }
      asort( $gPackages );

      foreach( $gPackages as $pid => $label ) {
      if( $pid == 0 ) continue;
      $newpid = $pid + 100;
      DoQuery( "update items set itemPackage = $newpid where itemPackage = $pid" );
      DoQuery( "update packages set id = $newpid where id=$pid" );
      }

      $newpid = 0;
      foreach( $gPackages as $pid => $label ) {
      if( $pid == 0 ) continue;
      $newpid++;
      $tpid = $pid + 100;
      DoQuery( "update items set itemPackage = $newpid where itemPackage = $tpid" );
      DoQuery( "update packages set id = $newpid where id = $tpid" );
      }
     */
    $gPackages = array();
    $gPackages[0] = '__Unassigned';
    $stmt = DoQuery("select id, label from packages order by label");
    while (list( $id, $label ) = $stmt->fetch(PDO::FETCH_BOTH)) {
        $gPackages[$id] = $label;
    }
    asort($gPackages);

    $gAction = "Edit";
    $_POST['area'] = 'package';
    if ($gTrace)
        array_pop($gFunction);
}

function UserVerify() {
    include 'globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $gError = array();

    if (!isset($_POST['username']))
        $gError[] = "Please fill out all fields";
    if (!isset($_POST['password']))
        $gError[] = "Please fill out all fields";

    $username = $_POST['username'];
    $gAction = 'Start';
    if ($user->isValidUsername($username)) {
        if (!isset($_POST['password'])) {
            $gError[] = 'A password must be entered';
        }
        $password = $_POST['password'];

        if ($user->login($username, $password)) {
            $_SESSION['username'] = $username;
            $gAction = 'Main';
            $gUserName = $username;
            echo "gUserName set to: $username<br>";
        } else {
            $gError[] = 'Wrong username or password or your account has not been activated.';
        }
    } else {
        $gError[] = 'Usernames are required to be Alphanumeric, and between 3-16 characters long';
    }

    if ($gTrace) {
        array_pop($gFunction);
    }
}

function WriteBody() {
    include( 'globals.php' );
    echo "<body>$gLF";
    AddOverlib();
    
    echo "<div class=center>$gLF";
    echo "<img src=\"assets/CBI_ner_tamid.png\">$gLF";
    echo "<h2>$gTitle</h2>$gLF";
    echo "</div>$gLF";    
    
    if( $gDebug ) {
        echo "<script type='text/javascript'>";
        echo "createDebugWindow();";
        echo "</script>";
    }
}

function WriteHeader() {
    include( 'globals.php' );

    echo "<html>$gLF";
    echo "<head>$gLF";

    $styles = array();
    $styles[] = "/css/CommonV2.css";
    $styles[] = "admin.css";
    $styles[] = "style/bootstrap.min.css";
    $styles[] = "style/main.css";

    foreach ($styles as $style) {
        printf("<link href=\"%s\" rel=\"stylesheet\" type=\"text/css\" />$gLF", $style);
    }

    $scripts = array();
    $scripts[] = "/scripts/overlib/overlib.js";
#    $scripts[] = "/scripts/overlib/overlib_hideform.js";
    $scripts[] = "/scripts/commonv2.js";
    $scripts[] = "/scripts/sorttable.js";
    $scripts[] = "hhPledges.js";

    foreach ($scripts as $script) {
        printf("<script type=\"text/javascript\" src=\"%s\"></script>$gLF", $script);
    }
    echo "</head>$gLF";

}
