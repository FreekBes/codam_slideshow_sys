<?php
	require_once("settings.php");

	if ($_SERVER['PHP_AUTH_USER'] != DASHBOARD_USERNAME || $_SERVER['PHP_AUTH_PW'] != DASHBOARD_PASSWORD) {
		header("WWW-Authenticate: Basic realm=\"" . ORGANIZATION_NAME . "\"");
		http_response_code(401);
		die("error:unauthorized");
	}
?>
