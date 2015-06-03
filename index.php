<?php

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
?>