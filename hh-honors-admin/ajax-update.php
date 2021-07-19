<?php
require_once( 'includes/config.php' );

error_log("");

$trace = 1;

if ($trace) {
    error_log("-------------");
    foreach ($_POST as $key => $val) {
        error_log("_POST[$key] = [$val]");
    }
}

$dataType = $_POST['type'];
list( $table, $field, $id ) = explode("__", $_POST['id'] );
$val = trim( str_replace('$','', $_POST['val']) );
$query = "update $table set `$field` = '$val' where id = $id";
if( $trace ) error_log($query);

DoQuery($query);

if($trace) error_log( "# rows updated: " . $gPDO_num_rows );

$obj = [];
$obj['type'] = 'update';
$obj['userid'] = $_POST['userId'];
$obj['item'] = $query;
EventLogRecord($obj);

$response_array['status'] = 'success';
$response_array['val'] = $val;

echo json_encode($response_array);