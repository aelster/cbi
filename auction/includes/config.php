<?php

//ob_start();
ob_implicit_flush(TRUE);

//set timezone
date_default_timezone_set('America/Los_Angeles');

if ($_SERVER['HTTP_HOST'] == 'www.cbi18.org') {
    $production = 1;
    $parts[] = '/usr/lib/php';
    $parts[] = '/usr/local/lib/php';
    $parts[] = '/home/cbi18/site/php';
    $parts[] = '/home/cbi18/site/Swift-5.0.1';
}

if (preg_match('/local/', $_SERVER['HTTP_HOST'])) {
    $production = 0;
    $parts[] = '/usr/local/site/php';
    $parts[] = '/usr/local/swiftmailer';
    $parts[] = '/usr/local/fpdf';
}

$path = join(PATH_SEPARATOR, $parts);
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include 'globals.php';
include 'library.php';
include 'local_cbi_auction.php';

//application address
define('DIR', 'http://local-cbi-test/');
define('SITEEMAIL', 'andy.elster@gmail.com');
define('SITETITLE', 'CBI Auction Manager');

try {
    //create PDO connection
    $gDb = new PDO($gPDO_dsn, $gPDO_user, $gPDO_pass, $gPDO_attr);
    if ($production) {
        $gDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    } else {
        $gDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    $gDb->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    //show error
    echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
    $gDb = NULL;
    $user->logout();
    exit;
}

//include the user class, pass in the database connection
include('classes/user.php');
include('classes/phpmailer/mail.php');
$user = new User($gDb);

require_once 'lib/swift_required.php';
require_once( 'SiteLoader.php' );
SiteLoad('Common');

SessionStuff('start');
?>