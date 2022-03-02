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
	<p>
		The default programme is a programme that contains media which is always displayed on the screen, no matter the date.
		<br>
		<a id="default-link" onmouseover="showProgTooltip(event)" onmouseout="hideProgTooltip(event)" class="day" href="programme.php?day=default">Edit default programme</a>
		<br>
		It is possible though, to disable the default programme on specific days. Do so by clicking on a day in the calendar below.
		<br><a href="javascript:void()" onclick="openSimpleUploader(event)">Add media in a more plain way</a>
	</p>
	<hr />
	<main></main>
	<div id="loading">Loading... Please wait</div>
</body>
</html>