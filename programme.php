<?php
	require_once("include/auth.php");
	require_once("include/useful.php");

	if (!isset($_GET["day"]) || empty($_GET["day"])) {
		header("Location: error.php?e=Day%20not%20set");
		die();
	}
	$day = $_GET["day"];
	if ($day != "default") {
		if ($_GET["day"] != "today" && preg_match('/[^0-9\-]/', $_GET["day"])) {
			header("Location: error.php?e=Invalid%20day");
			die();
		}

		$timestamp = strtotime($_GET["day"]);
		$date_internal = date("Y-m-d", $timestamp);
		$date_full = date("l, \\t\\h\\e jS \\o\\f F", $timestamp);
		$programme_name = "Programme for $date_internal";
	}
	else {
		$timestamp = 0;
		$date_internal = "default";
		$date_full = "default";
		$programme_name = "Default programme";
	}

	$programme = get_programme_overview($date_internal, false);

	$available_media = glob("media/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
	sort($available_media, SORT_STRING);
	$available_media = array_reverse($available_media);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $programme_name; ?></title>
	<script src="js/useful.js"></script>
	<script src="js/programme.js"></script>
	<script src="js/dragndrop.js"></script>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
	<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
	<header>
		<a class="header-btn back-btn" href="calendar.php" title="Back to calendar overview">arrow_back</a>
		<h1><?php echo $programme_name; ?></h1>
		<nav>
			<a class="header-btn" href="javascript:void(null)" onclick="openUploader()" title="Upload media">add</a>
			<a class="header-btn" href="show.php?day=<?php echo $_GET["day"]; ?>&num=0" target="codamshow" title="Show programme">slideshow</a>
		</nav>
	</header>
	<main>
		<?php if ($day != "default") { ?>
			<p>This is the programme that will be shown on <?php echo $date_full; ?>.</p>
		<?php } else { ?>
			<p>The default programme is a programme that contains media which is always displayed on the screen, no matter the date. It can be disabled on a day-to-day basis.</p>
		<?php } ?>
		<h3>Media to choose from</h3>
		<ul id="media-list"><?php foreach ($available_media as $media) { ?><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="<?php echo $media; ?>" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li><?php } ?></ul>
		<button onclick="openUploader()">Upload media</button>
		<h3>Media displayed on screen</h3>
		<ol id="selected-media" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)"><?php foreach($programme["media"] as $media) { ?><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="media/<?php echo $media['file']; ?>" /><button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button><input type="number" class="duration" value="<?php echo $media['duration'] / 1000; ?>" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" /></li><?php } ?></ol>
		<template id="media-item-template"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li></template>
		<template id="media-item-template-selected"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button><input type="number" class="duration" value="10" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" /></li></template>
		<?php if ($date_internal != "default") { ?><input type="checkbox" name="default_enabled" id="default_enabled" value="true" <?php echo ($programme['default_enabled'] ? "checked " : ""); ?>/><label for="default_enabled">Enable default programme</label><?php } ?>
		<br /><br />
		<button onclick="saveProgramme(event)">Save</button>
		<small><a href="calendar.php">Back to calendar</a></small>
	</main>
	<div id="loading">Loading... Please wait</div>
</body>
</html>
