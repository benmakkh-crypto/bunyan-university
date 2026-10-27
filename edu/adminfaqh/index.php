<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php
include("templates/header.php");
?>

<div class="posts-list w-100 p-5">
<?php
        if (isset($_SESSION["create"])) {
        ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION["create"];
            ?>
        </div>
        <?php
        unset($_SESSION["create"]);
        }
        ?>
         <?php
        if (isset($_SESSION["update"])) {
        ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION["update"];
            ?>
        </div>
        <?php
        unset($_SESSION["update"]);
        }
        ?>
        <?php
        if (isset($_SESSION["delete"])) {
        ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION["delete"];
            ?>
        </div>
        <?php
        unset($_SESSION["delete"]);
        }
  ?>
    <table class="table table-striped">
        <thead>
            <tr>
            <th style="width:15%;">تاريخ النشر:</th>
                <th style="width:15%;">العنوان:</th>
                <th style="width:20%;">مدرس المادة:</th>
                <th style="width:30%;">رابط المحاضرة:</th>
                <th style="width:30%;">الصورة:</th>
                <th style="width:25%;">العمليات:</th>
            

            </tr>
        </thead>
        <tbody>
            
            <?php
            include('../connect.php');
            $sqlSelect = "SELECT * FROM faqh";
            $result = mysqli_query($conn,$sqlSelect);
            while($data = mysqli_fetch_array($result)){
            ?>
            <tr>
            <td><?php echo $data["date"]?></td>
            <td><?php echo $data["title"]?></td>
            <td><?php echo $data["teacher"]?></td>
            <td><?php echo $data["link"]?></td>
            <td><?php echo $data["image"]?></td>
            <td>
                <a class="btn btn-dark" href="view.php?id=<?php echo $data["id"]?>">عرض</a>
                <a style="margin-top:0.55rem;" class="btn btn-dark"  href="edit.php?id=<?php echo $data["id"]?>">تعديل</a>
                <a style="margin-top:0.55rem;" class="btn btn-danger" href="delete.php?id=<?php echo $data["id"]?>">حذف</a>
            </td>
            </tr>
            <?php
            }
            ?>
           
        </tbody>
    </table>
</div>

<?php
include("templates/footer.php");
?>