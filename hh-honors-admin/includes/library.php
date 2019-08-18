<?php

function AddForm() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    echo "<form name=fMain id=fMain method=post action=\"$gSourceCode\">$gLF";

    $hidden = array('action', 'area', 'fields', 'func', 'from', 'id', 'key', 'listID', 'eventID', 'bypass');
    foreach ($hidden as $var) {
        $tag = MakeTag($var);
        echo "<input type=hidden $tag>$gLF";
    }
    define('FORM_OPEN', 1);
    if ($gTrace) {
        array_pop($gFunction);
    }
}

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
    $stmt = DoQuery("select * from assignments where jyear = $gJewishYear");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        printf("members_db[%d] = { %s };\n", $row['id'], join(', ', $tmp));
        $used = array_key_exists($row['id'], $member_assigned) ? $member_assigned[$row['id']] : 0;
        $acc = array_key_exists($row['id'], $member_accepted) ? $member_accepted[$row['id']] : 0;
        $rej = array_key_exists($row['id'], $member_declined) ? $member_declined[$row['id']] : 0;
        printf("members_status[%d] = { selected:0, assigned:%d, accepted:%d, declined:%d };\n", $row['id'], $used, $acc, $rej);
    }
    echo "</script>\n";
    $stmt_honors = DoQuery("select id, service, honor from honors order by sort");

    $query = "select id, `Last Name`, `Female 1st Name`, `Male 1st Name`, `Female Tribe`, `Male Tribe` from members";
    $query .= " where Status not like \"Non-Member\"";
    $query .= " order by `Last Name` asc";
    $stmt_member = DoQuery($query);
    ?>
    <div class="container">
        <div class="assign-top">
            <input type=button onclick="setValue('func', 'assign');addAction('Main');" value="Back">
            <input type=button onclick="clearDebugWindow();" value="Clear Debug">
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
                <input type="button" id="opt-cohen" value="Cohen" onclick="myClickCategory('opt-cohen');myDisplayRefresh();"/>
                <input type="button" id="opt-board" value="Board" onclick="myClickCategory('opt-board');myDisplayRefresh();"/>
                <input type="button" id="opt-staff" value="Staff" onclick="myClickCategory('opt-staff');myDisplayRefresh();"/>
                <input type="button" id="opt-vola" value="Vol A" onclick="myClickCategory('opt-vola');myDisplayRefresh();"/>
                <input type="button" id="opt-volc" value="Vol C" onclick="myClickCategory('opt-volc');myDisplayRefresh();"/>
                <br />
                <input type="button" value="None" onclick="myClickCategory('opt-none');myDisplayRefresh();"/>
                <input type="button" id="opt-levi" value="Levi" onclick="myClickCategory('opt-levi');myDisplayRefresh();"/>
                <input type="button" id="opt-donor" value="Donor" onclick="myClickCategory('opt-donor');myDisplayRefresh();"/>
                <input type="button" id="opt-new" value="New Member" onclick="myClickCategory('opt-new');myDisplayRefresh();"/>      
                <input type="button" id="opt-volb" value="Vol B" onclick="myClickCategory('opt-volb');myDisplayRefresh();"/>      
                <input type="button" id="opt-pastpres" value="Past Pres" onclick="myClickCategory('opt-pastpres');myDisplayRefresh();"/>      
                <input type="button" id="opt-other" value="Other" onclick="myClickCategory('opt-other');myDisplayRefresh();"/>      
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
                    if (!empty($ff)) {
                        if ($ft == "Kohen") {
                            $str .= " (C) $ff";
                        } elseif ($ft == "Levi") {
                            $str .= "(L) $ff";
                        } else {
                            $str .= " $ff";
                        }
                    }
                    if (!empty($mf)) {
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
    </form>
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

    $stmt = DoQuery("select honor_id from assignments where member_id = $member_id and jyear = $gJewishYear");
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
            DoQuery("select * from assignments where hash = '$random_hash' and jyear = $gJewishYear");
            $unique = $gPDO_num_rows == 0 ? 1 : 0;
        }
        DoQuery("insert into assignments set jyear = $gJewishYear, honor_id = $honor_id, member_id = $member_id, hash = '$random_hash'");
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
    $vx[] = "sent=1";
    $area = $_POST['area'];
    if ($area == 'accept') {
        $vx[] = "accepted=1";
        $vx[] = "declined=0";
    } elseif ($area == "decline") {
        $vx[] = "accepted=0";
        $vx[] = "declined=1";
    }

    $donation = $_POST['reply-amount'];
    $vx[] = "donation = $donation";

    $method = $_POST['reply-method'];
    $vx[] = "payby = $method";

    $tmp = preg_split("/,/", $_POST['fields']);
    foreach ($tmp as $field) {
        $tmp2 = preg_split("/_/", $field);
        if ($tmp2[0] == "honor") {
            $honor_id = $tmp2[1];
        } elseif ($tmp2[0] == "member") {
            $member_id = $tmp2[1];
        }
    }
    $vx[] = "updated = now()";

    $query = sprintf("update assignments set %s where honor_id = %d and member_id = %d", join(',', $vx), $honor_id, $member_id);
    DoQuery($query);

    $str = join(',', $vx);
    $mid = $GLOBALS['gUserId'];
    $query = "insert into event_log set type='rsvp', time=now(), userid=$mid, item=:v1";
    DoQuery($query, [':v1' => $str]);

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
/*
            if( ! empty($val) && (preg_match( "/DOB/", $key ) || preg_match( "/Anniversary/", $key ) ) ) {
                if( $id == 211 ) echo "Key: $key, old val: $val";
                $tmp = explode('/', $val );
                $tmp[2] += 1900;
                $val = implode('/', $tmp );
                if( $id == 211 ) echo ", new val: $val<br>";
            }
            if( $id == 211 ) echo "loaded val: $val<br>";
 * 
 */
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
            printf("<td>%d</td>", $row['ID']);
            printf("<td>%s, %s %s</td>", $row['Last Name'], $row['Female 1st Name'], $row['Male 1st Name']);
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
            printf("<td>%d</td>", $row['ID']);
            printf("<td>%s, %s %s</td>", $row['Last Name'], $row['Female 1st Name'], $row['Male 1st Name']);
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
                /*
            } elseif (in_array($key, array("Anniversary", "Female DOB", "Male DOB"))) {
                if (empty($row2["$key"])) {
                    $match = 0;
                } else {
                    $d1 = new DateTime($value);
                    $arr = explode("/", $row2["$key"]);
                    if ($arr[2] < 16) {
                        $arr[2] += 2000;
                    } elseif ($arr[2] < 100) {
                        $arr[2] += 1900;
                    }
                    $dstr = sprintf("%4d-%02d-%02d", $arr[2], $arr[0], $arr[1]);
#					printf( "val1: [%s], dstr: [%s]<br>", $value, $dstr );
                    $d2 = new DateTime($dstr);
                    $match = ($d1 == $d2);
                } */
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
            printf("<td>%d</td>", $row1['ID']);
            printf("<td>%s, %s %s</td>", $row1['Last Name'], $row1['Female 1st Name'], $row1['Male 1st Name']);
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

function DateUpdate() {
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

function DisplayDates() {
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
    $jsx[] = "setValue('from','DisplayDates')";
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

function DisplayMain() {
    include( 'includes/globals.php' );
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }

    if ($gArea == 'categories') {
        DisplayCategories();
    } elseif ($gArea == 'dates') {
        DisplayDates();
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
        echo "<input type=button onclick=\"addAction('Logout');\" value=Logout>";

        if (UserManager('authorized', 'control')) {
            Logger('here in auth(control)');
            echo "<div class=control>";
            echo "<h3>Control User Features</h3>";
            echo "
<input type=button onclick=\"setValue('func','source');addAction('Main');\" value=\"Source\">
<input type=button onclick=\"setValue('func','backup');addAction('Main');\" value=\"Backup\">
<input type=button onclick=\"setValue('func','users');addAction('Main');\" value=Users>
<input type=button onclick=\"setValue('func','privileges');addAction('Main');\" value=Privileges>
<input type=button onclick=\"setValue('func','build-memb');addAction('Main');\" value=\"Build Members\">
<input type=button onclick=\"setValue('func','comp-memb');addAction('Main');\" value=\"Compare Members\">
<input type=button onclick=\"setValue('func','display');addAction('Debug');\" value=\"Debug ($gDebug)\">
<input type=button onclick=\"setValue('func','special');addAction('Special');\" value=Special>
";

            echo "</div>";
        }

        if (UserManager('authorized', 'admin')) {
            echo "<div class=admin>";
            echo "<h3>Admin User Features</h3>";

            $jsx = array();
            $jsx[] = "setValue('area','dates')";
            $jsx[] = "addAction('Main')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button $js value=Dates>";

            echo "<input type=button onclick=\"setValue('func','users');addAction('Main');\" value=Users>";
            echo "<input type=button onclick=\"setValue('func','edit');addAction('Honors');\" value=\"Honors List - All Days\">";
            echo "<input type=button onclick=\"setValue('func','members');addAction('Main');\" value=\"Member List - This Year\">";
            echo "<input type=button onclick=\"setValue('area','mail');addAction('Main');\" value=\"Mail\">";
            echo "<input type=button onclick=\"setValue('func','log');addAction('Main');\" value=\"Log File\">";

            echo "</div>";
            echo "<br>";
        }

        if (UserManager('authorized', 'assign')) {
            echo "<div class=assign>";
            echo "<h3>Assignor</h3>";

            $jsx = array();
            $jsx[] = "setValue('area','assign')";
            $jsx[] = "addAction('Assign')";
            $js = sprintf("onClick=\"%s\"", join(';', $jsx));
            echo "<input type=button $js value='Assign/View'>";

            echo "</div>";
            echo "<br>";
        }

        if (UserManager('authorized', 'office')) {
            echo "<div class=assign>";
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
        }
    }

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
        Logger();
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
        Logger();
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
        Logger();
    }
    $ts = time() + $time_offset;
    $str = date('Ymj', $ts);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment;Filename=CBI-HH-Donations-$str.csv");

    $body = [];
    $body[] = '"Last Name","First Name(s)","Date","Amount","Method"';

    $query = "SELECT a.`female 1st name`, a.`male 1st name`, a.`last name`, b.updated, b.donation, b.payby";
    $query .= " from members a join assignments b on a.id = b.member_id";
    $query .= " where b.donation > 0 and b.jyear = $gJewishYear";
    $query .= " order by a.`last name` asc";
    $stmt_outer = DoQuery($query);
    while ($orow = $stmt_outer->fetch(PDO::FETCH_ASSOC)) {
        $values = [];

        $values[] = sprintf('"%s"', $orow["last name"]);
        if (empty($orow["female 1st name"])) {
            $values[] = sprintf('"%s"', $orow["male 1st name"]);
        } elseif (empty($orow["male 1st name"])) {
            $values[] = sprintf('"%s"', $orow["female 1st name"]);
        } else {
            $values[] = sprintf('"%s %s"', $orow["female 1st name"], $orow["male 1st name"]);
        }
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
        Logger();
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

function FYSelect() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger('fy select');
    }

    if (defined('DB_OPEN')) {
        echo "FYSelect can't be called twice";
        exit();
    }
    define('DB_OPEN', true);
    /*
     * First connect to the manager to determine the database
     */
    try {
        //create PDO connection
        if ($gProduction) {
            $gPDO_attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        } else {
            $gPDO_attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        $t = new PDO($gPDO_dsn, $gPDO_user, $gPDO_pass, $gPDO_attr);
    } catch (PDOException $e) {
        //show error
        error_log($e);
        error_log( "connection failed" );
        echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
        $gDbControl = NULL;
        throw $e;
    }

    $id = $idx = 0;
    
    preg_match( '/dbname=(.+)_(.+)_(.+);/', $gPDO_dsn, $matches );
    list( $na, $gPrefix, $gSiteName, $jewishYear) = $matches;

    $gDb = $gDbControl = $gDbVector[$id] = $t;
    $local_dbName[$id] = "{$gPrefix}_{$gSiteName}_{$jewishYear}";
    $local_Label[$id] = $jewishYear;

    if (!array_key_exists('dbId', $_SESSION) || empty($_SESSION['dbId'])) {
        $_SESSION['dbId'] = $idx;
    }
    
    if( array_key_exists( 'dbId', $_SESSION ) ) {
        $_SESSION['dbName'] = $local_dbName[$_SESSION['dbId']];
        $_SESSION['dbLabel'] = $local_Label[$_SESSION['dbId']];
        $gDb = $gDbVector[$_SESSION['dbId']];
    }
    
    LocalInit();
}

function FYSelectOrig() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger('fy select');
    }

    if (defined('DB_OPEN')) {
        echo "FYSelect can't be called twice";
        exit();
    }
    define('DB_OPEN', true);
    /*
     * First connect to the manager to determine the database
     */
    try {
        //create PDO connection
        if ($gProduction) {
            $gPDO_attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        } else {
            $gPDO_attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        $t = new PDO($gPDO_dsn, $gPDO_user, $gPDO_pass, $gPDO_attr);
    } catch (PDOException $e) {
        //show error
        error_log($e);
        error_log( "connection failed" );
        echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
        $gDbControl = NULL;
        throw $e;
    }

    $dbId = 0;
    
    $save_db = $gDb;
    
    $gDb = $gDbControl = $gDbVector[0] = $t;
    $idx = 0;
    if (!array_key_exists('dbId', $_SESSION))
        $_SESSION['dbId'] = $idx;
/*
    if( array_key_exists( 'dbId', $_SESSION ) ) {
        $_SESSION['dbName'] = $local_dbName[$_SESSION['dbId']];
        $_SESSION['dbLabel'] = $local_Label[$_SESSION['dbId']];
        $gDb = $gDbVector[$_SESSION['dbId']];
    }
*/    
    LocalInit();
}

function HonorsEdit() {
    include 'includes/globals.php';
    if ($gTrace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    echo "<div class=center>";
    echo "<input type=button value=Back onclick=\"setValue('from', 'HonorsEdit');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('from','HonorsEdit')";
    $jsx[] = "setValue('area','honors')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    echo "<div class=CommonV2>";
    echo "<table class=honors>";
    echo "<thead>";
    echo "<tr>";
    echo "  <td class=service>Service</td>";
    echo "  <td class=sort>Sort</td>";
    echo "  <td class=si>Shabbat<br>Include</td>";
    echo "  <td class=se>Shabbat<br>Exclude</td>";
    echo "  <td class=honor>Honor</td>";
    echo "  <td class=page>Page</td>";
    echo "  <td>Mail<br>Group</td>";
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

        $tag = sprintf("%03d_%s", $id, "service");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        echo "<td class=service><select name=$tag $js>";
        foreach ($services as $val) {
            $selected = ( $val == $row['service'] ) ? "selected" : "";
            echo "<option value=$val $selected>$val</option>";
        }
        echo "</select></td>";

        $tag = sprintf("%03d_%s", $id, "sort");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        printf("<td class=sort><input type=text size=2 value=%d name=$tag $js></td>", $row['sort']);

        $tag = sprintf("%03d_%s", $id, "shabbat_include");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $checked = $row['shabbat_include'] ? "checked" : "";
        echo "<td class=si><input type=checkbox value=1 name=$tag $js $checked></td>";

        $tag = sprintf("%03d_%s", $id, "shabbat_exclude");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $checked = $row['shabbat_exclude'] ? "checked" : "";
        echo "<td class=se><input type=checkbox value=1 name=$tag $js $checked></td>";

        $tag = sprintf("%03d_%s", $id, "honor");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        echo "<td class=honor><textarea rows=3 cols=50 name=$tag $js>" . $row['honor'] . "</textarea></td>";

        $tag = sprintf("%03d_%s", $id, "page");
        $jsx = array();
        $jsx[] = "setValue('from','HonorsEdit')";
        $jsx[] = "addField('$tag')";
        $jsx[] = "toggleBgRed('update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        printf("<td class=page><input type=text size=2 value=%d name=$tag $js></td>", $row['page']);

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
/*
* This function should not generate any output so Excel downloads can be sent to the local browser
*/
    include( 'includes/globals.php' );
    if ($gUserId > 0) {
        $stmt = DoQuery("select debug from users where userid = $gUserId");
        list($val) = $stmt->fetch(PDO::FETCH_NUM);
    } else {
        $val = 0;
    }
    $gDebug = $gTrace = $_SESSION['debug'] = $val;

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

    $dump = 0;
    if ($dump) {
        $v = array_keys($_SERVER);
        sort($v);
        foreach ($v as $key) {
            printf("_SERVER['%s'] = %s<br>", $key, $_SERVER[$key]);
        }
    }

    $gFrom = array_key_exists('from', $_POST) ? $_POST['from'] : '';
    $gArea = array_key_exists('area', $_POST) ? $_POST['area'] : '';
    $proto = ( array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ) ? "https" : "http";
    $gSourceCode = sprintf("%s://%s%s", $proto, $_SERVER['SERVER_NAME'], $_SERVER['SCRIPT_NAME']);
    $gFunction = array();

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
    $stmt = DoQuery("select ival from dates where label = \"mail_enabled\"");
    list( $mail_enabled ) = $stmt->fetch(PDO::FETCH_NUM);
    $stmt = DoQuery("select ival from dates where label = \"mail_live\"");
    list( $mail_live ) = $stmt->fetch(PDO::FETCH_NUM);

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
        } else {
            list( $maf, $mam, $new ) = $stmt2->fetch(PDO::FETCH_NUM);
            $tmp = array();
            if ($maf != $ft)
                $tmp[] = "ftribe = '$ft'";
            if ($mam != $mt)
                $tmp[] = "mtribe = '$mt'";
            $expected = ( $status == 'New' ) ? 1 : 0;
            if ($new != $expected)
                $tmp[] = "new = $expected";
            if (count($tmp)) {
                $query = "update member_attributes set " . join(',', $tmp) . " where id = $id";
                DoQuery($query);
            }
        }
    }
    $stmt = DoQuery("select * from misc where label = \"email_admin\"");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    list( $gMailAdmin, $gMailAdminName ) = preg_split('/,/', $row['value']);
    $gMailLive = $row['enabled'];
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


    echo "<div class=CommonV2>";
    echo "<table>";
    echo "<tr>";
    echo "  <th>Date/Time</th>";
    echo "  <th>Name</th>";
    echo "  <th>Response</th>";
    echo "</tr>";

    $stmt = DoQuery("select * from event_log where `type` = 'rsvp' order by `time` ASC");
    while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $uid = $event['userid'];
        $member = $members[$uid];
        if (!empty($member['Female 1st Name']) && empty($member['Male 1st Name'])) {
            $name = $member['Female 1st Name'];
        } elseif (empty($member['Female 1st Name']) && !empty($memeber['Male 1st Name'])) {
            $name = $member['Male 1st Name'];
        } else {
            $name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
        }
        $name .= sprintf(" %s", $member['Last Name']);
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

    $mail_override = array_key_exists('override', $_POST ) ? $_POST['override'] : 0;
    
    if ($mail_live == 1 && ! $preview ) {
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
        $str = sprintf("You have the honor of %s during the %s service on %s.", $honor['honor'],
                $gService[$honor['service']], $date->format("l, M jS, Y"));
    } else {
        $str = "Thank you for the support you have given to Congregation B'nai Israel during the past year.";
        $str .= sprintf(" In an effort to show our appreciation, we would like to offer you the honor of %s during the %s service on %s.",
                $honor['honor'], $gService[$honor['service']], $date->format("l, M jS, Y"));
    }

    $html[] = $str;
    $text[] = $str;

    $html[] = "";
    $text[] = "";

    $str = "We ask that you be in the sanctuary at least 30 minutes prior to your honor";
    $str .= " (15 minutes prior if it occurs at the beginning of the service,) and check in with";
    $str .= " the Shamash, the person in charge of making sure that everyone who has an honor";
    $str .= " is in the right place at the right time.";
/*
    if (!$remind) {
        $str .= " We will send you additional detailed information about your honor closer to the date.";
    }
*/
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
        echo "<br>";
        if ($_POST['from'] == "Assign") {
            echo "<br>";
            echo "<input type=button onclick=\"setValue('area','assign');addAction('Assign');\" value=Continue>";
            exit;
        }
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

        $html[] = "</body></html>";
        
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

        DoQuery("update assignments set sent = 1 where hash = '$hash'");
        $userid = $GLOBALS['gUserId'];

        EventLog('record', [
            'type' => 'mail',
            'userid' => $userid,
            'item' => "Sent honor to $name, has: $hash, status: $ret"
        ]);
    }
    if ($gTrace)
        array_pop($gFunction);
}

function MailAssignmentByID() {
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

        DoQuery("update assignments set sent = 1 where hash = '$hash'");
        $userid = $GLOBALS['gUserId'];

        EventLog('record', [
            'type' => 'mail',
            'userid' => $userid,
            'item' => "Sent honor to $name, has: $hash, status: $ret"
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
    $area = $_POST['area'];

    echo "<div class=CommonV2>";
    echo "<input type=button value=Refresh onclick=\"setValue('from', '$gFunc');setValue('area','mail');addAction('Main');\">";
    echo "<input type=button value=Back onclick=\"setValue('from', '$gFunc');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input $tag type=button value=Update $tag $js>";

    echo "<br><br>";

    if (UserManager('authorized', 'control')) {
        echo "<p>";
        echo "These settings are part of the local include file. This is the master on/off switch for all mail. If off, no mail will be sent.";
        echo "</p>";

        echo "<table>";
        echo "<tr>";
        echo "<th>Mail Enabled</th>";
        if ($GLOBALS['mail_enabled']) {
            echo "<td class=cok>OK to send mail</td>";
            $val = "Disable";
            $ival = 0;
        } else {
            echo "<td class=cbad>Mail System DISABLED</td>";
            $val = "Enable";
            $ival = 1;
        }
        $jsx = array();
        $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
        $jsx[] = "setValue('func','update')";
        $jsx[] = "setValue('area','mail')";
        $jsx[] = "addField('mail_enabled=$ival')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo "<td class=c><input type=button value=$val $js></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<th>Mail Admin</th>";
        echo "<td colspan=2>" . array_keys($GLOBALS['mail_admin'])[0] . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<th>Mail Server</th>";
        echo "<td colspan=2>" . $GLOBALS['mail_servers'][0]['server'] . "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<br><br>";
    }

    echo "<table>";
    echo "<tr>";
    echo "<th>Live Mail</th>";
    if ($mail_live) {
        echo "<td class=cok>Send email to members</td>";
        $val = "Disable";
        $ival = 0;
    } else {
        echo "<td class=cbad>Test Mode - Send email to admin</td>";
        $val = "Enable";
        $ival = 1;
    }

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','update')";
    $jsx[] = "setValue('area','mail_live')";
    $jsx[] = "addField('mail_live=$ival')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<td class=c><input type=button value=$val $js></td>";
    echo "</tr>";

    $stmt = DoQuery("select ival from dates where label = 'num_per_batch'");
    if ($gPDO_num_rows == 0) {
        $npb = -1;
    } else {
        list( $npb ) = $stmt->fetch(PDO::FETCH_NUM);
    }
    echo "<tr>";
    echo "<th colspan=2>Send/Batch (-1 => no limit)</th>";
    $tag = MakeTag('num_per_batch');
    echo "<td><input $tag onchange=\"addField('num_per_batch');toggleBgRed('update');\" type=text value=$npb></td>";
    echo "</tr>";

    echo "</table>";

    echo "<br><br>";

    echo "<table>";
    echo "<tr>";
    echo "<th>Ritual VPs</th>";
    $stmt = DoQuery( "select value from misc where label = 'ritual'");
    if( $gPDO_num_rows > 0 ) {
        list($val) = $stmt->fetch(PDO::FETCH_NUM);
    } else {
        $val = "n/a";
    }
    $tag = MakeTag('ritual');
    $js = "onchange=\"addField('ritual');toggleBgRed('update');\"";
    echo "<td><input $tag type=text size=40 value='$val' $js style='font-size: 16pt;'></td>";
    echo "</table>";
    
    echo "<br><br>";
    
    $stmt = DoQuery("select count(*), sum(sent), sum(accepted), sum(declined) from assignments where jyear = $gJewishYear");
    list( $total, $sent, $accepted, $declined ) = $stmt->fetch(PDO::FETCH_NUM);
    printf("%d/%d Aliyot mailed, %d accepted, %d declined<br>", $sent, $total, $accepted, $declined);

    echo "<br><br><br>";
    $tag = MakeTag('preview');
    echo "<input $tag type=checkbox value=1>&nbsp;Preview (don't send)<br>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','validate')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Validate E-Mails' $js>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','all')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Send All Mail' $js>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','unsent')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Send All Unsent' $js>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','noresponse')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Re-send If No Response' $js>";

    echo "<br>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','remind-rosh')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Send Rosh Reminders' $js>";

    $jsx = array();
    $jsx[] = sprintf("setValue('from','%s')", __FUNCTION__);
    $jsx[] = "setValue('func','remind-yom')";
    $jsx[] = "addAction('Mail')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value='Send Yom Reminders' $js>";


    if ($gTrace)
        array_pop($gFunction);
}

function MailUpdate() {
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

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','attributes')";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

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
    echo "  <td class=box>Staff</td>";
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
        if ((!empty($row['Female 1st Name']) ) &&
                (!empty($row['Male 1st Name']) ) &&
                (!preg_match('/ and/', $row['Female 1st Name']) )) {
            $class = 'namew';
            printf("<!-- male: %s, test: %d, female: %s, test: %d -->\n", $row['Male 1st Name'], empty($row['Male 1st Name']), $row['Female 1st Name'], preg_match('/ and/', $row['Female 1st Name']));
        }
        echo "<td class=$class>" . sprintf("%s, %s %s", $row['Last Name'], $row['Female 1st Name'], $row['Male 1st Name']) . "</td>\n";

        $class = ( (!empty($row['Male 1st Name']) ) && empty($attributes[$id]['mtribe']) ) ? "tribew" : "tribe";
        printf("<td class=$class>%s</td>", $attributes[$id]['mtribe']);
        $class = ( (!empty($row['Female 1st Name']) ) && empty($attributes[$id]['ftribe']) ) ? "tribew" : "tribe";
        printf("<td class=$class>%s</td>", $attributes[$id]['ftribe']);
        $checked = ( $attributes[$id]['new'] ) ? "checked" : "";
        $sort_key = ( $attributes[$id]['new'] ) ? 1 : 0;
        printf("<td class=box sorttable_customkey=$sort_key><input type=checkbox $checked disabled></td>\n");

        foreach (array("board", "pastpres", "staff", "donor", "vola", "volb", "volc") as $cat) {
            $itag = sprintf("%s_%s", $cat, $id);
            $tag = MakeTag($itag);
            $checked = "";
            if (array_key_exists($id, $attributes)) {
                $checked = empty($attributes[$id][$cat]) ? "" : "checked";
                $sort_key = empty($attributes[$id][$cat]) ? "0" : "1";
            }
            $jsx = array();
            $jsx[] = "setValue('from','DisplayMembers')";
            $jsx[] = "addField('$itag')";
            $jsx[] = "toggleBgRed('update')";
            $js = sprintf("onclick=\"%s\"", join(';', $jsx));
            echo "<td class=box sorttable_customkey=$sort_key><input type=\"checkbox\" $tag $checked $js value=1></td>\n";
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
    echo "<div class=center>";

    echo "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('id'," . $_POST['id'] . ")";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "setValue('func','update')";
    $jsx[] = "addAction('Update')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Update $tag $js>";

    $tag = MakeTag('update');
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('id'," . $_POST['id'] . ")";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "setValue('func','delete')";
    $txt = sprintf("Are you sure you want to delete this record?");
    $jsx[] = sprintf("myConfirm('%s')", CVT_Str_to_Overlib($txt));
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    echo "<input type=button value=Delete $tag $js>";

    $stmt = DoQuery( "select * from members where id = :id", [':id' => $_POST['id']] );
    $rec = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table>";
    
    echo "<tr>";
    $key = "Last Name";
    $tag = MakeTag($key);
    echo "<th>$key</th>";
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "addField('$key')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));
    echo "<td>" . "<input type=text $tag value='" . $rec[$key] . "' size=20 $js></td>";
    echo "</tr>";
    
    echo "<tr>";
    $key = "Female 1st Name";
    $tag = MakeTag($key);
    echo "<th>$key</th>";
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "addField('$key')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));
    echo "<td>" . "<input type=text $tag value='" . $rec[$key] . "' size=20 $js></td>";
    echo "</tr>";
    
    echo "<tr>";
    $key = "Male 1st Name";
    $tag = MakeTag($key);
    echo "<th>$key</th>";
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "addField('$key')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));
    echo "<td>" . "<input type=text $tag value='" . $rec[$key] . "' size=20 $js></td>";
    echo "</tr>";
    
    $options = [ '-- Select --', 'Couple', 'Divorced', 'Married', 'Member', 'Separated', 'Single', 'Widow', 'Widowed'];
    echo "<tr>";
    $key = "Marital Status";
    $tag = MakeTag($key);
    echo "<th>$key</th>";
    $jsx = array();
    $jsx[] = "setValue('area','members')";
    $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
    $jsx[] = "addField('$key')";
    $jsx[] = "toggleBgRed('update')";
    $js = sprintf("onChange=\"%s\"", join(';', $jsx));
    echo "<td>";
    echo "<select $tag $js>";
    foreach( $options as $opt ) {
        $selected = ( $rec[$key] == $opt ) ? 'selected' : '';
        echo "<option value='$opt' $selected>$opt</option>";
    }
    echo "</select>";
    echo "</td>";
    
    echo "</tr>";
    
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

    $query = "select a.updated, a.honor_id, a.member_id, a.donation, a.payby, a.comment";
    $query .= " from assignments a";
    $query .= " join members c on a.member_id=c.id";
    $query .= " where a.accepted = 1 and a.jyear = $gJewishYear";
    $query .= " order by a.updated desc";
    $accepts = DoQuery($query);
    $banner = sprintf("<a href=#accepts>Acceptances</a>: %d", $gPDO_num_rows);

    $query = "select a.updated, a.honor_id, a.member_id, a.donation, a.payby, a.comment";
    $query .= " from assignments a";
    $query .= " join members c on a.member_id=c.id";
    $query .= " where a.declined = 1 and a.jyear = $gJewishYear";
    $query .= " order by a.updated desc";
    $declines = DoQuery($query);
    $banner .= sprintf(", <a href=#declines>Declines</a>: %d", $gPDO_num_rows);

    $stmt = DoQuery("select sum(donation) from assignments where jyear = $gJewishYear");
    list( $amount ) = $stmt->fetch(PDO::FETCH_NUM);
    $banner .= sprintf(", Amount Raised: \$ %s", number_format($amount));
    $banner .= "&nbsp;&nbsp;&nbsp;";
    $banner .= "<input type=button value=Refresh onclick=\"setValue('from', '" . __FUNCTION__ . "');setValue('func','responses');addAction('Main');\">";
    $banner .= "<input type=button value=Back onclick=\"setValue('from', '" . __FUNCTION__ . "');addAction('Back');\">";

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

    while (list( $time, $hid, $mid, $donation, $payby, $comment ) = $accepts->fetch(PDO::FETCH_NUM)) {
        $stmt = DoQuery("select * from members where id = $mid");
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = DoQuery("select * from honors where id = $hid");
        $honor = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<tr>";
        if (!empty($member['Female 1st Name']) && empty($member['Male 1st Name'])) {
            $name = $member['Female 1st Name'];
        } elseif (empty($member['Female 1st Name']) && !empty($memeber['Male 1st Name'])) {
            $name = $member['Male 1st Name'];
        } else {
            $name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
        }
        $name .= sprintf(" %s", $member['Last Name']);
        $rows = empty($comment) ? 1 : 2;
        printf("<td class=c rowspan=$rows>%d</td>", $hid);
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

    while (list( $time, $hid, $mid, $donation, $payby, $comment ) = $declines->fetch(PDO::FETCH_NUM)) {
        $stmt = DoQuery("select * from members where id = $mid");
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = DoQuery("select * from honors where id = $hid");
        $honor = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<tr>";
        if (!empty($member['Female 1st Name']) && empty($member['Male 1st Name'])) {
            $name = $member['Female 1st Name'];
        } elseif (empty($member['Female 1st Name']) && !empty($memeber['Male 1st Name'])) {
            $name = $member['Male 1st Name'];
        } else {
            $name = $member['Female 1st Name'] . " " . $member['Male 1st Name'];
        }
        $name .= sprintf(" %s", $member['Last Name']);
        $rows = empty($comment) ? 1 : 2;
        printf("<td class=c rowspan=$rows>%d</td>", $hid);
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
    $message->setBcc(array(
        'cbi18@cbi18.org' => 'Ana Cottle',
    ));

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
    $key = 2;
    
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

function WriteHeader() {
    include 'includes/globals.php';

    $tag = 'LOADED_' . __FILE__;
    if (defined($tag))
        return;
    define($tag, 1);

    $styles = array();
    $styles[] = "styles/main.css";
    $styles[] = "styles/oneColFixCtr.css";
    $styles[] = "/css/Common.css";

    $scripts = array();
    $scripts[] = "scripts/assign.js";
    $scripts[] = "scripts/main.js";
    $scripts[] = "scripts/sorttable.js";
    $scripts[] = "/scripts/commonv2.js";

    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>\n";
    if (isset($title)) {
        echo "<title>$title</title>\n";
    }
    foreach ($styles as $style) {
        printf("<link href=\"%s\" rel=\"stylesheet\" type=\"text/css\" />\n", $style);
    }

    $force = 1;

    if ($force) {
        $tag = rand(0, 1000);
        $str = "?dev=$tag";
    } else {
        $str = "";
    }
    echo "<!-- Start of scripts -->\n";
    foreach ($scripts as $script) {
        printf("<script type=\"text/javascript\" src=\"%s$str\"></script>\n", $script);
    }
    echo "<!-- End of scripts -->\n";
    echo "</head>\n";
}
