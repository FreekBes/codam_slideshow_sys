<?php
	require_once("../include/auth.php");
	require_once("../include/useful.php");

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

	// get the first day of the requested month as a timestamp
	$first_day_of_month = strtotime("01-".$_GET["month"]."-".$_GET["year"]);
	if ($first_day_of_month === false) {
		http_response_code(400);
		die();
	}

	// calculate the amount of days in the requested month with the use of the timestamp above
	$days_in_month = intval(date("t", $first_day_of_month));

	$calendar = array();
	// for every day in the requested month, gather the programme and add it to an array
	for ($i = 1; $i <= $days_in_month; $i++) {
		$date_full = date("Y-m-", $first_day_of_month) . sprintf("%02d", $i);
		array_push($calendar, get_programme_overview($date_full, false));
	}

	// output the array in json format
	echo json_encode($calendar);
?>