<?php
	require_once("../include/auth.php");

	if (!isset($_GET["media"]) || empty($_GET["media"])) {
		http_response_code(400);
		die();
	}

	if (preg_match('/[^a-zA-Z0-9\-\.]/', $_GET["media"])) {
		http_response_code(406);
		die();
	}

	// delete the media file in the media folder
	// the delete.php file is supposed to get videos in the
	// .gif format (.mp4 is deleted later in this script)
	if (!unlink("../media/" . $_GET["media"])) {
		http_response_code(404);
	}
	else {
		http_response_code(204);
	}

	if (str_ends_with($_GET["media"], ".gif")) {
		// delete the mp4 file (replace .gif extension with .mp4)
		$mp4_file = str_replace(".gif", ".mp4", $_GET["media"]);
		@unlink("../media/" . $mp4_file);

		// delete file references in programmes
		foreach (glob("../programmes/*/*_*_" . $mp4_file) as $link) {
			@unlink($link);
		}
	}
	else {
		// delete file references in programmes
		foreach (glob("../programmes/*/*_*_" . $_GET["media"]) as $link) {
			@unlink($link);
		}
	}
?>