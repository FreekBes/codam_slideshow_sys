<?php
	require_once("include/auth.php");
	require_once("include/settings.php");
	$sync_domain = "";
	$sync_enabled = file_exists("./programmes/default/.mirror");
	if ($sync_enabled) {
		$sync_domain = file_get_contents("./programmes/default/.mirror");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Announcements Dashboard for <?php echo ORGANIZATION_NAME; ?></title>
	<script src="js/useful.js"></script>
	<script src="js/getcalendar.js"></script>
	<script src="js/calendar.js"></script>
	<script src="js/tooltip.js"></script>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
	<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
	<header>
		<h1>Announcements Dashboard for <?php echo ORGANIZATION_NAME; ?></h1>
		<nav>
			<span class="header-switch">
				<label class="switch" title="Synchronize with another screen">
					<input type="checkbox" id="mirror" onchange="mirror(this, this.checked)" <?php echo ($sync_enabled ? "checked " : ""); ?>/>
					<span class="slider"></span>
				</label>
				<span class="switch-label" id="mirror-source"><?php echo ($sync_enabled ? "Syncing with $sync_domain" : ""); ?></span>
			</span>
			<a class="header-btn day" id="default-link" onmouseover="showProgTooltip(event)" onmouseout="hideProgTooltip(event)" href="programme.php?day=default" title="Edit default programme">edit</a>
			<a class="header-btn" href="newsimple.php" onclick="openSimpleUploader(event)" title="Upload media">add</a>
			<a class="header-btn" href="show.php?nosync&day=today&num=0" target="codamshow" title="Show today's programme">slideshow</a>
		</nav>
	</header>
	<main class="center">
		<?php if (disk_free_space(__DIR__) < 100000000) { ?>
		<p class="notice"><b>Notice:</b> disk is almost full (less than 100MB remaining). It is recommended to remove some (unused) media from the <i>available media</i> list in the programme editor, so that new media can still be uploaded in the future.</p>
		<?php } ?>
		<p>Click on a day to edit the programme for that day, or click on the <span class="material-icons" style="font-size: inherit;">edit</span><i>edit button</i> in the toolbar to edit the <u title="The default programme is a programme that contains media which is always displayed on the screen, no matter the date. It can be disabled on a day-to-day basis.">default programme</u></p>
		<div id="calendar"></div>
	</main>
	<div id="loading">Loading... Please wait</div>
</body>
</html>