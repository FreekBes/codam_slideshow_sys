var uploader = null;
var draggedElem = null;
var emptyImage = new Image();
emptyImage.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=";

function openUploader() {
	if (uploader && !uploader.closed) {
		uploader.focus();
		return;
	}
	uploader = openPopUpWin("new.php", "uploadwin", 320, 320);
}

function deleteMe(ev) {
	var conf = confirm("Are you sure you want to permanently delete this media?\n\nIn order to use it again in the future, it will need to be reuploaded.");
	if (conf) {
		var fileName = ev.currentTarget.previousElementSibling.src.split("/").pop();
		var selected = document.querySelectorAll("#selected-media img[src*=\"" + fileName + "\"]");

		var delReq = new XMLHttpRequest();
		delReq.open("GET", "int/delete.php?media=" + encodeURIComponent(fileName));
		delReq.send();

		// remove the media from the selected list, if found there
		for (var i = 0; i < selected.length; i++) {
			selected[i].parentNode.remove();
		}
	}
}

function removeMe(ev) {
	ev.currentTarget.parentNode.remove();
}

function createMediaItem(mediaUrl) {
	var template = document.getElementById("media-item-template");
	var clonedItem = template.content.cloneNode(true);
	clonedItem.firstElementChild.firstElementChild.src = mediaUrl;
	return (clonedItem);
}

function createSelectedMediaItem(mediaUrl) {
	var template = document.getElementById("media-item-template-selected");
	var clonedItem = template.content.cloneNode(true);
	clonedItem.firstElementChild.firstElementChild.src = mediaUrl;
	return (clonedItem);
}

function addMedia(mediaUrl) {
	console.log("Media found to add!", mediaUrl);
	uploader.close();
	uploader = null;
	
	if (typeof mediaUrl == "string") {
		document.getElementById("media-list").appendChild(createMediaItem(mediaUrl));
	}
	else if (typeof mediaUrl == "object") {
		for (var i = 0; i < mediaUrl.length; i++) {
			document.getElementById("media-list").appendChild(createMediaItem(mediaUrl[i]));
		}
	}
	else {
		console.warn("Invalid mediaUrl given to addMedia()", mediaUrl);
	}
}

function allowDrop(ev) {
	if (ev.dataTransfer.types.includes("text/uri-list")) {
		ev.preventDefault();

		var dropLocation = document.getElementById("drop-location");
		if (!dropLocation) {
			dropLocation = document.createElement("li");
			dropLocation.setAttribute("id", "drop-location");
		}

		if (ev.target == dropLocation) {
			return;
		}

		if (ev.target.nodeName == "OL") {
			ev.currentTarget.appendChild(dropLocation);
		}
		else if (ev.target.nodeName == "IMG") {
			ev.currentTarget.insertBefore(dropLocation, ev.target.parentNode);
		}
		else if (ev.target.nodeName == "LI") {
			ev.currentTarget.insertBefore(dropLocation, ev.target);
		}
		dropLocation.parentNode.style.background = "lightblue";
	}
	else {
		return (false);
	}
}

function drag(ev) {
	draggedElem = ev.currentTarget.parentNode;

	ev.dataTransfer.setData("text/uri-list", ev.currentTarget.src);
	if (draggedElem.parentNode.getAttribute("id") == "media-list") {
		ev.dataTransfer.effectAllowed = "copy";
	}
	else {
		ev.dataTransfer.effectAllowed = "move";
		draggedElem.style.display = "none";
	}

	var ctx = document.createElement("canvas").getContext("2d");
	ctx.canvas.width = 128;
	ctx.canvas.height = 72;
	ctx.drawImage(ev.currentTarget, 0, 0, 128, 72);
	ev.dataTransfer.setDragImage(ctx.canvas, 10, 10);
}

function drop(ev) {
	ev.preventDefault();

	var mediaUrl = ev.dataTransfer.getData("text/uri-list");
	if (ev.dataTransfer.effectAllowed == "move") {
		draggedElem.remove();
	}

	var dropLocation = document.getElementById("drop-location");
	if (dropLocation) {
		var sMediaItem = createSelectedMediaItem(mediaUrl);
		if (mediaUrl.endsWith(".gif")) {
			var duration = parseInt(mediaUrl.substring(0, mediaUrl.lastIndexOf(".")).split("-").pop());
			if (!isNaN(duration) && duration > 0) {
				sMediaItem.querySelector(".duration").value = duration / 1000;
			}
		}
		dropLocation.parentNode.insertBefore(sMediaItem, dropLocation);
		dropLocation.parentNode.style.background = null;
		dropLocation.remove();
	}
}

function dragLeave(ev) {
	var bounds = document.getElementById("selected-media").getBoundingClientRect();
	if (ev.clientY < bounds.top || ev.clientY >= bounds.bottom || ev.clientX < bounds.left || ev.clientX >= bounds.right) {
		// mouse is now actually outside of drop boundaries, remove drop location
		var dropLocation = document.getElementById("drop-location");
		if (dropLocation) {
			dropLocation.parentNode.style.background = null;
			dropLocation.remove();
		}
	}
}

function dragEnd(ev) {
	var dropLocation = document.getElementById("drop-location");
	if (ev.dataTransfer.effectAllowed == "move" && !dropLocation) {
		draggedElem.remove();
	}
	else {
		draggedElem.style.display = null;
	}
	
	draggedElem = null;
	if (dropLocation) {
		dropLocation.parentNode.style.background = null;
		dropLocation.remove();
	}
}

function saveProgramme(ev) {
	document.getElementById("loading").style.display = "block";

	var formData = new FormData();
	var selectedMediaElems = document.getElementById("selected-media").children;
	var selectedMediaFiles = [];
	var durations = [];
	for (var i = 0; i < selectedMediaElems.length; i++) {
		selectedMediaFiles.push(selectedMediaElems[i].firstElementChild.src.split("/").pop());
		durations.push(parseFloat(selectedMediaElems[i].querySelector(".duration").value));
	}
	var defaultCheckbox = document.getElementById("default_enabled");
	
	formData.set("day", getParameterByName("day"));
	formData.set("media", selectedMediaFiles.join("|"));
	formData.set("durations", durations.join("|"));
	if (defaultCheckbox) {
		formData.set("default_enabled", defaultCheckbox.checked.toString());
	}
	else {
		formData.set("default_enabled", "false");
	}
	console.log(formData.get("day"));
	console.log(formData.get("media"));
	console.log(formData.get("durations"));
	console.log(formData.get("default_enabled"));

	var saveReq = new XMLHttpRequest();
	saveReq.open("POST", "int/save.php");
	saveReq.addEventListener("load", function(evSave) {
		document.getElementById("loading").style.display = "none";
		if (this.status == 204) {
			var conf = confirm("Programme has been saved. Go back to the calendar overview?");
			if (conf) {
				window.location.href = "calendar.php";
			}
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

window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
};