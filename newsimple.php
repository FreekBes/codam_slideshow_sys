<?php
	require_once("include/auth.php");
	$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Upload media and configure</title>
	<link rel="stylesheet" href="css/styles.css" />
	<script src="js/simpleuploader.js"></script>
</head>
<body>
	<form accept-charset="utf-8" autocomplete="off" name="uploadform">
		<label for="media[]">Choose a file to upload:</label><br />
		<input onchange="detectMedia(event)" type="file" name="media[]" id="media[]" accept="image/png, image/jpeg, image/webp, image/bmp, video/mp4" />
	</form>
	<br />
	<form accept-charset="utf-8" autocomplete="off" name="configureform">
		<input type="hidden" name="media" id="media" value="" />
		<br />
		<label for="duration">For how many seconds should this media be shown?</label><br />
		<input type="number" value="10" name="duration" id="duration" />
		<br /><br />
		<input type="checkbox" name="setup-dates" id="setup-dates" value="true" onchange="setupDatesChange(event)" /><label for="setup-dates">Only show between two dates...</label>
		<div id="timing" style="display: none;">
			<label for="start">Show from...</label><br />
			<input type="date" name="start" id="start" min="<?php echo $today; ?>" />
			<input type="button" onclick="document.getElementById('start').value = '<?php echo $today; ?>'" value="Today">
			<br /><br />
			<label for="end">...until (and including)...</label><br />
			<input type="date" name="end" id="end" min="<?php echo $today; ?>" />
			<input type="button" onclick="document.getElementById('end').value = '<?php echo $today; ?>'" value="Today">
		</div>
	</form>
	<button id="upload-btn" onclick="uploadMedia(event)">Upload</button>
	<div id="loading" style="display: none;">Uploading... Please wait</div>
</body>
</html>
