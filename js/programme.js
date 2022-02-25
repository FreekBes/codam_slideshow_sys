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

function createMediaItem(mediaUrl) {
	var template = document.getElementById("media-item-template");
	var clonedItem = template.content.cloneNode(true);
	clonedItem.firstElementChild.firstElementChild.src = mediaUrl;
	return (clonedItem);
}

function addMedia(mediaUrl) {
	console.log("Media found to add!", mediaUrl);
	uploader.close();
	uploader = null;

	document.getElementById("media-list").appendChild(createMediaItem(mediaUrl));
}

function allowDrop(event) {
	if (event.dataTransfer.types.includes("text/uri-list")) {
		event.preventDefault();

		var dropLocation = document.getElementById("drop-location");
		if (!dropLocation) {
			dropLocation = document.createElement("li");
			dropLocation.setAttribute("id", "drop-location");
		}

		if (event.target == dropLocation) {
			return;
		}

		if (event.target.nodeName == "OL") {
			event.currentTarget.appendChild(dropLocation);
		}
		else if (event.target.nodeName == "IMG") {
			event.currentTarget.insertBefore(dropLocation, event.target.parentNode);
		}
		else if (event.target.nodeName == "LI") {
			event.currentTarget.insertBefore(dropLocation, event.target);
		}
		dropLocation.parentNode.style.background = "lightblue";
	}
	else {
		return (false);
	}
}

function drag(event) {
	draggedElem = event.currentTarget.parentNode;

	event.dataTransfer.setData("text/uri-list", event.currentTarget.src);
	if (draggedElem.parentNode.getAttribute("id") == "media-list") {
		event.dataTransfer.effectAllowed = "copy";
	}
	else {
		event.dataTransfer.effectAllowed = "move";
		draggedElem.style.display = "none";
	}

	var ctx = document.createElement("canvas").getContext("2d");
	ctx.canvas.width = 128;
	ctx.canvas.height = 72;
	ctx.drawImage(event.currentTarget, 0, 0, 128, 72);
	event.dataTransfer.setDragImage(ctx.canvas, 10, 10);
}

function drop(event) {
	event.preventDefault();

	var mediaUrl = event.dataTransfer.getData("text/uri-list");
	if (event.dataTransfer.effectAllowed == "move") {
		draggedElem.remove();
	}

	var dropLocation = document.getElementById("drop-location");
	if (dropLocation) {
		dropLocation.parentNode.insertBefore(createMediaItem(mediaUrl), dropLocation);
		dropLocation.parentNode.style.background = null;
		dropLocation.remove();
	}
}

function dragLeave(event) {
	var bounds = document.getElementById("selected-media").getBoundingClientRect();
	if (event.clientY < bounds.top || event.clientY >= bounds.bottom || event.clientX < bounds.left || event.clientX >= bounds.right) {
		// mouse is now actually outside of drop boundaries, remove drop location
		var dropLocation = document.getElementById("drop-location");
		if (dropLocation) {
			dropLocation.parentNode.style.background = null;
			dropLocation.remove();
		}
	}
}

function dragEnd(event) {
	var dropLocation = document.getElementById("drop-location");
	if (event.dataTransfer.effectAllowed == "move" && !dropLocation) {
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