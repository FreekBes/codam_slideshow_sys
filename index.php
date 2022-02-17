<!DOCTYPE html>
<html lang="en">
<head>
	<title>Kodi Monitor Upload Test</title>
</head>
<body>
	<form action="int/upload.php" method="post" target="_self" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off" name="uploadform">
		<h1>Kodi Monitor Upload Test</h1>
		<p>Monitor on the left side</p>
		<input type="file" name="image" id="image" accept="image/*" />
		<input type="file" name="video" id="video" accept="video/*" />
		<input type="submit" value="Upload" />
	</form>
</body>
</html>
