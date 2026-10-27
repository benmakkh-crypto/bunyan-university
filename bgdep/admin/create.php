<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php 
include("templates/header.php");
?>
        <div class="create-form w-100 mx-auto p-4" style="max-width:700px;">
            <form action="process.php" method="post"enctype="multipart/form-data">
                <div class="form-field mb-4">
                    <input type="text" class="form-control" name="title" id="" placeholder="أدخل العنوان:">
                </div>
                <div class="form-field mb-4">
                    <textarea name="summary"  class="form-control" id="" cols="30" rows="10" placeholder="أدخل الملخص:"></textarea>
                </div>
                <div class="form-field mb-4">
                    <textarea name="content" class="form-control" id="" cols="30" rows="10" placeholder="أدخل البوست:"></textarea>
                </div>
                <div class="form-field mb-4">
                    <textarea name="class"  class="form-control" id="" cols="30" rows="10" placeholder="أدخل التصنيف :"></textarea>
                </div>
                <input type="hidden" name="date" value="<?php echo date("Y/m/d"); ?>">
                <label>     
      الصورة
      <input type="file" class="form-control" name="image">
      </label>
     
 

                <div class="form-field">
                    <input type="submit" class="btn btn-primary" value="أضافة" name="create">
                </div>
            </form>
        </div>
<?php 
include("templates/footer.php");
?>