<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>CBI HH Honors Assignments</title>
<link href="oneColFixCtr.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="assign.js"></script>
</head>
-->
<div class="container">

  <div class="assign-top">
    <input type=button onclick="setValue('func','assign');addAction('Main');" value="Back">
    <br>
    <br>
  </div>

  <div class="content-box1">
   <p>Filters
    <input type="button" id="filter-reset" value="Reset" onclick="myPress('filter-reset');"/>
    </p>
    <!-- end .content -->
  </div>
  
  <div class="content-box2">
      <input type="button" id="day-rh1" value="Rosh 1" onclick="myPress('day-rh1');"/>
      <input type="button" id="day-kn" value="Kol Nidre" onclick="myPress('day-kn');"/>
      <input type="button" id="day-ykpm" value="YK - PM" onclick="myPress('day-ykpm');"/>
<br />
      <input type="button" id="day-rh2" value="Rosh 2" onclick="myPress('day-rh2');"/>
      <input type="button" id="day-ykam" value="YK - AM" onclick="myPress('day-ykam');"/>
      <input type="button" id="day-all" value="All" onclick="myPress('day-all');"/>      
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
      <input type="button" id="opt-all" value="All" onclick="myPress('opt-all');"/>      
</div>
<hr />

<div class="cong-box1">
<p>Honors</p>
<textarea name="honors" id="honors" cols=50 rows=30>
  Opening the Ark
  Holding the Torah
</textarea>
</div>

<div class="cong-box2">
<p>Mode</p>
<input name="mode-view" type="submit" id="mode-view" value="View"><br>
<input name="mode-assign" type="submit" id="mode-assign" value="Assign"><br><br><br><br>
<input name="mode-adddel" type="submit" id="mode-adddel" value="Add/Del"><br>
</div>

<div class="cong-box3">
<p>Congregants</p>
<textarea name="congregants" id="congregants" cols=50 rows=30>
  Elster, Andy & Beth
  Leavitt, Stacy & Marc
</textarea>
</div>

</div>

</form>
</body>
</html>
