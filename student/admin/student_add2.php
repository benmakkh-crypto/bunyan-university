<!-- ===============================================
	**** A COMPLETE VALIDATE FORM WITH PHP ****
================================================ -->

<!-- ==============  PHP begin  =================-->

   <?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';

// Function to handle file uploads

// Database connection
$cn = db_mysqli('logweb');
if (!$cn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables and error messages
$fname = $lname = $email = $phone = $birth = $id_iden = $id_pass = $gender = $address = $country = $stage = $depart = $img1 = $imgpass = "";
$efname = $elname = $eemail = $ephone = $ebirth = $eid_iden = $eid_pass = $egender = $eaddress = $ecountry = $estage = $edepart = $eimg1 = $eimgpass = ""; 

$er=0;
if (isset($_POST['submit'])) {
   
    
    // Process the form if no errors



    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $birth =$_POST['birth'];
    $id_iden = $_POST['id_iden'];
    $id_pass = $_POST['id_pass'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $country =$_POST['country'];
    $stage = $_POST['stage'];  
    $depart =$_POST['depart'];
    $img1 = uploads('admin/img', 'img1',"a");
    $imgpass = uploads('admin/img', 'imgpass',"b");
    
    $efname = "";
    $elname = "";
    $eemail = "";
    $ephone = "";
    $ebirth = "";
    $eid_iden = "";
    $eid_pass = "";
    $egender = "";
    $eaddress = "";
    $ecountry = "";
    $estage = "";
    $edepart = "";
    $eimg1 = "";
    $eimgpass = "";




  
        $sql = "INSERT INTO info (fname, lname, email, phone, birth, id_iden, id_pass, gender, address, country, stage, depart, img1, imgpass) 
        VALUES (
            '". $fname."',
            '". $lname."',
            '". $email."',
            '". $phone."',
            '". $birth."',
            '". $id_iden."',
            '". $id_pass."',
            '". $gender."',
            '". $address."',
            '". $country."',
            '". $stage."',
            '". $depart."',
            '". $img1."',
            '". $imgpass."'
        )";

        // echo $sql;
        // exit;

        if (mysqli_query($cn, $sql)) {
            echo '<span class="successMessage"style="color:#568203">تم حفظ البيانات.</span>';
            // Clear form fields after successful submission
            $fname = $lname = $email = $phone = $birth = $id_iden = $id_pass = $gender = $address = $country = $stage = $depart = $img1 = $imgpass = "";
        } else {
            echo '<span class="errorMessage"style="color:#568203">'.mysqli_error($cn).'</span>';
        }
    } else {
        echo '<span class="errorMessage"style="color:#568203">نرجو ملأ البيانات بصورة صحيحة.</span>';
    }


function uploads($nameFolder,$namePostFile,$ortName="",$nameFile=""){
    
    if(isset($_FILES[$namePostFile])){
      $temp = explode(".", $_FILES[$namePostFile]["name"]);
      $ext = strtolower(end($temp));
      // يُسمح فقط بالصور وملفات PDF
      if (!in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true)) return $nameFile;
      $newfilename =$ortName.round(microtime(true)) . '.' . $ext;
      $target_dir = $nameFolder."/";


    
      $target_file = $target_dir . basename($newfilename );
        if (move_uploaded_file($_FILES[$namePostFile]["tmp_name"], $target_file)) {
          return $newfilename;
        } else {
          return $nameFile;
        }
    }else{
      return $nameFile;
    }
     
  }


?>

<div class="form-area">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
            <div  class="section-title">
					<h3 style="background-color:#568203;">واجهة تسجيل الطلبة   </h3>
				</div>

				<div class="row">
					<div style="text-align:center;" class="col-md-12">
						<form action="" method="post" enctype="multipart/form-data">
							<div class="row">
								<div class="col-md-6">
									<div class="left-side-form">
										<h5><label for="fname"> الاسم الاول</label>
										<span class="error"><?php print $efname; ?></span></h5>
										<p><input type="text" name="fname" value="<?php print $fname; ?>"></p>

										<h5><label for="lname">الاسم الثاني</label><span class="error">
												<?php print $elname; ?></span></h5>
										<p><input type="text" name="lname" value="<?php print $lname; ?>"></p>

										<h5><label for="email">البريد الالكتروني</label><span class="error">
												<?php print $eemail; ?></span></h5>
										<p><input type="text" name="email" value="<?php print $email; ?>"></p>

										<h5><label for="phone">رقم الجوال</label><span class="error">
												<?php print $ephone; ?></span></h5>
                                                
										<p><input type="text" name="phone" value="<?php print $phone; ?>"></p>
										<h5><label for="birth">تاريخ الميلاد  </label><span class="error">
												<?php print $ebirth; ?></span></h5>
										<p><input type="text" name="birth" value="<?php print $birth; ?>"></p>

										<h5><label for="gender">الجنس</label></h5>
										<input type="radio"id="male" name="gender" value="ذكر">
                                        <label>ذكر</label>
										<input type="radio"id="female" name="gender" value="أنثى">
                                        <label>أنثى</label>
										<span class="error">
											<?php print $egender; ?></span>

										<h5><label for="id_iden"> رقم الهوية</label><span class="error">
												<?php print $eid_iden; ?></span></h5>
										<p><input type="text" name="id_iden" value="<?php print $id_iden; ?>"></p>

										<h5><label for="id_pass">رقم جواز السفر</label><span class="error">
												<?php print $eid_pass; ?></span></h5>
										<p><input type="text" name="id_pass" value="<?php print $id_pass; ?>"></p>


										<h5><label for="address">العنوان</label><span class="error">
												<?php print $eaddress; ?></span></h5>
										<p><textarea name="address"><?php print $address; ?></textarea></p>
									
								

										<h5><label for="country">البلد</label>
                                        <span class="error">
			<?php print $ecountry; ?></span>
                                    </h5>
										<p><select name="country" id="">

                                        <option value="0">أختر </option>  
                                        <option value="نيجيريا"> نيجيريا</option>  
                                        <option value="المملكة العربية السعودية"> المملكة العربية السعودية</option>
                                        <option value="الكويت">الكويت </option>
                                        <option value="البحرين">البحرين</option>
                                        <option value="قطر"> قطر</option>
                                        <option value="سلطنة عمان"> سلطنة عمان</option>
                                        <option value="الامارات"> الامارات</option>
                                        <option value="جنوب أفريقيا">جنوب أفريقيا </option>
                                        <option value="مصر"> مصر</option>
                                        <option value="العراق"> العراق</option>
                                        <option value="الأردن"> الأردن</option>
                                        <option value="سوريا">سوريا </option>
                                        <option value="لبنان">لبنان </option>
                                        <option value="ليبيا"> ليبيا</option>
                                        <option value="فلسطين">فلسطين </option>
                                        <option value="اليمن">اليمن </option>
                                        <option value="الصومال"> الصومال</option>
                                        <option value="أندنوسيا">أندنوسيا </option>
                                        <option value="ماليزيا">ماليزيا </option>
                                        <option value="تركيا"> تركيا</option>
                                        <option value="بريطانيا">بريطانيا </option>
                                      

			</select></p>


									

										

                                      <h5><label for="stage">المرحلة الدراسية </label>
                                        <span class="error">
                                        <?php print $estage; ?></span>
                                    </h5>
										<p><select name="stage" id="">
												
                                               
                                        <option value="0">أختر </option>  
                                       
                                                <option value="مرحلة البكالوريوس">مرحلة البكالوريوس</option>
                                                  <option value="مرحلة الماجستير">مرحلة الماجستير</option>
                                                    <option value="مرحلة الدكتوراه">مرحلة الدكتوراه </option>
											</select>
                                            </p>


												<h5><label for="depart">الكلية أو المعهد</label>
                                                <span class="error">
												<?php print $edepart; ?></span>
                                            </h5>
										<p><select name="depart" id="">
												<option value="0">أختر</option>
                                                <option value="كلية القران الكريم">كلية القران الكريم</option>
                <option value="كلية الدراسات الاسلامية">كلية الدراسات الاسلامية</option>
                <option value="كلية الشريعة والقانون">كلية الشريعة والقانون</option>
                <option value="كلية الفقه وأصوله">كلية الفقه وأصوله</option>
                <option value="كلية اللغة العربية">كلية اللغة العربية</option>
                <option value="كلية التربية">كلية التربية</option>
                <option value="كلية الاعلام">كلية الاعلام</option>
                <option value="كلية الاقتصاد والعلوم الادارية">كلية الاقتصاد والعلوم الادارية</option>
                <option value="كلية التقنية والبرمجة">كلية التقنية والبرمجة</option>
                <option value="كلية اللغات الاجنبية">كلية اللغات الاجنبية</option>
                <option value=" معهد البحوث والاستشارات ">معهد البحوث والاستشارات</option>
											</select></p>

                                            <h5><label for="img1">صورة الهوية</label><span class="error">
												<?php print $eimg1; ?></span></h5>
                                                <p><input type="file" name="img1" ></p>

                                                
                                            <h5><label for="imgpass">صورة جواز السفر</label><span class="error">
												<?php print $eimgpass; ?></span></h5>
                                                <p><input type="file" name="imgpass" ></p>

												

										<p><input  style="width:160px;font-size:22px;background-color:#568203;" type="submit" name="submit" value="إضافة"></p>
                                        <a href="pay/index.php"style="text-direction:none;width:160px;font-size:22px;" class="btn btn-secondary">دفع</a> 
                                        
										</div>
								</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
