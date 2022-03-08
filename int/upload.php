<?php
	// error_reporting(E_ALL); ini_set('display_errors', 1);

	require_once("../include/auth.php");
	require_once("../include/useful.php");

	$files = array();

	if (isset($_FILES['media'])) {
		$file_count = count($_FILES['media']['name']);
		// handle each file separately in this for loop
		for ($i = 0; $i < $file_count; $i++) {
			if ($_FILES['media']['error'][$i] == UPLOAD_ERR_OK && $_FILES['media']['size'][$i] > 0)
			{
				$mime = mime_content_type($_FILES['media']['tmp_name'][$i]);
				if (strstr($mime, "image/")) {
					$new_loc = "media/" . time() . "-" . generate_id(4) . ".jpeg";
					$printable_loc = htmlspecialchars(strip_tags($new_loc));
					// load image into memory (php's gd addon)
					$input_img = imagecreate_wrapper($_FILES['media']['tmp_name'][$i]);
					if ($input_img === false) {
						http_response_code(500);
						die("file_read_fail");
					}
					// resize image to fit on a 1920x1080 screen
					$output_img = fit_image_for_screen($input_img);
					// write image to disk with imagejpeg function
					if (imagejpeg($output_img, "../$new_loc", 95)) {
						array_push($files, $printable_loc);
					}
					else {
						http_response_code(500);
						die("file_write_fail");
					}
				}
				else if (strstr($mime, "video/")) {
					$temp = explode(".", $_FILES['media']['name'][$i]);
					$ext = array_pop($temp);
					$video_id = time() . "-" . generate_id(4);
					// calculate the duration of the video using ffprobe command
					$duration = round(floatval(exec("ffprobe -v error -show_entries format=duration -of csv=p=0 \"" . $_FILES['media']['tmp_name'][$i] . "\"")) * 1000);
					if ($duration == 0) {
						http_response_code(500);
						die("video_duration_unknown_or_zero");
					}
					$new_loc = "media/$video_id-$duration.$ext";
					$new_loc_gif = "media/$video_id-$duration.gif";
					$printable_loc = htmlspecialchars(strip_tags($new_loc_gif));
					// move the uploaded file to the media folder
					if (move_uploaded_file($_FILES['media']['tmp_name'][$i], "../$new_loc") === true) {
						// convert the video to GIF format for use in the dashboard and programme editor
						exec("ffmpeg -i ../$new_loc -vf \"fps=5,scale=-1:72:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse\" -loop 0 ../$new_loc_gif");
						array_push($files, $printable_loc);
					}
					else {
						http_response_code(500);
						die("file_write_fail");
					}
				}
				else {
					http_response_code(400);
					die("unsupported_file_type");
				}
			}
			else {
				http_response_code(400);
				die("file_upload_fail");
			}
		}
	}
	else {
		http_response_code(400);
		die("file_upload_fail");
	}

	http_response_code(201);
	// if GET key popup is defined in the URL, it means the window is running in a popup
	// and we have access to the opener window, where we can call a function to notify
	// that window that new media is available (to list in the programme editor, for example)
	if (isset($_GET["popup"])) {
		?><script>window.opener.addMedia(["<?php echo implode("\", \"", $files); ?>"]);</script><?php
	}
	else {
		// if it is not defined, simply output them in a string, each file URL split by a |
		echo implode("|", $files);
	}
?>
