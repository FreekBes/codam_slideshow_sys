<?php
	header('Content-Type: application/json; charset=utf-8');

	if (!isset($_GET["month"]) || empty($_GET["month"])) {
		http_response_code(400);
		die();
	}

	if (!isset($_GET["year"]) || empty($_GET["year"])) {
		http_response_code(400);
		die();
	}

	if (preg_match('/[^0-9]/', $_GET["month"]) || preg_match('/[^0-9]/', $_GET["year"])) {
		http_response_code(406);
		die();
	}

	$first_day_of_month = strtotime("01-".$_GET["month"]."-".$_GET["year"]);
	if ($first_day_of_month === false) {
		http_response_code(400);
		die();
	}
	$days_in_month = intval(date("t", $first_day_of_month));

	$calendar = array();
	for ($i = 1; $i <= $days_in_month; $i++) {
		$date_full = date("Y-m-", $first_day_of_month) . sprintf("%02d", $i);
		$programme_folder = "../programmes/$date_full";
		if (is_dir($programme_folder)) {
			$programme_overview = file_get_contents("$programme_folder/overview.json");
			if ($programme_overview !== false) {
				array_push($calendar, json_decode($programme_overview));
			}
			else {
				// unable to read file or overview.json is missing
				// should not happen, so this is a server error
				http_response_code(500);
				die();
			}
		}
		else {
			array_push($calendar, (object) array(
				'default_enabled' => true,
				'media' => array()
			));
		}
	}

	echo json_encode($calendar);
?>