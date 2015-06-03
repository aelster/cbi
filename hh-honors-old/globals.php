<?php

global $mail_live;

global $mysql_last_id;
global $mysql_numrows;
global $mysql_result;
#=====================================================

global $gAccessLevel;
global $gAction;
global $gActive;
global $gDb;
global $gDebug;
global $gEnabled;
global $gFrom;
global $gFunction;
global $gLF;
global $gMailAdmin;
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
?>
