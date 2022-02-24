<?php http_response_code(400); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Error</title>
</head>
<body>
	<h1>Something went wrong</h1>
	<p><?php echo (isset($_GET["e"]) ? $_GET["e"] : "An unknown error occurred"); ?></p>
	<?php if (isset($_GET["win"])) { ?>
	<p><a href="javascript:window.close()">Close this window</a></p>
	<?php } else { ?>
	<p><a href="index.php">Go back to the dashboard</a></p>
	<?php } ?>
</body>
</html>
