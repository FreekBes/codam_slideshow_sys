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

	// check if source key is in POST, can be empty
	// if empty, sync/mirroring will be disabled
	// if not empty, it should be an IP address or domain of another instance of
	// one of these slideshow systems
	if (!array_key_exists("source", $_POST)) {
		http_response_code(400);
		die("missing_key_source");
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

	// cd into day programme directory
	if (!chdir("./$date_full")) {
		http_response_code(500);
		die("no_access_to_day_folder");
	}

	// if source is empty or false, disable syncing/mirroring
	if (empty($_POST["source"]) || $_POST["source"] == "false") {
		if (file_exists(".mirror")) {
			unlink(".mirror");
		}
		http_response_code(204);
	}
	else {
		// if not empty, check if the IP is a valid instance of a system like this one
		// by trying to retrieve the default programme...
		// if it exists, the domain/IP will be saved in a file called .mirror in the programme folder
		// if it does not exist, syncing/mirroring will be disabled
		$domain = (strpos($_POST["source"], "http") !== false ? preg_replace("(^https?://)", "", $_POST["source"] ) : $_POST["source"]);
		$domain = explode('/', $domain)[0];
		$headers = @get_headers("http://" . $domain . "/int/get.php?day=default");
		if (!$headers || $headers[0] != "HTTP/1.1 200 OK") {
			if (file_exists(".mirror")) {
				unlink(".mirror");
			}
			http_response_code(502);
			die("source_not_found");
		}
		echo $domain;
		file_put_contents(".mirror", $domain);
		http_response_code(201);
	}
?>