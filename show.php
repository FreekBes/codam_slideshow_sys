<?php
	require_once("include/useful.php");
	require_once("include/settings.php");
	require_once("include/shm.php");

	// get the internal code for the day's programme (simply YYYY-MM-DD)
	if (isset($_GET["day"]) && !empty($_GET["day"])) {
		if ($_GET["day"] != "today" && $_GET["day"] != "default" && preg_match('/[^0-9\-]/', $_GET["day"])) {
			http_response_code(406);
			die();
		}

		if ($_GET["day"] != "default") {
			$day_timestamp = strtotime($_GET["day"]);
			if ($day_timestamp === false) {
				http_response_code(400);
				die("day_parse_error");
			}
			$date_full = date("Y-m-d", $day_timestamp);
		}
		else {
			$date_full = "default";
		}
	}
	else {
		$date_full = date("Y-m-d");
	}

	// if the num GET parameter was not set or invalid (below 0), redirect to 0 (the first piece of media)
	if (!isset($_GET["num"]) || intval($_GET["num"]) < 0) {
		header("Location: show.php?".(isset($_GET["nosync"]) ? "nosync&" : "")."day=".$_GET["day"]."&num=0");
		http_response_code(302);
		die();
	}

	// retrieve the day's programme and prepend default programme if enabled
	// also set the mirror/syncing source if it is enabled in the default programme
	$day_programme = get_programme_overview($date_full, true);
	if ($day_programme["default_enabled"]) {
		$default_programme = get_programme_overview("default", true);
		$day_programme["media"] = combine_media_prepend($day_programme, $default_programme);
		if ($default_programme["mirror"] !== false) {
			$day_programme["mirror"] = $default_programme["mirror"];
		}
	}

	// if mirroring (synchronization) is enabled, redirect now
	if ($day_programme["mirror"] !== false) {
		header("Location: syncshow.php?source=" . $day_programme["mirror"]);
		http_response_code(302);
		die();
	}

	// num is the index of which media to display
	$num = intval($_GET["num"]);
	$total = count($day_programme["media"]);

	// check if the index is bigger than the total amount of media available for the day.
	// if so, loop back to the beginning by redirection.
	if ($total > 0 && $num >= $total) {
		header("Location: show.php?".(isset($_GET["nosync"]) ? "nosync&" : "")."day=".$_GET["day"]."&num=0&looped=1");
		http_response_code(302);
		die();
	}

	// if media to display, get the media requested by the index ($num)
	if ($total != 0) {
		$current_media = "media/" . $day_programme["media"][$num]["file"];
		$duration = $day_programme["media"][$num]["duration"];
	}
	else {
		// if no media to display (day's programme is empty), display the default image
		$current_media = "0_10_default.jpeg";
		$duration = 10000;
	}
	$media_type = (strpos($current_media, ".mp4") === false ? "img" : "vid");

	if (!isset($_GET["nosync"])) {
		// store currently loaded media and the current time in shared memory
		// for synchronizing with other screens
		$sync = new stdClass();
		$sync->num = $num;
		$sync->load_time = microtime(true) * 1000;
		$sync->media_type = $media_type;
		$sync->current_media = $current_media;
		$sync->show_until = microtime(true) * 1000 + $duration;
		shm_put_var($shm, 0x01, serialize(json_decode(json_encode($sync, true))));
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo ($num + 1) . " / $total - " . ORGANIZATION_NAME; ?></title>
	<script src="js/useful.js"></script>
	<script src="js/progress.js"></script>
	<link rel="stylesheet" href="css/show.css" />
</head>
<body onload="startCountdown();">
<script>
var num = <?php echo $num; ?>;
var total = <?php echo $total; ?>;
var duration = <?php echo $duration; ?>;
</script>
<main id="container">
<?php switch ($media_type) { case "img": ?>
<img class="media img" src="<?php echo $current_media; ?>" alt="Could not load media <?php echo $num; ?> (image)" />
<?php break; case "vid": ?>
<video class="media vid" src="<?php echo $current_media; ?>" autoplay muted preload="auto" loop>Could not load media <?php echo $num; ?> (video)</video>
<?php break; default: ?>
Unknown media type
<?php break; } ?>
</main>
<script>
const progressBar = new CodamProgressBar();
function startCountdown() {
	setTimeout(function() {
		window.location.replace("?day="+getParameterByName("day")+"&num="+(total > 1 ? num+1 : 0));
	}, duration);
	setTimeout(function() {
		if (total > 1) {
			progressBar.stop();
			document.getElementById("container").className = "hide-fade";
		}
	}, duration - 100);
	// assume it takes 200ms to load the next slide on a Raspberry Pi Model 3B
	if (total > 1) {
		document.getElementById("container").className = "show-fade";
		progressBar.start(duration);
	}
	else {
		document.getElementById("container").className = "show";
	}
}
</script>
</body>
</html>