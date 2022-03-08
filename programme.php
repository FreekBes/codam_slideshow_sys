<?php
	require_once("include/auth.php");

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
		$programme_name = "Programme for $date_full";
	}
	else {
		$timestamp = 0;
		$date_internal = "default";
		$date_full = "default";
		$programme_name = "Default programme";
	}

	$programme_dir = "programmes/$date_internal";
	$selected_durations = array();
	if (is_dir($programme_dir)) {
		$default_enabled = ($date_internal != "default" ? file_exists("$programme_dir/.default_enabled") : false);
		$selected_media = glob("$programme_dir/*.{jpg,jpeg,png,mp4}", GLOB_BRACE);
		sort($selected_media, SORT_STRING);
		for ($i = 0; $i < count($selected_media); $i++) {
			$temp = explode("_", $selected_media[$i]);
			if (count($temp) < 3) {
				array_splice($selected_media, $i, 1);
				continue;
			}
			array_push($selected_durations, intval($temp[1]) / 1000);
			$selected_media[$i] = "media/".array_pop($temp);
			$selected_media[$i] = str_replace(".mp4", ".gif", $selected_media[$i]);
		}
	}
	else {
		$default_enabled = ($date_internal != "default" ? true : false);
		$selected_media = array();
	}

	$available_media = glob("media/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
	sort($available_media, SORT_STRING);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $programme_name; ?></title>
	<link rel="stylesheet" href="css/styles.css" />
	<script src="js/useful.js"></script>
	<script src="js/programme.js"></script>
</head>
<body onload="document.getElementById('loading').style.display = 'none';">
	<h1><?php echo $programme_name; ?></h1>
	<h3>Media to choose from</h3>
	<ul id="media-list">
		<?php foreach ($available_media as $media) { ?><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="<?php echo $media; ?>" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li><?php } ?>
	</ul>
	<button onclick="openUploader()">Upload media</button>
	<h3>Media displayed on screen</h3>
	<ol id="selected-media" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
		<?php for ($i = 0; $i < count($selected_media); $i++) { ?><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="<?php echo $selected_media[$i]; ?>" /><button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button><input type="number" class="duration" value="<?php echo $selected_durations[$i]; ?>" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" /></li><?php } ?>
	</ol>
	<template id="media-item-template"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li></template>
	<template id="media-item-template-selected"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button><input type="number" class="duration" value="10" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" /></li></template>
	<?php if ($date_internal != "default") { ?><input type="checkbox" name="default_enabled" id="default_enabled" value="true" <?php echo ($default_enabled ? "checked " : ""); ?>/><label for="default_enabled">Enable default programme</label><?php } ?>
	<button onclick="saveProgramme(event)">Save</button>
	<small><a href="calendar.php">Back to calendar</a></small>
	<div id="loading">Loading... Please wait</div>
</body>
</html>
