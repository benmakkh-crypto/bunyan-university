<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php 
include("templates/header.php");
?>

<?php
$id = intval($_GET['id'] ?? 0);
if($id){
    include("../connect.php");
    $sqlEdit = "SELECT * FROM ecoms WHERE id = $id";
    $result = mysqli_query($conn, $sqlEdit);
}else{
    echo "لايوجد المقال";
}

?>
        <div class="create-form w-100 mx-auto p-4" style="max-width:700px;">
            <form action="process.php" method="post" enctype="multipart/form-data">
                <?php 
                while ($data = mysqli_fetch_array($result)) {
                ?>

                <div class="form-field mb-4">
                    <input type="text" class="form-control" name="title" id="" placeholder=" أدخل العنوان:" value="<?php echo $data['title']; ?>">
                </div>
                <div class="form-field mb-4">
                    <textarea name="teacher"  class="form-control" id="" cols="30" rows="10" placeholder="أدخل مدرس المادة:"><?php echo $data['teacher']; ?></textarea>
                </div>
               
                <div class="form-field mb-4">
                    <textarea name="link" class="form-control" id="" cols="30" rows="10" placeholder="أدخل رابط المحاضرة :"><?php echo $data['link']; ?></textarea>
                </div>

                <input type="hidden" name="date" value="<?php echo date("Y/m/d"); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <label>     
      الصورة
      <input type="file" name="image1" class="form-control">

       <input type="hidden" name="image2" value="<?php echo $data['image']; ?>">

      </label>
                <div class="form-field">
                <input type="submit" class="btn btn-secondary" value="تعديل" name="update">
                </div>

                <?php
                }
                ?>
            </form>
        </div>
<?php 
include("templates/footer.php");
?>