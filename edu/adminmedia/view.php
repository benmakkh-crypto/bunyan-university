<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php
include("templates/header.php");
?>

<div class="post w-100 bg-light p-5">
    <?php
    $id = intval($_GET['id'] ?? 0);
    if ($id) {
        include("../connect.php");
        $sqlSelectPost = "SELECT * FROM media WHERE id = $id";
        $result = mysqli_query($conn, $sqlSelectPost);
        while ($data = mysqli_fetch_array($result)) {
        ?>
        <h1><?php echo  $data['title']; ?></h1>
        <p><?php echo $data['teacher']; ?></p>
        <p><?php echo $data['date']; ?></p>
        <p><?php echo $data['link']; ?></p>
        <p><?php echo $data['image']; ?></p>
        <?php
        }
    }else{
        echo "Post Not Found";
    }
    ?>
</div>

<?php
include("templates/footer.php");
?>