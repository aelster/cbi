<?php

//set timezone
date_default_timezone_set('America/Los_Angeles');

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

p.test {
                 font-size: 16pt;
            text-align: center;
            background-color: red;
        }

</style>
</head>";

$path = __FILE__;
$test = preg_match('/andy\/src/', $path);
if ($test) {
    echo "<p class=test>** TEST **</p>";
    echo "Path is $path";
}

echo "<h3>Directories:</h3>";
echo "<ul>

  <li><a href='http://local-cbi/'>CBI - Published</a></li>
    <li><a href='http://local-cbi-test/'>CBI - Test</a></li>
  <br>";

$dh = opendir(".");
while (($file = readdir($dh) ) !== false) {
    if ($file == "." || $file == "..")
        continue;
    if ($file == ".git")
        continue;
    if (is_dir($file)) {
        printf("<li><a href=%s>%s</a></li>\n", $file, $file);
    }
}
closedir($dh);

function getMySQLVersion() {
    $output = shell_exec('/usr/local/mysql/bin/mysql --version');
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
    return $version[0];
}

function getApacheVersion() {
    $output = apache_get_version();
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
    return $version[0];
}

$php_vers = phpversion();
$mysql_vers = getMySQLVersion();
$apache_vers = getApacheVersion();

echo "</ul>";
echo "
<h3>Software (As of 3/5/17):</h3>
<table>
<tr><th class=e>PHP</th><td class=v>$php_vers</td></td></tr>
<tr><th class=e>MySQL</th><td class=v>$mysql_vers</td></td></tr>
<tr><th class=e>Apache</th><td class=v>$apache_vers</td></td></tr>
</table>
";
?>