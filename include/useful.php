<?php
	require_once("settings.php");

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

	function get_video_duration($path_to_video) {
		// calculate the duration of the video using ffprobe command
		return (round(floatval(exec("ffprobe -v error -show_entries format=duration -of csv=p=0 \"$path_to_video\"")) * 1000));
	}

	function setup_video($path_to_video, $path_to_gif) {
		// convert the video to GIF format for use in the dashboard and programme editor
		exec("ffmpeg -i \"$path_to_video\" -t 10 -vf \"fps=5,scale=-1:72:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse\" -loop 0 \"$path_to_gif\"");
		return (true);
	}

	function setup_iframe_url($url) {
		// read the base of the redirector page and replace the URL_REPLACE string with the given URL
		$redirbase = file_get_contents(WWW_DIR . "/include/redirbase.html");
		$redirbase = str_replace("//URL_REPLACE\\", $url, $redirbase);
		return ($redirbase);
	}

	function get_programme_overview($date_full, $as_mp4 = false) {
		$programme_folder = WWW_DIR . "/programmes/$date_full";
		$returnable = array();
		if (!is_dir($programme_folder)) {
			$returnable['default_enabled'] = ($date_full != "default");
			$returnable['mirror'] = false;
			$returnable['media'] = array();
			return ($returnable);
		}

		$selected_media = glob("$programme_folder/*_*_*.{jpg,jpeg,png,mp4,html}", GLOB_BRACE);
		sort($selected_media, SORT_STRING);
		$default_enabled = ($date_full != "default" && file_exists("$programme_folder/.default_enabled"));
		$mirror_source = (file_exists("$programme_folder/.mirror") ? file_get_contents("$programme_folder/.mirror") : false);

		$returnable = array();
		$returnable['default_enabled'] = $default_enabled;
		$returnable['mirror'] = $mirror_source;
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
		$programme_dir = WWW_DIR . "/programmes/$full_date";
		chdir($programme_dir);

		// get last file in order of media
		$selected_media = glob("./*_*_*.{jpg,jpeg,png,mp4,html}", GLOB_BRACE);
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

	// AN UGLY FUNCTION TO PRINT A MEDIA ITEM, BUT FUNCTION > ONELINER, RIGHT?
	// media (object): a media object, which includes the source and duration (e.g. { file: "", duration: 10000 }), set to NULL for template element
	// media (string): a path to a media file
	// imported: set to TRUE if media was imported from the default programme and a special class should be applied for it
	// hidden: set to TRUE if media item should not be displayed
	// draggable: set to TRUE if media item should be draggable
	// full_delete: set to TRUE if media item should be deleted from the server instead of removed from the programme on x button click
	// editable: set to TRUE if the duration should be editable
	function echo_media_item($media, $imported, $hidden, $draggable, $full_delete, $editable) {
		$class_name = "media-item";
		$title = "";
		$styles = "";
		if (gettype($media) == "string") {
			$src = $media;
			$dur = 10;
		}
		else {
			$src = (!empty($media) ? "media/".$media['file'] : "");
			$dur = (!empty($media) ? $media['duration'] / 1000 : 10);
		}
		if ($imported) {
			$class_name .= " from-default";
			$title = "Imported media from the default programme. To modify this media, edit the default programme instead.";
			if ($hidden) {
				$styles = "display: none;";
			}
		}
?><li class="<?php echo $class_name; ?>" title="<?php echo $title; ?>" style="<?php echo $styles; ?>">
<?php if (str_ends_with($src, ".html")) { ?>
<iframe src="<?php echo $src; ?>" frameborder="0" sandbox="allow-scripts"></iframe>
<?php } else { ?>
<img src="<?php echo $src; ?>" <?php if ($draggable) { ?>draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)"<?php } ?> />
<?php } ?>
<?php if (!$imported) { if ($full_delete) { ?>
<button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button>
<?php } else { ?>
<button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button>
<?php } } if ($editable) { ?>
<input type="number" class="duration" value="<?php echo $dur; ?>" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" onfocusout="if (this.value.trim() == '') { this.value = 10; }" />
<?php } ?>
</li><?php } // end of echo_media_function ?>
