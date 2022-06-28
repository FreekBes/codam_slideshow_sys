<?php
	require_once("include/settings.php");

	// check if source key is in GET
	if (!isset($_GET["source"]) || empty($_GET["source"])) {
		http_response_code(400);
		die("missing_key_source");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $_GET["source"] . " mirror - " . ORGANIZATION_NAME; ?></title>
	<script src="js/useful.js"></script>
	<script src="js/progress.js"></script>
	<script src="js/sync.js"></script>
	<link rel="stylesheet" href="css/show.css" />
</head>
<body onload="startSync('<?php echo $_GET['source']; ?>');">
	<main id="container">
		<img class="media img" id="img" src="<?php echo $current_media; ?>" alt="Could not load media <?php echo $num; ?> (image)" style="display: none;" />
		<video class="media vid" id="vid" src="<?php echo $current_media; ?>" autoplay muted preload="auto" loop style="display: none;">Could not load media <?php echo $num; ?> (video)</video>
		<iframe class="media iframe" id="iframe" src="<?php echo $current_media; ?>" sandbox="allow-scripts">Could not load <?php echo $num; ?> (iframe)</iframe>
	</main>
</body>
