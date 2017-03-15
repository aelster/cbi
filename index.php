<?php
echo "
<head>
<style>
table {
      border-collapse: collapse;
      margin-left: 20px;
}

td, th {
      border: 1px solid;
      padding: 4px;
}

.e {
   background-color: #ccf;
}

.v {
   background-color: #ddd;
}

</style>
</head>";

echo "<h3>Directories:</h3>";
echo "<ul>";

$dh = opendir(".");
while( ($file = readdir($dh) ) !== false) {
       if( $file == "." || $file == ".." ) continue;
       if( $file == ".git" ) continue;
       if( is_dir($file) ) {
       	   printf( "<li><a href=%s>%s</a></li>\n", $file, $file );
	   }
}
closedir($dh);
echo "</ul>";
echo "
<h3>Software (As of 3/5/17):</h3>
<table>
<tr><th class=e>PHP</th><td class=v>5.6.30</td></td></tr>
<tr><th class=e>MySQL</th><td class=v>5.6.35</td></td></tr>
<tr><th class=e>Apache</th><td class=v>2.4.25</td></td></tr>
</table>
"
?>