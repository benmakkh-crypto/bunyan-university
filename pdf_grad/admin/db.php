<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
if(!isset($_SESSION))session_start();
try {

    $con = db_pdo('logweb');
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException  $e) {
    echo "<p style=\"text-align:center;color:#a00\">تعذّر الاتصال بقاعدة البيانات حالياً.</p>"; $con = null;
}
function getData($db, $query, $parm = [])
{
    $stmt = $db->prepare($query);
    $stmt->execute($parm);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
}
function setData($db, $query, $parm = [])
{
    $stmt = $db->prepare($query);
    $stmt->execute($parm);
    $count = $stmt->rowCount();
    return $count;
}
?>