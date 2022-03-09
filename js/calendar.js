var today = new Date();
var cacheId = Math.random();
var requestsTodo = 1;
var requestsDone = 0;
var checkReqDoneInterval = null;

function getDefaultProgramme() {
	var req = new XMLHttpRequest();
	req.open("GET", "int/get.php?day=default&c" + cacheId, true);
	req.addEventListener("loadend", function (fEv) {
		requestsDone++;
		if (this.status == 200) {
			var defaultLink = document.getElementById("default-link");
			var overview = JSON.parse(this.responseText);

			defaultLink.setAttribute("data-media", JSON.stringify(overview["media"]));
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
		var month = getParameterByName("month", req.responseURL);
		var year = getParameterByName("year", req.responseURL);
		var monthCal = document.querySelector(".month-"+year+"-"+month);
		if (!monthCal) {
			console.warn("Could not found month element!");
			return;
		}

		var days = monthCal.getElementsByClassName("day");
		var overview = JSON.parse(req.responseText);
		// add the media for each day to its element in the calendar in an attribute
		for (var j = 0; j < overview.length; j++) {
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
	var calendarCode = getCalendar(today.getFullYear(), today.getMonth(), 3);

	// and add it to the main element of the page
	document.getElementsByTagName("main")[0].innerHTML = calendarCode;

	// gather all the months
	var months = document.getElementsByClassName("month");
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
	var req = null;
	for (var i = 0; i < months.length; i++) {
		req = new XMLHttpRequest();
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
	var internalDate = getYear(ev.currentTarget) + "-" + getMonth(ev.currentTarget) + "-" + getDate(ev.currentTarget);
	window.location.href = "programme.php?day=" + encodeURIComponent(internalDate);
}

function openSimpleUploader(ev) {
	ev.preventDefault();
	openPopUpWin('newsimple.php', 'simuploadwin', 320, 400);
	return false;
}

window.onload = initCalendar;
window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
};