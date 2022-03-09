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

	function get_programme_overview($date_full, $as_mp4 = false) {
		$programme_folder = "/var/www/dashboard/programmes/$date_full";
		$returnable = array();
		if (!is_dir($programme_folder)) {
			$returnable['default_enabled'] = ($date_full != "default");
			$returnable['media'] = array();
			return ($returnable);
		}
		
		$selected_media = glob("$programme_folder/*_*_*.{jpg,jpeg,png,mp4}", GLOB_BRACE);
		sort($selected_media, SORT_STRING);
		$default_enabled = ($date_full != "default" && file_exists("$programme_folder/.default_enabled"));

		$returnable = array();
		$returnable['default_enabled'] = $default_enabled;
		$returnable['media'] = array();
		foreach ($selected_media as $media) {
			$temp = explode("_", $media);
			$item = array();
			$src = array_pop($temp);
			$item['file'] = ($as_mp4 === true ? $src : str_replace(".mp4", ".gif", $src));
			$item['duration'] = intval($temp[1]);
			array_push($returnable['media'], $item);
		}
		return ($returnable);
	}

	function simply_add_to_programme($full_date, $media, $duration) {
		$pwd = getcwd();
		$programme_dir = "/var/www/dashboard/programmes/$full_date";

		// add to default programme
		chdir($programme_dir);

		// get last file in order of media
		$selected_media = glob("./*_*_*.{jpg,jpeg,png,mp4}", GLOB_BRACE);
		rsort($selected_media, SORT_STRING);
		$i = intval(explode("_", array_pop($selected_media))[0]) + 1;

		if (str_ends_with($media, ".gif")) {
			$mp4_file = str_replace(".gif", ".mp4", $media);
			if (!link("../../media/" . $mp4_file, sprintf("%03d", $i)."_".$duration."_$mp4_file")) {
				return (false);
			}
		}
		else {
			if (!link("../../media/" . $media, sprintf("%03d", $i)."_".$duration."_".$media)) {
				return (false);
			}
		}
		chdir($pwd);
		return (true);
	}

	function combine_media_prepend($p1, $p2) {
		return (array_merge($p2["media"], $p1["media"]));
	}

	function combine_media_append($p1, $p2) {
		return (array_merge($p1["media"], $p2["media"]));
	}
?>
