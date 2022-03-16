function uploadDroppedMedia(files) {
	return new Promise(function(resolve, reject) {
		var allowedTypes = ["image/png", "image/jpeg", "image/webp", "image/bmp", "video/mp4"];
		var formData = new FormData();
		var filesToUpload = 0;

		for (var i = 0; i < files.length; i++) {
			if (allowedTypes.indexOf(files[i].type) > -1) {
				formData.append('media[]', files[i], files[i].name);
				filesToUpload++;
			}
			else {
				console.log("Unsupported file format " + files[i].type);
			}
		}

		if (filesToUpload == 0) {
			reject("Unsupported file or no files to upload found");
			return;
		}

		var uploadReq = new XMLHttpRequest();
		uploadReq.open("POST", "int/upload.php");
		uploadReq.addEventListener("loadend", function(fEv) {
			if (this.status == 201) {
				// upload complete and succesful!
				resolve(this.responseText.split("|"));
			}
			else {
				reject("Something went wrong with the file upload. Status code: " + this.status);
			}
		});
		uploadReq.addEventListener("error", function(err) {
			reject(err);
		});
		uploadReq.send(formData);
	});
}

async function dropUpload(ev) {
	document.getElementById("loading").style.display = "block";
	try {
		const mediaUrls = await uploadDroppedMedia(ev.dataTransfer.files);
		console.log(mediaUrls);
		addMedia(mediaUrls);
		for (var i = 0; i < mediaUrls.length; i++) {
			insertMediaItemAtPlaceholder(mediaUrls[i]);
		}
	}
	catch (err) {
		alert("An error occurred while uploading your file(s).\n\n" + err);
	}
	console.log("Removing placeholder now.");
	removePlaceholder(document.getElementById("drop-location"));
	document.getElementById("loading").style.display = "none";
 }