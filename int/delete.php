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

	if (!unlink("../media/" . $_GET["media"])) {
		http_response_code(404);
	}
	else {
		http_response_code(204);
	}

	if (str_ends_with($_GET["media"], ".gif")) {
		$mp4_file = str_replace(".gif", ".mp4", "../media/" . $_GET["media"]);
		@unlink("../media/" . $mp4_file);
	}
?>