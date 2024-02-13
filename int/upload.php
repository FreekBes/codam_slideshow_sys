<?php
	// error_reporting(E_ALL); ini_set('display_errors', 1);

	require_once("../include/auth.php");
	require_once("../include/useful.php");

	$files = array();

	if (!isset($_FILES['media']) && !isset($_POST['url'])) {
		http_response_code(400);
		die("media_missing");
	}

	if (isset($_POST['url'])) {
		// handle URLs to embed as media
		// simply copy include/redirbase.html to the media folder and append the URL to it
		$redirpage = setup_iframe_url($_POST['url']);
		$new_loc = "media/" . time() . "-" . generate_id(4) . ".html";
		$printable_loc = htmlspecialchars(strip_tags($new_loc));
		if (file_put_contents("../$new_loc", $redirpage)) {
			array_push($files, $printable_loc);
		}
		else {
			http_response_code(500);
			die("file_write_fail");
		}
	}

	if (isset($_FILES)) {
		$file_count = count($_FILES['media']['name']);
		// handle each file separately in this for loop
		for ($i = 0; $i < $file_count; $i++) {
			if ($_FILES['media']['error'][$i] != UPLOAD_ERR_OK || $_FILES['media']['size'][$i] == 0) {
				http_response_code(400);
				die("file_upload_fail");
			}
			$mime = mime_content_type($_FILES['media']['tmp_name'][$i]);
			if ($mime == "image/gif") {
				// hacky way of implementing gifs.
				// since gifs are internally used as a preview for mp4 files, we cannot simply
				// show a gif on a screen - while saving, it will convert all .gif extensions to
				// their corresponding .mp4 files.
				// so, in order to work around that, we convert the gif to an mp4 here.
				$video_id = time() . "-" . generate_id(4);
				exec("ffmpeg -i \"" . $_FILES['media']['tmp_name'][$i] . "\" -movflags faststart -pix_fmt yuv420p -vf \"scale=trunc(iw/2)*2:trunc(ih/2)*2\" ../media/$video_id.mp4");
				$duration = get_video_duration("../media/$video_id.mp4");
				$new_loc = "media/$video_id-$duration.mp4";
				if (!rename("../media/$video_id.mp4", "../$new_loc")) {
					http_response_code(500);
					die("file_write_fail");
				}
				$new_loc_gif = "media/$video_id-$duration.gif";
				$printable_loc = htmlspecialchars(strip_tags($new_loc_gif));
				if (!setup_video("../$new_loc", "../$new_loc_gif")) {
					http_response_code(500);
					die("video_setup_failed");
				}
				array_push($files, $printable_loc);
			}
			else if (strstr($mime, "image/")) {
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
				$duration = get_video_duration($_FILES['media']['tmp_name'][$i]);
				if ($duration == 0) {
					http_response_code(500);
					die("video_duration_zero");
				}
				$new_loc = "media/$video_id-$duration.$ext";
				$new_loc_gif = "media/$video_id-$duration.gif";
				$printable_loc = htmlspecialchars(strip_tags($new_loc_gif));
				// move the uploaded file to the media folder
				if (move_uploaded_file($_FILES['media']['tmp_name'][$i], "../$new_loc") === true) {
					if (!setup_video("../$new_loc", "../$new_loc_gif")) {
						http_response_code(500);
						die("video_setup_failed");
					}
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
