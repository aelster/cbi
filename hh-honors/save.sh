#!/bin/sh

site_file=/usr/local/site/php/local_cbi_honors.php

host=`grep mysql_host $site_file | grep = | cut -d '"' -f2`
user=`grep mysql_user $site_file | grep = | cut -d '"' -f2`
pass=`grep mysql_pass $site_file | grep = | cut -d '"' -f2`
dbnm=`grep mysql_dbname $site_file | grep = | cut -d '"' -f2`

mysqldump --add-drop-table \
    --lock-tables \
    --host $host \
    --user $user \
    --password=$pass \
    $dbnm > $dbnm.sql