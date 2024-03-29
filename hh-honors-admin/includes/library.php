<?php
function Assign() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $assign = UserManager('authorized', 'assign');

    $honor_assigned = [];
    $honor_accepted = [];
    $member_assigned = [];
    $member_accepted = [];
    $member_declined = [];
    $member_sent = [];
    $stmt = DoQuery("select * from assignments where jyear = $gJewishYear");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $member_sent[$row['member_id']] = ($row['sent'] != NULL );
        if ($row['declined']) {
            $member_declined[$row['member_id']] = $row['honor_id'];
        } else if ($row['accepted']) {
            $honor_accepted[$row['honor_id']] = $row['member_id'];
            $honor_assigned[$row['honor_id']] = $row['member_id'];
            $member_accepted[$row['member_id']] = $row['honor_id'];
            $member_assigned[$row['member_id']] = $row['honor_id'];
        } else {
            $honor_assigned[$row['honor_id']] = $row['member_id'];
            $member_assigned[$row['member_id']] = $row['honor_id'];
        }
    }

    $stmt = DoQuery("select id, service, honor from honors order by sort");
    echo "<script type='text/javascript' id=honors-database>\n";
    while (list( $id, $service, $honor ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $qx = [];
        $qx[] = sprintf("service:'day-%s'", $service);
        $qx[] = sprintf("honor:'%s'", $honor);
        $qx[] = sprintf("selected:0");
        $qx[] = sprintf("assigned:%d", array_key_exists($id, $honor_assigned) ? $honor_assigned[$id] : 0);
        $qx[] = sprintf("accepted:%d", array_key_exists($id, $honor_accepted) ? $honor_accepted[$id] : 0);
        printf("honors_db[%d] = { %s };\n", $id, join(',', $qx));
    }
    echo "</script>";
    $stmt = DoQuery("select * from member_attributes order by id asc");
    echo "<script type='text/javascript' id=member-database>\n";
    $tot_other = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id =  $row['id'];
        $tmp = array();
        $other = 1;
        $cohen = $levi = 0;
        foreach ($row as $key => $val) {
            if ($key == "id") {
                continue;
            } elseif ($key == "ftribe" || $key == "mtribe") {
                $cohen = $cohen || ( $val == "Kohen" );
                $levi = $levi || ( $val == "Levi" );
            } else {
                $tmp[] = sprintf("%s:%d", $key, $val);
                if ($val) {
                    $other = 0;
                }
            }
        }
        if ($cohen || $levi) {
            if ($cohen) {
                $levi = 0;
            }
            $other = 0;
        }
        $tmp[] = sprintf("%s:%d", "cohen", $cohen);
        $tmp[] = sprintf("%s:%d", "levi", $levi);
        $tmp[] = sprintf("%s:%d", "other", $other);
        printf("members_db[%d] = { %s };\n", $id, join(', ', $tmp));
        $sent = array_key_exists($row['id'],  $member_sent) ? $member_sent[$id] : 0;
        $used = array_key_exists($row['id'], $member_assigned) ? $member_assigned[$id] : 0;
        $acc = array_key_exists($row['id'], $member_accepted) ? $member_accepted[$id] : 0;
        $rej = array_key_exists($row['id'], $member_declined) ? $member_declined[$id] : 0;
        printf("members_status[%d] = { selected:0, sent:%d, assigned:%d, accepted:%d, declined:%d };\n", $id, $sent, $used, $acc, $rej);
    }
    echo "</script>\n";
    $stmt_honors = DoQuery("select id, service, honor from honors order by sort");
//                while (list( $id, $last, $ff, $mf, $ft, $mt ) = $stmt_member->fetch(PDO::FETCH_NUM)) {

    $query = "select m.id, m.`Last Name`, m.`Female 1st Name`, m.`Male 1st Name`, a.ftribe, a.mtribe";
    $query .=  " from members m join member_attributes a on m.ID = a.id";
    $query .= " where m.Status not like \"Non-Member\"";
    $query .= " order by m.`Last Name` asc";
    $stmt_member = DoQuery($query);
    ?>
    <div class="container">
        <div class="assign-top">
            <input type=button onclick="setValue('func', 'assign');addAction('Main');" value="Back">
        </div>

        <div class="button-bar">
            <div class="day-buttons">
                <input type="button" value="All" onclick="myClickDay('day-all');myDisplayRefresh();"/>      
                <input type="button" id="day-rh1" value="Rosh 1" onclick="myClickDay('day-rh1');myDisplayRefresh();"/>
                <input type="button" id="day-kn" value="Kol Nidre" onclick="myClickDay('day-kn');myDisplayRefresh();"/>
                <input type="button" id="day-ykp" value="YK - PM" onclick="myClickDay('day-ykp');myDisplayRefresh();"/>
                <br />
                <input type="button" value="None" onclick="myClickDay('day-none');myDisplayRefresh();"/>      
                <input type="button" id="day-rh2" value="Rosh 2" onclick="myClickDay('day-rh2');myDisplayRefresh();"/>
                <input type="button" id="day-yka" value="YK - AM" onclick="myClickDay('day-yka');myDisplayRefresh();"/>
            </div>

            <div class="category-buttons">
                <input type="button" value="All" onclick="myClickCategory('opt-all');myDisplayRefresh();"/>      
                <input type="button" value="None" onclick="myClickCategory('opt-none');myDisplayRefresh();"/>
                <input type="button" id="opt-cohen" value="Cohen" onclick="myClickCategory('opt-cohen');myDisplayRefresh();"/>
                <input type="button" id="opt-levi" value="Levi" onclick="myClickCategory('opt-levi');myDisplayRefresh();"/>
                <input type="button" id="opt-pastpres" value="Past Pres" onclick="myClickCategory('opt-pastpres');myDisplayRefresh();"/>      
                <input type="button" id="opt-board" value="Board" onclick="myClickCategory('opt-board');myDisplayRefresh();"/>
                <input type="button" id="opt-vola" value="Vol A" onclick="myClickCategory('opt-vola');myDisplayRefresh();"/>
                <input type="button" id="opt-volb" value="Vol B" onclick="myClickCategory('opt-volb');myDisplayRefresh();"/> 
                <br />
                <input type="button" id="opt-staff" value="Staff" onclick="myClickCategory('opt-staff');myDisplayRefresh();"/>
               
                <input type="button" id="opt-memb" value="Member" onclick="myClickCategory('opt-memb');myDisplayRefresh();"/>
                <input type="button" id="opt-sharon" value="Sharon" onclick="myClickCategory('opt-sharon');myDisplayRefresh();"/>
                <input type="button" id="opt-associate" value="Assoc" onclick="myClickCategory('opt-associate');myDisplayRefresh();"/>
               
                <input type="button" id="opt-donor" value="Donor" onclick="myClickCategory('opt-donor');myDisplayRefresh();"/>
                <input type="button" id="opt-new" value="New Member" onclick="myClickCategory('opt-new');myDisplayRefresh();"/>      
                <input type="button" id="opt-other" value="Other" onclick="myClickCategory('opt-other');myDisplayRefresh();"/>      
                <input type="button" id="opt-volc" value="Vol C" onclick="myClickCategory('opt-volc');myDisplayRefresh();"/>
            </div>
            <div style="clear:both"></div>
        </div>

        <hr />

        <div class="honors-box">
            <p id=tot-honors>Honors Assigned</p>
            <div id=honors-div class=honors-div>
                <?php
                while (list( $id, $service, $honor ) = $stmt_honors->fetch(PDO::FETCH_NUM)) {
                    echo "<p id=honor_$id class='hidden' onclick=\"myClickHonor($id);myDisplayRefresh();\">$service: $honor</p>\n";
                }
                ?>
            </div>
        </div>

        <div class="mode-box">
            <p>Mode</p>
            <input id=mode-view type=button class=mode-off onclick="mySetMode('view');" value='View'>
            <?php
            if ($assign) {
                ?>
                <input id=mode-assign type=button class=mode-off onclick="mySetMode('assign');" value='Assign'>
                <br><br><br><br>
                <p>Action</p>
                <?php
                $tmp = [];
                $tmp[] = "setValue('from','Assign')";
                $tmp[] = "setValue('func','add')";
                $tmp[] = "mySaveChoices()";
                $tmp[] = "addAction('Update')";
                $js = join(';', $tmp);
                echo "<input id=action-assign type=button class=mode-assign-hidden onclick=\"$js\" value=\"Add\">";

                $tmp = [];
                $tmp[] = "setValue('from','Assign')";
                $tmp[] = "setValue('func','del')";
                $tmp[] = "mySaveChoices()";
                $txt = sprintf("Are you sure you want to delete this assignment?");
                $tmp[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
                $js = join(';', $tmp);
                echo "<input id=action-view type=button class=mode-view-hidden onclick=\"$js\" value=\"Delete\">";
            }

            if (UserManager('authorized', 'office')) {
                echo "<div id=reply-block class=action-hidden>";
                echo "<hr>";


                $tag = MakeTag('reply-amount');
                echo "<select $tag>";
                echo "<option value=0>- Amount -</option>";
                $price_points = [18, 36, 54, 72, 108, 144, 180, 270, 360, 540, 720, 1080];
                foreach ($price_points as $val) {
                    printf("<option value=%d>\$ %s</option>", $val, number_format($val));
                }
                echo "</select>";

                $tag = MakeTag('reply-method');
                echo "<select $tag>";
                $pay_by = ["- Method -", "credit", "check", "call"];
                foreach ($pay_by as $i => $val) {
                    printf("<option value=%d>%s</option>", $i, $val);
                }
                echo "</select>";
                
                $tag = MakeTag('action-reply');
                $tmp = [];
                $tmp[] = "setValue('from','Assign')";
                $tmp[] = "setValue('func','manual')";
                $tmp[] = "setValue('area','accept')";
                $tmp[] = "mySaveChoices()";
                $tmp[] = "addAction('Update')";
                $js = join(';', $tmp);
                echo "<input $tag type=button disabled onclick=\"$js\" value=\"Accept\"><br>";

                $tmp = [];
                $tmp[] = "setValue('from','Assign')";
                $tmp[] = "setValue('func','manual')";
                $tmp[] = "setValue('area','decline')";
                $tmp[] = "mySaveChoices()";
                $tmp[] = "addAction('Update')";
                $js = join(';', $tmp);
                echo "<input $tag type=button disabled onclick=\"$js\" value=\"Decline\">";
 
                echo "</div>";
            }
            if (UserManager('authorized', 'admin')) {
                echo "<hr>";
                $tmp = [];
                $tmp[] = "setValue('from','Assign')";
                $tmp[] = "setValue('func','mail')";
                $tmp[] = "setValue('area','unsent')";
                $tmp[] = "mySaveChoices()";
                $tmp[] = "addAction('Update')";
                $js = join(';', $tmp);
                echo "<div id=preview class=preview-hidden>";
                echo "<input id=action-mail type=button disabled class=mode-mail-hidden onclick=\"$js\" value=\"Mail\">";
                echo "<hr>";
                $tag = MakeTag('preview');
                echo "<input $tag type=checkbox value=1 class=mode-mail-hidden>Preview<br>";
                echo "<hr>";
                $tag = MakeTag('override');
                echo "<input $tag type=checkbox value=1 class=mode-mail-hidden>Mail<br>Override<br>";
                echo "<hr>";
                echo "</div>";
            }
            ?>
            <br><br><br><br>
            <p>Key:</p>
            <span class=assigned>&nbsp;Assigned&nbsp;</span><br>
            <span class=accepted>&nbsp;Accepted&nbsp;</span><br>
            <span class=declined>&nbsp;Declined&nbsp;</span><br>

            <?php ?>
        </div>

        <div class="member-box">
            <p id=tot-members>Members Assigned</p>
            <div id=members-div class=members-div>
                <?php
                while (list( $id, $last, $ff, $mf, $ft, $mt ) = $stmt_member->fetch(PDO::FETCH_NUM)) {
                    $str = "$last,";
                    $num_members = 0;
                    if (!empty($ff)) {
                        $num_members++;
                        if ($ft == "Kohen") {
                            $str .= " (C) $ff";
                        } elseif ($ft == "Levi") {
                            $str .= "(L) $ff";
                        } else {
                            $str .= " $ff";
                        }
                    }
                    if (!empty($mf)) {
                        $num_members++;
                        if(  $num_members > 1 ) {
                            $str .=  " and ";
                        }
                        if ($mt == "Kohen") {
                            $str .= " (C) $mf";
                        } elseif ($mt == "Levi") {
                            $str .= "(L) $mf";
                        } else {
                            $str .= " $mf";
                        }
                    }

                    echo "<p id=member_$id class='hidden' onclick=\"myClickMember($id);myDisplayRefresh();\">$str</p>\n";
                }
                ?>
            </div>
        </div>
    </div>
    </body>
    <script type='text/javascript'>
        myButtonInit();
    <?php
    $mode_set = 0;
    $tmp = preg_split('/,/', $_POST['fields']);
    foreach ($tmp as $field) {
        if (preg_match("/^day-/", $field)) {
            echo "myClickDay('" . $field . "');\n";
        } elseif (preg_match("/^opt-/", $field)) {
            echo "myClickCategory('" . $field . "');\n";
        } elseif (preg_match("/^mode/", $field)) {
            $mode_set = 1;
            $tmp2 = preg_split("/_/", $field);
            echo "mySetMode('" . $tmp2[1] . "');\n";
        }
    }
    if (!$mode_set) {
        echo "mySetMode('assign');\n";
    }
    ?>
    </script>
    </html>
    <?php
    if ($gTrace)
        array_pop($gFunction);
}

function AssignAdd() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $tmp = preg_split("/,/", $_POST['fields']);
    foreach ($tmp as $field) {
        $tmp2 = preg_split("/_/", $field);
        if ($tmp2[0] == "honor") {
            $honor_id = $tmp2[1];
        } elseif ($tmp2[0] == "member") {
            $member_id = $tmp2[1];
        }
    }
    $bypass = $_POST['bypass'];
    DoQuery("start transaction");

    $stmt = DoQuery("select honor_id from assignments where member_id = $member_id and jyear = $gJewishYear and active = 1");
    if ($gPDO_num_rows && !$bypass) {
        list( $hid ) = $stmt->fetch(PDO::FETCH_NUM);
        $stmt = DoQuery("select honor from honors where id = $hid");
        list( $honor ) = $stmt->fetch(PDO::FETCH_NUM);
        $stmt = DoQuery("select `Last Name` from members where id = $member_id");
        list( $name ) = $stmt->fetch(PDO::FETCH_NUM);
        $str2 = ucfirst($honor);
        $str = sprintf("The following honor was already assigned to the %s family:\\n\\n%s", $name, $str2);
        ?>
        <script type='text/javascript'>
            var x = window.confirm("<?php echo $str ?>");
            if (x) {
                setValue('fields', '<?php echo $_POST['fields'] ?>');
                setValue('func', 'add');
                setValue('from', 'Assign');
                setValue('bypass', 1);
                addAction('Update');
            } else {
                setValue('from', 'assign');
                setValue('fields', '<?php echo $_POST['fields'] ?>');
                addAction('Assign');
            }
        </script>
        <?php
        DoQuery("rollback");
    } else {
        $unique = 0;
        while (!$unique) {
            $random_hash = substr(md5(uniqid(rand(), true)), 8, 6); // 6 characters long
            DoQuery("select * from assignments where `hash` like '$random_hash' and jyear = $gJewishYear");
            $unique = $gPDO_num_rows == 0 ? 1 : 0;
        }
        DoQuery("insert into assignments set jyear = $gJewishYear, honor_id = $honor_id, member_id = $member_id, `hash` = '$random_hash'");
        DoQuery("commit");
    }

    if ($gTrace)
        array_pop($gFunction);
}

function AssignDel() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $tmp = preg_split("/,/", $_POST['fields']);
    foreach ($tmp as $field) {
        $tmp2 = preg_split("/_/", $field);
        if ($tmp2[0] == "honor") {
            DoQuery("delete from assignments where honor_id = $tmp2[1] and jyear = $gJewishYear");
        } elseif ($tmp2[0] == "member") {
            DoQuery("delete from assignments where member_id = $tmp2[1] and jyear = $gJewishYear");
        }
    }

    if ($gTrace)
        array_pop($gFunction);
}

function AssignRSVP() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $vx = [];
    $area = $_POST['area'];
    if ($area == 'accept') {
        $vx[] = "accepted=1";
        $vx[] = "declined=0";
    } elseif ($area == "decline") {
        $vx[] = "accepted=0";
        $vx[] = "declined=1";
    }

    $tmp = preg_split("/,/", $_POST['fields']);
    foreach ($tmp as $field) {
        $tmp2 = preg_split("/_/", $field);
        if ($tmp2[0] == "honor") {
            $honor_id = $tmp2[1];
        } elseif ($tmp2[0] == "member") {
            $member_id = $tmp2[1];
        }
    }

    # sent=1 is artificial. If admin is accepting, 
    $query = sprintf("update assignments set updated=now(), active=0, sent=now(), %s where honor_id = %d and member_id = %d",
            join(',', $vx), $honor_id, $member_id);
    DoQuery($query);
    
    $donation = $_POST['reply-amount'];
    $vx[] = "donation = $donation";

    $method = $_POST['reply-method'];
    $vx[] = "payby = $method";
    $vx[] = "jyear = " . $_SESSION['dbLabel'];
    $vx[] = "honor_id = $honor_id";
    $vx[] = "member_id = $member_id";
    $query = sprintf("insert into replies set updated=now(), %s", join(',', $vx));
    DoQuery($query);

    $payby = $gPayMethods[$method];
    $name = formatName($member_id);
    $tail = "";
    if( $donation ) {
        $tail = ", donated \$ " . number_format($donation,2) . ", pay by: $payby";
    }
    if( $accepted ) {
        $msg = "$name accepted honor $hash" . $tail;
    } else {
        $msg = "$name accepted honor $hash" . $tail;
    }

    EventLog('record', [
        'type' => 'rsvp',
        'userid' => $gUserId,
        'item' => "$msg"
    ]);

    if ($gTrace)
        array_pop($gFunction);
}

function BuildMembers() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $quals = [];
    $quals[] = "`TABLE_SCHEMA` like 'cbi18_honors_$gJewishYear'";
    $quals[] = "`TABLE_NAME` = 'members'";
    $query = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where " . join(' and ', $quals);
    $stmt = DoQuery($query);
    $valid_cols = [];
    while (list($col) = $stmt->fetch(PDO::FETCH_NUM)) {
        $valid_cols[$col] = 1;
    }

    DoQuery("truncate table members");

    $qx = array();
    $qx[] = "status = 'Member'";
    $qx[] = "status = 'New'";
    $qx[] = "status = 'Sharon'";
    $qx[] = "status = 'Staff'";
    $query = "select * from members_master where " . join(' or ', $qx);
    $stmt = DoQuery($query);

    $j = 0;
    $num_non_empty = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $flds = $args = [];
        $i = 0;
        foreach ($row as $key => $val) {
            if (!array_key_exists($key, $valid_cols)) {
                if( $j == 0 ) {
                    echo "Invalid column: $key<br>";
                }
                continue;
            }
            $i++;
            $flds[] = sprintf("`%s` = :v$i", $key, $i);
            if( $key == "ID" ) {
                $id = $val;
            }
            $args[":v$i"] = $val;
            if (!empty($val))
                $num_non_empty++;
        }
        if ($num_non_empty > 5) {
            $query = "insert into members set " . join(',', $flds);
            DoQuery($query, $args);
            if( $id == -211 ) {
                echo "$query<br>";
                print_r($args);
                    echo "<br>";
            }
        }
        $num_non_empty = 0;
        $j++;
    }

    echo "$j members added to database<br>";
    
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

function CompareMembers() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=center>";

    $hdr = "<a href=#NewMembers><input type=button value='New Members'></a>";
    $hdr .= "&nbsp;&nbsp;";
    $hdr .= "<a href=#OldMembers><input type=button value='Old Members'></a>";
    $hdr .= "&nbsp;&nbsp;";
    $hdr .= "<a href=#Changes><input type=button value='Changes'></a>";
    $hdr .= "&nbsp;&nbsp;";
    $hdr .= "<input type=button value=Back onclick=\"setValue('from', 'CompareMembers');addAction('Back');\">";
    $hdr .= "</br>";

    echo "<span id=NewMembers>$hdr</span>";

    $year = $gJewishYear - 1;
    $old_db = "members_master_$year";

    $stmt_outer = DoQuery("select * from members order by `Last Name`, `Female 1st Name`");
    $i = 0;
    while ($row = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['ID'];
        DoQuery("select * from $old_db where ID = $id");
        if ($gPDO_num_rows == 0) {
            $i++;
            if ($i == 1) {
                echo "<div class=CommonV2>";
                echo "<table>";
                echo "<tr>";
                echo "  <th>#</th>";
                echo "  <th>ID #</th>";
                echo "  <th>New Member Name(s)</th>";
                echo "</tr>\n";
            }
            echo "<tr>";
            echo "<td>$i</td>";
            printf("<td>%d</td>", $id);
            printf("<td>%s</td>", formatName($id) );
            echo "</tr>\n";
            DoQuery("update members_master set status = 'New' where `ID` = $id");
            DoQuery("update members set status = 'New' where `ID` = $id");
        }
    }
    if ($i > 0) {
        echo "</table>";
        echo "</div>";
        echo "<br>";
        LocalInit();
    }

    echo "<span id=OldMembers>$hdr</span>";

    $stmt_outer = DoQuery("select * from $old_db order by `Last Name`, `Female 1st Name`");
    $i = 0;
    while ($row = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['ID'];
        DoQuery("select * from members where ID = $id");
        if ($gPDO_num_rows == 0) {
            $i++;
            if ($i == 1) {
                echo "<div class=CommonV2>";
                echo "<table>";
                echo "<tr>";
                echo "  <th>#</th>";
                echo "  <th>ID #</th>";
                echo "  <th>Member Dropout Name(s)</th>";
                echo "</tr>\n";
            }
            echo "<tr>";
            echo "<td>$i</td>";
            printf("<td>%d</td>", $id);
            printf("<td>%s</td>", formatName($id) );
            echo "</tr>\n";
        }
    }
    if ($i > 0) {
        echo "</table>";
        echo "</div>";
    }

    echo "<span id=Changes>$hdr</span>";

    $i = 0;

    $stmt_outer = DoQuery("select * from members order by `Last Name`, `Female 1st Name`");
    while ($row1 = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $stmt_inner = DoQuery("select * from $old_db where `ID` = " . $row1['ID']);
        if ($gPDO_num_rows == 0)
            continue;

        $row2 = $stmt_inner->fetch(PDO::FETCH_ASSOC);
        foreach ($row1 as $key => $value) {
            if (empty($value) && empty($row2["$key"])) {
                $match = 1;
            } else {
                $match = ( trim($value) == trim($row2["$key"]) );
            }
            if ($match)
                continue;

            $i++;
            if ($i == 1) {
                echo "<div class=CommonV2>";
                echo "<table>";
                echo "<tr>";
                echo "  <th>#</th>";
                echo "  <th>ID #</th>";
                echo "  <th>Name</th>";
                echo "  <th>Field</th>";
                echo "  <th>New</th>";
                echo "  <th>Old</th>";
                echo "</tr>\n";
            }
            echo "<tr>";
            echo "<td>$i</td>";
            $id = $row1['ID'];
            printf("<td>%d</td>", $id);
            printf("<td>%s</td>", formatName($id));
            printf("<td>%s</td>", $key);
            printf("<td>" . $row1["$key"] . "</td>");
            printf("<td>" . $row2["$key"] . "</td>");
            echo "</tr>";
        }
    }
    echo "</div>";
}

function CreateHonors() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $stmt = DoQuery("select date from dates where `label` = \"erev\"");
    if ($gPDO_num_rows == 0) {
        ?>
        <script type='text/javascript'>
            alert('You must first select a date for Rosh Hashanah');
        </script>
        <?php
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $date = new DateTime($row['date']);

        DoQuery('truncate table honors');
#===========
        $date->add(new DateInterval('P1D'));  # Advance to day #1
        $shabbat = $date->format('w') == 6 ? 1 : 0;

        $service = "rh1";
        $stmt = DoQuery("select * from honors_master where service = '$service' order by `sort` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qx = array();
            $qx[] = "`id` = " . $row['id'];
            $qx[] = "`service` = '" . $service . "'";
            $qx[] = "`page` = " . $row['page'];
            $qx[] = "`sort` = " . $row['sort'];
            $qx[] = "`arrival_time` = '" . $row['arrival_time'] . "'";
            $qx[] = "`mail_group` = " . $row['mail_group'];
            $qx[] = "`honor` = :v1";
            $query = "insert into honors set " . join(',', $qx);
            $add = 1;
            if ($shabbat && $row['shabbat_exclude'])
                $add = 0;
            if (!$shabbat && $row['shabbat_include'])
                $add = 0;
            if ($add)
                DoQuery($query, [':v1' => $row['honor']]);
        }

#===========
        $date->add(new DateInterval('P1D'));  # Advance to day #2
        $shabbat = $date->format('w') == 6 ? 1 : 0;

        $service = "rh2";
        $stmt = DoQuery("select * from honors_master where service = '$service' order by `sort` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qx = array();
            $qx[] = "`id` = " . $row['id'];
            $qx[] = "`service` = '" . $service . "'";
            $qx[] = "`page` = " . $row['page'];
            $qx[] = "`sort` = " . $row['sort'];
            $qx[] = "`arrival_time` = '" . $row['arrival_time'] . "'";
            $qx[] = "`mail_group` = " . $row['mail_group'];
            $qx[] = "`honor` = :v1";
            $query = "insert into honors set " . join(',', $qx);
            $add = 1;
            if ($shabbat && $row['shabbat_exclude'])
                $add = 0;
            if (!$shabbat && $row['shabbat_include'])
                $add = 0;
            if ($add)
                DoQuery($query, [':v1' => $row['honor']]);
        }

#===========
        $date->add(new DateInterval('P5D'));  # Advance to Kol Nidre
        $shabbat = $date->format('w') == 6 ? 1 : 0;

        $service = "kn";
        $stmt = DoQuery("select * from honors_master where service = '$service' order by `sort` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qx = array();
            $qx[] = "`id` = " . $row['id'];
            $qx[] = "`service` = '" . $service . "'";
            $qx[] = "`page` = " . $row['page'];
            $qx[] = "`sort` = " . $row['sort'];
            $qx[] = "`arrival_time` = '" . $row['arrival_time'] . "'";
            $qx[] = "`mail_group` = " . $row['mail_group'];
            $qx[] = "`honor` = :v1";
            $query = "insert into honors set " . join(',', $qx);
            $add = 1;
            if ($shabbat && $row['shabbat_exclude'])
                $add = 0;
            if (!$shabbat && $row['shabbat_include'])
                $add = 0;
            if ($add)
                DoQuery($query, [':v1' => $row['honor']]);
        }

#===========
        $date->add(new DateInterval('P1D'));  # Advance to Yom Kippur
        $shabbat = $date->format('w') == 6 ? 1 : 0;

        $service = "yka";
        $stmt = DoQuery("select * from honors_master where service = '$service' order by `sort` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qx = array();
            $qx[] = "`id` = " . $row['id'];
            $qx[] = "`service` = '" . $service . "'";
            $qx[] = "`page` = " . $row['page'];
            $qx[] = "`sort` = " . $row['sort'];
            $qx[] = "`arrival_time` = '" . $row['arrival_time'] . "'";
            $qx[] = "`mail_group` = " . $row['mail_group'];
            $qx[] = "`honor` = :v1";
            $query = "insert into honors set " . join(',', $qx);
            $add = 1;
            if ($shabbat && $row['shabbat_exclude'])
                $add = 0;
            if (!$shabbat && $row['shabbat_include'])
                $add = 0;
            if ($add)
                DoQuery($query, [':v1' => $row['honor']]);
        }

#===========
        $service = "ykp";
        $stmt = DoQuery("select * from honors_master where service = '$service' order by `sort` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qx = array();
            $qx[] = "`id` = " . $row['id'];
            $qx[] = "`service` = '" . $service . "'";
            $qx[] = "`page` = " . $row['page'];
            $qx[] = "`sort` = " . $row['sort'];
            $qx[] = "`arrival_time` = '" . $row['arrival_time'] . "'";
            $qx[] = "`mail_group` = " . $row['mail_group'];
            $qx[] = "`honor` = :v1";
            $query = "insert into honors set " . join(',', $qx);
            $add = 1;
            if ($shabbat && $row['shabbat_exclude'])
                $add = 0;
            if (!$shabbat && $row['shabbat_include'])
                $add = 0;
            if ($add)
                DoQuery($query, [':v1' => $row['honor']]);
        }
    }
    if ($gTrace)
        array_pop($gFunction);
}

function CreateHonorsMaster() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<table>";
    echo "<tr>";
    echo "  <td>Source</td>";
    echo "  <td>Service</td>";
    echo "  <td>Sort</td>";
    echo "  <td>S-Incl</td>";
    echo "  <td>S-Excl</td>";
    echo "  <td>Page</td>";
    echo "  <td>Honor</td>";
    echo "</tr>";

    $tsort = 0;

    DoQuery("truncate table honors_master");

    $stmt = DoQuery("select Code, Honor, Page from honors_temp order by Code asc");
    while (list( $code, $honor, $page ) = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<tr>";
        echo "  <td>$code</td>";
        $service = substr($code, 0, 3);
        echo "  <td>$service</td>";
        $sort = substr($code, 3, 2);
        echo "  <td>$sort</td>";
        $s_incl = strpos($code, '+') ? 1 : 0;
        $s_excl = strpos($code, '-') ? 1 : 0;
        echo "  <td>$s_incl</td>";
        echo "  <td>$s_excl</td>";
        echo "  <td>$page</td>";
        echo "  <td>$honor</td>";
        echo "</tr>";
        $qx = array();
        $qx[] = sprintf("`service` = '%s'", $service);
        $qx[] = sprintf("`sort` = $tsort");
        $qx[] = sprintf("`shabbat_include` = $s_incl");
        $qx[] = sprintf("`shabbat_exclude` = $s_excl");
        $qx[] = "`honor` = :v1";
        $qx[] = sprintf("`page` = $page");
        $query = "insert into honors_master set " . join(',', $qx);
        DoQuery($query, [':v1' => $honor]);
        $tsort += 10;
    }
    echo "</table>";
    if ($gTrace)
        array_pop($gFunction);
}


function DisplayCategories() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $area = $_POST['area'];

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$area')";
    $jsx[] = "setValue('from','DisplayCategories')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<table class=sortable>";

    echo "<tr>";
    echo "<th>#</th>";
    echo "<th>Label</th>";
    echo "<th># of Items</th>";
    echo "<th>Action</th>";
    echo "</tr>\n";

    $i = 0;
    foreach ($gCategories as $id => $label) {
        $i++;
        $tag = MakeTag("cat_$id");

        echo "<tr>";
        echo "<td>$i</td>";
        echo "<td><input type=text $tag value=\"$label\" onChange=\"addField('$id');toggleBgRed('update');\"></td>";
        $stmt = DoQuery("select count(id) from items where itemCategory = '$id'");
        list($num) = $stmt->fetch(PDO::FETCH_NUM);
        echo "<td class=c>$num</td>";
        echo "<td class=c>";

        if ($num == 0) {
            $jsx = array();
            $jsx[] = "setValue('area','category')";
            $jsx[] = "setValue('from','DisplayCategories')";
            $jsx[] = "addField('$id')";
            $jsx[] = "setValue('func','delete')";
            $txt = sprintf("Are you sure you want to delete the category $label?");
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Del $tag $js>";
        } else {
            echo "&nbsp;";
        }

        echo "</td>";
        echo "</tr>";
    }

    $i++;
    $id = 0;
    $label = "";
    $tag = MakeTag("cat_$id");

    echo "<tr>";
    echo "<td>$i</td>";
    echo "<td><input type=text $tag value=\"$label\" onChange=\"toggleBgRed('add_$id');\"></td>";
    $stmt = DoQuery("select count(id) from items where itemCategory = '$id'");
    list($num) = $stmt->fetch(PDO::FETCH_NUM);
    echo "<td class=c>$num</td>";
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


function DisplayFinancial() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $ts = time() + $time_offset;
    $today = date('j-M-Y', $ts);

    $area = $_POST['area'];

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

    $stmt = DoQuery("select sum(amount) from pledges where pledgeType = $PledgeTypeFinancial");
    list( $total ) = $stmt->fetch(PDO::FETCH_NUM);


    $stmt = DoQuery("select amount from pledges where pledgeType = $PledgeTypeFinGoal");
    list( $goal ) = $stmt->fetch(PDO::FETCH_NUM);
#	DoQuery( "select * from pledges where pledgeType = $PledgeTypeFinancial order by amount desc, lastName asc" );
    $stmt = DoQuery("select * from pledges where pledgeType = $PledgeTypeFinancial order by timestamp desc");
    $num_pledges = $gPDO_num_rows;
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
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
            $jsx[] = "setValue('area','$area')";
            $jsx[] = sprintf("setValue('id','%d')", $id);
            $jsx[] = "addAction('Edit')";
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Edit $js>$lf";

            $jsx = array();
            $jsx[] = "setValue('area','$area')";
            $jsx[] = "setValue('from','DisplayFinancial')";
            $jsx[] = "setValue('func','delete')";
            $jsx[] = sprintf("setValue('id','%d')", $id);
            $txt = sprintf("Are you sure you want to delete %s %s's donation for \$ %s?", $firstName, $lastName, number_format($amount, 2));
            $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<input type=button value=Delete $js>$lf";

            $jsx = array();
            $jsx[] = "setValue('area','$area')";
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
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $area = $_POST['area'];

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
    list( $num ) = $stmt->fetch(PDO::FETCH_NUM);
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

    $i = 0;
    foreach ($gCategories as $cid => $label) {
        $stmt_outer = DoQuery("select * from items where itemCategory = '$cid' order by `itemTitle` asc");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i++;
            $iid = $row['id'];
            echo "<tr>";
            echo "<td class=c>$iid</td>";
            $val = ( $row['itemAuction'] == 0 ) ? "Silent" : "Live";
            echo "<td class=c>$val</td>";
            printf("<td class=c>%s</td>", $gStatus[$row['status']]);
            echo "<td>" . $gCategories[$cid] . "</td>";
            echo "<td>" . $row['itemType'] . "</td>";
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
    }

    echo "</table>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}


function EditItem() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $iid = $_POST['id'];
    $area = $_POST['area'];
    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$area')";
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
        echo "<th>$fld</th>";
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
        } elseif ($fld == "itemAuction") {
            $live = $row[$fld];
            echo "<td>";
            $jsx = array();
            $jsx[] = "setValue('from','EditItem')";
            $jsx[] = "addField('$fld')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onChange=\"%s\"", join(';', $jsx));
            echo "<select $tag $js>";
            if ($live == -1) {
                echo "<option value=0 selected>-- Click Here --</option>";
            }
            $tlive = array(0 => "Silent", 1 => "Live");
            foreach ($tlive as $val => $label) {
                $selected = ( $val == $live ) ? "selected" : "";
                echo "<option value=$val $selected>$label</option>";
            }
            echo "</select></td>";
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
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $area = $_POST['area'];

    if ($area == 'category') {
        EditCategory();
    } elseif ($area == 'honors') {
        HonorsEdit();
    } elseif ($area == 'item') {
        EditItem();
    } elseif( $area == 'members') {
        MembersEdit();
    }

    if ($gTrace)
        array_pop($gFunction);
}

function ExcelGabbai() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-HH-Honors-$str.csv");

    $body = [];
    $body[] = '"Service","Page","Honor","Last Name","First Name(s)","Status","Date"';

    $query = "select * from honors order by sort asc";
    $stmt_outer = DoQuery($query);
    while ($orow = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $values = [];

        $values[] = '"' . $orow["service"] . '"';
        $values[] = $orow["page"];
        $values[] = '"' . $orow["honor"] . '"';

        $query = "select a.accepted, a.updated,";
        $query .= " b.`female 1st name`, b.`male 1st name`, b.`last name`";
        $query .= " from assignments a";
        $query .= " join members b on b.id = a.member_id";
        $query .= " where a.honor_id = " . $orow['id'];
        $query .= " and a.declined = 0 and a.jyear = $gJewishYear";
        $stmt = DoQuery($query);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values[] = sprintf('"%s"', $row["last name"]);
            if (empty($row["female 1st name"])) {
                $values[] = sprintf('"%s"', $row["male 1st name"]);
            } elseif (empty($row["male 1st name"])) {
                $values[] = sprintf('"%s"', $row["female 1st name"]);
            } else {
                $values[] = sprintf('"%s %s"', $row["female 1st name"], $row["male 1st name"]);
            }
            $values[] = $row['accepted'] ? "Accepted" : "Pending";
            $values[] = $row['updated'];
        }
        $body[] = join(",", $values);
    }

    echo join("\n", $body);
    exit;

    if ($gTrace)
        array_pop($gFunction);
}

function ExcelItems() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-Auction-Items-$str.csv");

    $body = array();
    $stmt = DoQuery("show fields from items");
    $fields = array();
    $types = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $label = $row['Field'];
        $fields[] = '"' . $row['Field'] . '"';
        $types[$label] = $row['Type'];
    }

    $body[] = join(',', $fields);

    $stmt_outer = DoQuery("select * from items order by id asc");
    while ($row = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $stmt = DoQuery("select max(bid) from bids where itemId = $id");
        list( $bid) = $stmt->fetch(PDO::FETCH_NUM);
        $line = array();
        foreach ($row as $x => $fld) {
            if ($x == "itemCategory") {
                $fld = $gCategories[$fld];
            } elseif ($x == "itemAuction") {
                $fld = ($fld == 0 ) ? "Silent" : "Live";
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

function ExcelMoney() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-HH-Donations-$str.csv");

    $body = [];
    $body[] = '"Last Name","First Name(s)","Date","Amount","Method"';

    $query = "SELECT a.ID, a.`female 1st name`, a.`male 1st name`, a.`last name`, b.updated, b.donation, b.payby";
    $query .= " from members a join replies b on a.id = b.member_id";
    $query .= " where b.donation > 0 and b.jyear = $gJewishYear";
    $query .= " order by a.`last name` asc";
    $stmt_outer = DoQuery($query);
    while ($orow = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $values = [];
        $mid = $orow['ID'];        
        $values[] = formatName($mid);
        $values[] = sprintf('"%s"', $orow['updated']);
        $values[] = sprintf('"%s"', $orow['donation']);
        $values[] = sprintf('"%s"', $gPayMethods[$orow['payby']]);

        $body[] = join(",", $values);
    }

    echo join("\n", $body);
    exit;

    if ($gTrace)
        array_pop($gFunction);
}

function ExcelSpiritual() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-HH-Spiritual-Pledges-$str.csv");

    $body = array();
    $line = array('"#"', '"Time"', '"Category"', '"Mitzvah"', '"Name"', '"Phone"', '"E-Mail"');
    $body[] = join(',', $line);

    $stmt = DoQuery("select * from pledges where pledgeType = $PledgeTypeSpiritual order by lastName asc, firstName asc");
    $i = 0;
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
function HonorsEdit() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    echo "<div class=center>";
    echo "<input type=button value=Back onclick=\"setValue('from', 'HonorsEdit');addAction('Back');\">";
    echo "&nbsp;";
    echo "<input type=button value=Rebuild onclick=\"setValue('from', 'HonorsEdit');addAction('update');\">";

    echo "<div class=CommonV2>";
    echo "<table class=honors>";
    echo "<thead>";
    echo "<tr>";
    echo "  <td class=service>Service</td>";
    echo "  <td class=sort>Sort</td>";
    echo "  <td class=si>Shabbat<br>Include</td>";
    echo "  <td class=se>Shabbat<br>Exclude</td>";
    echo "  <td class=service>Arrival<br>Time</td>";
    echo "  <td class=honor>Honor</td>";
    echo "  <td class=page>Page</td>";
    echo "  <td class=service>Mail<br>Group</td>";
    echo "</tr>\n";
    echo "</thead>";
    echo "<tbody>";

    $services = [];
    $stmt = DoQuery("select distinct service, sort from honors_master order by sort asc");
    while (list( $service ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $services[] = $service;
    }

    $stmt = DoQuery("select * from honors_master order by sort asc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        echo "<tr>";

        $ajax_id = "id=\"honors_master__service__$id\"";
        echo "<td class=service><select class=ajax $ajax_id>";
        foreach ($services as $val) {
            $selected = ( $val == $row['service'] ) ? "selected" : "";
            echo "<option value=$val $selected>$val</option>";
        }
        echo "</select></td>";

        $ajax_id = "id=\"honors_master__sort__$id\"";
        printf("<td class=sort><input type=text size=4 value=%d class=ajax $ajax_id></td>", $row['sort']);

        $ajax_id = "id=\"honors_master__shabbat_include__$id\"";
        if( $row['shabbat_include'] ) {
            $checked = "checked";
            $val = 0;
        } else {
            $checked = "";
            $val = 1;
        }
        echo "<td class=si><input type=checkbox value=$val class=ajax $ajax_id $checked></td>";

        $ajax_id = "id=\"honors_master__shabbat_exclude__$id\"";
        if( $row['shabbat_exclude'] ) {
            $checked = "checked";
            $val = 0;
        } else {
            $checked = "";
            $val = 1;
        }
        echo "<td class=si><input type=checkbox value=$val class=ajax $ajax_id $checked></td>";

        $ajax_id = "id=\"honors_master__arrival_time__$id\"";
        printf("<td class=service><input type=text size=6 value='%s' class=ajax $ajax_id></td>", substr($row['arrival_time'],0,5));

        $ajax_id = "id=\"honors_master__honor__$id\"";
        echo "<td class=honor><textarea rows=3 cols=50 class=ajax $ajax_id>" . $row['honor'] . "</textarea></td>";

        $ajax_id = "id=\"honors_master__page__$id\"";
        printf("<td class=page><input type=text size=4 value=%d class=ajax $ajax_id></td>", $row['page']);

        $ajax_id = "id=\"honors_master__mail_group__$id\"";
        printf("<td class=service><input type=text size=2 value=%d class=ajax $ajax_id></td>", $row['mail_group']);

        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function HonorsReSort() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $stmt_outer = DoQuery("select id from honors_master order by sort asc");
    $sort = 10;
    while (list( $id ) = $stmt_outer->fetch(PDO::FETCH_NUM)) {
        DoQuery("update honors_master set sort = $sort where id = $id");
        $sort += 10;
    }
    if ($gTrace)
        array_pop($gFunction);
}

function HonorsUpdate() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $tmp2 = preg_split("/,/", $_POST['fields']);
    sort($tmp2);
    $tmp = array_unique($tmp2);
    $tmp[] = "999_endoftheline";
    $flds = $args = [];
    $last_id = -1;
    foreach ($tmp as $fld) {
        $id = intval(substr($fld, 0, 3));
        if ($id != $last_id && !empty($args)) {
            $query = "update honors_master set " . join(",", $flds) . " where id = $last_id";
            DoQuery($query, $args);
            $flds = $args = [];
        }
        $key = substr($fld, 4);
        $val = array_key_exists($fld, $_POST) ? $_POST[$fld] : 0;
        $i = count($args) + 1;
        $flds[] = "`$key` = :v$i";
        $args[":v$i"] = $val;
        $last_id = $id;
    }
    HonorsReSort();
    CreateHonors();
    if ($gTrace)
        array_pop($gFunction);
}

function LocalInit() {
    include 'includes/globals.php';
/*
* This function should not generate any output so Excel downloads can be sent to the local browser
*/
    $gDb = $gPDO[0]['inst'];
    
    $req = $_SERVER['QUERY_STRING'];
    if (!empty($req)) {
        $tmp = parse_str($req, $qs);
        if (array_key_exists('action', $qs) && $qs['action'] == 'password' &&
                array_key_exists('func', $qs) && $qs['func'] == 'reset') {
            $gAction = 'reset';
            $gFunc = 'newpassword';
            $gFrom = 'email';
            $gResetKey = $qs['key'];
            $gUserId = $qs['id'];
        
        } elseif (!array_key_exists('XDEBUG_SESSION_START', $qs)) {
            UserManager('logout');
            exit;
        }
    }
    $noAction = empty($gAction) && !array_key_exists('action', $_POST);

    if ($noAction) {
        $gAction = 'UserManager';
        $gFunc  = 'login';
    }

    $tmp = ['action', 'area', 'from', 'func', 'mode', 'where'];
    foreach ($tmp as $name) {
        $gn = 'g' . ucfirst($name);
        if (!isset($$gn)) {
            $$gn = array_key_exists($name, $_POST) ? $_POST[$name] : "";
        }
    }
    if (empty($gMode)) {
        $gMode = "office";
    }
    $dump = 0;
    if ($dump) {
        $v = array_keys($_SERVER);
        sort($v);
        foreach ($v as $key) {
            printf("_SERVER['%s'] = %s<br>", $key, $_SERVER[$key]);
        }
    }
    $gLF = "\n";

    $proto = ( array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ) ? "https" : "http";
    $gSourceCode = sprintf("%s://%s%s", $proto, $_SERVER['SERVER_NAME'], $_SERVER['SCRIPT_NAME']);
    $gFunction = array();

    $date_server = new DateTime('2000-01-01');
    $date_calif = new DateTime('2000-01-01', new DateTimeZone('America/Los_Angeles'));
    $time_offset = $date_server->format('U') - $date_calif->format('U');

    $gPreSelected = ( preg_match('/id=(\d+)/', $gSourceCode, $matches) ) ? $matches[1] : 0;
    if ($gPreSelected > 0) {
        $stmt = preg_match('/(.+)\?(.+)/', $gSourceCode, $matches);
        $gSourceCode = $matches[1];
    }
#============
    $gAccessNameToLevel = array();
    $gAccessNameEnabled = array();
    $gAccessLevels = array();
    $gCategories = array();
    $gPackages = array();
    $gError = [];

    $gCategories[0] = '__Unassigned';
    $gPackages[0] = '__Unassigned';

    $stmt = DoQuery('select * from privileges order by level desc');
    if ($gPDO_num_rows == 0) {
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

#============
    $stmt = DoQuery("select date from dates where `label` = \"erev\"");
    list( $td ) = $stmt->fetch(PDO::FETCH_NUM);
    $date = new DateTime($td);
    $date->add(new DateInterval('P1D'));
    $jd = cal_to_jd(CAL_GREGORIAN, $date->format('m'), $date->format('d'), $date->format('Y'));
    $arr = cal_from_jd($jd, CAL_JEWISH);
    $gJewishYear = $arr['year'];

#============
    $date_server = new DateTime('2000-01-01');
    $date_calif = new DateTime('2000-01-01', new DateTimeZone('America/Los_Angeles'));
    $time_offset = $date_server->format('U') - $date_calif->format('U');
#============

    $stmt = DoQuery("select id, `Female Tribe`, `Male Tribe`, `Status` from members");
    while (list( $id, $ft, $mt, $status ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $stmt2 = DoQuery("select ftribe, mtribe, new from member_attributes where id = $id");
        if ($gPDO_num_rows == 0) {
            $tmp = array();
            $tmp[] = "id = $id";
            $tmp[] = "ftribe = '$ft'";
            $tmp[] = "mtribe = '$mt'";
            if ($status == "New")
                $tmp[] = "new = 1";
            $query = "insert into member_attributes set " . join(',', $tmp);
            DoQuery($query);
        }
    }
    
    $stmt = DoQuery( "select value from misc where label = 'force_debug'");
    if( $gPDO_num_rows == 0 ) {
        DoQuery("insert into misc set label = 'force_debug', value ='0', enabled = 0" );
    } else {
        list( $gDebug ) = $stmt->fetch(PDO::FETCH_NUM);
    }
}

function LogfileDisplay() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $members = [];
    $stmt = DoQuery("select * from members");
    while ($member = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mid = $member['ID'];
        $members[$mid] = $member;
    }
    echo "<div class=center>";
    echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";
    echo "&nbsp;";
    echo "<input type=button value=Refresh onclick=\"setValue('from', '" . __FUNCTION__ . "');setValue('func','display');addAction('Log');\">";

    if (UserManager('authorized', 'control')) {
        $tmp = [];
        $tmp[] = "setValue('from','" . __FUNCTION__ . "')";
        $tmp[] = "setValue('func','log-reset')";
        $txt = sprintf("Are you sure you want to initialize the event log?");
        $tmp[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
        $js = join(';', $tmp);
        echo "<input id=action-view type=button class=mode-view-hidden onclick=\"$js\" value=\"Reset\">";
    }

    echo "</div>";

    echo "<br>";
    
    echo "<div class=CommonV2>";
    echo "<table>";
    echo "<tr>";
    echo "  <th>Date/Time</th>";
    echo "  <th>Name</th>";
    echo "  <th>Response</th>";
    echo "</tr>";

    $users = [];
    $stmt = DoQuery("select * from event_log order by `time` DESC");
    while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $uid = $event['userid'];
        if( $uid == 0 ) {
            $users[0] = "n/a";
        } elseif( ! array_key_exists($uid,$users) ) {
            $stmt2 = DoQuery( "select username from users where id = $uid" );
            list( $name  ) = $stmt2->fetch(PDO::FETCH_NUM);
            $users[$uid] = $name;
        }
        $name = $users[$uid];
        
        echo "<tr>";
        echo "  <td>" . $event['time'] . "</td>";
        echo "  <td>" . $name . "</td>";
        echo "  <td>" . $event['item'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    if ($gTrace)
        array_pop($gFunction);
}

function LogfileReset() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    DoQuery("truncate event_log");
    if ($gTrace)
        array_pop($gFunction);
}

function MailAssignment() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    loadMailSettings();
    
    $argc = func_num_args();
    if ($argc > 0) {
        $area = func_get_arg(0);
    } else {
        $area = "";
    }
    $remind = preg_match("/remind/", $area);

    $preview = ( array_key_exists('preview', $_POST) ) ? 1 : 0;

    $tmp = preg_split('/,/', $_POST['fields']);
    foreach ($tmp as $fld) {
        if (preg_match("/^honor_/", $fld)) {
            list( $xx, $honor_id ) = preg_split('/_/', $fld);
        } elseif (preg_match("/^member_/", $fld)) {
            list( $xx, $member_id ) = preg_split('/_/', $fld);
        }
    }
    $stmt = DoQuery("select * from members where id = $member_id");
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = DoQuery("select * from honors where id = $honor_id");
    $honor = $stmt->fetch(PDO::FETCH_ASSOC);

    $tmp = [];
    $tmp[] = "honor_id = $honor_id";
    $tmp[] = "member_id = $member_id";
    $tmp[] = "jyear = $gJewishYear";
            
    $stmt = DoQuery("select * from assignments where " . join(' and ', $tmp ) );
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    $hash = $assignment['hash'];

    $mail_override = array_key_exists('override', $_POST ) ? $_POST['override'] : 0;
    
    if ( $gMailLive && (! $preview ) ) {
        $today = date('Y-m-d');
        DoQuery("select * from event_log where item like '%$hash%' and time >= '$today'");
        if ($gPDO_num_rows && ! $gDebug ) {
            if ($gTrace)
                array_pop($gFunction);
            echo "Email already sent today<br>";
            if( ! $mail_override ) return;
            echo "Override selected, mail sent<br>";
        }
    }

    $name = formatName($member_id);

    $stmt = DoQuery("select date from dates where `label` = \"erev\"");
    list( $td ) = $stmt->fetch(PDO::FETCH_NUM);
    $date = new DateTime($td);

    $service = $honor['service'];
    switch ($service) {
        case( 'rh1' ):
            $date->add(new DateInterval('P1D'));
            break;

        case( 'rh2' ):
            $date->add(new DateInterval('P2D'));
            break;

        case( 'kn' ):
            $date->add(new DateInterval('P9D'));
            break;

        case( 'yka' ):
        case( 'ykp' ):
            $date->add(new DateInterval('P10D'));
            break;
    }

    $html = $text = array();

    $html[] = "<html><head></head><body>";
    if (!$preview) {
        $html[] = sprintf("<img src=\"cid:sigimg\" width=\"%d\" height=\"%d\"/>",
                    $GLOBALS['gMailSignatureImageSize']['width'],
                    $GLOBALS['gMailSignatureImageSize']['height']);

    }

    $html[] = "Congregation B'nai Israel";
    $text[] = "Congregation B'nai Israel";

    $html[] = "";
    $text[] = "";

    $html[] = sprintf("Dear %s,", $name);
    $text[] = sprintf("Dear %s,", $name);

    $html[] = "";
    $text[] = "";

    if ($remind) {
        $str = sprintf("You have the honor of %s during the %s service on %s.", $honor['honor'],
                $gService[$honor['service']], $date->format("l, M jS, Y"));
    } else {
        $str = "We look forward to our congregation gathering in person for High Holy Days once again, and";
        $str .= " thank you for the support you have given to Congregation B'nai Israel during the past year.";
        $str .= " In an effort to show our appreciation, we would like to offer you the following honor:";
//        $str .honor of {$honor['honor']}";
//        $str .= " during the {$gService[$honor['service']]} service on {$date->format("l, M jS, Y")}";
    }

    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $t = strtotime( $honor['arrival_time'] );
    $arriveBy = date("g:i A",$t);
    
    $stmt = DoQuery("select date from dates where label = 'reply_date'");
    list( $str ) = $stmt->fetch(PDO::FETCH_NUM);
    $ts = strtotime($str);
    $reply_date = date('F jS, Y', $ts);
    $url = DIR . "hh-honors/?hash=$hash";
    
    $space = "&nbsp;&nbsp;&nbsp;&nbsp;";
    $var = "html";
    $$var[] = "{$space}<b>Honor</b>: " . $honor['honor'];
    $$var[] = "{$space}<b>Service</b>: " . $gService[$honor['service']];
    $$var[] = "{$space}<b>Date</b>: " . $date->format("l, M jS, Y");
    $$var[] = "{$space}<b>Time</b>: Please arrive by $arriveBy";
    $$var[] = "{$space}<b>RSVP</b>: Click <a href=\"$url\"><input type=button value=\"here\"></a> to confirm or decline this honor by $reply_date.";
    
    $space = "";
    $var = "text";
    $$var[] = "{$space}Honor: " . $honor['honor'];
    $$var[] = "{$space}Service: " . $gService[$honor['service']];
    $$var[] = "{$space}Date: " . $date->format("l, M jS, Y");
    $$var[] = "{$space}Time: Please arrive by $arriveBy";
    

    $html[] = "";
    $text[] = "";
    
    $str = "For this honor we ask that you be in the sanctuary by $arriveBy";
    $str .= " and check in with the Shamash, the person in charge of making sure that everyone";
    $str .= " who has an honor is in the right place at the right time.";

    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";
    
    $html[] = "Note that morning services begin at 8:00 AM both days of Rosh Hashanah and on Yom Kippur.";
    $html[] = "";
    
    if (!$remind) {
        $stmt = DoQuery("select date from dates where label = 'reply_date'");
        list( $str ) = $stmt->fetch(PDO::FETCH_NUM);
        $ts = strtotime($str);
        $reply_date = date('F jS, Y', $ts);

        $html[] = "<b>Click <a href=\"$url\">here</a> to confirm or decline this honor by $reply_date.</b>";
        $text[] = "Click on the following link, $url, to confirm or decline this honor by $reply_date.";

        $html[] = "";
        $text[] = "";
    }

    $str = "If you have any questions, please do not hesitate to contact the CBI office at (714) 730-9693";
    $str .= " or via e-mail at <a href=\"mailto:cbi18@cbi18.org?Subject=$gTitle\">cbi18@cbi18.org</a>";
    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $str = "Thank you again and we wish you and your loved ones a happy and healthy New Year.";
    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $html[] = "L&rsquo;Shana Tova,";
    $text[] = "L'Shana Tova,";

    $html[] = "";
    $text[] = "";

    $stmt = DoQuery( "select value from misc where label = 'ritual'");
    list($rit) = $stmt->fetch(PDO::FETCH_NUM);
    $html[] = $rit;
    $text[] = $rit;

    $html[] = "Ritual Vice Presidents";
    $text[] = "Ritual Vice Presidents";

    if ($preview) {
        $mx = [];
        if( ! empty( $member['E-Mail Address'] ) ) $mx[] = $member['E-Mail Address'];
        if( ! empty( $member['E-Mail Address 2'] ) ) $mx[] = $member['E-Mail Address 2'];
        $em = implode(', ', $mx  );
        echo "<hr>E-mail(s): $em";
        echo "<hr>" . join('<br>', $html);
        echo "<br>";
        if ($_POST['from'] == "Assign") {
            echo "<br>";
            echo "<input type=button onclick=\"setValue('area','assign');addAction('Assign');\" value=Continue>";
            exit;
        }
    } else {
        $addrs = [];
        if( $gMailLive ) {
            if( ! empty( $member['E-Mail Address'] ) )
                $addrs[] = ['email' => $member['E-Mail Address'], 'name' => ''];
            if( ! empty( $member['E-Mail Address 2'] ) )
                $addrs[] = ['email' => $member['E-Mail Address 2'], 'name' => ''];

        } elseif( ! empty($gMailTesting) ) {
            foreach( $gMailTesting as $addr ) {
                $addrs[] = $addr;
            }
        }

        $html[] = "</body></html>";
        
        $mail = NULL;
        $mail = MyMailerNew();

        //Recipients
        
        foreach( $addrs as $obj ) {
            $mail->AddAddress( $obj['email'], $obj['name']);
        }
        $mail->setFrom('cbi18@cbi18.org', 'CBI');

        //Attachments
        $mail->AddEmbeddedImage($gMailSignatureImage, 'sigimg', $gMailSignatureImage);

        //Content
        if( $gMailLive ) {
            $mail->Subject = "$gJewishYear CBI High Holy Day Honor";
        } else {
            $mail->Subject = "$gJewishYear CBI High Holy Day Honor ** Test Mode **";            
        }

        $mail->Body = implode('<br>',$html );
        $mail->AltBody = implode('\n',$text);
        
        $ret = MyMailerSend($mail);

        DoQuery("update assignments set sent = now() where `hash` like '$hash'");
        $userid = $GLOBALS['gUserId'];

        EventLog('record', [
            'type' => 'mail',
            'userid' => $userid,
            'item' => "Sent honor to $name, hash: $hash, status: $ret"
        ]);
    }
    if ($gTrace)
        array_pop($gFunction);
}

function MailAssignmentByID_notYetUsed() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $argc = func_num_args();
    if ($argc > 0) {
        $area = func_get_arg(0);
    } else {
        $area = "";
    }
    $remind = preg_match("/remind/", $area);

    $preview = ( array_key_exists('preview', $_POST) ) ? 1 : 0;

    $tmp = preg_split('/,/', $_POST['fields']);
    foreach ($tmp as $fld) {
        if (preg_match("/^honor_/", $fld)) {
            list( $xx, $honor_id ) = preg_split('/_/', $fld);
        } elseif (preg_match("/^member_/", $fld)) {
            list( $xx, $member_id ) = preg_split('/_/', $fld);
        }
    }
    $stmt = DoQuery("select * from members where id = $member_id");
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = DoQuery("select * from honors where id = $honor_id");
    $honor = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = DoQuery("select * from assignments where honor_id = $honor_id and member_id = $member_id and jyear = $gJewishYear");
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    $hash = $assignment['hash'];

    if ($mail_live == 1) {
        $today = date('Y-m-d');
        DoQuery("select * from event_log where item like '%$hash%' and time >= '$today'");
        if ($gPDO_num_rows && ! $gDebug ) {
            if ($gTrace)
                array_pop($gFunction);
            echo "Email already sent today<br>";
            return;
        }
    }

    if (!empty($member['Female 1st Name']) && empty($member['Male 1st Name'])) {
        $name = $member['Female 1st Name'];
    } elseif (empty($member['Female 1st Name']) && !empty($memeber['Male 1st Name'])) {
        $name = $member['Male 1st Name'];
    } else {
        $name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
    }

    $name .= sprintf(" %s", $member['Last Name']);

    $stmt = DoQuery("select date from dates where `label` = \"erev\"");
    list( $td ) = $stmt->fetch(PDO::FETCH_NUM);
    $date = new DateTime($td);

    $service = $honor['service'];
    switch ($service) {
        case( 'rh1' ):
            $date->add(new DateInterval('P1D'));
            break;

        case( 'rh2' ):
            $date->add(new DateInterval('P2D'));
            break;

        case( 'kn' ):
            $date->add(new DateInterval('P9D'));
            break;

        case( 'yka' ):
        case( 'ykp' ):
            $date->add(new DateInterval('P10D'));
            break;
    }

    $html = $text = array();

    $html[] = "<html><head></head><body>";
    if (!$preview) {
        $html[] = sprintf("<img src=\"cid:sigimg\" width=\"%d\" height=\"%d\"/>",
                    $GLOBALS['gMailSignatureImageSize']['width'],
                    $GLOBALS['gMailSignatureImageSize']['height']);

    }

    $html[] = "Congregation B'nai Israel";
    $text[] = "Congregation B'nai Israel";

    $html[] = "";
    $text[] = "";

    $html[] = sprintf("Dear %s,", $name);
    $text[] = sprintf("Dear %s,", $name);

    $html[] = "";
    $text[] = "";

    if ($remind) {
        $str = sprintf("You have the honor of %s during the %s service on %s.", $honor['honor'], $gService[$honor['service']], $date->format("l, M jS, Y"));
    } else {
        $str = "Thank you for the support you have given to Congregation B'nai Israel during the past year.";
        $str .= sprintf(" In an effort to show our appreciation, we would like to offer you the honor of %s during the %s service on %s.", $honor['honor'], $gService[$honor['service']], $date->format("l, M jS, Y"));
    }

    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $str = "We ask that you be in the sanctuary at least 30 minutes prior to your honor";
    $str .= " (15 minutes prior if it occurs at the beginning of the service,) and check in with";
    $str .= " the Shamash, the person in charge of making sure that everyone who has an honor";
    $str .= " is in the right place at the right time.";

    if (!$remind) {
        $str .= " We will send you additional detailed information about your honor closer to the date.";
    }

    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    if (!$remind) {
        $stmt = DoQuery("select date from dates where label = 'reply_date'");
        list( $str ) = $stmt->fetch(PDO::FETCH_NUM);
        $ts = strtotime($str);
        $reply_date = date('F jS, Y', $ts);

        $url = DIR . "hh-honors/?hash=$hash";
        $html[] = "<a href=\"$url\">Click here</a> to confirm or decline this honor by $reply_date.";
        $text[] = "Click on the following link, $url, to confirm or decline this honor by $reply_date.";

        $html[] = "";
        $text[] = "";
    }

    $str = "If you have any questions, please do not hesitate to contact the CBI office at (714) 730-9693";
    $str .= " or via e-mail at <a href=\"mailto:cbi18@cbi18.org?Subject=$gTitle\">cbi18@cbi18.org</a>";
    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $str = "Thank you again and we wish you and your family a happy and healthy New Year.";
    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $html[] = "L&rsquo;Shana Tova,";
    $text[] = "L'Shana Tova,";

    $html[] = "";
    $text[] = "";

    $stmt = DoQuery( "select value from misc where label = 'ritual'");
    list($rit) = $stmt->fetch(PDO::FETCH_NUM);
    $html[] = $rit;
    $text[] = $rit;

    $html[] = "Ritual Vice Presidents";
    $text[] = "Ritual Vice Presidents";

    if ($preview) {
        echo "<hr>" . join('<br>', $html);
        echo "<input type=button value=Continue>";
        exit;
    } else {
        $str = preg_replace("/\s+/", " ", $member['E-Mail Address']);
        if (preg_match("/,/", $str)) {
            $email = preg_split("/,/", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } elseif (preg_match("/;/", $str)) {
            $email = preg_split("/;/", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } elseif (preg_match("/ /", $str)) {
            $email = preg_split("/ /", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } else {
            if (empty($str)) {
                return;
            }
            $email = [$str];
        }
        if (empty($email))
            return;

        $mail = NULL;
        $mail = MyMailerNew();

        //Recipients
        foreach( $email as $addr ) {
            $mail->AddAddress($addr);
        }
        $mail->setFrom('cbi18@cbi18.org', 'CBI');

        //Attachments
        $mail->AddEmbeddedImage($gMailSignatureImage, 'sigimg', $gMailSignatureImage);

        //Content
        $mail->Subject = "$gJewishYear CBI High Holy Day Honor";

        $mail->msgHTML(join('<br>', $html), DIR);

        $ret = MyMailerSend($mail);

        DoQuery("update assignments set sent = now() where `hash` = '$hash'");
        $userid = $GLOBALS['gUserId'];

        EventLog('record', [
            'type' => 'mail',
            'userid' => $userid,
            'item' => "Sent honor to $name, hash: $hash, status: $ret"
        ]);
    }
    if ($gTrace)
        array_pop($gFunction);
}

function MailAssignments($area) {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $stmt = DoQuery("select ival from dates where label = 'num_per_batch'");
    list( $val ) = $stmt->fetch(PDO::FETCH_NUM);
    if ($val > 0) {
        $limited = 1;
        $num_per_batch = $val;
    } else {
        $limited = 0;
    }

    $query = "select a.honor_id, a.member_id";
    $query .= " from assignments a";
    $query .= " join members c on a.member_id=c.id";

    if ($area == "all") {
        $query .= " where a.jyear = $gJewishYear";
        $query .= " order by c.`Last Name` asc, c.`Female 1st Name` asc";
    } elseif ($area == "unsent") {
        $query .= " where a.sent = 0 and a.jyear = $gJewishYear";
        $query .= " order by c.`last name` asc, c.`female 1st name` asc";
        if ($limited) {
            $query .= " limit $num_per_batch";
        }
    } elseif ($area == "noresponse") {
        $query .= " where a.accepted = 0 and a.declined = 0 and a.jyear = $gJewishYear";
        $query .= " order by c.`last name` asc, c.`female 1st name` asc";
        if ($limited) {
            $query .= " limit $num_per_batch";
        }
    } elseif ($area == "remind-rosh") {
        $query .= " join honors b on a.honor_id=b.id";
        $query .= " where a.accepted = 1 and a.declined = 0 and b.service like 'rh%' and a.jyear = $gJewishYear";
        $query .= " order by c.`last name` asc, c.`female 1st name` asc";
        if ($limited) {
            $query .= " limit $num_per_batch";
        }
    } elseif ($area == "remind-yom") {
        $query .= " join honors b on a.honor_id=b.id";
        $query .= " where a.accepted = 1 and a.declined = 0 and b.service not like 'rh%' and a.jyear = $gJewishYear";
        $query .= " order by c.`last name` asc, c.`female 1st name` asc";
        if ($limited) {
            $query .= " limit $num_per_batch";
        }
    }
    $stmt_outer = DoQuery($query);
    while (list( $hid, $mid ) = $stmt_outer->fetch(PDO::FETCH_NUM)) {
        $_POST['fields'] = sprintf("honor_%d,member_%d", $hid, $mid);
        MailAssignment($area);
    }
    if ($gTrace)
        array_pop($gFunction);
}

function MailDisplay() {
    include 'includes/globals.php';
    
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=center>";
    echo "<h2>Mail Controls</h2>";
//    echo "<span style='background-color: yellow; width: 200px; text-align: left; display: inline-block; font-size: 12pt;'>";
    echo "</span>";
    echo "</div>";
    echo "<input type=button value=Back onclick=\"setValue('from', 'Mail');addAction('Back');\">";
        $jsx = [];
    $jsx[] = "setValue('from','" .  __FUNCTION__ . "')";
    $jsx[] = "setValue('func','new')";
    $jsx[] = "addAction('update')";
    $js = implode(';', $jsx); 
    echo "&nbsp;";
    echo "<td class=c><input type=submit onclick=\"$js\" value=New></td>";

    echo "<br><br>";
    echo "<table>";

    echo "<thead>";
    echo "<tr>";
    echo "<th class=col1>Label</th>";
    echo "<th class=col2>Value</th>";
    echo "<th class=col3>Enabled</th>";
    echo "<th class=col5>Action</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";
    $stmt = DoQuery("select * from mail where lower(label) like '%email:%' order by label asc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $label = $row['label'];
        $value = $row['value'];

        if ($label == 'Email: Server') {
            echo "<tr>";
            echo "<td class=col1>$label</td>";
            echo "<td class=col2>";
            $ajax_id = "id=\"mail__value__{$id}\"";
            echo "<select class=\"'col2' ajax\" $ajax_id>";
            for( $mid = 0; $mid < count($gMailDB); $mid++  )  {
                $selected = ( $mid == $value ) ? "selected" : "";
                echo "<option value=$mid $selected>{$gMailDB[$mid]['Label']}</option>";
            }
            echo "</select>";
            echo "</td>";
            echo "<td>&nbsp;</td>";
            echo "<td>&nbsp;</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td class=col1>$label</td>";
            $ajax_id = "id=\"mail__value__{$id}\"";
            echo "<td class=col2><input class=\"'col2' ajax\" size=60 $ajax_id value='" . $row['value'] . "'></td>";

            $tag = MakeTag("enabled_$id");
            $acts = array();
            $acts[] = "addField('$label|enabled|$id')";
            $acts[] = sprintf("setValue('from','%s')", __FUNCTION__);
            $acts[] = "setValue('mode','control')";
            $acts[] = "setValue('area','mail')";
            $acts[] = "setValue('func','update')";
            $acts[] = "setValue('id', '$id')";
            $acts[] = "setValue('key', '$label')";
            $acts[] = "addAction('update')";
            if( empty($row['enabled']) )  {
                $checked = "";
                $val = 1;
            } else {
                $checked = "checked";
                $val = 0;
            }
            $ajax_id = "id=\"mail__enabled__{$id}\"";
            $js = "";
            echo "<td class=box><input class=ajax type=\"checkbox\" $ajax_id $checked $js value=\"$val\"></td>\n";

            $acts = array();
            $acts[] = sprintf("setValue('from','%s')", __FUNCTION__);
            $acts[] = "setValue('area','mail')";
            $acts[] = "setValue('func','del')";
            $acts[] = "setValue('id', '$id')";
            $acts[] = "addAction('update')";
            printf("<td class='col5 c'><input type=button onClick=\"%s\" value='Del'></td>", join(';', $acts));

            echo "</tr>";
        }
    }

    $id = 0;

    echo "<tr>";

    $tag = MakeTag('label_' . $id);
    $js = "onChange=\"toggleBgRed('add');\" onClick=\"this.select();\"";
    echo "<td class=col1><input $tag type='text' size=15 $js value='-- enter label --'></td>";

    $tag = MakeTag('value_' . $id);
    $js = "onChange=\"addField('new|value|$id');toggleBgRed('add');\"";
    echo "<td class=col2><input $tag type='text' size=60 $js></td>";

    $tag = MakeTag('enabled_' . $id);
    $js = "onChange=\"addField('new|enabled|$id');toggleBgRed('add');\"";
    echo "<td class='col3 c'><input $tag type='checkbox' value=1 $js></td>";

    $tag = MakeTag('add');
    $acts = array();
    $acts[] = "addField('new|label|$id')";
    $acts[] = "addField('new|value|$id')";
    $acts[] = "addField('new|enabled|$id')";
    $acts[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $acts[] = "setValue('mode','control')";
    $acts[] = "setValue('area','mail')";
    $acts[] = "setValue('func','add')";
    $acts[] = "addAction('update')";
    printf("<td class='col5 c'><input $tag type=button onClick=\"%s\" value=Add></td>", join(';', $acts));

    echo "</tr>";
    echo "</tbody>";
    echo "</table>";
    
    echo "<br><br>";

    echo "<h1>Email: Admin</h1>";
    echo "<ul class=mail-desc>";
    echo "<li>All emails are sent from this account</li>";
    echo "<li class=warn>If enabled, emails are sent to members</b></li>";
    echo "<li>If not enabled, emails are sent to Testing accounts</li>";
    echo "</ul>";

    echo "<br><br>";

    echo "<h1>Email: Default</h1>";
    echo  "<ul class=mail-desc>";
    echo "<li>This is the default mail account if nothing else is set up</li>";
    echo "</ul>";
    
    echo "<br><br>";

    echo "<h1>Email: Testing</h1>";
    echo  "<ul class=mail-desc>";
    echo "<li>If Admin is disabled, mail is sent to these accounts</li>";
    echo "<li>Multiple accounts can be created</li>";
    echo "</ul>";
    
    if ($gTrace) {
        array_pop($gFunction
        );
    }
}
function MailUpdate() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    if ($gFunc == 'add') {
        $v = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
        $flds = array_unique($v);
        $qx = [];
        $args = [];
        $i = 0;
        $ok = 1;
        foreach ($flds as $fld) {
            list( $label, $colName, $id ) = preg_split('/\|/', $fld);
            $var = implode("_", [$colName, $id]);
            if (array_key_exists($var, $_POST) && !empty($_POST[$var])) {
                $val = $_POST[$var];
                if (stripos($val, "email: admin") !== false) {
                    DoQuery("select id from mail where lower(label) like \"%email: admin%\"");
                    if ($gPDO_num_rows > 0)
                        $ok = 0;
                }
                if (stripos($val, "email: default") !== false) {
                    DoQuery("select id from mail where lower(label) like \"%email: default%\"");
                    if ($gPDO_num_rows > 0)
                        $ok = 0;
                }
                $qx[] = sprintf("`%s` = :v%d", $colName, $i);
                $args[":v$i"] = $val;
                $i++;
            }
        }
        $query = "insert into mail set " . join(',', $qx);
        if ($ok) {
            Logger("query: [$query]");
            Logger("args: [" . print_r($args, true) . "]");
            DoQuery($query, $args);
        }
    } elseif ($gFunc == 'del') {
        $id = $_POST['id'];
        DoQuery("delete from mail where id = :id", [':id' => $id]);
    } elseif ($gFunc == 'update') {
        $v = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
        $flds = array_unique($v);
        foreach ($flds as $fld) {
            $args = [];
            list( $label, $colName, $id ) = preg_split('/\|/', $fld);
            $query = "update mail set " . sprintf("%s = :v1", $colName);
            $query .= " where id = :v2";
            $var = implode("_", [$colName, $id]);
            $newVal = array_key_exists($var, $_POST) ? $_POST[$var] : 0;
            $args[":v1"] = $newVal;
            $args[":v2"] = $id;
            DoQuery($query, $args);
            if ($label == "Email: Admin" && $newVal == 1) {
                DoQuery("update mail set enabled = 0 where Label = \"Email: Testing\"");
            } elseif ($label == "Email: Testing" && $newVal == 1) {
                DoQuery("update mail set enabled = 0 where Label = \"Email: Admin\"");
            }
        }
    } elseif( $gFunc  == 'new' )  {
        DoQuery( "insert into mail set Label = 'Email:'");
    }
    DoQuery("update mail set enabled = 1 where Label = \"Email: Default\"");
    loadMailSettings();
    if ($gTrace) {
        array_pop($gFunction);
    }
}

function MailUpdatex() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $tmp2 = preg_split('/,/', $_POST['fields']);
    $tmp = array_unique($tmp2);

    foreach ($tmp as $field) {
        if( $field == 'ritual' ) {
            DoQuery("select * from misc where label = 'ritual'");
            if( $gPDO_num_rows == 0 ) {
                $query = "insert into misc set label = :v1, value = :v2";
            } else {
                $query = "update misc set value = :v2 where label = :v1";
            }
            DoQuery($query, [':v1' => 'ritual', ':v2' => $_POST['ritual']]);
            continue;
        }
        $tmp3 = preg_split("/=/", $field);
        if (count($tmp3) > 1) {  #This is a mail_[enabled|live]=[0|1]
            $fld = $tmp3[0];
            $new = $tmp3[1];
            DoQuery("select ival from dates where label = \"$fld\"");
            if ($gPDO_num_rows == 0) {
                DoQuery("insert into dates set label = \"$fld\", ival = 0");
            } else {
                DoQuery("update dates set ival = $new where label = \"$fld\"");
            }
            $$fld = $new;
        } else {
            DoQuery("select ival from dates where label = \"$field\"");
            if ($gPDO_num_rows == 0) {
                DoQuery("insert into dates set label = \"$field\", ival = -1");
            } else {
                $val = $_POST[$field];
                DoQuery("update dates set ival = $val where label = '$field'");
            }
        }
    }
    if ($gTrace)
        array_pop($gFunction);
}

function MailValidate() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    echo "<div class=CommonV2>";

    $query = "select a.honor_id, a.member_id";
    $query .= " from assignments a";
    $query .= " join members c on a.member_id=c.id";
    $query .= " where a.jyear = $gJewishYear";
    $query .= " order by c.`Last Name` asc, c.`Female 1st Name` asc";
    $stmt_outer = DoQuery($query);
    echo "<table>";
    while (list( $hid, $mid ) = $stmt_outer->fetch(PDO::FETCH_NUM)) {
        $stmt = DoQuery("select * from members where id = $mid");
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($member['Female 1st Name']) && empty($member['Male 1st Name'])) {
            $name = $member['Female 1st Name'];
        } elseif (empty($member['Female 1st Name']) && !empty($memeber['Male 1st Name'])) {
            $name = $member['Male 1st Name'];
        } else {
            $name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
        }

        echo "<tr>";
        $name .= sprintf(" %s", $member['Last Name']);
        echo "<td>$name</td>";

        $str = preg_replace("/\s+/", " ", $member['E-Mail Address']);
        if (preg_match("/,/", $str)) {
            $email = preg_split("/,/", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } elseif (preg_match("/;/", $str)) {
            $email = preg_split("/;/", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } elseif (preg_match("/ /", $str)) {
            $email = preg_split("/ /", $str, NULL, PREG_SPLIT_NO_EMPTY);
        } elseif (empty($str)) {
            $email = [];
        } else {
            $email = [$str];
        }
        if (count($email) == 0) {
            echo "<td class=cbad></td>";
        } else {
            $addrlist = [];
            foreach ($email as $str) {
                if (Swift_Validate::email($str)) {
                    $addrlist[] = $str;
                } else {
                    echo "<!-- bad email: [$str] -->\n";
                    $addrlist[] = "<span style='background-color:red'>$str</span>";
                }
            }
            printf("<td>%s</td>", join(', ', $addrlist));
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    if ($gTrace)
        array_pop($gFunction);
}

function MembersDisplay() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    echo "<div class=center>";

    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    
        $tag = MakeTag('edit');
        $jsx = array();
        $jsx[] = "setValue('area','members')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','new')";
        $jsx[] = "setValue('id',0)";
        $jsx[] = "addAction('Edit')";
        $js = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo "<td class=box><input type=button $tag $js value=New></td>";

    $stmt = DoQuery("select * from members order by `Last Name` asc");
    $members = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['ID'];
        $members[$id] = $row;
    }

    $stmt = DoQuery("select * from member_attributes");
    $attributes = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attributes[$row['id']] = $row;
    }

    echo "<br><br>";
    echo "<div class=CommonV2>";
    echo "<table class=members>";
    echo "<thead>";
    echo "<tr>";
    echo "  <td class=box>ID</td>";
    echo "  <td class=name>Name</td>";
    echo "  <td class=tribe>Male<br>Tribe</td>";
    echo "  <td class=tribe>Female<br>Tribe</td>";
    echo "  <td class=box>New</td>";
    echo "  <td class=box>Board</td>";
    echo "  <td class=box>Past Pres</td>";
    echo "  <td class=box>Memb</td>";
    echo "  <td class=box>Sha-<br>ron</td>";
    echo "  <td class=box>Staff</td>";
    echo "  <td class=box>Assoc</td>";
    echo "  <td class=box>Donor</td>";
    echo "  <td class=box>Vol A</td>";
    echo "  <td class=box>Vol B</td>";
    echo "  <td class=box>Vol C</td>";
    echo "</tr>\n";
    echo "</thead>";
    echo "<tbody>";

    foreach ($members as $id => $row) {
        echo "<tr>";
        $tag = MakeTag('edit');
        $jsx = array();
        $jsx[] = "setValue('area','members')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('id',$id)";
        $jsx[] = "addAction('Edit')";
        $js = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo "<td class=box><input type=button $tag $js value=Edit></td>";
        
        $class = 'name';
        if( empty($row['Male 1st Name'] ) ) {
            $label = "{$row['Last Name']}, {$row['Female 1st Name']}";
        } else if( empty( $row['Female 1st Name'] ) ) {
            $label = "{$row['Last Name']}, {$row['Male 1st Name']}";
        } else {
            $label = "{$row['Last Name']}, {$row['Female 1st Name']} and {$row['Male 1st Name']}";
        }
        echo "<td class=$class>$label</td>\n";

        $class = "tribe";
        printf("<td class=$class>%s</td>", $attributes[$id]['mtribe']);
        printf("<td class=$class>%s</td>", $attributes[$id]['ftribe']);

        foreach (array("new", "board", "pastpres", "member", "sharon", "staff", "associate", "donor", "vola", "volb", "volc") as $cat) {
            $itag = sprintf("%s_%s", $cat, $id);
            $tag = MakeTag($itag);
            $checked = "";
            if (array_key_exists($id, $attributes)) {
                $checked = $attributes[$id][$cat] ? "checked" : "";
                $sort_key = $attributes[$id][$cat] ? "1" : "0";
                $val = $attributes[$id][$cat] ? 0 : 1;
            } else {
                $val = 1;
            }
            $jsx = array();
            $jsx[] = "setValue('from','DisplayMembers')";
            $jsx[] = "addField('$itag')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            $js = "";
            $ajax_id = "id=\"member_attributes__{$cat}__{$id}\"";
            echo "<td class=box sorttable_customkey=$sort_key><input class=ajax type=\"checkbox\" $ajax_id $checked $js value=\"$val\"></td>\n";
        }

        echo "</tr>\n";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";

    if ($gTrace)
        array_pop($gFunction);
}

function MembersEdit() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    
    $id = $_POST['id'];
    if( $id == 0 ) {
        $stmt = DoQuery( "select max(ID) from members" );
        list($id) = $stmt->fetch(PDO::FETCH_NUM);
        if( $id > 10000  ) {
            $id++; # Use the next available
        } else {
            $id = 10000; #$ start manual adds here
        }
        DoQuery(  "insert into members (ID,Status) values ($id,'Member')" );
        DoQuery(  "insert into member_attributes (id) values ($id)" );
    }
    
    echo "<div class=center>";

    echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";
    
    $stmt = DoQuery( "select m.*, a.* from members m join member_attributes a on m.ID = a.id where m.ID = :id", [':id' => $id] );
    $rec = $stmt->fetch(PDO::FETCH_ASSOC);

    $prompt =  "Are you sure you want to delete the member record for {$rec['Last Name']} ($id)";
    $jsx = array();
    $jsx[] = "setValue('from','" .  __FUNCTION__ . "')";
    $jsx[] = "setValue('func','delete')";
    $jsx[] = "setValue('id',$id)";
    $jsx[] = "myConfirm('$prompt')";
    $js = sprintf( "onclick=\"%s\"", implode(';',$jsx));
    echo "<input type=button value=Delete $js>";
    
    echo "<br>";
    echo "<br>";
    echo "<table>";
    echo  "<tr>";
    $key = "Last Name";
    echo "<th>$key</th>";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<td>" . "<input type=text class=ajax $ajax_id value='" . $rec[$key] . "' size=20 tabindex=1></td>";
    echo "</tr>";
    
    $options = [ 'Member', 'Sharon', 'Associate', 'Staff'];
    foreach( $options as $opt ) {
        echo "<tr>";
        $key = strtolower($opt);
        echo "<th>$opt</th>";
        $ajax_id = "id=\"member_attributes__{$key}__{$id}\"";
        if( $rec[$key] ) {
            $checked = "checked";
            $val  = 0;
        } else {
            $checked  = "";
            $val = 1;
        }
        echo "<td><input class=ajax type=\"checkbox\" $ajax_id $checked value=\"$val\"></td>\n";
        echo "</tr>";
    }
    
    $options = [ '-- Select --', 'Couple', 'Divorced', 'Married', 'Member', 'Separated', 'Single', 'Widow', 'Widowed'];
    echo "<tr>";
    $key = "Marital Status";
    echo "<th>$key</th>";
    echo "<td>";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<select class=ajax $ajax_id tabindex=2>";
    foreach( $options as $opt ) {
        $selected = ( $rec[$key] == $opt ) ? 'selected' : '';
        echo "<option value='$opt' $selected>$opt</option>";
    }
    echo "</select>";
    echo "</td>";
    
    echo "</tr>";
    echo "</table>";
    
    echo "<br>"; #--------------
    
    echo "<table>";
    
    echo "<thead>";
    echo "<tr>";
    echo "<th>Item</th>";
    echo "<th>1st Person</th>";
    echo "<th>2nd Person</th>";
    echo  "</tr>";
    echo "</thead>";
    
    echo "<tbody>";
    
    echo "<tr>";
    echo  "<th>First Name</th>";    
    $key = "Female 1st Name";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<td>" . "<input type=text class=ajax $ajax_id value='" . $rec[$key] . "' size=20 tabindex=3></td>";
    $key = "Male 1st Name";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<td>" . "<input type=text class=ajax $ajax_id value='" . $rec[$key] . "' size=20 tabindex=6></td>";
    echo "</tr>";

    
    echo "<tr>";
    echo "<th>Tribe</th>";  
    $key = "ftribe";
    $ajax_id = "id=\"member_attributes__{$key}__{$id}\"";
    echo "<td>";
    echo "<select class=ajax $ajax_id tabindex=4>";
    foreach( ["", "Yisrael", "Levi", "Kohen"] as $opt ) {
        $selected = ( $rec[$key] == $opt ) ? 'selected' : '';
        echo "<option value='$opt' $selected>$opt</option>";
    }
    echo  "</select>";
    echo "</td>";
    $key = "mtribe";
    $ajax_id = "id=\"member_attributes__{$key}__{$id}\"";
    echo "<td>";
    echo "<select class=ajax $ajax_id tabindex=7>";
    foreach( ["", "Yisrael", "Levi", "Kohen"] as $opt ) {
        $selected = ( $rec[$key] == $opt ) ? 'selected' : '';
        echo "<option value='$opt' $selected>$opt</option>";
    }
    echo  "</select>";
    echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo  "<th>E-Mail</th>";    
    $key = "E-Mail Address";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<td>" . "<input type=text class=ajax $ajax_id value='" . $rec[$key] . "' size=30 tabindex=5></td>";
    $key = "E-Mail Address 2";
    $ajax_id = "id=\"members__{$key}__{$id}\"";
    echo "<td>" . "<input type=text class=ajax $ajax_id value='" . $rec[$key] . "' size=30 tabindex=8></td>";
    echo "</tr>";


    echo "</tbody>";
    echo  "<table>";
    
    echo "</div>";
    if ($gTrace)
        array_pop($gFunction);
}

function MembersUpdate() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $gArea = $_POST['area'];
    
    if( $gArea == "attributes") {
        DoQuery("start transaction");
        $tmp = array_unique(preg_split('/,/', $_POST['fields']));
        foreach ($tmp as $field) {
            if (empty($field))
                continue;
            list( $f, $id ) = preg_split('/_/', $field);
            $new_val = array_key_exists($field, $_POST) ? 1 : 0;
            DoQuery("select * from member_attributes where id = $id");
            if ($gPDO_num_rows > 0) {
                DoQuery("update member_attributes set `$f` = $new_val where id = $id");
            } else {
                DoQuery("insert into member_attributes set `$f` = $new_val, `id` = $id");
            }
        }
        DoQuery("commit");
        
    } elseif( $gArea == "members" ) {
        if( $gFunc == "update" ) {
            $tmp = preg_split('/,/', $_POST['fields']);  // This is what was touched
            $keys = array_unique($tmp);
            $qx = array();
            $flds = $args = [];

            foreach ($keys as $key) {
                $i = count($args) + 1;
                $flds[] = "`$key` = :v$i";
                $tkey = preg_replace('/ /', '_', $key );
                $args[":v$i"] = CleanString($_POST[$tkey]);
            }
            DoQuery( "update members set " . join(',', $flds ) . " where id = " . $_POST['id'], $args );
        } elseif( $gFunc == "delete" ) {
            DoQuery("delete from members where id = " . $_POST['id']);
        }
    }

    if ($gTrace)
        array_pop($gFunction);
}

function PayPal() {
    include 'includes/globals.php';
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
	<input type="image" src="
END;
    echo DIR;
    echo <<<END
    images/Donate_sm.jpg" border="0"
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
function Phase1() {     # Phase1 is for pre-output actions that would interfere with PDF production
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;
    
    $dpv_pre = "Begin";
    $dpv_phase = 1;
    $dpv_tag = "pre-html";

    $str = "Phase: $dpv_phase, gDebug: $gDebug";
    echo "<!-- $str -->";
    error_log($str);
    
    if ($gDebug) {
        DumpPostVars(sprintf("++ %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }

    addForm();

    $val = 0;
    if ($user->is_logged_in()) {
        Logger("user logged in");
        UserManager('load', $_SESSION['userid']);
        $saveDb = $gDb;
        $gDb = $gPDO[0]['inst'];
        $stmt = DoQuery("select debug from users where id = $gUserId");
        list($val) = $stmt->fetch(PDO::FETCH_NUM);
        $gDebug = $val;
        $gDb = $saveDb;
    }


    switch ($gAction) {
        case( 'Back' ):
            break;
        
        case 'backup':
            BackupMySql();
            break;
        
        case( 'New' ):
            if ($gFrom == "UserReleaseNotes") {
                $gAction = "Update";
            }
            break;

        case 'Reset':
            if (array_key_exists('password', $_POST)) {
                UserManager('reset');
            }
            break;

        case 'Special':
            SpecialCode();
            $gAction = "Main";
            break;

        default:
            if ($gFrom == "UserFeatures") {
                $gAction = "Update";
            } elseif ($gFrom == "UserManager") {
                $gAction = "Update";
            } elseif ($gFrom == "UserReleaseNotes") {
                $gAction = "Update";
            } elseif (empty($gAction)) {
                $gAction = "Start";
            } else {
                Logger('** No action taken for Phase #1 **');
            }
            break;
    }
    if ($gDebug) {
        $dpv_pre = "End";
        DumpPostVars(sprintf("-- %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }
    array_pop($gFunction);
}

function Phase2() {
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;

    $dpv_pre = "Begin";
    $dpv_phase = 2;
    $dpv_tag = "perform actions/updates";

    $str = "Phase: $dpv_phase, gDebug: $gDebug";
    echo "<!-- $str -->";
    error_log($str);
    
    if ($gDebug) {
        DumpPostVars(sprintf("++ %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }

    switch ($gAction) {
        case 'Back':
            $gAction = 'Main';
            if ($gFrom == "EditItem") {
                $gAction = 'Main';
                $_POST['area'] = 'items';
            } elseif ($gFrom == "MembersEdit") {
                MembersDisplay();
                exit;
                $gAction = 'Main';
                $gFunc = "members";
            }
            break;

        case( 'Continue' ):
            $gAction = "Start";
            break;

        case( 'forgot' ):
            if ($gArea == 'check') {
                UserManager('forgot');
                $gAction = 'Start';
            }
            break;

        case( 'login' ):
            if( $gFunc == 'verify' ) {
                UserManager($gFunc);
                if($user->is_logged_in()) {
                    $gAction = 'Main';
                } else {
                    $gAction = 'UserManager';
                    $gFunc = 'login';
                }
            } elseif( $gFunc == 'mail' ) {
                loadMailSettings();
                $gMode = "before";
                UserManager($gFunc);
                $gMode ==  "after";
                
            } elseif( $gFunc == 'reset' ) {
                UserManager($gFunc);
                
            }
            break;

        case( 'Mail'):
            if ($gFunc == "validate") {
                MailValidate();
                $gAction = "Mail";
            }

            if ($gFunc == "all") {
                MailAssignments($gFunc);
                $gAction = "Mail";
            }

            if ($gFunc == "unsent") {
                MailAssignments($gFunc);
                $gAction = "Mail";
            }

            if ($gFunc == "noresponse") {
                MailAssignments($gFunc);
                $gAction = "Mail";
            }

            if ($gFunc == "remind-rosh") {
                MailAssignments($gFunc);
                $gAction = "Mail";
            }

            if ($gFunc == "remind-yom") {
                MailAssignments($gFunc);
                $gAction = "Mail";
            }

            break;

        case( 'Main' ):
        case( 'main' ):
            if ($gFunc == "backup") {
                Logger("About to perform backup ...");
                exec("perl /home/cbi18/bin/hh_honors_backup.pl", $out);
                Logger($out);
            }

            if ($gFunc == "members") {
                MembersDisplay();
                exit;
            }

            if ($gFunc == "build-memb") {
                BuildMembers();
                $gAction = 'Main';
            }

            if ($gFunc == "comp-memb") {
                CompareMembers();
                $gAction = 'Done';
            }

            if ($gFunc == "responses") {
                Responses();
                $gAction = "Mail";
            }
            
            if ($gFunc == "source") {
                array_map('unlink', glob("tmp/*.sql")); # delete all the existing .sql files
                array_map('unlink', glob("tmp/*.txt")); # delete all the existing .txt files

                $saveDb = $gDb;

                for ($dbx = 0; $dbx < count($gPDO); $dbx++) {
                    $gDb = $gPDO[$dbx]['inst'];
                    $level1 = DoQuery("show tables");
                    while (list($table) = $level1->fetch(PDO::FETCH_NUM)) {
                        if (preg_match("/^gl_/", $table))
                            continue;

                        $tfile = sprintf("tmp/%s_%s.sql", $gPDO[$dbx]['dbname'], $table);
                        $fp = fopen($tfile, 'w');

                        $level2 = DoQuery("show fields from $table");
                        while ($fields = $level2->fetch(PDO::FETCH_NUM)) {
                            for ($i = 0; $i < count($fields); $i++) {
                                if ($fields[$i] == "DEFAULT_GENERATED") {
                                    $fields[$i] = "";
                                }
                            }
                            array_unshift($fields, $table);
                            $str = implode('|', $fields);
                            fwrite($fp, $str . "\n");
                        }

                        fclose($fp);
                    }
                }

                $gDb = $saveDb;
            }

            break;

        case( 'Update' ):
        case( 'update' ):
            if ($gFrom == "Assign") {
                if ($gFunc == "add") {
                    AssignAdd();
                    $gAction = "Assign";
                } elseif ($gFunc == "del") {
                    AssignDel();
                    $gAction = "Assign";
                } elseif ($gFunc == "mail") {
                    MailAssignment();
                    $gAction = "Assign";
                } elseif ($gFunc == "mails") {
                    MailAssignments();
                    $gAction = "Assign";
                } elseif ($gFunc == "manual") {
                    captureResponse();
                    SendConfirmation();
                    $gAction = "Assign";
                }
            } elseif ($gFrom == "displayDates") {
                updateDates();
                $gAction = 'Main';
                $_POST['area'] = 'dates';
                
            } elseif( $gFrom  == "displayMisc" ) {
                updateMisc();
                $gAction = "misc";
                
            } elseif( $gFrom == "resetHash" ) {
                if( $gFunc  == "delete"  ) {
                    resetHash();
                }
                $gAction = "Main";
                
            } elseif ($gFrom == "LogfileDisplay") {
                if ($gFunc == "log-reset") {
                    LogfileReset();
                    LogfileDisplay();
                    $gAction = "Done";
                }
            } elseif ($gFrom == "MailDisplay") {
                MailUpdate();
                $gAction = 'Main';
                $_POST['area'] = 'mail';
            } elseif ($gFrom == "MembersDisplay") {
                MembersUpdate();
                MembersDisplay();
                exit;
            } elseif ($gFrom == "MembersEdit") {
                if( $gFunc  == "delete" ) {
                    deleteMember();
                    MembersDisplay();
                } else  {
                    MembersUpdate();
                    MembersEdit();
                }
                exit;
                    
            } elseif ($gFrom == "DisplayFinancial") {
                PledgeUpdate();
                $gAction = 'Main';
            } elseif ($gFrom == "DisplaySpiritual") {
                PledgeUpdate();
                $gAction = 'Main';
            } elseif ($gFrom == 'displayMain') {
                if ($gArea == 'reset') {
                    DoQuery("start transaction");
                    DoQuery("update items set status = 0 where status = 1");
                    DoQuery("commit");
                } elseif( $gArea == 'responses' ) {
                    DoQuery("delete from replies where jyear = $gJewishYear");
                    $j = 0;
                    $columns = [];
                    $columns[] = "accepted = 0";
                    $columns[] = "declined = 0";
                    $columns[] = "active = 1";
                    $columns[] = "sent = NULL";
                    $str = implode(",", $columns);
                    DoQuery("update assignments set $str where jyear = $gJewishYear" );
                }
                $gAction = 'Main';
            } elseif ($gFrom == "HonorsEdit") {
                HonorsReSort();
                CreateHonors();
                $gAction = "Honors";
                $gFunc = "edit";
            } elseif ($gFrom == "UserManagerPrivileges") {
                UserManager('update');
                $gAction = 'Main';
                $gFunc = 'privileges';
            } elseif ($gFrom == 'Users') {
                UserManager('update');
                $gAction = "Main";
                $gFunc = 'users';
            } elseif ($gFrom == 'PledgeEdit') {
                PledgeUpdate();
                $gAction = 'Main';
            } elseif ($gFrom == "EditItem") {
                UpdateItem();
                $gAction = 'Main';
                $_POST['area'] = 'items';
            } elseif ($gFrom == "DisplayItems") {
                UpdateItem();
                $gAction = 'Main';
                $_POST['area'] = 'items';
            } elseif ($gFrom == "DisplayCategories") {
                UpdateCategories();
                $gAction = 'Main';
                $_POST['area'] = 'categories';
            } elseif ($gFrom == "MyDebug") {
                MyDebug();
                $gAction = 'Debug';
                $gFunc = 'display';
            } else {
                UserManager('update');
                $gAction = 'Welcome';
            }
            break;
            
        case "UserManager":
            if ($gFrom == "UserManagerPassword") {
                UserManager('update');
                $gAction = 'Main';
            } elseif( $gFunc == 'mail' ) {
                loadMailSettings();
                UserManagerMail();
                $gAction = 'Main';
                $gFunc = 'users';
            }
            break;

    }
    if ($gDebug) {
        $dpv_pre = "End";
        DumpPostVars(sprintf("-- %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }
    array_pop($gFunction);
}

function Phase3() { # Display
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;

    $dpv_pre = "Begin";
    $dpv_phase = 3;
    $dpv_tag = "Display";

    $str = "Phase: $dpv_phase, gDebug: $gDebug";
    echo "<!-- $str -->";
    error_log($str);
    
    if ($gDebug) {
        DumpPostVars(sprintf("++ %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }

    if( $gFunc == 'mail' ) {
        loadMailSettings();
    }
    
    $vect = $args = array();

    $vect['Assign'] = 'Assign';
    $vect['Debug'] = 'MyDebug';
    $vect['Edit'] = 'EditManager';
    $vect['Honors'] = 'HonorsEdit';
    $vect['Inactive'] = 'UserManager';
    $vect['Log'] = 'LogfileDisplay';
    $vect['Login'] = 'UserManager';
    $vect['Logout'] = 'UserManager';
    $vect['Mail'] = 'MailDisplay';
    $vect['Main'] = 'displayMain';
    $vect['New'] = 'UserManager';
    $vect['Resend'] = 'UserManager';
    $vect['Start'] = 'UserManager';
    $vect['UserManager'] = 'UserManager';
    $vect['Welcome'] = 'displayMain';
    $vect['backup'] = 'displayMain';
    $vect['forgot'] = 'UserManager';
    $vect['hash'] = 'resetHash';
    $vect['login'] = 'UserManager';
    $vect['misc'] = 'displayMisc';
    $vect['reset'] = 'UserManager';

    $args['Inactive'] = array('inactive');
    $args['Login'] = array('verify');
    $args['Logout'] = array('logout');
    $args['New'] = ['new'];
    $args['Resend'] = array('resend');
    $args['Start'] = array('login');
    $args['UserManager'] = array($gFunc);
    $args['forgot'] = array('forgot');
    $args['login'] = array('login');
    $args['reset'] = array('newpassword');

//    echo "<div class=center>";
    if (!empty($vect[$gAction])) {
        $fn = $vect[$gAction];
        $arg = array_key_exists($gAction, $args) ? $args[$gAction] : [];
        switch (count($arg)) {
            case( 0 ):
                $fn();
                break;

            case( 1 ):
                $fn($arg[0]);
                break;

            case( 2 ):
                $fn($arg[0], $arg[1]);
                break;
        }
    } else {
        switch ($gAction) {
            case( 'Done' ):
                break;

            case 'password';
                loadMailSettings();
                if ($gFunc == 'getemail') {
                    UserManager('forgot');
                } elseif ($gFunc == 'send') {
                    UserManager('welcome');
                } elseif ($gFunc == 'welcome') {
                    UserManager('welcome');
                } elseif ($gFunc == "reset") {
                    UserManager('reset');
                }
                break;

            case( 'Reset Password' ):
                loadMailSettings();
                UserManager('reset');
                SessionStuff('logout');
                break;

            default:
                echo "action: $gAction<br>";
                echo "I'm sorry but something unexpected occurred.  Please send all details<br>";
                echo "of what you were doing and any error messages to $gSupport<br>";
                echo "<input type=submit name=action value=Back>";
        }
    }

    echo "</div>";
    if ($gDebug) {
        $dpv_pre = "End";
        DumpPostVars(sprintf("-- %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }
    array_pop($gFunction);
}

function Phase4() {     # Write out javascript error code
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;
    
    $dpv_pre = "Begin";
    $dpv_phase = 4;
    $dpv_tag = "pre-html";

    $str = "Phase: $dpv_phase, gDebug: $gDebug";
    echo "<!-- $str -->";
    error_log($str);
    
    if ($gDebug) {
        DumpPostVars(sprintf("++ %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }

    if( count( $gError ) ) {
        echo "<script type=\"text/javascript\">\n";
        foreach( $gError as $ln ) {
            echo  "$ln\n";
        }
        echo "</script>\n";
    }
    if ($gDebug) {
        $dpv_pre = "End";
        DumpPostVars(sprintf("-- %s Phase #%d (%s): gAction: [%s], gFrom: [%s], gMode: [%s], gArea: [%s], gFunc: [%s]",
                        $dpv_pre, $dpv_phase, $dpv_tag, $gAction, $gFrom, $gMode, $gArea, $gFunc));
    }
    array_pop($gFunction);
}

function PledgeEdit() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $area = $_POST['area'];
    $stmt = DoQuery("select * from pledges where id = '$id'");
    $rec = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<input type=button value=Back onclick=\"setValue('from', 'PledgeEdit');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$area')";
    $jsx[] = "setValue('from','PledgeEdit')";
    $jsx[] = "setValue('id','$id')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<div class=CommonV2>";

    if ($area == 'financial') {
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
            $jsx[] = "setValue('area','$area')";
            $jsx[] = "setValue('from','PledgeEdit')";
            $jsx[] = "addField('$key')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onKeyDown=\"%s\"", join(';', $jsx));
            printf("<td><input type=text size=50 name=%s value=\"%s\" $js></td>", $key, $rec[$key]);
            echo "</tr>";
        }
        $jsx = array();
        $jsx[] = "setValue('area','$area')";
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
        $jsx[] = "setValue('area','$area')";
        $jsx[] = "setValue('from','PledgeEdit')";
        $jsx[] = "addField('paymentMethod')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        echo "<td><select $tag $js>\n";
        foreach ($types as $val => $label) {
            $selected = ( $val == $rec['paymentMethod'] ) ? "selected" : "";
            echo "<option value=$val $selected>$label</option>\n";
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
    include 'includes/globals.php';
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
    $id = $gPDO_lastInsertID;

    if ($gTrace)
        array_pop($gFunction);
    return $id;
}

function PledgeUpdate() {
    include 'includes/globals.php';
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
            $stmt = DoQuery("select * from pledges where id = $id");
            $rec = $stmt->fetch(PDO::FETCH_ASSOC);
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

function Responses() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<div class=center>";
    echo "<div class=CommonV2>";

    echo "<br><br>";

    $query = "select a.* from replies a";
    $query .= " join members c on a.member_id=c.id";
    $query .= " where a.accepted = 1 and a.jyear = $gJewishYear";
    $query .= " order by a.updated desc";
    $accepts = DoQuery($query);
    $banner = sprintf("<a href=#accepts>Acceptances</a>: %d", $gPDO_num_rows);

    $query = "select a.* from replies a";
    $query .= " join members c on a.member_id=c.id";
    $query .= " where a.declined = 1 and a.jyear = $gJewishYear";
    $query .= " order by a.updated desc";
    $declines = DoQuery($query);
    $banner .= sprintf(", <a href=#declines>Declines</a>: %d", $gPDO_num_rows);

    $stmt = DoQuery("select sum(donation) from replies where jyear = $gJewishYear");
    list( $amount ) = $stmt->fetch(PDO::FETCH_NUM);
    $banner .= sprintf(", Amount Raised: \$ %s", number_format($amount));
    $banner .= "&nbsp;&nbsp;&nbsp;";
    $banner .= "<input type=button value=Refresh onclick=\"setValue('from', '" . __FUNCTION__ . "');setValue('func','responses');addAction('Main');\">";
    $banner .= "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";

    $control = UserManager('authorized', 'control');

    echo "<h2 id=accepts>$banner</h2>";
    echo "<h3>Acceptances</h3>";

    echo "<table class=sortable>";
    echo "<tr>";
    echo "  <th>Id</th>";
    echo "  <th>Time</th>";
    echo "  <th>Member</th>";
    echo "  <th>Honor</th>";
    echo "  <th>Amount</th>";
    echo "  <th>PayBy</th>";
    echo "</tr>";

//    while (list( $time, $hid, $mid, $donation, $payby, $comment ) = $accepts->fetch(PDO::FETCH_NUM)) {
    while( $accept = $accepts->fetch(PDO::FETCH_ASSOC) ) {
        $id = $accept['id'];
        $time = $accept['updated'];
        $hid = $accept['honor_id'];
        $mid  = $accept['member_id'];
        $hash  = $accept['hash'];
        $donation = $accept['donation'];
        $payby  = $accept['payby'];
        $comment = $accept['comment'];
            
        $stmt = DoQuery("select * from members where id = $mid");
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = DoQuery("select * from honors where id = $hid");
        $honor = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<tr>";
        $name = formatName($mid);
        $rows = empty($comment) ? 1 : 2;
        printf("<td class=c rowspan=$rows>%d</td>", $id);
        printf("<td class=c rowspan=$rows>%s</td>", preg_replace("/ /", "<br>", $time));
        echo "<td rowspan=$rows>$name</td>\n";
        printf("<td>%s</td>\n", $honor['honor']);
        $style = ( $donation > 0 ) ? "text-align:right;background-color:lightgreen;" : "text-align:right";
        printf("<td style='%s'>\$ %s</td>", $style, number_format($donation, 2));
        printf("<td class=c>%s</td>", $gPayMethods[$payby]);
        echo "</tr>";
        if (!empty($comment)) {
            echo "<tr>";
            echo "<td colspan=3><textarea cols=120>$comment</textarea></td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<br><br>";
    echo "<h2 id=declines>$banner</h2>";
    echo "<h3>Declines</h3>";


    echo "<table class=sortable>";
    echo "<tr>";
    echo "  <th>Id</th>";
    echo "  <th>Time</th>";
    echo "  <th>Member</th>";
    echo "  <th>Honor</th>";
    echo "  <th>Amount</th>";
    echo "  <th>Pay By</th>";
    echo "</tr>";

//    while (list( $time, $hid, $mid, $donation, $payby, $comment ) = $declines->fetch(PDO::FETCH_NUM)) {
    while ($decline = $declines->fetch(PDO::FETCH_ASSOC)) {
        $id =  $decline['id'];
        $time = $decline['updated'];
        $hid = $decline['honor_id'];
        $mid = $decline['member_id'];
        $donation = $decline['donation'];
        $payby  = $decline['payby'];
        $comment = $decline['comment'];
        $stmt = DoQuery("select * from honors where id = $hid");
        $honor = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<tr>";
        $name = formatName($mid);
        $rows = empty($comment) ? 1 : 2;
        printf("<td class=c rowspan=$rows>%d</td>", $id);
        printf("<td class=c rowspan=$rows>%s</td>", preg_replace("/ /", "<br>", $time));
        echo "<td rowspan=$rows>$name</td>\n";
        printf("<td>%s</td>\n", $honor['honor']);
        $style = ( $donation > 0 ) ? "text-align:right;background-color:lightgreen;" : "text-align:right";
        printf("<td style='%s'>\$ %s</td>", $style, number_format($donation, 2));
        printf("<td class=c>%s</td>", $gPayMethods[$payby]);
        echo "</tr>";
        if (!empty($comment)) {
            echo "<tr>";
            echo "<td colspan=3><textarea cols=120>$comment</textarea></td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "</div>";
    echo "</div>";
    exit;

    if ($gTrace)
        array_pop($gFunction);
}
function SendConfirmation() {
//    This was copied from HH_HONORS
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    if( array_key_exists('func', $_POST) && $_POST['func'] == 'manual' ) {
        $fields = preg_split('/,/', $_POST['fields'] );
        $hid = $mid = 0;
        foreach( $fields as $field ) {
            if( preg_match( '/^honor/', $field ) ) {
                list( $na, $hid ) = explode( '_', $field );
            } elseif( preg_match( '/^member/',  $field  ) ) {
                list( $na, $mid ) = explode( '_', $field  );
            }
        }
        $stmt = DoQuery( "select * from assignments where honor_id = $hid and member_id = $mid and jyear = $gJewishYear" );
        if( $gPDO_num_rows == 0 ) return;
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    } else  {
        return;
    }
        
    $hash = $assignment['hash'];
    
    $stmt = DoQuery( "select * from members where id = $mid");
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
    $stmt = DoQuery( "select * from replies where hash = '$hash'" );
    $reply = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $name = formatName($mid);

    $html = $text = array();

    $html[] = "<html><head></head><body>";
    $html[] = sprintf("<img src=\"cid:sigimg\" width=\"%d\" height=\"%d\"/>", $GLOBALS['gMailSignatureImageSize']['width'], $GLOBALS['gMailSignatureImageSize']['height']);

    $html[] = "Congregation B'nai Israel";
    $text[] = "Congregation B'nai Israel";

    $html[] = "";
    $text[] = "";

    $html[] = sprintf("Dear %s,", $name);
    $text[] = sprintf("Dear %s,", $name);

    $html[] = "";
    $text[] = "";

    if ( $assignment['accepted'] ) {
        $str_html = sprintf("Thank you for allowing us to honor you.");
        $str_text = sprintf("Thank you for allowing us to honor you.");
    } elseif ($assignment['declined'] ) {
        $str_html = sprintf("Thank you for letting us know you are declining your honor.");
        $str_text = sprintf("Thank you for letting us know you are declining your honor.");
    }

    $amount = $reply['donation'];

    if (!empty($amount)) {
        $str_html .= sprintf(" We also thank you for your generous donation of \$ %s.", number_format($amount, 2));
        $str_text .= sprintf(" We also, thank you for your generous donation of \$ %s.", number_format($amount, 2));

        if ( $reply['payby'] == $PaymentCredit ) {
            $str_html .= sprintf(" We will be charging your credit card on file.");
            $str_text .= sprintf(" We will be charging your credit card on file.");
        } elseif ($reply['payby'] == $PaymentCheck ) {
            $str_html .= sprintf(" We will be expecting your check in the next few days.");
            $str_text .= sprintf(" We will be expecting your check in the next few days.");
        } elseif ($reply['payby'] == $PaymentCall ) {
            $str_html .= sprintf(" We will be contacting you to arrange for payment.");
            $str_text .= sprintf(" We will be contacting you to arrange for payment.");
        }
    } else {
        $qarr[] = "donation = $amount";
    }

    $html[] = $str_html;
    $text[] = $str_text;

    $html[] = "";
    $text[] = "";

    $comment = $reply['comment'];

    if (!empty($comment)) {
        $html[] = sprintf("We appreciate your following comments:");
        $text[] = sprintf("We appreciate your following comments:");
        $html[] = "";
        $text[] = "";
        $html[] = "&nbsp;&nbsp;&nbsp;&nbsp;$comment";
        $text[] = "    $comment";
    }

    $mid = $member['ID'];

    $html[] = "";
    $text[] = "";

    $html[] = "Sincerely,";
    $text[] = "Sincerely";

    $html[] = "The CBI HH Honors Committee";
    $text[] = "The CBI HH Honors Committee";

    loadMailSettings();
    
         $addrs = [];
        if( $gMailLive ) {
            if( ! empty( $member['E-Mail Address'] ) )
                $addrs[] = $member['E-Mail Address'];
            if( ! empty( $member['E-Mail Address 2'] ) )
                $addrs[] = $member['E-Mail Address 2'];

            if (empty($email))
                return;
        } elseif( ! empty($gMailTesting) ) {
            foreach( $gMailTesting as $addr ) {
                $addrs[] = $addr;
            }
        }

        $html[] = "</body></html>";

    $mail = NULL;
    $mail = MyMailerNew();

    //Recipients
        foreach( $addrs as $obj ) {
            $mail->AddAddress( $obj['email'], $obj['name']);
        }
    $mail->setFrom('cbi18@cbi18.org', 'CBI');

    //Attachments
    $mail->AddEmbeddedImage($gMailSignatureImage, 'sigimg', $gMailSignatureImage);

    //Content
    if( $gMailLive ) {
        $mail->Subject = "$gJewishYear CBI High Holy Day Honor Confirmation";
    } else {
        $mail->Subject = "$gJewishYear CBI High Holy Day Honor Confirmation ** Test Mode **";
    }

        $mail->Body = implode('<br>',$html );
        $mail->AltBody = implode('\n',$text);

    $ret = MyMailerSend($mail);

    EventLog('record', [
        'type' => 'mail',
        'userid' => $mid,
        'item' => "Sent confirmation to {$member['Last Name']}, hash: $hash, status: $ret"
    ]);

    if ($gTrace)
        array_pop($gFunction);
}

function SendConfirmationOrig() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $subject = "$gJewishYear CBI High Holy Day Honor";

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

    $html[] = sprintf("Dear %s,", $firstName);
    $text[] = sprintf("Dear %s,", $firstName);

    $html[] = "";
    $text[] = "";

    $button = $_POST['RadioGroup2'];
    if ($button == 'accept') {
        $html[] = sprintf("Thank you for allowing us to honor you.");
        $text[] = sprintf("Thank you for allowing us to honor you.");
    } elseif ($button == 'decline') {
        $html[] = sprintf("Thank you for letting us know you are declining your honor.");
        $text[] = sprintf("Thank you for letting us know you are declining your honor.");
    }

    $amount = $_POST['hh-amount'];
    if (!empty($amount)) {
        $html[] = sprintf("  In addition, thank you for your generous donation of \$ %s.", number_format($amount, 2));
        $text[] = sprintf("  In addition, thank you for your generous donation of \$ %s.", number_format($amount, 2));

        $html[] = "";
        $text[] = "";

        $payment = $_POST['hh-payment'];
        if ($payment == 'credit') {
            $html[] = sprintf("  We will be charging your credit card on file.");
            $text[] = sprintf("  We will be charging your credit card on file.");
        } elseif ($payment == 'check') {
            $html[] = sprintf("  We will be expecting your check in the next few days.");
            $text[] = sprintf("  We will be expecting your check in the next few days.");
        } elseif ($payment == 'call') {
            $html[] = sprintf("  We will be contacting you to arrange for payment.");
            $text[] = sprintf("  We will be contacting you to arrange for payment.");
        }
    }

    $html[] = "";
    $text[] = "";

    $comment = $_POST['hh-comment'];
    if (!empty($comment)) {
        $html[] = sprintf("We appreciate your following comments:");
        $text[] = sprintf("We appreciate your following comments:");
        $html[] = "";
        $text[] = "";
        $html[] = $comment;
        $text[] = $comment;
    }

    $message->setTo(array('bethelster1@gmail.com' => 'Beth Elster'));
    $message->setFrom(array('cbi18@cbi18.org' => 'CBI'));
    $message->setBcc(array('cbi18@cbi18.org' => 'Ana Cottle'));

    $message
            ->setBody(join('<br>', $html), 'text/html')
            ->addPart(join('\n', $text), 'text/plain')
    ;

    MyMail($message);

    $message->setTo(array($email => "$firstName"));
    $message->setBcc(array());
    MyMail($message);


    if ($gTrace)
        array_pop($gFunction);
}

function SpecialCode() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    Logger( "Made it to " . __FUNCTION__ );
    $key = 6;
    
    if( $key == 6 ) {
        $stmt1 = DoQuery( "select ID, `Female 1st Name` from members_master where `Female 1st Name` like '% and'");
        while(  list( $id, $name ) = $stmt1->fetch(PDO::FETCH_NUM) ) {
            $name2 = preg_replace( '/ and/', '', $name );
            DoQuery( "update members_master set `Female 1st Name` = '$name2' where ID = $id");
            DoQuery( "update members set `Female 1st Name` = '$name2' where ID = $id");
        }
        
        $stmt = DoQuery( "select * from members limit 1" );
        $mrow = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt1 = DoQuery( "select * from members_master" );
        while( $row = $stmt1->fetch(PDO::FETCH_ASSOC) ) {
            $id = $row['ID'];
            $stmt2 = DoQuery( "select * from members where ID = $id" );
            if( $gPDO_num_rows == 0 ) {
                $i = 0;
                $fields = $values = [];
                foreach( $mrow as $fld => $val )  {
                    if( array_key_exists($fld, $row ) ) {
                        $fields[] = "`$fld` = :v$i";
                        $values[":v$i"] = $row[$fld];
                        $i++;
                    }
                }
                DoQuery( "insert into members set " . implode(',',$fields), $values );
            }
            
            $stmt2 = DoQuery( "select * from member_attributes where id = $id");
            if( $gPDO_num_rows == 0 ) {
                $fields = $values = [];
                $i = 0;
                $fields[] = "id =  :v$i";
                $values[":v$i"] = $id;
                $i++;
                $fields[] = "ftribe =  :v$i";
                $values[":v$i"] = $row['Female Tribe'];
                $i++;
                $fields[] = "mtribe =  :v$i";
                $values[":v$i"] = $row['Male Tribe'];
                $i++;
                DoQuery( "insert into member_attributes set " . implode(',',$fields), $values );
            }
        }
        
        $stmt1 = DoQuery( "select ID, Status from  members_master");
        while(  list($id,$status)  =  $stmt1->fetch(PDO::FETCH_NUM) ) {
            if( $status == "Member" ) {
                DoQuery( "update member_attributes set member = 1 where id = $id" );
            } elseif( $status ==  "Associate") {
                DoQuery( "update member_attributes set associate = 1 where id = $id" );
            } elseif( $status ==  "Sharon") {
                DoQuery( "update member_attributes set sharon = 1 where id = $id" );
            } elseif( $status ==  "Staff") {
                DoQuery( "update member_attributes set staff = 1 where id = $id" );
            }
        }
    }
    
    if( $key == 5 ) {
        $stmt1 = DoQuery( "select id, service, honor from honors order by id asc" );
        while( list( $id, $service, $honor ) = $stmt1->fetch(PDO::FETCH_NUM ) ) {
            $stmt2 = DoQuery( "select member_id from replies where honor_id = $id and jyear = 5780 and accepted = 1" );
            if( $gPDO_num_rows > 1 ) {
                echo "$id (# accpt - $gPDO_num_rows): $service -> $honor<br>";
                while( list( $mid ) = $stmt2->fetch(PDO::FETCH_NUM )) {
                    $stmt3 = DoQuery( "select * from members where id = $mid" );
                    $rec = $stmt3->fetch(PDO::FETCH_ASSOC);
                    printf( "&nbsp;&nbsp;%s (%d)<br>", $rec['Last Name'], $rec['ID'] );
                }                   
            }
        }
    }
    
    if( $key == 4 ) {
        DoQuery( "update assignments set active = 1, accepted = 0, declined = 0 where `hash` like '8a34ce'" );
        DoQuery( "delete from replies where jyear = 5780 and member_id = 259" );
    }
    
    if ($key == 3) {
        $stmt = DoQuery("select * from assignments order by jyear asc");
        while ($arec = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i = 0;
            $data = $flds = $vals = [];
            foreach ($arec as $key => $val) {
                if (preg_match("/$key/", "id,hash,sent") == 0) {

                    $data[$key] = $val;
                    $flds[] = "`$key`";
                    $vals[] = ":$key";
                }
            }
            $query = sprintf("insert into replies (%s) values (%s)", join(",", $flds), join(",", $vals));
            DoQuery($query, $data);
        }
    }
    
    if( $key == 2 ) {
        $stmt = DoQuery( "select id from member_attributes" );
        while( list($id) = $stmt->fetch( PDO::FETCH_NUM)) {
            $stmt2 = DoQuery( "select id from members where id = $id" );
            if( $gPDO_num_rows == 0 ) {
                DoQuery( "delete from member_attributes where id = $id");
            }
        }
    }
    
    if( $key == 1 ) {
        $stmt = DoQuery("select id, honor from honors_master where honor like \"%\'%\"");
        while (list( $id, $honor ) = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "Id: $id, Honor: [$honor]<br>";
            $str1 = str_replace("'", "&apos;", $honor);
            $str2 = str_replace("\"", "&quot;", $str1);
            DoQuery("update honors_master set honor = :v1 where id = $id", [':v1' => $str2]);
        }
    }
    if ($gTrace)
        array_pop($gFunction);
}

function UpdateCategories() {
    include 'includes/globals.php';
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
        DoQuery("insert into categories set label = '$label'");
    }

    $gCategories = array();
    $stmt = DoQuery("select id, label from categories order by label");
    while (list( $id, $label ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $gCategories[$id] = $label;
    }
    asort($gCategories);

    $gAction = "Edit";
    $_POST['area'] = 'category';
    if ($gTrace)
        array_pop($gFunction);
}

function UpdateItem() {
    include 'includes/globals.php';
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

function WriteBody() {
    include 'includes/globals.php';
    echo "<body onload='loginSetFocus();scrollableTable();'>$gLF";

    if( $gProduction == 0 ) {
        echo "
<style type='text/css'>
p.dev {
    font-size: 16pt;
    text-align: center;
    background-color: red;
}
</style>
<p class=dev>Development Site</p>";
    }

    echo "<div class=center>$gLF";
    echo "<img src=\"assets/CBI_ner_tamid.png\">$gLF";
    echo "<h2>$gJewishYear CBI High Holy Day Honors</h2>$gLF";

    if ($user->is_logged_in()) {
        echo "<br>User: $gUserName<br>";
    }
    echo "</div>$gLF";

    if ($gDebug) {
        echo "<script type='text/javascript'>";
        echo "createDebugWindow();";
        echo "</script>";
    }
}

function WriteFooter() {
    include 'includes/globals.php';

    if (defined('FORM_OPEN')) {
        echo "</form>";
    }
    echo "</div>";
    echo "</body>";
    echo "</html>";
}

function addDebuggerWindow() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    if ($gDebug & $gDebugWindow) {
        echo "<script type='text/javascript'>\n";
        $tag = ($gDreamweaver) ? "Dreamweaver" : "";
        echo "createDebugWindow('$tag');\n";
        echo "var d = new Date();\n";
        echo "debug('--- Non-Production. Start of run @ ' + d + ' ---')\n";
        echo "</script>\n";
    }    
}
function addForm() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<form name=fMain id=fMain method=post action=\"$gSourceCode\">$gLF";

    $hidden = array();
    $hidden[] = 'action';   # what needs to be done or what was pressed
    $hidden[] = 'mode';     # top banner modes, i.e. logout, control, admin, office
    $hidden[] = 'area';     # sidebar area
    $hidden[] = 'bypass';
    $hidden[] = 'crumbs';
    $hidden[] = 'eventID';
    $hidden[] = 'familyId';
    $hidden[] = 'fields';   # what fields were touched: using js(addField)
    $hidden[] = 'filter';
    $hidden[] = 'filter_fy';
    $hidden[] = 'fiscalYear';
    $hidden[] = 'from';     # name of function
    $hidden[] = 'func';     # more detailed description of action
    $hidden[] = 'id';       # overloaded variable
    $hidden[] = 'id2';      # overloaded variable
    $hidden[] = 'key';
    $hidden[] = 'listID';
    $hidden[] = 'parentId';
    $hidden[] = 'reset';
    $hidden[] = 'studentId';
    $hidden[] = 'user_id';
    $hidden[] = 'vars';
    $hidden[] = 'where';    # where the action took place

    foreach ($hidden as $var) {
        $tag = MakeTag($var);
        echo "<input type=hidden $tag>$gLF";
    }
    define('FORM_OPEN', 1);
    if ($gTrace) {
        array_pop($gFunction);
    }
}

function addHtmlHeader() {
    include 'includes/globals.php';

    $tag = 'LOADED_' . __FILE__;
    if (defined($tag))
        return;
    define($tag, 1);

    $styles = array();
    $styles[] = "css/main.css";
    $styles[] = "css/oneColFixCtr.css";
    $styles[] = "css/Common.css";

    $scripts = array();
    $scripts[] = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";    
    $scripts[] = "scripts/sha256.js";
    $scripts[] = "scripts/commonv2.js";
    $scripts[] = "scripts/assign.js";
    $scripts[] = "scripts/main.js";
    $scripts[] = "scripts/sorttable.js";
    $scripts[] = "scripts/my_ajax.js";

    foreach ($styles as $style) {
        printf("<link href=\"%s\" rel=\"stylesheet\" type=\"text/css\" />\n", $style);
    }
    echo "<link rel='shortcut icon' type='image/x-icon' href='assets/favicon.ico' />";

    $force = 1;

    if ($force) {
        $tag = rand(0, 1000);
        $str = "?dev=$tag";
    } else {
        $str = "";
    }
    foreach ($styles as $style) {
        printf("<link href=\"%s$str\" rel=\"stylesheet\" type=\"text/css\" />\n", $style);
    }

    foreach ($scripts as $script) {
        printf("<script type=\"text/javascript\" src=\"%s$str\"></script>\n", $script);
    }
        
    if ($gDebug & $gDebugWindow) {
        echo "<script type='text/javascript'>\n";
        $tag = ($gDreamweaver) ? "Dreamweaver" : "";
        echo "createDebugWindow('$tag');\n";
        echo "var d = new Date();\n";
        echo "debug('--- Non-Production. Start of run @ ' + d + ' ---')\n";
        echo "</script>\n";
    }
}
function captureResponse() {
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;

    if( array_key_exists('func', $_POST) && $_POST['func'] == 'manual' ) {
        $fields = preg_split('/,/', $_POST['fields'] );
        $hid = $mid = 0;
        foreach( $fields as $field ) {
            if( preg_match( '/^honor/', $field ) ) {
                list( $na, $hid ) = explode( '_', $field );
            } elseif( preg_match( '/^member/',  $field  ) ) {
                list( $na, $mid ) = explode( '_', $field  );
            }
        }

        $stmt = DoQuery( "select * from assignments where honor_id = $hid and member_id = $mid and jyear = $gJewishYear" );
        if( $gPDO_num_rows == 0 ) return;
        $assignment  = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $hash = $assignment['hash'];
    
    if( $_POST["area"] ==  'accept' ) {
            $accepted = 1;
            $declined = 0;
            $active =  0;
    } elseif( $_POST["area"]  == 'decline'  ) {
            $accepted = 0;
            $declined = 1;
            $active = 1;
    }

        $columns = $values = [];
        $i = 0;
        $columns[] = "updated = now()";
        $columns[] = "active = :v$i"; $values[":v$i"] = $active; $i++;
        $columns[] = "accepted = :v$i"; $values[":v$i"] = $accepted; $i++;
        $columns[] = "declined = :v$i"; $values[":v$i"] = $declined; $i++;
        $values[":v$i"] = $assignment['id'];
        $query  = "update assignments set " . implode(', ', $columns );
        $query .=  " where id = :v$i";
        DoQuery( $query, $values );

        $donation = $_POST["reply-amount"];
        $method = $_POST["reply-method"];
        
        $columns = $values = [];
        $i = 0;
        $columns[] = "updated = now()";
        $columns[] = "jyear = :v$i"; $values[":v$i"] = $assignment['jyear']; $i++;
        $columns[] = "honor_id = :v$i"; $values[":v$i"] = $hid; $i++;
        $columns[] = "member_id = :v$i"; $values[":v$i"] = $mid; $i++;
        $columns[] = "hash = :v$i"; $values[":v$i"] = $hash; $i++;
        $columns[] = "accepted = :v$i"; $values[":v$i"] = $accepted; $i++;
        $columns[] = "declined = :v$i"; $values[":v$i"] = $declined; $i++;
        $columns[] = "donation = :v$i"; $values[":v$i"] = $donation; $i++;
        $columns[] = "payby = :v$i"; $values[":v$i"] = $method; $i++;
        $columns[] = "comment = :v$i"; $values[":v$i"] = ""; $i++;
        $query = "insert into replies set " . implode(', ', $columns );
        DoQuery( $query, $values );

        $name = formatName($mid);
        $tail = "";
        if( $donation > 0 ) {
            $tail = ", donated \$ " . number_format($donation,2) . ", pay by: " . ucfirst($gPayMethods[$method]);
        }
        if( $accepted ) {
            $msg = "$name accepted honor $hash" . $tail;
        } else {
            $msg = "$name declined honor $hash" . $tail;
        }
        EventLog('record', [
            'type' => 'rsvp',
            'userid' => $gUserId,
            'item' => "$msg"
        ]);
        

    array_pop($gFunction);
}

function checkForDownloads() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    if ($gAction == 'Download') {
        $area = $_POST['area'];
        if ($area == "spiritual") {
            ExcelSpiritual();
        } elseif ($area == "items") {
            ExcelItems();
        } elseif ($area == "gabbai") {
            ExcelGabbai();
        } elseif ($area == "donations") {
            ExcelMoney();
        }
    }
}
function deleteMember() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    $id = $_POST['id'];
    DoQuery( "delete from members where ID = $id");
    DoQuery( "delete from member_attributes where id  = $id" );
    if ($gTrace) {
        array_pop($gFunction);
    }
}    

function displayDates() {
    include( 'includes/globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $area = $_POST['area'];

    echo "<div class=CommonV2>";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";
    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','$area')";
    $jsx[] = "setValue('from','displayDates')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";
    echo "<input type=hidden id=id>";

    printf("<h3>Current date: %s</h3>", date("D M jS, Y, g:i A"));
    echo "<table>";

    echo "<tr>";
    echo "<th>Label</th>";
    echo "<th>Date</th>";
    echo "</tr>\n";

    $stmt = DoQuery("select date from dates where `label` = \"erev\"");
    if ($gPDO_num_rows > 0) {
        list( $val ) = $stmt->fetch(PDO::FETCH_NUM);
        $date = new DateTime($val);
    } else {
        $date = new DateTime();
    }

    $jsx = array();
    $jsx[] = "addField('erev')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));

    echo "<tr>";
    printf("<td>%s</td>", "Erev Rosh Hashanah");
    $tag = MakeTag('erev');
    printf("<td><input $tag $js size=30 value=\"%s\"></td>", $date->format("l, M jS, Y"));
    echo "</tr>\n";

    $date->add(new DateInterval('P1D'));
    echo "<tr>";
    echo "<td>" . $gService['rh1'] . "</td>";
    printf("<td>%s</td>", $date->format("l, M jS, Y"));
    echo "</tr>";

    $date->add(new DateInterval('P1D'));
    echo "<tr>";
    echo "<td>" . $gService['rh2'] . "</td>";
    printf("<td>%s</td>", $date->format("l, M jS, Y"));
    echo "</tr>";

    $date->add(new DateInterval('P7D'));
    echo "<tr>";
    echo "<td>" . $gService['kn'] . "</td>";
    printf("<td>%s</td>", $date->format("l, M jS, Y"));
    echo "</tr>";

    $date->add(new DateInterval('P1D'));
    echo "<tr>";
    echo "<td>" . $gService['yka'] . "</td>";
    printf("<td>%s</td>", $date->format("l, M jS, Y"));
    echo "</tr>";

    echo "<tr>";
    echo "  <td colspan=2 style='background-color:grey;'>&nbsp;</td>";
    echo "</tr>";

    $stmt = DoQuery("select date from dates where `label` = 'reply_date'");
    if ($gPDO_num_rows > 0) {
        list( $val ) = $stmt->fetch(PDO::FETCH_NUM);
        $date2 = new DateTime($val);
    } else {
        $date2 = new DateTime();
    }
    $jsx = array();
    $jsx[] = "addField('reply_date')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));

    echo "<tr>";
    printf("<td>%s</td>", "Email Reply Deadline");
    $tag = MakeTag('reply_date');
    printf("<td><input $tag $js size=30 value=\"%s\"></td>", $date2->format("l, M jS, Y"));
    echo "</tr>\n";

    echo "</table>\n";
    echo "</div>\n";

    if ($gTrace)
        array_pop($gFunction);
}

function displayMain() {
    include( 'includes/globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if ($gArea == 'categories') {
        DisplayCategories();
    } elseif ($gArea == 'dates') {
        displayDates();
    } elseif ($gArea == 'financial') {
        DisplayFinancial();
    } elseif ($gArea == 'items') {
        DisplayItems();
    } elseif ($gArea == 'mail') {
        MailDisplay();
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
        echo "<br>";
        echo "<div class=center>";
        echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";
        echo  "<br><br>";
        echo "</div>";
        
        if (UserManager('authorized', 'control')) {
            Logger('here in auth(control)');
            echo "<div class=control>";
            echo "<br>";
            echo "<h3>Control User Features</h3>";
            echo "
<input type=button onclick=\"setValue('func','source');addAction('Main');\" value=\"Source\">
<input type=button onclick=\"setValue('func','backup');addAction('backup');\" value=\"Backup\">
<input type=button onclick=\"setValue('func','users');addAction('Main');\" value=Users>
<input type=button onclick=\"setValue('func','privileges');addAction('Main');\" value=Privileges>
<input type=button onclick=\"setValue('func','build-memb');addAction('Main');\" value=\"Build Members\">
<input type=button onclick=\"setValue('func','comp-memb');addAction('Main');\" value=\"Compare Members\">
<input type=button onclick=\"setValue('func','display');addAction('Debug');\" value=\"Debug ($gDebug)\">
<input type=button onclick=\"setValue('func','special');addAction('Special');\" value=Special>
<br><br>
";

            echo "</div>";
        }

        if (UserManager('authorized', 'admin')) {
            echo "<div class=admin>";
            echo "<br>";
            echo "<h3>Admin User Features</h3>";

            $jsx = array();
            $jsx[] = "setValue('area','dates')";
            $jsx[] = "addAction('Main')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            $from =  "setValue('from','" . __FUNCTION__ . "')";
            $resetMessage = "Are you sure you want to delete all responses for $gJewishYear?";
            echo "
<input type=button $js value=Dates>
<input type=button onclick=\"$from;setValue('area','mail');addAction('Main');\" value=\"Mail\">
<input type=button onclick=\"$from;setValue('area','misc');addAction('misc');\" value=\"Misc\">
<input type=button onclick=\"$from;setValue('func','users');addAction('Main');\" value=\"Users\">
<input type=button onclick=\"$from;setValue('func','edit');addAction('Honors');\" value=\"Honors List - All Days\">
<input type=button onclick=\"$from;setValue('func','members');addAction('Main');\" value=\"Member List - This Year\">
<input type=button onclick=\"$from;setValue('func','display');addAction('Log');\" value=\"Log File\">
<br><br>
<input type=button onclick=\"$from;setValue('func','hash');addAction('hash');\" value=\"Reset Hash\">
<input type=button onclick=\"$from;setValue('area','responses');setValue('func','reset');myConfirm('$resetMessage');\" value=\"Reset Responses\">
<br><br>
";

            echo "</div>";
        }

        if (UserManager('authorized', 'assign')) {
            echo "<div class=assign>";
            echo "<br>";
            echo "<h3>Assignor</h3>";

            $jsx = array();
            $jsx[] = "setValue('area','assign')";
            $jsx[] = "addAction('Assign')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button $js value='Assign/View'>";
            echo "<br><br>";
            echo "</div>";
        }

        if (UserManager('authorized', 'office')) {
            echo "<div class=office>";
            echo "<br>";
            echo "<h3>Office Staff</h3>";

            $jsx = array();
            $jsx[] = "setValue('area','assign')";
            $jsx[] = "addAction('Assign')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button $js value='View'>";

            echo "<input type=button onclick=\"setValue('area','gabbai');addAction('Download');\" value=\"Gabbai Download\">";

            echo "<input type=button onclick=\"setValue('area','donations');addAction('Download');\" value=\"Money Download\">";

            $jsx = array();
            $jsx[] = "setValue('from','$gFunc')";
            $jsx[] = "setValue('func','responses')";
            $jsx[] = "addAction('Main')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button value='Responses' $js>";
            echo "<br><br>";
            echo "</div>";
        }
    }

    if ($gTrace)
        array_pop($gFunction);
}

function displayMisc() {
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;

    echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";
    
    $jsx = [];
    $jsx[] = "setValue('from','" .  __FUNCTION__ . "')";
    $jsx[] = "setValue('func','new')";
    $jsx[] = "addAction('update')";
    $js = implode(';', $jsx); 
    echo "&nbsp;";
    echo "<td class=c><input type=submit onclick=\"$js\" value=New></td>";
    echo "<br><br>";
    
    echo "<table>";
    
    echo "<thead>";
    echo "<tr>";
    echo "<th>Label</th>";
    echo "<th>Value</th>";
    echo "<th>Action</th>";
    echo "</tr>";
    echo "</thead>";
    
    echo "<tbody>";
    $stmt = DoQuery( "select * from misc order by label asc" );
    while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        $id = $row['id'];
        echo "<tr>";
        $ajax_id = "id=misc__label__$id";
        echo "<td><input class=ajax $ajax_id size=20 value=\"" . $row['label'] . "\"></td>";
        $ajax_id = "id=misc__value__$id";
        echo "<td><input class=ajax $ajax_id size=50 value=\"" . $row['value'] . "\"></td>";
        $jsx = [];
        $jsx[] = "setValue('id',$id)";
        $jsx[] = "setValue('from','" .  __FUNCTION__ . "')";
        $jsx[] = "setValue('func','delete')";
        $jsx[] = "addAction('update')";
        $js = implode(';', $jsx);        
        echo "<td class=c><input type=submit onclick=\"$js\" value=Del></td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    array_pop($gFunction);
}

function formatName($member_id) {
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;
    $stmt = DoQuery( "select * from members where ID = $member_id"  );
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    if( empty($member['Male 1st Name'] ) ) {
        $name = "{$member['Female 1st Name']} {$member['Last Name']}";
    } else if( empty( $member['Female 1st Name'] ) ) {
        $name = "{$member['Male 1st Name']} {$member['Last Name']}";
    } else {
        $name = "{$member['Female 1st Name']} and {$member['Male 1st Name']} {$member['Last Name']}";
    }
    array_pop($gFunction);
    return $name;
}
function loadMailSettings() {
    include 'includes/globals.php';

    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $gMailAdmin = $gMailDefault = $gMailTesting = [];
    $query = "select label, `value`, enabled from mail where lower(label) like '%email:%'";

    $stmt = DoQuery($query);
    if ($gPDO_num_rows == 0) {
        DoQuery("insert into mail (label, value, enabled) values ('Email Server','',0)");
        DoQuery("insert into mail (label, value, enabled) values ('Email: Default','andy.elster@gmail.com, Andy Elster',1)");
    }

    $gMailLive = 0;
    $stmt = DoQuery($query);
    while (list( $label, $value, $enabled ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $tmp = preg_split("/,/", $value, NULL, PREG_SPLIT_NO_EMPTY);
        $j = count($tmp);
        if ($j == 1) {
            $email = $name = $tmp[0];
        } elseif ($j == 2) {
            $email = $tmp[0];
            $name = $tmp[1];
        }
        if (stripos($label, "admin") !== false) {
            $gMailAdmin[] = ['email' => "$email", 'name' => "$name"];

            if( $enabled && ! $gProduction) {
                DoQuery("update mail set enabled = 0 where label = 'Email: Admin'"); # Don't let me send out live emails from home
                echo "<script type=\"text/javascript\">alert('WARNING: Non-Production Machine: gMailLive forced to off');</script>";
                $enabled = false;
            }
            $gMailLive = $enabled;
        } elseif (stripos($label, "default") !== false) {
            $gMailDefault[] = ['email' => "$email", 'name' => "$name"];
        } elseif (stripos($label, "backup") !== false) {
            $gMailBackup[] = ['email' => "$email", 'name' => "$name"];
        } elseif ($enabled && stripos($label, "testing") !== false) {
            $gMailTesting[] = ['email' => "$email", 'name' => "$name"];
        } elseif (stripos($label, "server") !== false) {
            $gMailServer = $gMailDB[$value];
        }
    }

    if (count($gMailAdmin) == 0) {
        $gMailAdmin = $gMailDefault;
    }
    if (count($gMailTesting) == 0) {
        $gMailTesting = $gMailDefault;
    }

    if ($gTrace) {
        array_pop($gFunction);
    }
}
function resetHash() {
    include 'includes/globals.php';
    $gFunction[] = __FUNCTION__;
    
    $hash = array_key_exists('hash',$_POST) ?  $_POST['hash'] : "";
    if( empty($hash) )  {
        echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";
        echo "<br><br>";
        $tag = MakeTag('hash');
        echo  "<input type=text $tag size=5>";
        echo  "&nbsp;";
        echo "<input type=button value=Reset onclick=\"setValue('from', '" . __FUNCTION__ . "');setValue('area','hash');setValue('func','delete');addAction('update');\">";
    } elseif( $gFunc  == "delete" ) {
        DoQuery( "update assignments set active = 1, accepted = 0, declined = 0 where hash = '$hash'");
        DoQuery( "delete from replies where hash = '$hash'");
    }
    array_pop($gFunction);
}
function selectDB() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
    }
    
    $openType = ( func_num_args() == 0 ) ? 'local' : 'remote';
    for ($i = 0; $i < count($gPDO); $i++) {
        $gPDO[$i]['open'] = false;
        if( $gPDO[$i]['mode'] != $openType ) continue;
        $tmp = [];
        $tmp[] = $gPDO[$i]['host'];
        $tmp[] = 'dbname=' . $gPDO[$i]['dbname'];
        $tmp[] = 'charset=' . $gPDO[$i]['charset'];
        $dsn = implode(';', $tmp);
        $user = $gPDO[$i]['user'];
        $pass = $gPDO[$i]['pass'];
        $attr = $gPDO[$i]['attr'];
        try {
            //create PDO connection
            if ($gProduction) {
                $attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
            } else {
                $attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            }
            $inst = new PDO($dsn, $user, $pass, $attr);
            $gPDO[$i]['inst'] = $inst;
            $gPDO[$i]['open'] = true;
        } catch (PDOException $e) {
            //show error
            echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
            $gDbControl = NULL;
            throw $e;
        }
    }
    $gDb = $gPDO[0]['inst'];
    
    
    LocalInit();
}

function updateDates() {
    include( 'includes/globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $from = $_POST['from'];

    if ($gFunc == "update") {
        $tmp2 = preg_split("/,/", $_POST['fields']);
        $tmp = array_unique($tmp2);

        foreach ($tmp as $field) {
            $str = $_POST[$field];
            $ts = strtotime($str);
            $date = date('Y-m-d', $ts);

            DoQuery("select `date` from dates where `label` = \"$field\"");
            if ($gPDO_num_rows == 0) {
                $query = sprintf("insert into dates set `label` = '%s', `date` = '%s'", $field, $date);
            } else {
                $query = sprintf("update dates set `date` = '%s' where `label` = '%s'", $date, $field);
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

function updateMisc() {
    include( 'includes/globals.php' );
    $gFunction[] = __FUNCTION__;

    $id = $_POST['id'];
    if( $gFunc == "delete" ) {
        DoQuery( "delete from misc where id = $id");

    } elseif( $gFunc == "new" )  {
        DoQuery( "insert into misc (label,value) value ('',  '')");
    }
    array_pop($gFunction);
}