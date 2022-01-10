<?php

/* Getting file name */

if(!isset($_FILES) || empty($_FILES)) die();

$uploaded = array();
foreach ($_FILES["file"]["error"] as $key => $error) {
	if ($error == UPLOAD_ERR_OK) {
		$tmp_name = $_FILES["file"]["tmp_name"][$key];
		// basename() может спасти от атак на файловую систему;
		// может понадобиться дополнительная проверка/очистка имени файла
		$extension = pathinfo($_FILES['file']['name'][$key], PATHINFO_EXTENSION);
		$name = basename($_FILES["file"]["name"][$key]);
		$name = md5(time().rand(1000,9999).$name);
		$name = $name.'.'.$extension;
		
		$upload_file_path = 'upload/'.$name;
		
		if(move_uploaded_file($tmp_name, $upload_file_path)) {
			$uploaded[] = $upload_file_path;
		}
	}
}

echo $uploaded[0];

die();

$filename = $_FILES['file']['name'];

/* Location */
$location = "upload/".$filename;
$uploadOk = 1;
$imageFileType = pathinfo($location,PATHINFO_EXTENSION);

/* Valid extensions */
$valid_extensions = array("jpg","jpeg","png");

/* Check file extension */
if(!in_array(strtolower($imageFileType), $valid_extensions)) {
   $uploadOk = 0;
}

if($uploadOk == 0){
   echo 0;
}else{
   /* Upload file */
   if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
     echo $location;
   }else{
     echo 0;
   }
}