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
	// add default programme to media overview for clearity
	// even when it's disabled, as then the media should just not display but still be there
	// for when user does enable it
	if ($date_internal != "default") {
		$default_programme = get_programme_overview("default", true);
		for ($i = 0; $i < count($default_programme["media"]); $i++) {
			$default_programme["media"][$i]["from_default"] = true;
		}
		$programme["media"] = combine_media_prepend($programme, $default_programme);
	}

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
	<script src="js/dragnupload.js"></script>
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
			<a class="header-btn" href="show.php?nosync&day=<?php echo $_GET["day"]; ?>&num=0" target="codamshow" title="Show programme">slideshow</a>
		</nav>
	</header>
	<main>
		<?php if ($day != "default") { ?>
			<p>This is the programme that will be shown on <?php echo $date_full; ?>.</p>
		<?php } else { ?>
			<p>The default programme is a programme that contains media which is always displayed on the screen, no matter the date. It can be disabled on a day-to-day basis.</p>
		<?php } ?>
		<h3>Media to choose from</h3>
		<ul id="media-list" ondrop="drop(event)" ondragover="allowDrop(event)"><?php foreach ($available_media as $media) { echo_media_item($media, false, false, true, true, false); } ?></ul>
		<button onclick="openUploader()">Upload media</button>
		<h3>Media displayed on screen</h3>
		<ol id="selected-media" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)"><?php foreach($programme["media"] as $media) { echo_media_item($media, isset($media['from_default']), !$programme['default_enabled'], !isset($media['from_default']), false, !isset($media['from_default'])); } ?></ol>
		<template id="media-item-template"><?php echo_media_item(NULL, false, false, true, true, false); ?></template>
		<template id="media-item-template-selected"><?php echo_media_item(NULL, false, false, true, false, true); ?></template>
		<?php if ($date_internal != "default") { ?><input type="checkbox" name="default_enabled" id="default_enabled" value="true" <?php echo ($programme['default_enabled'] ? "checked " : ""); ?> onchange="hideShowDefaults(this.checked)" /><label for="default_enabled">Enable default programme</label><?php } ?>
		<br /><br />
		<button onclick="saveProgramme(event)">Save</button>
		<small><a href="calendar.php">Back to calendar</a></small>
	</main>
	<div id="loading">Loading... Please wait</div>
</body>
</html>
