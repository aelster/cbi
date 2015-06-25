<?php
  DoQuery( "select id, service, honor from honors order by sort" );
  echo "<script type='text/javascript'>\n";
  echo "var honors_db = new Array();\n";
  while( list( $id, $service, $honor ) = mysql_fetch_array( $GLOBALS['mysql_result'] ) ) {
    printf( "honors_db.push( { id:%d, service:\"day-%s\", honor:\"%s\", selected:0 } );\n", $id, $service, mysql_escape_string($honor) );
  }
  echo "</script>\n";
  DoQuery( "select id, service, honor from honors order by sort" );
  $honors_res = $GLOBALS['mysql_result'];
  
  $query = "select id, `Last Name`, `Female 1st Name`, `Male 1st Name` from members";
  $query .= " where Status not like 'Non-Member'";
  $query .= " order by `Last Name` asc";
  DoQuery( $query );
  $member_res = $GLOBALS['mysql_result'];
  ?>
  
<div class="container">

  <div class="assign-top">
    <input type=button onclick="setValue('func','assign');addAction('Main');" value="Back">
    <br>
    <br>
  </div>

  <div class="content-box1">
   <p>Filters
    <input type="button" id="filter-reset" value="Reset" onclick="myFilterReset('reset');"/>
    </p>
    <!-- end .content -->
  </div>
  
  <div class="content-box2">
      <input type="button" id="day-rh1" value="Rosh 1" onclick="myDayClick('day-rh1');"/>
      <input type="button" id="day-kn" value="Kol Nidre" onclick="myDayClick('day-kn');"/>
      <input type="button" id="day-ykp" value="YK - PM" onclick="myDayClick('day-ykp');"/>
<br />
      <input type="button" id="day-rh2" value="Rosh 2" onclick="myDayClick('day-rh2');"/>
      <input type="button" id="day-yka" value="YK - AM" onclick="myDayClick('day-yka');"/>
      <input type="button" id="day-all" value="All" onclick="myFilterReset('day-all');"/>      
  <!-- end .content -->
  </div>
  <div class="content-box3">
      <input type="button" id="opt-cohen" value="Cohen" onclick="myPress('opt-cohen');"/>
      <input type="button" id="opt-board" value="Board" onclick="myPress('opt-board');"/>
      <input type="button" id="opt-staff" value="Staff" onclick="myPress('opt-staff');"/>
      <input type="button" id="opt-vola" value="Vol A" onclick="myPress('opt-vola');"/>
      <input type="button" id="opt-volc" value="Vol C" onclick="myPress('opt-volc');"/>
<br />
      <input type="button" id="opt-levi" value="Levi" onclick="myPress('opt-levi');"/>
      <input type="button" id="opt-donor" value="Donor" onclick="myPress('opt-donor');"/>
      <input type="button" id="opt-newmember" value="New Member" onclick="myPress('opt-newmember');"/>      
      <input type="button" id="opt-volb" value="Vol B" onclick="myPress('opt-volb');"/>      
      <input type="button" id="opt-pastpres" value="Past Pres" onclick="myPress('opt-pastpres');"/>      
      <input type="button" id="opt-all" value="All" onclick="myFilterReset('opt-all');"/>      
</div>
<hr />

<div class="cong-box1">
<p>Honors <div id=tot-honors></div></p>
<div class=honors-div>
<?php
  $last_service = "";
  while( list( $id, $service, $honor ) = mysql_fetch_array( $honors_res ) ) {
    if( $service != $last_service && ! empty($last_service) ) {
      echo "<hr>";
    }
    echo "<p id=honor_$id style='display:none' onclick=\"myHonorsClick($id);\">$service: $honor</p>\n";
    $last_service = $service;
  }
?>
</div>
</div>

<div class="cong-box2">
<p>Mode</p>
<input name="mode-view" type="submit" id="mode-view" value="View"><br>
<input name="mode-assign" type="submit" id="mode-assign" value="Assign"><br><br><br><br>
<input name="mode-adddel" type="submit" id="mode-adddel" value="Add/Del"><br>
</div>

<div class="cong-box3">
<p>Congregants</p>
<div class=cong-div>
<?php
  while( list( $id, $last, $ff, $mf ) = mysql_fetch_array( $member_res ) ) {
    echo "<p id=cong_$id onclick=\"myCongClick('cong_' + $id);\">$last, $ff & $mf</p>\n";
  }
?>
</div>
</div>
</div>

</form>
</body>
<script type='text/javascript'>
  button_init();
  myDisplayHonors();
  myPress('opt-all');
</script>
</html>
