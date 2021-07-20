<?php
//set timezone
date_default_timezone_set('America/Los_Angeles');

// Turn off output buffering
ini_set('output_buffering', 'off');

// Turn off PHP output compression
ini_set('zlib.output_compression', false);

//ob_start();
ob_implicit_flush(TRUE);
    
$http_host = $_SERVER['HTTP_HOST'];

if (preg_match('/^dev.cbi18.org/', $http_host) || $http_host == "192.168.86.7" ) {
    $gProduction = 0;
    $gSiteDir = "/usr/local/site";
    $gSiteName = "Dev-Admin";
    define( 'DIR', 'http://dev.cbi18.org/');
    
} elseif ( preg_match( '/cbi18.org/', $_SERVER['HTTP_HOST']) ) {
    $gProduction = 1;
    $gSiteDir = '/home/cbi18/site';
    $gSiteName = 'CBI-Live';
    define( 'DIR', 'https://cbi18.org/');

} else {
    echo "Unknown config for http_host: [$http_host]<br>";
    exit;
}

$gSiteSubPath = "cbi";

$parts = [];
$parts[] = $gSiteDir . "/$gSiteSubPath/php";
$parts[] = $gSiteDir . "/bin";
$parts[] = $gSiteDir . "/php";
$parts[] = $gSiteDir . "/PHPMailer";
$parts[] = $gSiteDir . "/fpdf";
$parts[] = $gSiteDir;
$str = get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $parts);
set_include_path($str);

include 'includes/globals.php';
include 'includes/library.php';

include 'local-hh-honors.php';
include 'local_mailer.php';

//application address
define('SITEEMAIL', 'andy.elster@gmail.com');
define('SITETITLE', 'CBI HH Honor Admin');

$gTitle = SITETITLE;
$gMailSignature = ['Andy Elster'];
$gMailSignatureImage = 'assets/CBI_ner_tamid.png';
$gMailSignatureImageSize = ['width' => 94, 'height' => 110]; 

require_once( 'SiteLoader.php' );
SiteLoad('Common');

require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/POP3.php';
require_once 'src/Exception.php';

session_start();

selectDB();

$user = new User($gPDO[$gDbControlId]['inst']);