<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';

$conn = db_mysqli('logweb');

if (!$conn) {
    echo "Connection Failed";
}