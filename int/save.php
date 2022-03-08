<?php
	require_once("../include/auth.php");

	// check if day key is in POST
	if (!isset($_POST["day"]) || empty($_POST["day"])) {
		http_response_code(400);
		die("missing_key_day");
	}

	// parse day into format of 'date +%F' command
	if ($_POST["day"] != "default") {
		if ($_POST["day"] != "today" && preg_match('/[^0-9\-]/', $_POST["day"])) {
			http_response_code(406);
			die();
		}

		$day_timestamp = strtotime($_POST["day"]);
		if ($day_timestamp === false) {
			http_response_code(400);
			die("day_parse_error");
		}
		$date_full = date("Y-m-d", $day_timestamp);
	}
	else {
		$day_timestamp = 0;
		$date_full = "default";
	}

	// check if media key is in POST, can be empty
	if (!array_key_exists("media", $_POST)) {
		http_response_code(400);
		die("missing_key_media");
	}

	// check if durations key is in POST
	if (!array_key_exists("durations", $_POST)) {
		http_response_code(400);
		die("missing_key_durations");
	}

	// check if default_enabled key is in POST, can be empty (is then false)
	if (!array_key_exists("default_enabled", $_POST)) {
		http_response_code(400);
		die("missing_key_default_enabled");
	}

	// cd into programmes folder
	if (!chdir("../programmes")) {
		http_response_code(500);
		die("no_access_to_programmes_folder");
	}

	// create day programme directory if it does not exist or is not a directory
	if (!is_dir($date_full)) {
		unlink($date_full);
		mkdir($date_full, 0755);
	}
	else {
		// clear the entire directory if it exists, we simply recreate all links
		@array_map('unlink', array_filter((array) glob("./$date_full/*")));
	}

	// cd into day programme directory
	if (!chdir("./$date_full")) {
		http_response_code(500);
		die("no_access_to_day_folder");
	}

	// if default programme is enabled, create a file to indicate this
	// else remove it if it exists (is not removed by unlink above, as it is a hidden file)
	if ($date_full != "default" && $_POST["default_enabled"] === "true") {
		file_put_contents(".default_enabled", "");
	}
	else if (file_exists(".default_enabled")) {
		unlink(".default_enabled");
	}

	// parse media files selected, are already in order
	$media = explode("|", $_POST["media"]);
	$amount = count($media);

	// perform security check on all referenced file locations
	// should not contain any path, directory, etc.
	for ($i = 0; $i < $amount; $i++) {
		if (preg_match('/[^a-zA-Z0-9\-\.]/', $media[$i])) {
			http_response_code(406);
			die("invalid_media_file");
		}
	}

	// parse durations, are already in order
	$durations = explode("|", $_POST["durations"]);
	if (count($durations) != $amount) {
		http_response_code(500);
		die("durations_arr_size_differs");
	}
	for ($i = 0; $i < $amount; $i++) {
		$durations[$i] = intval(floatval($durations[$i]) * 1000);
	}

	// create links to media files in day programme directory
	// replace media ending in .gif with media ending in .mp4
	// those are video files, .gif are just the previews in the dashboard
	// sadly we cannot link to any file (.*), since then the .gif might
	// show up... so we can only handle .mp4 files, hardcoded here.
	for ($i = 0; $i < $amount; $i++) {
		if (empty($media[$i])) {
			continue;
		}
		if (str_ends_with($media[$i], ".gif")) {
			$mp4_file = str_replace(".gif", ".mp4", $media[$i]);
			if (!link("../../media/" . $mp4_file, sprintf("%03d", $i)."_".$durations[$i]."_$mp4_file")) {
				http_response_code(500);
				die("link_creation_fail");
			}
		}
		else {
			if (!link("../../media/" . $media[$i], sprintf("%03d", $i)."_".$durations[$i]."_".$media[$i])) {
				http_response_code(500);
				die("link_creation_fail");
			}
		}
	}

	http_response_code(204);
?>