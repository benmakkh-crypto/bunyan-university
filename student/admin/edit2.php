<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; uwciv_require_super();
$cn = db_mysqli('logweb');
if (!$cn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if 'eid' is set in the query string
$id = isset($_GET['eid']) ? intval($_GET['eid']) : 0; 

if ($id > 0) {
    // Fetch the existing record
    $stmt = $cn->prepare("SELECT * FROM info WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {

        die("No record found with ID $id");
    }
} else {
    die("Invalid ID");
}

// Initialize variables
$fname = $lname = $email = $phone = $birth = $id_iden = $id_pass = $gender = $address = $country = $stage = $depart = $img1=$imgpass= "";
$efname = $elname = $eemail = $ephone = $ebirth = $eid_iden = $eid_pass = $egender = $eaddress = $ecountry = $estage = $edepart =$eimg1=$eimgpass= "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Retrieve POST data
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birth = $_POST['birth'] ?? '';
    $id_iden = $_POST['id_iden'] ?? '';
    $id_pass = $_POST['id_pass'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $country = $_POST['country'] ?? '';
    $stage = $_POST['stage'] ?? '';
    $depart = $_POST['depart'] ?? '';
    $img1=uploads('img','img1',"a",$_POST['img2'])?? '';
    $imgpass=uploads('img','imgpass',"b",$_POST['imgpass2'])?? '';

    // Error checking
    $er = 0;
    $errors = [
        'fname' => '',
        'lname' => '',
        'email' => '',
        'phone' => '',
        'birth' => '',
        'id_iden' => '',
        'id_pass' => '',
        'gender' => '',
        'address' => '',
        'country' => '',
        'stage' => '',
        'depart' => '',
        'img1' => '',
        'imgpass' => '',
    ];

    if (empty($fname)) { $er++; $errors['fname'] = "*مطلوب"; }
    if (empty($lname)) { $er++; $errors['lname'] = "*مطلوب"; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $er++; $errors['email'] = "*مطلوب / الادخال خطأ"; }
    if (empty($phone) || !preg_match("/^[+0-9]*$/", $phone)) { $er++; $errors['phone'] = "*مطلوب / الادخال خطأ"; }
    if (empty($birth)) { $er++; $errors['birth'] = "*مطلوب"; }
    if (empty($gender)) { $er++; $errors['gender'] = "*مطلوب"; }
    if (empty($id_iden) || !preg_match("/^[0-9-A-Z-a-z]*$/", $id_iden)) { $er++; $errors['id_iden'] = "*مطلوب / الادخال خطأ"; }
    if (empty($id_pass) || !preg_match("/^[0-9-A-Z-a-z]*$/", $id_pass)) { $er++; $errors['id_pass'] = "*مطلوب / الادخال خطأ"; }
    if (empty($address)) { $er++; $errors['address'] = "*مطلوب"; }
    if (empty($country)) { $er++; $errors['country'] = "*أختر";}
    if(empty($stage)) { $er++; $errors['stage'] = "*أختر"; }
    if (empty($depart)) { $er++; $errors['depart'] = "*أختر"; }
    if (empty($img1)) { $er++; $errors['img1'] = "*مطلوب"; }
    if (empty($imgpass)) { $er++; $errors['imgpass'] = "*مطلوب"; }

    if ($er === 0) {
        // Prepare the SQL query


       $stmt = $cn->prepare("UPDATE info SET fname = '$fname', lname = ' $lname,', email = '$email', phone = '$phone', birth = '$birth',
        gender = '$gender', id_iden = '$id_iden', id_pass = '$id_pass', 
        `address` = '$address', country = '$country', stage = '$stage', depart = '$depart',img1 = '$img1',imgpass = '$imgpass' WHERE id = '$id'");
        // $stmt->bind_param("ssssssssssiii", , , , , , , , 
        // , , , , );

        // var_dump($stmt);
        // exit;

        if ($stmt->execute()) {
            echo '<span class="successMessage">تم التعديل بنجاح</span>';
        } else {
            echo '<span>' . $stmt->error . '</span>';
        }
        $stmt->close();
    }
}

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}


  function uploads($nameFolder,$namePostFile,$ortName="",$nameFile=""){
    
    if(isset($_FILES[$namePostFile])){
      $temp = explode(".", $_FILES[$namePostFile]["name"]);
      $newfilename =$ortName.round(microtime(true)) . '.' . end($temp);
      $target_dir = $nameFolder."/";

    //   echo $target_dir;
    //   exit;
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>واجهة التعديل</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    
<div class="form-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h3 style="background-color:#568203;" id="et">تعديل المعلومات رقم: 
                        <?php print $_GET['eid'].', اسم: '.$row["fname"]; ?></h3>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="" method="post"enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="left-side-form">
                                        <h5><label for="fname">الاسم الأول</label>
                                            <span class="error">
                                                <?php print $efname; ?></span></h5>
                                        <p><input type="text" name="fname" value="<?php print $row['fname']; ?>"></p>

                                        <h5><label for="lname"> الاسم الثاني </label><span class="error">
                                                <?php print $elname; ?></span></h5>
                                        <p><input type="text" name="lname" value="<?php print $row['lname']; ?>"></p>

                                        <h5><label for="email">البريد الالكتروني  </label><span class="error">
                                                <?php print $email; ?></span></h5>
                                        <p><input type="text" name="email" value="<?php print $row['email']; ?>"></p>

                                        <h5><label for="phone">رقم الجوال  </label><span class="error">
                                                <?php print $ephone; ?></span></h5>
                                        <p><input type="text" name="phone" value="<?php print $row['phone']; ?>"></p>

                                        <h5><label for="birth">تاريخ الميلاد   </label><span class="error">
                                                <?php print $ebirth; ?></span></h5>
                                        <p><textarea name="birth"><?php print $row['birth']; ?></textarea></p>
                                    </div>
                          
                                <div class="col-md-6">
                                    <div class="right-side-form">
                                        <h5><label for="id_iden"> رقم الهوية  </label><span class="error">
                                                <?php print $eid_iden; ?></span></h5>
                                        <p><input type="text" name="id_iden" value="<?php print $row['id_iden']; ?>"></p>

                                        <h5><label for="id_pass">رقم جواز السفر  </label><span class="error">
                                                <?php print $eid_pass; ?></span></h5>
                                        <p><input type="text" name="id_pass" value="<?php print $row['id_pass']; ?>"></p>

                                        <h5><label for="gender">الجنس</label>
                                        <span class="error">
                                        <?php print $egender; ?></span></h5>
                                   
                                  
                                            <label>ذكر</label>

										<input type="radio"id="male" name="gender" value="ذكر"><br>

                                        <label>أنثى</label>

										<input type="radio"id="female" name="gender" value="أنثى">
                                
										
                                        

                                         <h5><label style="margin-top:1rem;" for="address">العنوان :</label></h5>
                                        <p><textarea name="address"><?php print $row['address']; ?></textarea></p>
                                        <span class="error">
                                         <?php print $eaddress; ?></span>

                                        
                                 
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

			

                                            <h5><label for="depart">الكلية أو المعهد </label> <span class="error">
                                            <?php print $edepart; ?></span>
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
                                                <p>   <input type="hidden" name="img2" value="<?php echo $row['img1']; ?>"> </p>


                                                
                                            <h5><label for="imgpass">صورة جواز السفر</label><span class="error">
												<?php print $eimgpass; ?></span></h5>
                                                <p><input type="file" name="imgpass" ></p>
                                                <p>   <input type="hidden" name="imgpass2" value="<?php echo $row['imgpass']; ?>"> </p>

                                       

                                        <p><input style="background-color:#000;" type="submit" name="submit" value="حفظ التعديل"></p>
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
</body>
</html>