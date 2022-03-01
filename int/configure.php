<?php
	require_once("../include/auth.php");
	require_once("../include/useful.php");

	if (!isset($_POST["media"]) || empty($_POST["media"])) {
		http_response_code(400);
		die("media_not_set");
	}

	if (preg_match('/[^a-zA-Z0-9\-\.]/', $_POST["media"])) {
		http_response_code(406);
		die();
	}

	if (!isset($_POST["duration"]) || empty($_POST["duration"])) {
		http_response_code(400);
		die("duration_not_set");
	}

	$media = $_POST["media"];
	$duration = intval(floatval($_POST["duration"]) * 1000);

	if (array_key_exists("setup-dates", $_POST) && $_POST["setup-dates"] == "true") {
		// add to all programmes of a specific date range... more complicated!
		try {
			$begin = new DateTime($_POST["start"]);
			$end = new DateTime($_POST["end"]);
		}
		catch (Exception $e) {
			http_response_code(400);
			die("invalid_start_or_end_date");
		}

		$interval = DateInterval::createFromDateString("1 day");
		$period = new DatePeriod($begin, $interval, $end);
		foreach ($period as $date) {
			$full_date = $date->format("Y-m-d");
			if (!is_dir("../programmes/$full_date")) {
				mkdir("../programmes/$full_date", 0755);
			}
			simply_add_to_programme($full_date, $media, $duration);
		}
	}
	else {
		simply_add_to_programme("default", $media, $duration);
		http_response_code(201);
	}
?>