<?php
require_once( 'includes/config.php' );

$trace = 1;

if ($trace) {
    error_log("");
    error_log("-------------");
    foreach ($_POST as $key => $val) {
        error_log("_POST[$key] = [$val]");
    }
}

$dataType = $_POST['type'];
list( $table, $field, $id ) = explode("__", $_POST['target'] );
$val = trim( str_replace('$','', $_POST['val']) );
if( $field  == "debug" ) {
    $query = "update $table set `$field` = `$field` ^ $val where id = $id";
} else {
    $query = "update $table set `$field` = '$val' where id = $id";
}
if( $trace ) error_log($query);

DoQuery($query);

if($trace) error_log( "# rows updated: " . $gPDO_num_rows );

EventLogRecord( [
    'type' => 'update',
    'user_id' => $_POST['user_id'],
    'item' => $query ] );

$refresh = false;
if( $table == "access" ) {
    if( $field == "priv_id" ) {
        $refresh = true;
    }
}
$response_array = array(
    "status" => "success",
    "val" => "$val",
    "refresh"  => "$refresh"
);

echo json_encode($response_array);