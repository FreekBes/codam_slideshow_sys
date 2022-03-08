<?php
	require_once("include/useful.php");
	require_once("include/settings.php");

	if (isset($_GET["day"]) && !empty($_GET["day"])) {
		if ($_GET["day"] != "today" && preg_match('/[^0-9\-]/', $_GET["day"])) {
			http_response_code(406);
			die();
		}

		$day_timestamp = strtotime($_GET["day"]);
		if ($day_timestamp === false) {
			http_response_code(400);
			die("day_parse_error");
		}
		$date_full = date("Y-m-d", $day_timestamp);
	}
	else {
		$date_full = date("Y-m-d");
	}

	if (!isset($_GET["num"]) || intval($_GET["num"]) < 0) {
		header("Location: show.php?day=".$_GET["day"]."&num=0");
		http_response_code(302);
		die();
	}
	
	// retrieve the day's programme and prepend default programme if enabled
	$day_programme = get_programme_overview($date_full, true);
	if ($day_programme["default_enabled"]) {
		$default_programme = get_programme_overview("default", true);
		$day_programme["media"] = combine_media_prepend($day_programme, $default_programme);
	}

	$num = intval($_GET["num"]);
	$total = count($day_programme["media"]);
	if ($total > 0 && $num >= $total) {
		header("Location: show.php?day=".$_GET["day"]."&num=0&looped=1");
		http_response_code(302);
		die();
	}
	if ($total != 0) {
		$current_media = $day_programme["media"][$num]["file"];
		$duration = $day_programme["media"][$num]["duration"];	
	}
	else {
		$current_media = "0_10_default.jpeg";
		$duration = 10;
	}
	$media_type = (strpos($current_media, ".mp4") === false ? "img" : "vid");
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo ($num + 1) . " / $total - " . ORGANIZATION_NAME; ?></title>
	<script src="js/useful.js"></script>
	<script src="js/nprogress.js"></script>
	<link rel="stylesheet" href="css/nprogress.css" />
	<link rel="stylesheet" href="css/show.css" />
</head>
<body onload="startCountdown();">
<script>
var num = <?php echo $num; ?>;
var total = <?php echo $total; ?>;
var duration = <?php echo $duration; ?>;
NProgress.configure({
	minimum: 0.0,
	easing: 'linear',
	speed: duration,
	showSpinner: false
});
</script>
<main id="container">
<?php switch ($media_type) { case "img": ?>
<img class="media img" src="media/<?php echo $current_media; ?>" alt="Could not load media <?php echo $num; ?> (image)" />
<?php break; case "vid": ?>
<video class="media vid" src="media/<?php echo $current_media; ?>" autoplay muted preload="auto" loop>Could not load media <?php echo $num; ?> (video)</video>
<?php break; default: ?>
Unknown media type
<?php break; } ?>
</main>
<script>
function startCountdown() {
	NProgress.start();
	NProgress.set(1);
	setTimeout(function() {
		NProgress.done(true);
		window.location.replace("?day="+getParameterByName("day")+"&num="+(num+1));
	}, duration);
	setTimeout(function() {
		document.getElementById("container").className = "hide";
	}, duration - 300);
	document.getElementById("container").className = "show";
}
</script>
</body>
</html>