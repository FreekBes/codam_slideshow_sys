<?php
	header('Content-Type: application/json; charset=utf-8');

	if (!isset($_GET["day"]) || empty($_GET["day"])) {
		http_response_code(400);
		die();
	}

	if ($_GET["day"] != "default") {
		if (preg_match('/[^0-9\-]/', $_GET["day"])) {
			http_response_code(406);
			die();
		}

		$day_timestamp = strtotime($_GET["day"]);
		if ($day_timestamp === false) {
			http_response_code(400);
			die();
		}
		$date_full = date("Y-m-d", $day_timestamp);
	}
	else {
		$date_full = "default";
	}

	$programme_folder = "../programmes/$date_full";
	if (!is_dir($programme_folder)) {
		http_response_code(204);
		die();
	}
	
	$programme_overview = file_get_contents("$programme_folder/overview.json");
	if ($programme_overview === false) {
		http_response_code(404);
		die();
	}
	echo $programme_overview;
?>