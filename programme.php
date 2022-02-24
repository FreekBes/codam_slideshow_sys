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
	<ul id="media-list">
		<?php foreach (glob("media/*.{jpg,jpeg,png,gif,mp4,flv,avi,mov,wmv,mkv,mpeg}", GLOB_BRACE) as $media) { ?>
			<li class="media-item"><img src="<?php echo $media; ?>" /></li>
		<?php } ?>
	</ul>
	<button onclick="openUploader()">Upload media</button>
	<ol id="selected-media">

	</ol>
	<button>Save</button>
</body>
</html>
