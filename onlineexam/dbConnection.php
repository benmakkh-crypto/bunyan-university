<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
//all the variables defined here are accessible in all the files that include this one
$con= db_mysqli('oesm')or die("Could not connect to mysql".mysqli_error($con));

?>