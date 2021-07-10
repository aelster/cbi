<?php
//set timezone
date_default_timezone_set('America/Los_Angeles');

// Turn off output buffering
ini_set('output_buffering', 'off');

// Turn off PHP output compression
ini_set('zlib.output_compression', false);

//ob_start();
ob_implicit_flush(TRUE);

$prefix = "NoPrefixFound";
    
$http_host = $_SERVER['HTTP_HOST'];
    
if (preg_match('/^dev.cbi18.org/', $http_host) || $http_host == "192.168.86.7" ) {
    $gProduction = 0;
    $gSiteDir = "/usr/local/site";
    $gSiteName = "Dev";
    $prefix = "";

//} elseif (preg_match('/dev.cbi18.org/', $_SERVER['HTTP_HOST'])) {
//    $gProduction = 0;
//    $parts[] = '/usr/local/site/php'; # This is for Common
//    $parts[] = '/usr/local/site/cbi/hh-honors-admin'; # local customization
//    $parts[] = '/usr/local/PHPMailer';
//    $parts[] = '/usr/local/fpdf';
//    define('DIR', 'https://dev.cbi18.org/');

} elseif ( preg_match( '/cbi18.org/', $_SERVER['HTTP_HOST']) ) {
    $gProduction = 1;
    $gSiteDir = '/home/cbi18/site';
    $gSiteName = 'CBI-Live';
}
$gSiteSubPath = "cbi";

$parts[] = $gSiteDir . "/$gSiteSubPath/php";
$parts[] = $gSiteDir . "/bin";
$parts[] = $gSiteDir . "/php";
$parts[] = $gSiteDir . "/PHPMailer";
$parts[] = $gSiteDir . "/fpdf";
$path = join(PATH_SEPARATOR, $parts);

set_include_path(get_include_path() . PATH_SEPARATOR . $path );

include 'includes/globals.php';
include 'includes/library.php';

include 'local-hh-honors.php';

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

selectDb();

$user = new User($gPDO[$gDbControlId]['inst']);