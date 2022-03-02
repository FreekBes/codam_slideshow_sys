<?php
	define('SCREEN_RES_W', 1920);
	define('SCREEN_RES_H', 1080);

	function generate_id($length) {
		if (empty($length)) {
			throw new Exception('Length of ID to generate not set');
			return null;
		}

		$code = "";
		$possible = "123456789abcdefghijkmnpqrstuvwxyz";
		for ($i = 0; $i < $length; $i++) {
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
		}

		return ($code);
	}

	function imagecreate_wrapper($file) {
		$img_type = exif_imagetype($file);
		switch ($img_type) {
			case IMAGETYPE_JPEG:
				return (imagecreatefromjpeg($file));
			case IMAGETYPE_PNG:
				return (imagecreatefrompng($file));
			case IMAGETYPE_GIF:
				return (imagecreatefromgif($file));
			case IMAGETYPE_WEBP:
				return (imagecreatefromwebp($file));
			case IMAGETYPE_BMP:
				return (imagecreatefrombmp($file));
			default:
				return (false);
		}
	}

	function fit_image_for_screen($image) {
		$width = imagesx($image);
		$height = imagesy($image);
		$ratio = $width / $height;
		$wanted_width = SCREEN_RES_W;
		$wanted_height = SCREEN_RES_H;
		$wanted_ratio = SCREEN_RES_W / SCREEN_RES_H;

		if ($wanted_ratio > $ratio) {
			$wanted_width = floor($wanted_height * $ratio);
		}
		else {
			$wanted_height = floor($wanted_width / $ratio);
		}

		$new = imagecreatetruecolor($wanted_width, $wanted_height);
		$bg_color = imagecolorallocate($new, 0, 0, 0);
		imagefill($new, 0, 0, $bg_color);
		imagecopyresampled($new, $image, 0, 0, 0, 0, $wanted_width, $wanted_height, $width, $height);
		return ($new);
	}

	function get_programme_overview($date_full) {
		$programme_folder = "../programmes/$date_full";
		if (!is_dir($programme_folder)) {
			return ((object) array(
				'default_enabled' => true,
				'media' => array()
			));
		}
		
		$selected_media = glob("$programme_folder/*.{jpg,jpeg,png,mp4}", GLOB_BRACE);
		$default_enabled = file_exists("$programme_folder/.default_enabled");

		$returnable = array();
		$returnable['default_enabled'] = $default_enabled;
		$returnable['media'] = array();
		foreach ($selected_media as $media) {
			$temp = explode("_", $media);
			array_push($returnable['media'], (object) array (
				'file' => str_replace(".mp4", ".gif", array_pop($temp)),
				'duration' => intval($temp[1])
			));
		}
		return ($returnable);
	}

	function simply_add_to_programme($full_date, $media, $duration) {
		$pwd = getcwd();
		$programme_dir = "/var/www/dashboard/programmes/$full_date";

		// add to default programme
		chdir($programme_dir);

		// get last file in order of media
		$selected_media = array_reverse(glob("./*.{jpg,jpeg,png,mp4}", GLOB_BRACE));
		$i = intval(explode("_", array_pop($selected_media))[0]) + 1;

		if (str_ends_with($media, ".gif")) {
			$mp4_file = str_replace(".gif", ".mp4", $media);
			if (!link("../../media/" . $mp4_file, $i."_".$duration."_$mp4_file")) {
				return (false);
			}
		}
		else {
			if (!link("../../media/" . $media, $i."_".$duration."_".$media)) {
				return (false);
			}
		}
		chdir($pwd);
		return (true);
	}
?>
