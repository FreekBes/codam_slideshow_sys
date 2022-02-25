<?php
	// error_reporting(E_ALL); ini_set('display_errors', 1);

	require_once("../include/auth.php");
	require_once("../include/useful.php");

	if (isset($_FILES['media']) and $_FILES['media']['error'] == UPLOAD_ERR_OK
		&& $_FILES['media']['size'] > 0)
	{
		$mime = mime_content_type($_FILES['media']['tmp_name']);
		if (strstr($mime, "image/")) {
			$new_loc = "media/" . generate_id(14) . ".jpeg";
			$printable_loc = htmlspecialchars(strip_tags($new_loc));
			$input_img = imagecreate_wrapper($_FILES['media']['tmp_name']);
			if ($input_img === false) {
				http_response_code(500);
				die("file_read_fail");
			}
			$output_img = fit_image_for_screen($input_img);
			if (imagejpeg($output_img, "../$new_loc", 95)) {
				http_response_code(201);
				echo "success:$printable_loc";
				?><script>window.opener.addMedia("<?php echo $printable_loc; ?>");</script><?php
				die();
			}
			else {
				http_response_code(500);
				die("file_write_fail");
			}
		}
		else if (strstr($mime, "video/")) {
			$temp = explode(".", $_FILES['media']['name']);
			$ext = array_pop($temp);
			$new_loc = "media/" . generate_id(14) . "." . $ext;
			$printable_loc = htmlspecialchars(strip_tags($new_loc));
			if (move_uploaded_file($_FILES['media']['tmp_name'], "../$new_loc") === true) {
				http_response_code(201);
				echo "success:$printable_loc";
				?><script>window.opener.addMedia("<?php echo $printable_loc; ?>");</script><?php
				die();
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

	/*
	if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK
		&& $_FILES['image']['size'] > 0) {
			$temp = explode(".", $_FILES['image']['name']);
			$new_loc = "media/temp." . end($temp);
			if (move_uploaded_file($_FILES['image']['tmp_name'], "../$new_loc") === true) {
				exec("xbmc-send -a \"ShowPicture(/var/www/dashboard/$new_loc)\"", $output, $exit_code);
				echo "Executed xbmc-send ShowPicture, exit code $exit_code";
			}
			else {
				echo "Error: could not move file to specified location";
			}
	}
	else if (isset($_FILES['video']) && $_FILES['video']['error'] == UPLOAD_ERR_OK
	&& $_FILES['video']['size'] > 0) {
		$temp = explode(".", $_FILES['video']['name']);
		$new_loc = "media/temp." . end($temp);
		if (move_uploaded_file($_FILES['video']['tmp_name'], "../$new_loc") === true) {
			exec("xbmc-send -a \"Action(Stop)\"", $output, $exit_code);
			echo "Executed xbmc-send Action Stop to exit out of picture mode, exit code $exit_code";
			exec("xbmc-send -a \"PlayMedia(/var/www/dashboard/$new_loc)\"", $output, $exit_code);
			echo "Executed xbmc-send PlayMedia, exit code $exit_code";
			exec("xbmc-send -a \"PlayerControl(RepeatOne)\"", $output, $exit_code);
			echo "Executed xbmc-send PlayerControl RepeatOne, exit code $exit_code";
		}
		else {
			echo "Error: could not move file to specified location";
		}
	}
	else {
		echo "Something went wrong";
	}
	*/
?>
