<?php
	require_once("../include/auth.php");
	require_once("../include/useful.php");

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

	echo json_encode(get_programme_overview($date_full));
?>