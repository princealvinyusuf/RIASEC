<?php

$db['db_host']="localhost";
$db['db_user']="root";
$db['db_pass']="";
$db['db_name']="riasec_test";
foreach($db as $key => $value){
    define(strtoupper($key),$value);
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$connection) {
    error_log('Database connection failed: ' . mysqli_connect_error());
}
?>
