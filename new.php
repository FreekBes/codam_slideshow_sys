<?php require_once("include/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Upload media</title>
	<link rel="stylesheet" href="styles.css" />
	<script src="js/uploader.js"></script>
</head>
<body>
	<form action="int/upload.php?popup" method="post" target="_self" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off" name="uploadform">
		<input type="file" name="media[]" id="media[]" accept="image/png, image/jpeg, image/webp, image/bmp, video/mp4" multiple="multiple" />
		<input type="submit" value="Upload" />
	</form>
	<div id="loading" style="display: none;">Uploading... Please wait</div>
</body>
</html>
