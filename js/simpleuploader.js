let uploadedFile = null;

window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
	document.getElementById("load-msg").innerText = "Loading";
};

function setupDatesChange(ev) {
	if (ev.currentTarget.checked) {
		// user wants to specify a date range where the media should be displayed
		document.getElementById("timing").style.display = "block";
		document.getElementById("start").required = true;
		document.getElementById("end").required = true;
	}
	else {
		// user does not want to specify a date range
		// add it to the default programme instead
		document.getElementById("timing").style.display = "none";
		document.getElementById("start").required = false;
		document.getElementById("end").required = false;
	}
}

function configureMedia(response) {
	document.getElementById("load-msg").innerText = "Configuring";

	// retrieve the media's internal file name from the response body
	// and add it to the configuration form
	document.getElementById("media").value = response.split("/").pop();

	// gather the configuration form's data
	const confFormData = new FormData(document.getElementsByName("configureform")[0]);

	// send a request to the server to configure the media
	const cReq = new XMLHttpRequest();
	cReq.open("POST", "int/configure.php");
	cReq.addEventListener("load", function(cEv) {
		if (this.status == 201) {
			// all done! we can close the window if possible.
			// if not possible, reload the window so that the user can upload more.
			alert("All done!");
			window.close();
			if (!window.closed) {
				window.location.reload();
			}
		}
		else {
			alert("Something went wrong configuring the media. Status code: " + this.status);
			console.log(this);
		}
	});
	cReq.addEventListener("error", function(err) {
		alert("An error occurred while configuring the file");
		console.error(err);
	});
	cReq.send(confFormData);
}

function uploadMedia(ev) {
	document.getElementById("load-msg").innerText = "Uploading";
	document.getElementById("loading").style.display = "block";

	// gather the file to upload
	const uploadFormData = new FormData(document.getElementsByName("uploadform")[0]);

	// upload the file
	const uReq = new XMLHttpRequest();
	uReq.open("POST", "int/upload.php");
	uReq.addEventListener("load", function(uEv) {
		if (this.status == 201) {
			// upload complete and succesful, now configure the media to display
			configureMedia(this.responseText);
		}
		else {
			alert("Something went wrong with the file upload. Status code: " + this.status);
			console.error(this);
		}
	});
	uReq.addEventListener("error", function(err) {
		alert("An error occurred while uploading file");
		console.error(err);
	});
	uReq.send(uploadFormData);
}

function getVideoDuration(file) {
	// get the video duration from a local file by loading it into a temporary video element
	return new Promise(function(resolve, reject) {
		const video = document.createElement("video");
		video.preload = "metadata";
		video.onloadedmetadata = function(vEv) {
			URL.revokeObjectURL(vEv.target.src);
			resolve(vEv.target.duration);
		};
		video.onerror = function(vEv) {
			reject();
		};
		video.onabort = function(vEv) {
			reject();
		};
		video.src = URL.createObjectURL(file);
	});
}

function detectMedia(ev) {
	// new media to upload found!
	// if it's a video, we need to calculate the duration and specify that in the configuration form.
	// if not, set the duration to the default 10 seconds.
	if (ev.target.files[0].type == "video/mp4") {
		getVideoDuration(ev.target.files[0]).then(function(dur) {
			document.getElementById("duration").value = dur;
		});
		document.getElementById("media-type").innerText = "video";
	}
	else {
		document.getElementById("duration").value = 10;
		document.getElementById("media-type").innerText = "image";
	}
	document.getElementsByName("uploadform")[0].style.display = "none";
}

function showFileDropper() {
	const uploadForm = document.getElementsByName("uploadform")[0];
	document.getElementById("media[]").value = null;
	uploadForm.className = "fullscreen-file";
	uploadForm.style.display = "block";
}