const today = new Date();
const cacheId = Math.random();
let requestsTodo = 1;
let requestsDone = 0;
let checkReqDoneInterval = null;
let simpleUploader = null;
let defaultMedia = [];
let copyFromDay = null;

function getDefaultProgramme() {
	const req = new XMLHttpRequest();
	req.open("GET", "int/get.php?day=default&c" + cacheId, true);
	req.addEventListener("loadend", function (fEv) {
		requestsDone++;
		if (this.status == 200) {
			const defaultLink = document.getElementById("default-link");
			const overview = JSON.parse(this.responseText);

			defaultLink.setAttribute("data-media", JSON.stringify(overview["media"]));
			defaultMedia = overview["media"];
		}
	});
	req.addEventListener("error", function(err) {
		requestsDone++;
		console.error(err);
	});
	req.send();
}

function loadMonthsProgramme(req) {
	if (req.status == 200) {
		// get the month and year from the responseURL of the request
		const month = getParameterByName("month", req.responseURL);
		const year = getParameterByName("year", req.responseURL);
		const monthCal = document.querySelector(".month-"+year+"-"+month);
		if (!monthCal) {
			console.warn("Could not found month element!");
			return;
		}

		const days = monthCal.getElementsByClassName("day");
		const overview = JSON.parse(req.responseText);
		// add the media for each day to its element in the calendar in an attribute
		for (let j = 0; j < overview.length; j++) {
			days[j].setAttribute("data-media", JSON.stringify(overview[j]["media"]));
			if (overview[j]["media"].length > 0) {
				days[j].className += " custom" + (!overview[j]["default_enabled"] ? " default-disabled": "");
			}
			else if (!overview[j]["default_enabled"]) {
				days[j].className += " default-disabled";
			}
		}
	}
	else {
		console.warn(req.status);
	}
}

function getMonth(elem) {
	return (elem.getAttribute("data-month"));
}

function getYear(elem) {
	return (elem.getAttribute("data-year"));
}

function getDate(elem) {
	return (elem.getAttribute("data-date"));
}

function initCalendar() {
	// get the HTML code of a calendar from a secondary script
	const calendarCode = getCalendar(today.getFullYear(), today.getMonth(), 3);

	// and add it to the main element of the page
	document.getElementById("calendar").innerHTML = calendarCode;

	// gather all the months
	const months = document.getElementsByClassName("month");
	requestsTodo += months.length;

	// continuously check if all requested programmes have been gathered
	// once so, remove the loading screen and stop checking
	checkReqDoneInterval = setInterval(function() {
		if (requestsDone == requestsTodo) {
			document.getElementById("loading").style.display = "none";
			clearInterval(checkReqDoneInterval);
			checkReqDoneInterval = null;
		}
	}, 250);

	// request the default programme's media and add it to the "edit default programme" link
	getDefaultProgramme();

	// request all programmes for the months listed in the calendar
	for (let i = 0; i < months.length; i++) {
		let req = new XMLHttpRequest();
		req.open("GET", "int/getmonth.php?month=" + getMonth(months[i]) + "&year=" + getYear(months[i]) + "&c=" + cacheId, true);
		req.addEventListener("loadend", function(fEv) {
			requestsDone++;
			loadMonthsProgramme(this);
		});
		req.addEventListener("error", function(err) {
			requestsDone++;
			console.error(err);
		});
		req.send();
	}

	// add context menu to all the days
	const days = document.getElementsByClassName("day");
	for (const day of days) {
		day.addEventListener("contextmenu", drawContextMenu, false);
	}

	// add event listener to remove context menu on any click
	document.body.addEventListener("click", removeContextMenu);
}

function editProgramme(ev) {
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}
	const internalDate = getYear(ev.currentTarget) + "-" + getMonth(ev.currentTarget) + "-" + getDate(ev.currentTarget);
	window.location.href = "programme.php?day=" + encodeURIComponent(internalDate);
}

function openSimpleUploader(ev) {
	ev.preventDefault();
	simpleUploader = openPopUpWin('newsimple.php', 'simuploadwin', 320, 400);
	return false;
}

function startCopyFrom(intoDay) {
	// get date of the day to copy the programme from
	const copyFromDayDate = getYear(copyFromDay)+"-"+getMonth(copyFromDay)+"-"+getDate(copyFromDay);

	// get date of the day to copy the programme to
	const copyToDayDate = getYear(intoDay)+"-"+getMonth(intoDay)+"-"+getDate(intoDay);

	// confirm if user wants to overwrite existing programme if one already exists
	if (intoDay.getAttribute("data-media")) {
		const sure = confirm("Overwrite programme for " + copyToDayDate + "?");
		if (!sure) {
			return;
		}
	}

	// show loading screen
	document.getElementById("loading").style.display = "block";

	// send new copy request
	let req = new XMLHttpRequest();
	req.open("GET", "int/copy.php?from=" + copyFromDayDate + "&to=" + copyToDayDate + "&c=" + cacheId, true);
	req.addEventListener("loadend", function(fEv) {
		if (this.status == 204) {
			console.log("Copied programme from " + copyFromDayDate + " to " + copyToDayDate);

			// change day preview: copy all media
			intoDay.setAttribute("data-media", copyFromDay.getAttribute("data-media"));

			// copy classes used for the preview tooltip, but only copy the class if it exists on the source day
			intoDay.classList.toggle("default-disabled", copyFromDay.classList.contains("default-disabled"));
			intoDay.classList.toggle("custom", copyFromDay.classList.contains("custom"));
		}
		else {
			alert("Unable to copy programme\n\n" + this.statusText + " / " + this.responseText);
			console.error("Unable to copy programme", this.statusText, this.responseText);
		}

		// hide loading screen
		document.getElementById("loading").style.display = "none";
	});
	req.addEventListener("error", function(err) {
		alert("Unable to copy programme\n\n" + err);
		console.error(err);

		// hide loading screen
		document.getElementById("loading").style.display = "none";
	});
	req.send();
}

function removeContextMenu(ev) {
	const ctxMenu = document.getElementById("ctx-menu");
	if (ctxMenu) {
		ctxMenu.forDay.classList.remove("selected");
		ctxMenu.remove();
	}
}

function drawContextMenu(ev) {
	ev.preventDefault();
	removeContextMenu(ev);

	const ctxMenu = document.createElement("div");
	ctxMenu.setAttribute("id", "ctx-menu");
	ctxMenu.style.top = ev.pageY + "px";
	ctxMenu.style.left = ev.pageX + "px";
	ctxMenu.forDay = ev.target;

	const copyBtn = document.createElement("div");
	copyBtn.innerText = "Copy";
	copyBtn.addEventListener("click", function(cEv) {
		// set copyFromDay global for use when pasting
		copyFromDay = cEv.target.parentNode.forDay;
	});
	ctxMenu.appendChild(copyBtn);

	// if a day to copy from exists, add paste option to the context menu
	if (copyFromDay) {
		const pasteBtn = document.createElement("div");
		pasteBtn.innerText = "Paste";
		pasteBtn.addEventListener("click", function(pEv) {
			// paste into day that this context menu was created for
			startCopyFrom(pEv.target.parentNode.forDay);
		});
		ctxMenu.appendChild(pasteBtn);
	}

	// add context menu to the body of the webpage
	document.body.appendChild(ctxMenu);
	ev.target.classList.add("selected");
}

function mirror(inputElem, enabled) {
	let alertUser = true;
	let source = "";
	let req = new XMLHttpRequest();
	req.open("POST", "int/setsync.php?c=" + cacheId, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.addEventListener("loadend", function(rEv) {
		console.log("Synchronization request status code: " + this.status);
		if (!alertUser) {
			return;
		}

		document.getElementById("mirror-source").innerText = "";
		if (this.status == 201) {
			document.getElementById("mirror-source").innerText = "Syncing with " + this.responseText;
			alert("Synchronization enabled with screen at " + this.responseText);
		}
		else if (this.status == 204) {
			alert("Synchronization was successfully disabled. You can now customize this screen again.");
		}
		else {
			inputElem.checked = false;
			alert("Could not enable synchronization. Reason: " + this.responseText);
		}
	});
	req.addEventListener("error", function(err) {
		inputElem.checked = false;
		document.getElementById("mirror-source").innerText = "";
		alert("Could not enable synchronization. Reason: " + err.message);
	});

	if (enabled) {
		source = prompt("Enter the domain of a screen to mirror:", "");
		if (source != null && source.trim() != "") {
			req.send("day=default&source="+source.trim());
		}
		else {
			inputElem.checked = false;
			alertUser = false;
			req.send("day=default&source=false");
		}
	}
	else {
		req.send("day=default&source=false");
	}
}

window.onload = initCalendar;
window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
	if (simpleUploader) {
		simpleUploader.close();
	}
};
