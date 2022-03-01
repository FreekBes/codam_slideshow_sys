<?php
	require_once("include/auth.php");

	header("Location: calendar.php");
	http_response_code(302);
	die();
?>
