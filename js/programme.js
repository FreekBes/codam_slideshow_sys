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

// function to run when dragging something over an element (determines if a drop is allowed)
function allowDrop(ev) {
	// only allow droppable entities that include the text/uri-list parameter
	if (ev.dataTransfer.types.includes("text/uri-list")) {
		// allow dropping by running preventDefault (what???)
		ev.preventDefault();

		// create a placeholder at the drop location
		// so that the user can see where the media will be placed in a list
		var dropLocation = document.getElementById("drop-location");
		if (!dropLocation) {
			dropLocation = document.createElement("li");
			dropLocation.setAttribute("id", "drop-location");
		}

		if (ev.target == dropLocation) {
			return;
		}

		// append the placeholder, based on which parent element we have
		// OL means we need to add the placeholder to the last position in a list (appendChild to parent)
		// IMG means we need to add the placeholder in front of the hovered over media (an image, IMG element)
		// LI means the same as IMG but rarely ever happens, since the IMG will usually catch the event
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

// function to run when starting a drag & drop scenario
function drag(ev) {
	draggedElem = ev.currentTarget.parentNode;

	// add text/uri-list data, set it to the media source
	ev.dataTransfer.setData("text/uri-list", ev.currentTarget.src);

	// if dragged from the media-list (available media), we need to clone the element
	// else, assume it's dragged from the selected media list, we need to (re)move the element
	// (and not show the media that the user wants to drag for a while, by setting display to none)
	if (draggedElem.parentNode.getAttribute("id") == "media-list") {
		ev.dataTransfer.effectAllowed = "copy";
	}
	else {
		ev.dataTransfer.effectAllowed = "move";
		draggedElem.style.display = "none";
	}

	// create an image to show underneath the cursor while dragging
	// it is a clone of the image that's being dragged
	// but since that image is hidden now, we need to clone it (otherwise nothing is shown being dragged)
	var ctx = document.createElement("canvas").getContext("2d");
	ctx.canvas.width = 128;
	ctx.canvas.height = 72;
	ctx.drawImage(ev.currentTarget, 0, 0, 128, 72);
	ev.dataTransfer.setDragImage(ctx.canvas, 10, 10);
}

// function to run when user decided to drop something onto an element
function drop(ev) {
	// allow the drop!
	ev.preventDefault();

	var mediaUrl = ev.dataTransfer.getData("text/uri-list");
	if (ev.dataTransfer.effectAllowed == "move") {
		// original element had to be moved, simply remove it as we're cloning it next anyways.
		draggedElem.remove();
	}

	// get the drop location based on the placeholder!
	// we actually simply replace the placeholder with a new media item element.
	// simple, but effective.
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
		// mouse is now actually outside of drop boundaries, remove drop location (drop placeholder)
		var dropLocation = document.getElementById("drop-location");
		if (dropLocation) {
			dropLocation.parentNode.style.background = null;
			dropLocation.remove();
		}
	}
}

// drag & drop was ended, runs after drop(ev)...
// but if not dropped on a suitable location, this function will have to handle that
function dragEnd(ev) {
	var dropLocation = document.getElementById("drop-location");
	// if no placeholder drop location was found, and the expected behavior was to move the element dragged
	// we remove the element that was being dragged, as there was no suitable location found to drop it to
	// if the expected behavior was not to move, display the original media again
	if (ev.dataTransfer.effectAllowed == "move" && !dropLocation) {
		draggedElem.remove();
	}
	else {
		draggedElem.style.display = null;
	}
	
	// remove the drop location placeholder if one is found
	draggedElem = null;
	if (dropLocation) {
		dropLocation.parentNode.style.background = null;
		dropLocation.remove();
	}
}

function saveProgramme(ev) {
	document.getElementById("loading").style.display = "block";

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

	// send the form to the server
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
	if (uploader) {
		uploader.close();
	}
};