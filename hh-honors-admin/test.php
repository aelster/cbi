<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$password = "";
$algos = [PASSWORD_DEFAULT,PASSWORD_DEFAULT,PASSWORD_BCRYPT,PASSWORD_BCRYPT];
$options = [ ];
 
$hashes = [];
foreach( $algos as $algo ) {
    $t0 = microtime(true);
    $hashes[] = password_hash($password, $algo, $options);
    $t1 = microtime(true);
    $delta = $t1 - $t0;
    echo "Hash time: $delta<br>";
}

$i = 0;
foreach( $hashes as $hash ) {
    $i++;
    echo "Hash #$i [$hash]<br>";
    $t0 = microtime(true);
    $stat = password_verify($password,$hash);
    $t1 = microtime(true);
     $delta = $t1 - $t0;
   echo "  Verified: $stat, Time: $delta<br>";
}

