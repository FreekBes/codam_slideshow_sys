var uploadedFile = null;

window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
};

function setupDatesChange(ev) {
	if (ev.currentTarget.checked) {
		document.getElementById("timing").style.display = "block";
		document.getElementById("start").required = true;
		document.getElementById("end").required = true;
	}
	else {
		document.getElementById("timing").style.display = "none";
		document.getElementById("start").required = false;
		document.getElementById("end").required = false;
	}
}

function uploadAndConfigure(ev) {
	document.getElementById("loading").style.display = "block";

	var uploadFormData = new FormData(document.getElementsByName("uploadform")[0]);

	var uReq = new XMLHttpRequest();
	uReq.open("POST", "int/upload.php");
	uReq.addEventListener("load", function(uEv) {
		if (this.status == 201) {
			document.getElementById("media").value = this.responseText.split("/").pop();
			var confFormData = new FormData(document.getElementsByName("configureform")[0]);

			var cReq = new XMLHttpRequest();
			cReq.open("POST", "int/configure.php");
			cReq.addEventListener("load", function(cEv) {
				if (this.status == 201) {
					alert("All done!");
					window.close();
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
	return new Promise(function(resolve, reject) {
		var video = document.createElement("video");
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
	if (ev.target.files[0].type == "video/mp4") {
		getVideoDuration(ev.target.files[0]).then(function(dur) {
			document.getElementById("duration").value = dur;
		});
	}
	else {
		document.getElementById("duration").value = 10;
	}
}