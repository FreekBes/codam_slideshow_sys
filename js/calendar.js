const today = new Date();
const cacheId = Math.random();
let requestsTodo = 1;
let requestsDone = 0;
let checkReqDoneInterval = null;
let simpleUploader = null;
let defaultMedia = [];

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

		let days = monthCal.getElementsByClassName("day");
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