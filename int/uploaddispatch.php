<?php
	error_reporting(E_ALL); ini_set('display_errors', 1);

	require_once("../include/auth.php");
	require_once("../include/useful.php");

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
		$ext = array_pop($temp);
		$video_id = time() . "-" . generate_id(4);
		$duration = round(floatval(exec("ffprobe -v error -show_entries format=duration -of csv=p=0 \"" . $_FILES['video']['tmp_name'] . "\"")) * 1000);
		if ($duration == 0) {
			die("Video duration is 0");
		}
		$new_loc = "media/$video_id-$duration.$ext";
		$new_loc_gif = "media/$video_id-$duration.gif";
		if (move_uploaded_file($_FILES['video']['tmp_name'], "../$new_loc") === true) {
			ignore_user_abort(true);
			set_time_limit(0);
			exec("ffmpeg -i ../$new_loc -vf \"fps=5,scale=-1:72:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse\" -loop 0 ../$new_loc_gif");
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
?>