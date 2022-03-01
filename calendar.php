<?php require_once("include/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Calendar overview</title>
	<script src="js/useful.js"></script>
	<script src="js/getcalendar.js"></script>
	<script src="js/calendar.js"></script>
	<link rel="stylesheet" href="styles.css" />
</head>
<body>
	<h1>Calendar</h1>
	<a id="default-link" onmouseover="showProgTooltip(event)" onmouseout="hideProgTooltip(event)" class="day" href="programme.php?day=default">Edit default programme</a>
	<hr />
	<main></main>
</body>
</html>