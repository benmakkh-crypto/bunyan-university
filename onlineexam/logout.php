<?php
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
unset($_SESSION['oe_email'], $_SESSION['oe_name'], $_SESSION['oe_start'], $_SESSION['oe_answers'], $_SESSION['email'], $_SESSION['name'], $_SESSION['key']);
header('Location: index.php'); exit;
