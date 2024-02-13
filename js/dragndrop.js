let draggedElem = null;

// function to run when dragging something over an element (determines if a drop is allowed)
function allowDrop(ev) {
	// only allow droppable entities that include the text/uri-list parameter, or dragged files
	// (on the selected media list)
	if (ev.currentTarget.id == "selected-media" && (ev.dataTransfer.types.includes("text/uri-list") || ev.dataTransfer.types.includes("Files"))) {
		// allow dropping by running preventDefault (what???)
		ev.preventDefault();

		// create a placeholder at the drop location
		// so that the user can see where the media will be placed in a list
		let dropLocation = document.getElementById("drop-location");
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
		else if (ev.target.nodeName == "IMG" || ev.target.nodeName == "BUTTON" || ev.target.nodeName == "INPUT") {
			ev.currentTarget.insertBefore(dropLocation, ev.target.parentNode);
		}
		else if (ev.target.nodeName == "DIV" && ev.target.className == "iframe-wrapper") {
			ev.currentTarget.insertBefore(dropLocation, ev.target.parentNode);
		}
		else if (ev.target.nodeName == "LI") {
			ev.currentTarget.insertBefore(dropLocation, ev.target);
		}
		dropLocation.parentNode.style.background = "lightblue";
	}
	else if (ev.currentTarget.id == "media-list" && ev.dataTransfer.types.includes("Files")) {
		// allow dropping by running preventDefault (what???)
		ev.preventDefault();
	}
	else {
		return (false);
	}
}

// function to run when starting a drag & drop scenario
function drag(ev) {
	console.log("Drag started");
	draggedElem = ev.currentTarget.parentNode;

	// add text/uri-list data, set it to the media source
	ev.dataTransfer.setData("text/uri-list", ev.currentTarget.src);

	// disable scrolling in available media list
	document.getElementById("media-list").style.overflowY = "hidden";

	// if dragged from the media-list (available media), we need to clone the element
	// else, assume it's dragged from the selected media list, we need to (re)move the element
	// (and hide the element that is being dragged for a while, by adding the dragging class)
	if (draggedElem.parentNode.getAttribute("id") == "media-list") {
		ev.dataTransfer.effectAllowed = "copy";
	}
	else {
		ev.dataTransfer.effectAllowed = "move";
		draggedElem.className += " dragging";
	}

	// create an image to show underneath the cursor while dragging
	// it is a clone of the image that's being dragged
	// but since that image is hidden now, we need to clone it (otherwise nothing is shown being dragged)
	const ctx = document.createElement("canvas").getContext("2d");
	ctx.canvas.width = 128;
	ctx.canvas.height = 72;
	ctx.drawImage(ev.currentTarget, 0, 0, 128, 72);
	ev.dataTransfer.setDragImage(ctx.canvas, 10, 10);
}

function removePlaceholder(dropLocation) {
	if (!dropLocation) {
		return;
	}
	dropLocation.parentNode.style.background = null;
	dropLocation.remove();
}

// get the drop location based on the placeholder!
// insert the new media before it.
// to remove the placeholder after, call removePlaceholder() manually
// this function returns the placeholder element to easily do this.
function insertMediaItemAtPlaceholder(mediaUrl) {
	const dropLocation = document.getElementById("drop-location");
	if (dropLocation) {
		let sMediaItem = createSelectedMediaItem(mediaUrl);
		if (mediaUrl.endsWith(".gif")) {
			const duration = parseInt(mediaUrl.substring(0, mediaUrl.lastIndexOf(".")).split("-").pop());
			if (!isNaN(duration) && duration > 0) {
				sMediaItem.querySelector(".duration").value = duration / 1000;
			}
		}
		dropLocation.parentNode.insertBefore(sMediaItem, dropLocation);
		unsavedChanges = true;
	}
	return (dropLocation);
}

// function to run when user decided to drop something onto an element
function drop(ev) {
	console.log("Dropped!");
	if (ev.dataTransfer.types.includes("text/uri-list")) {
		// allow the drop!
		ev.preventDefault();
		const mediaUrl = ev.dataTransfer.getData("text/uri-list");
		if (ev.dataTransfer.effectAllowed == "move") {
			// original element had to be moved, simply remove it as we're cloning it next anyways.
			draggedElem.remove();
		}
		const placeholder = insertMediaItemAtPlaceholder(mediaUrl);
		removePlaceholder(placeholder);
	}
	else if (ev.dataTransfer.types.includes("Files")) {
		// allow the drop!
		ev.preventDefault();
		dropUpload(ev);
	}
	else {
		console.warn("Unknown dataTransfer type for drop");
	}
}

function dragLeave(ev) {
	const bounds = document.getElementById("selected-media").getBoundingClientRect();
	if (ev.clientY < bounds.top || ev.clientY >= bounds.bottom ||
		ev.clientX < bounds.left || ev.clientX >= bounds.right) {
		console.log("Left boundaries, removing placeholder");
		// mouse is now actually outside of drop boundaries, remove drop location (drop placeholder)
		removePlaceholder(document.getElementById("drop-location"));
	}
}

// drag & drop was ended, runs after drop(ev)...
// but if not dropped on a suitable location, this function will have to handle that
function dragEnd(ev) {
	console.log("Drag ended");
	const dropLocation = document.getElementById("drop-location");
	// if no placeholder drop location was found, and the expected behavior was to move the element dragged
	// we remove the element that was being dragged, as there was no suitable location found to drop it to
	// if the expected behavior was not to move, display the original media again
	if (ev.dataTransfer.effectAllowed == "move" && !dropLocation) {
		draggedElem.remove();
	}
	else {
		draggedElem.className = "media-item";
	}

	// remove the drop location placeholder if one is found
	draggedElem = null;
	removePlaceholder(dropLocation);

	// re-enable overflow (scrolling) in available media list
	document.getElementById("media-list").style.overflowY = "auto";
}
