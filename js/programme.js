var uploader = null;

function openUploader() {
	if (uploader) {
		uploader.focus();
		return;
	}
	uploader = openPopUpWin("new.php", "uploadwin", 320, 320);
}

function addMedia(mediaUrl) {
	console.log("Media found to add!", mediaUrl);
	uploader.close();
	uploader = null;
}
