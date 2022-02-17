<?php
	error_reporting(E_ALL); ini_set('display_errors', 1);

	if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK
		&& $_FILES['image']['size'] > 0) {
			$temp = explode(".", $_FILES['image']['name']);
			$new_loc = "media/temp." . end($temp);
			if (move_uploaded_file($_FILES['image']['tmp_name'], "../$new_loc") === true) {
				exec("xbmc-send -a \"ShowPicture(/var/www/dashboard/$new_loc)\"", $output, $exit_code);
				echo "Executed xbmc-send, exit code $exit_code";
			}
			else {
				echo "Error: could not move file to specified location";
			}
	}
?>
