<?php require_once("include/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Upload media</title>
	<link rel="stylesheet" href="css/styles.css" />
	<script src="js/uploader.js"></script>
</head>
<body>
	<form class="fullscreen-file" action="int/upload.php?popup" method="post" target="_self" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off" name="uploadform">
		<label for="media[]"><span>drag and drop media file(s) here</span><br /><small>or just click to select file(s)...</small><br /><small class="extra">accepts PNG, JPG, MP4, GIF, WEBP, BMP</small></label><br />
		<input type="file" name="media[]" id="media[]" accept="image/png, image/jpeg, image/webp, image/bmp, image/gif, video/mp4" multiple="multiple" ondragenter="this.parentNode.className='fullscreen-file dragover';" ondragleave="this.parentNode.className='fullscreen-file';" onchange="this.parentNode.submit()" />
	</form>
	<div id="loading" style="display: none;">Uploading... Please wait</div>
</body>
</html>
