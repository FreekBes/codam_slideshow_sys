<?php
	require_once("include/auth.php");

	if (!isset($_GET["day"]) || empty($_GET["day"])) {
		header("Location: error.php?e=Day%20not%20set");
		die();
	}
	$day = $_GET["day"];
	$timestamp = strtotime($_GET["day"]);
	$dateFull = date("l, \\t\\h\\e jS \\o\\f F", $timestamp);
	$programmeName = $dateFull;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Programme for <?PHP echo $dateFull; ?></title>
	<link rel="stylesheet" href="styles.css" />
	<script src="js/useful.js"></script>
	<script src="js/programme.js"></script>
</head>
<body>
	<h1>Programme for <?PHP echo $dateFull; ?></h1>
	<!-- PHP script below looks ugly but gotta prevent whitespace between elements in the list -->
	<h3>Media to choose from</h3>
	<ul id="media-list">
		<?php foreach (glob("media/*.{jpg,jpeg,png,gif}", GLOB_BRACE) as $media) { ?><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="<?php echo $media; ?>" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li><?php } ?>
	</ul>
	<button onclick="openUploader()">Upload media</button>
	<h3>Media displayed on screen</h3>
	<ol id="selected-media" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">

	</ol>
	<template id="media-item-template"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="deleteMe(event)" title="Delete media (no undo)">&#x2715;</button></li></template>
	<template id="media-item-template-selected"><li class="media-item"><img draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" src="" /><button onclick="removeMe(event)" title="Remove from programme">&#x2715;</button><input type="number" class="duration" value="10" step="0.1" min="1" title="Duration in seconds" placeholder="Duration in seconds" /></li></template>
	<button onclick="saveProgramme(event)">Save</button>
</body>
</html>
