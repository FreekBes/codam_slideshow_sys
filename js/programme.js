var uploader = null;
var unsavedChanges = false;

function openUploader() {
	if (uploader && !uploader.closed) {
		uploader.focus();
		return;
	}
	uploader = openPopUpWin("new.php", "uploadwin", 320, 320);
}

// delete media using this function, run on remove button click
function deleteMe(ev) {
	var conf = confirm("Are you sure you want to permanently delete this media?\n\nIn order to use it again in the future, it will need to be reuploaded.");
	if (conf) {
		var fileName = ev.currentTarget.previousElementSibling.src.split("/").pop();
		var selected = document.querySelectorAll("#selected-media img[src*=\"" + fileName + "\"]");

		// delete the media from the server by sending a request to the delete page
		var delReq = new XMLHttpRequest();
		delReq.open("GET", "int/delete.php?media=" + encodeURIComponent(fileName));
		delReq.send();

		// remove the media from the current webpage
		ev.currentTarget.parentNode.remove();

		// remove the media from the selected list, if found there
		for (var i = 0; i < selected.length; i++) {
			selected[i].parentNode.remove();
		}
	}
}

function removeMe(ev) {
	ev.currentTarget.parentNode.remove();
}

// create a media item for the available media list
function createMediaItem(mediaUrl) {
	var template = document.getElementById("media-item-template");
	var clonedItem = template.content.cloneNode(true);
	clonedItem.firstElementChild.firstElementChild.src = mediaUrl;
	return (clonedItem);
}

// create a media item for the selected media list
function createSelectedMediaItem(mediaUrl) {
	var template = document.getElementById("media-item-template-selected");
	var clonedItem = template.content.cloneNode(true);
	clonedItem.firstElementChild.firstElementChild.src = mediaUrl;
	return (clonedItem);
}

// function to call from a upload window
// never called from this webpage itself
function addMedia(mediaUrl) {
	console.log("Media found to add!", mediaUrl);
	uploader.close();
	uploader = null;
	
	if (typeof mediaUrl == "string") {
		document.getElementById("media-list").prepend(createMediaItem(mediaUrl));
	}
	else if (typeof mediaUrl == "object") {
		for (var i = 0; i < mediaUrl.length; i++) {
			document.getElementById("media-list").prepend(createMediaItem(mediaUrl[i]));
		}
	}
	else {
		console.warn("Invalid mediaUrl given to addMedia()", mediaUrl);
	}
}

function getProgrammeFormData() {
	// create a new form to submit
	var formData = new FormData();
	var selectedMediaElems = document.getElementById("selected-media").children;
	var selectedMediaFiles = [];
	var durations = [];
	
	// gather all the media URLs (without the media/ prefix)
	// and the durations of each piece of media
	for (var i = 0; i < selectedMediaElems.length; i++) {
		selectedMediaFiles.push(selectedMediaElems[i].firstElementChild.src.split("/").pop());
		durations.push(parseFloat(selectedMediaElems[i].querySelector(".duration").value));
	}
	var defaultCheckbox = document.getElementById("default_enabled");
	
	// add configurations to the form data
	formData.set("day", getParameterByName("day"));
	formData.set("media", selectedMediaFiles.join("|"));
	formData.set("durations", durations.join("|"));
	if (defaultCheckbox) {
		formData.set("default_enabled", defaultCheckbox.checked.toString());
	}
	else {
		formData.set("default_enabled", "false");
	}

	// debugging
	console.log(formData.get("day"));
	console.log(formData.get("media"));
	console.log(formData.get("durations"));
	console.log(formData.get("default_enabled"));

	return (formData);
}

function saveProgramme(ev) {
	document.getElementById("loading").style.display = "block";

	// retrieve the programme configurations in a formdata object
	var formData = getProgrammeFormData();

	// send the form to the server
	var saveReq = new XMLHttpRequest();
	saveReq.open("POST", "int/save.php");
	saveReq.addEventListener("load", function(evSave) {
		document.getElementById("loading").style.display = "none";
		if (this.status == 204) {
			unsavedChanges = false;
			alert("Programme saved");
		}
		else {
			alert("Failed to save programme ("+this.status+" "+this.statusText+": "+this.responseText+")");
		}
	});
	saveReq.addEventListener("error", function(evSave) {
		document.getElementById("loading").style.display = "none";
		alert("Failed to save programme (unknown error)");
	});
	saveReq.send(formData);
}

// this function adds horizontal scrolling to the selected media list
function horiScroll(ev) {
	ev.preventDefault();
	ev.currentTarget.scrollBy({ left: ev.deltaY < 0 ? -40 : 40 });
}

window.addEventListener("DOMContentLoaded", function(ev) {
	// document.getElementById("media-list").addEventListener("wheel", horiScroll);
	document.getElementById("selected-media").addEventListener("wheel", horiScroll);
	document.getElementById("loading").style.display = "none";
}, false);

window.onbeforeunload = function(ev) {
	if (unsavedChanges) {
		var confirmMessage = "You have unsaved changes for this programme. If you leave before saving, these changes will be lost.";
		ev.returnValue = confirmMessage;
		return (confirmMessage);
	}
	document.getElementById("loading").style.display = "block";
	if (uploader) {
		uploader.close();
	}
};