<?php

global $mail_live;

global $mysql_last_id;
global $mysql_numrows;
global $mysql_result;
#=====================================================

// This are the PDO global variables that I will use

global $gPDO_attr;
global $gPDO_dsn;
global $gPDO_lastInsertID;
global $gPDO_num_rows;
global $gPDO_pass;
global $gPDO_user;

#=====================================================

global $gAccessLevels;
global $gAccessLevel;
global $gAccessLevelEnabled;
global $gAccessLevels;
global $gAccessNameEnabled;
global $gAccessNameToId;
global $gAccessNameToLevel;
global $gAction;
global $gActive;
global $gArea;
global $gAuctionYear;
global $gDb;
global $gDebug;
global $gEnabled;
global $gError;
global $gFrom;
global $gFunc;
global $gFunction;
global $gGala;
global $gLF;
global $gMailAdmin;
global $gProduction;
global $gResetKey;
global $gSourceCode;
global $gSpiritIDtoDesc;
global $gSpiritIDtoType;
global $gSpiritIDstats;
global $gTitle;
global $gTrace;
global $gUserId;
global $gUserName;

global $error;
global $user;           // Object: active user

global $gSiteEnabled;

global $site_enabled;
global $site_send_confirms;
global $time_offset;

$gFunction = array('index.php');
#=====================================================
global $PaymentCredit;
global $PaymentCheck;
global $PaymentCall;

$PaymentCredit = 1;
$PaymentCheck = 2;
$PaymentCall = 3;

#=====================================================
global $PledgeTypeFinancial;
global $PledgeTypeSpiritual;
global $PledgeTypeFinGoal;

$PledgeTypeFinancial = 1;
$PledgeTypeSpiritual = 2;
$PledgeTypeFinGoal = 3;

#=====================================================
global $SpiritualTorah;
global $SpiritualAvodah;
global $SpiritualGemilut;

$SpiritualTorah = 1;
$SpiritualAvodah = 2;
$SpiritualGemilut = 3;

#=====================================================
# Auction Specific
#=====================================================
global $gCategories;
global $gPackages;
global $gPreSelected; # set to item_id
global $gPreUser;     # set to user_hash

global $gStatus;
global $gStatusOpen;
global $gStatusClosed;
global $gStatusHidden;

$gStatus = array();
$gStatusOpen = 0;
$gStatusClosed = 1;
$gStatusHidden = 2;
$gStatus[ $gStatusOpen   ] = 'Open';
$gStatus[ $gStatusClosed ] = 'Closed';
$gStatus[ $gStatusHidden ] = 'Hidden';

global $gSendTop;
global $gSendOld;
global $gSendBought;
global $gSendOldBought;

$gSendTop = 1;
$gSendOld = 2;
$gSendBought = 3;
$gSendOldBought = 4;

global $gDebugInLine; # 0
global $gDebugErrorLog; # 1
global $gDebugWindow; # 2
global $gDebugMask;

$gDebugInLine = 2**0;
$gDebugErrorLog = 2**1;
$gDebugWindow = 2**2;
$gDebugMask = 7;
?>
