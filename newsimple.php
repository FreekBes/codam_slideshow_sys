<?php
	require_once("include/auth.php");
	$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Upload media and configure</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
	<link rel="stylesheet" href="css/styles.css" />
	<script src="js/simpleuploader.js"></script>
</head>
<body>
	<main style="background: inherit;">
		<form class="fullscreen-file" accept-charset="utf-8" autocomplete="off" name="uploadform">
			<label for="media[]"><span>drag and drop a media file here</span><br /><small>or just click to select a file...</small><br /><small class="extra">accepts PNG, JPG, MP4, WEBP, BMP</small></label><br />
			<input onchange="detectMedia(event)" type="file" name="media[]" id="media[]" accept="image/png, image/jpeg, image/webp, image/bmp, video/mp4" ondragenter="this.parentNode.className='fullscreen-file dragover';" ondragleave="this.parentNode.className='fullscreen-file';" />
		</form>
		<br />
		<form accept-charset="utf-8" autocomplete="off" name="configureform">
			<a href="javascript:showFileDropper()" class="ugly-back-btn"><span class="material-icons">arrow_back</span><span>choose a different file</span></a>
			<input type="hidden" name="media" id="media" value="" />
			<br />
			<label class="fancy-label" for="duration">Show <span id="media-type"></span> for how many seconds?</label><br />
			<input class="fancy" type="number" value="10" min="1" step="0.1" name="duration" id="duration" />
			<br /><br />
			<input type="checkbox" name="setup-dates" id="setup-dates" value="true" onchange="setupDatesChange(event)" /><label class="fancy-label" for="setup-dates">Only show between two dates...</label>
			<div id="timing" style="display: none;">
				<label class="fancy-label" for="start">Show from...</label><br />
				<input class="fancy" type="date" name="start" id="start" min="<?php echo $today; ?>" />
				<input class="fancy-btn" type="button" onclick="document.getElementById('start').value = '<?php echo $today; ?>'" value="Today">
				<br /><br />
				<label class="fancy-label" for="end">...until (and including)...</label><br />
				<input class="fancy" type="date" name="end" id="end" min="<?php echo $today; ?>" />
				<input class="fancy-btn" type="button" onclick="document.getElementById('end').value = '<?php echo $today; ?>'" value="Today">
			</div>
		</form>
		<button class="big-btn" id="upload-btn" onclick="uploadMedia(event)">Upload</button>
	</main>
	<div id="loading" style="display: none;"><span id="load-msg">Loading</span>... Please wait</div>
</body>
</html>
