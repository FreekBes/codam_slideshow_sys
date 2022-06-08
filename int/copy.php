<?php
	require_once("../include/auth.php");

	// check if from key is in POST
	if (!isset($_GET["from"]) || empty($_GET["from"])) {
		http_response_code(400);
		die("missing_key_from");
	}

	// parse from day into format of 'date +%F' command
	if ($_GET["from"] != "default") {
		if ($_GET["from"] != "today" && preg_match('/[^0-9\-]/', $_GET["from"])) {
			http_response_code(406);
			die();
		}

		$from_timestamp = strtotime($_GET["from"]);
		if ($from_timestamp === false) {
			http_response_code(400);
			die("day_parse_error");
		}
		$from_full = date("Y-m-d", $from_timestamp);
	}
	else {
		$from_timestamp = 0;
		$from_full = "default";
	}

	// check if to key is in POST
	if (!isset($_GET["to"]) || empty($_GET["to"])) {
		http_response_code(400);
		die("missing_key_from");
	}

	// parse to day into format of 'date +%F' command
	if ($_GET["to"] != "default") {
		if ($_GET["to"] != "today" && preg_match('/[^0-9\-]/', $_GET["to"])) {
			http_response_code(406);
			die();
		}

		$to_timestamp = strtotime($_GET["to"]);
		if ($to_timestamp === false) {
			http_response_code(400);
			die("day_parse_error");
		}
		$to_full = date("Y-m-d", $to_timestamp);
	}
	else {
		$to_timestamp = 0;
		$to_full = "default";
	}

	// cd into programmes folder
	if (!chdir("../programmes")) {
		http_response_code(500);
		die("no_access_to_programmes_folder");
	}

	// delete to programme contents
	if (is_dir("./$to_full")) {
		$files = glob("./$to_full/{,.}*", GLOB_BRACE);
		foreach ($files as $file) {
			if (is_file($file) && !unlink($file)) {
				http_response_code(500);
				die("rm_file_failure");
			}
		}
		if (!rmdir("./$to_full")) {
			http_response_code(500);
			die("rm_failure");
		}
	}

	// check if from programme exists. if it does not, there's nothing to copy.
	// then we create a new programme with the default programme enabled.
	if (!is_dir("./$from_full")) {
		mkdir("./$to_full", 0755);
		if ($to_full != "default") {
			file_put_contents("./$to_full/.default_enabled", "");
		}
		http_response_code(204);
		exit();
	}

	// copy entire programme
	exec("cp -r ./$from_full ./$to_full", $output, $return_var);
	if ($return_var != 0) {
		http_response_code(500);
		die("copy_failure");
	}

	http_response_code(204);
?>
