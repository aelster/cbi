<?php
//set timezone

date_default_timezone_set('America/Los_Angeles');

//ob_start();
ob_implicit_flush(TRUE);

if (preg_match('/dev.cbi18.org/', $_SERVER['HTTP_HOST'])) {
    $gProduction = 0;
    $parts[] = '/usr/local/site/php'; # This is for Common
    $parts[] = '/usr/local/site/cbi/hh-honors/php';
    $parts[] = '/usr/local/PHPMailer';
    $parts[] = '/usr/local/fpdf';
    define('DIR', 'https://dev.cbi18.org/');

} elseif ( preg_match( '/cbi18.org/', $_SERVER['HTTP_HOST']) ) {
    $gProduction = 1;
    $parts[] = '/home/cbi18/site/php';
    $parts[] = '/home/cbi18/site/hh-honors/';
    $parts[] = '/home/cbi18/site/PHPMailer';
    $parts[] = '/home/cbi18/site/fpdf';
    define('DIR', 'https://cbi18.org/');
}

$path = join(PATH_SEPARATOR, $parts);
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include 'includes/globals.php';
include 'includes/library.php';
include 'local_hh_honors.php';

//application address
define('SITEEMAIL', 'aelster@irvinehebrewday.org');
define('SITETITLE', 'CBI HH Honor Site');

$gTitle = SITETITLE;
$gMailSignature = ['Andy Elster'];
$gMailSignatureImage = 'assets/CBI_ner_tamid.png';
$gMailSignatureImageSize = ['width' => 94, 'height' => 110]; 

$gDb = null;
$limit = 10;
$counter = 0;
while(true) {
    try {
        //create PDO connection
        $gDb = new PDO($gPDO_dsn, $gPDO_user, $gPDO_pass, $gPDO_attr);
        if ($gProduction) {
            $gDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        } else {
            $gDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $gDb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        break;
    } catch (PDOException $e) {
        //show error
        print_r($e);
        echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
        $gDb = NULL;
        $counter++;
        if( $counter == $limit )
            throw $e;
    }
}

require_once( 'SiteLoader.php' );
SiteLoad('Common');

require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/POP3.php';
require_once 'src/Exception.php';

$user = new User($gDb);

SessionStuff('start');