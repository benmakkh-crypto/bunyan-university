<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($dbHost, $dbUser, $dbPass, $dbName) = db_cfg('courses_std');

$conn = db_mysqli_raw($dbHost, $dbUser, $dbPass, $dbName);
if (!$conn) {
    die("Something went wrong. Database is not connected;");
}
?>