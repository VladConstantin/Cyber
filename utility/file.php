<?php

class File {

	public static function Upload($array,$local=false) {
		$f3 = Base::instance();
		extract($array);

		$allow_upload = 1;

		//checks the mime type
		$fileType = $array['type'];
		$mimes = array('jpg|jpeg' => 'image/jpeg' , 'png' => 'image/png');
		if(!in_array($fileType, $mimes)){
			$allow_upload=0;
			\StatusMessage::add('Only files of type jpg, jpeg or png are allowed.','danger');
		}

		//checks the file extension
		if($allow_upload==1){
			$filePath = $array['name'];
			$fileExt = pathinfo($filePath , PATHINFO_EXTENSION);
			if($fileExt != "jpg" && $fileExt != "jpeg" && $fileExt != "png"){
				 $allow_upload = 0;
				 \StatusMessage::add(".$fileExt files are not allowed. Only files with the extension .jpg, .jpeg or .png are allowed.",'danger');
			}
		}

		//checks for double file extension
		if($allow_upload==1){
			$fileName = pathinfo($array['name'], PATHINFO_BASENAME);
			$fileArray = explode(".", $fileName);
			if(count($fileArray) !== 2){
				$allow_upload = 0;
				\StatusMessage::add("No periods are allowed in the file name.",'danger');
			}
		}

		if($allow_upload == 1){
			$directory = getcwd() . '/uploads';
			$destination = $directory . '/' . $name;
			$webdest = '/uploads/' . $name;

			//Local files get moved
			if($local) {
				if (copy($tmp_name,$destination)) {
					chmod($destination,0666);
					return $webdest;
				} else {
					return false;
				}
			//POSTed files are done with move_uploaded_file
			} else {
				if (move_uploaded_file($tmp_name,$destination)) {
					chmod($destination,0666);
					return $webdest;
				} else {
					return false;
				}
			}
		}
		else{
			return false;
		}
	}
}

?>
