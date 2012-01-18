<?
	$src = preg_replace('![^a-zA-Z0-9\._\-]!', '', $_GET[src]);

	if (!file_exists("thumbs/$src")){

		#
		# get output size
		#

		$info = getimagesize("images/$src");

		if ($info[0] > $info[1]){

			$w = 100;
			$h = round(100 * ($info[1] / $info[0]));
		}else{
			$h = 100;
			$w = round(100 * ($info[0] / $info[1]));
		}


		#
		# make thumb
		#

		$dst_img = imagecreatetruecolor($w, $h);
		$src_img = imagecreatefromjpeg("images/$src");

		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);

		imagejpeg($dst_img, "thumbs/$src");

		imagedestroy($src_img);
		imagedestroy($dst_img);
		#exit;
	}

	header("Content-type: image/jpeg");

	echo file_get_contents("thumbs/$src");
	#echo "thumbs/$src ($w, $h)";
?>