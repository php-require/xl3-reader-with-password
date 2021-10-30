<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

// Каталог, в который мы будем принимать файл:
$uploaddir = 'uploads/';
$uploadfile = $uploaddir.basename($_FILES['uploadfile']['name']);
$fileName = $_FILES['uploadfile']['name'];
$fileSize = $_FILES['uploadfile']['size'];
$ext = substr(strrchr($fileName, '.'), 1);
require_once __DIR__.'/SimpleXLSX.php'; 

if($fileSize != 0) {
	if($ext === 'xlsx') {
		 
		// загружаем файл
		// Копируем файл из каталога для временного хранения файлов:
		if (copy($_FILES['uploadfile']['tmp_name'], $uploadfile))
		{
		echo "<h3>Файл успешно загружен на сервер</h3>";
		}
		else { echo "<h3>Ошибка! Не удалось загрузить файл на сервер!</h3>"; exit; }
	 	if($_POST["pass"]) {
	 		
			require_once('PHPDecryptXLSXWithPassword.php');

			$encryptedFilePath = __DIR__.'/uploads/'.$fileName;
			$password = $_POST["pass"]; // password to "open" the file
			$decryptedFilePath = 'decrypted.xlsx';

			decrypt($encryptedFilePath, $password, $decryptedFilePath);

			$fileName = $decryptedFilePath;

	 	} else {
	 		$fileName = 'uploads/'.$fileName;
	 	}


		echo '<h1>Check XLSX</h1>';
		if ( $xlsx = SimpleXLSX::parse($fileName)) {

			// Produce array keys from the array values of 1st array element
			$header_values = $rows = [];
			$err = [];
			foreach ( $xlsx->rows() as $k => $r ) {
				if ( $k === 0 ) {
					$header_values = $r;
					continue;
				}
		        // check dates
		        $birth = mb_substr($r[2], 0, 10);
		        $material = mb_substr($r[9], 0, 10);
		        $sick = mb_substr($r[8], 0, 10);
		        $sex = $r[3];

 
		        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/", $birth)) {
		            $err[] = 'Неверно заполнено поле день рождения ' . $birth . ' в строке ' .$r[0].'<br>'; 
		        }
		        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/", $material)) {
		            $err[] = 'Неверно заполнено поле дата отбора материала ' . $material . ' в строке ' .$r[0].'<br>'; 
		        }

		        if($sick) {
		        	 if (!preg_match("/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/", $birth)) {
		            $err[] = 'Неверно заполнено дата заболевания ' . $sick . ' в строке ' .$r[0].'<br>'; 
		           }
		        }
		        if(!preg_match("/[МЖ]/", $sex) or $sex = '') {
		        	$err[] = 'Неверно указан пол в строке ' .$r[0].'<br>';
		        }
		        
		        //check fio
		        if (preg_match('/[0-9\<>,:!_+=@#$%&*[\](){}\?!`"\']/', $r[1])) {
		          $err[] = 'Неверно заполнено поле Ф.И.О ' . $r[1] . ' в строке ' .$r[0].'<br>'; 
		        } 
 
				//$rows[] = array_combine( $header_values, $r );
			}
		 
			if($err) {
				echo '<p style="color:red;">Найдены ошибки в файле.</p>';
				foreach ($err as &$value) {
				    echo $value;
				}
			} else {
				echo 'Файл прошел проверку';
			}

		 //    echo '<pre>';
		 //    print_r( $rows );
		 //    echo '</pre>';
		 
		} else {
			echo 'Пароль неверный!';
		}

	} else {
	 echo 'Допустимый формат файла xlsx';	
	}
} else {
	 echo 'Размер файла превышает допустимый размер ' . $upload_mb.'mb';	
}





