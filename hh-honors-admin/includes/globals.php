<?php
#=====================================================

// This are the PDO global variables that I will use

global $gPDO_attr;
global $gPDO_dsn;
global $gPDO_lastInsertID;
global $gPDO_num_rows;
global $gPDO_pass;
global $gPDO_user;
global $gPrefix;

#=====================================================
global $gDebugInLine; # 0
global $gDebugErrorLog; # 1
global $gDebugWindow; # 2
global $gDebugHTML; # 3
global $gDebugAll;

$gDebugInLine = 2**0;
$gDebugErrorLog = 2**1;
$gDebugWindow = 2**2;
$gDebugHTML = 2**3;
$gDebugAll = 2**4 - 1;
#=====================================================
# Everything here is to fix a problem

global $gAccessNameToId;
global $gAccessNameToLevel;
global $gAccessNameEnabled;
global $gAccessLevelEnabled;
global $gAccessLevels;
global $gArea;
global $gFunc;
global $gMailAdmin;
global $gMailAdminName;
global $gMailLive;
global $gMailSignature;
global $gMailSignatureImage;
global $gMailSignatureImageSize;
global $gProduction;
global $gResetKey;

global $gTitle;
global $gUserName;
global $user;           // Object: active user

#=====================================================

global $mail_enabled;
global $mail_live;

#=====================================================

global $gAccessLevel;
global $gAction;
global $gActive;
global $gDb;
global $gDbControl;
global $gDbVector;
global $gDebug;
global $gEnabled;
global $gFrom;
global $gFunction;
global $gJewishYear;
global $gLF;
global $gSiteName;
global $gSourceCode;
global $gSpiritIDtoDesc;
global $gSpiritIDtoType;
global $gSpiritIDstats;
global $gTrace;
global $gUserId;

global $gSiteEnabled;

global $site_enabled;
global $site_send_confirms;
global $time_offset;

$gFunction = array('index.php');
#=====================================================
global $PaymentCredit;
global $PaymentCheck;
global $PaymentCall;
global $gPayMethods;

$PaymentCredit = 1;
$PaymentCheck = 2;
$PaymentCall = 3;
$gPayMethods = array("", "Credit", "Check", "Call");

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

$gService = [];
$gService['rh1'] = "Rosh Hashanah Day #1";
$gService['rh2'] = "Rosh Hashanah Day #2";
$gService['kn'] = "Kol Nidre";
$gService['yka'] = "Yom Kippur Morning";
$gService['ykp'] = "Yom Kippur Afternoon";
?>
