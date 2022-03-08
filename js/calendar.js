var today = new Date();
var cacheId = Math.random();
var requestsTodo = 1;
var requestsDone = 0;
var checkReqDoneInterval = null;

function initCalendar() {
	// get the HTML code of a calendar from a secondary script
	var calendarCode = getCalendar(today.getFullYear(), today.getMonth(), 3);

	// and add it to the main element of the page
	document.getElementsByTagName("main")[0].innerHTML = calendarCode;

	// gather all the months
	var months = document.getElementsByClassName("month");
	requestsTodo += months.length;

	// request the default programme's media and add it to the "edit default programme" link
	var req;
	req = new XMLHttpRequest();
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

	// request all programmes for the months listed in the calendar
	for (var i = 0; i < months.length; i++) {
		req = new XMLHttpRequest();
		req.open("GET", "int/getmonth.php?month=" + months[i].getAttribute("data-month") + "&year=" + months[i].getAttribute("data-year") + "&c=" + cacheId, true);
		req.addEventListener("loadend", function(fEv) {
			requestsDone++;
			if (this.status == 200) {
				// get the month and year from the responseURL
				// cannot store them in variables earlier, since these requests
				// happen simultaneously, so we can only store them in this
				// unnamed function in the event listener
				var month = getParameterByName("month", this.responseURL);
				var year = getParameterByName("year", this.responseURL);
				var monthCal = document.querySelector(".month-"+year+"-"+month);
				if (!monthCal) {
					console.warn("Could not found month element!");
					return;
				}

				var days = monthCal.getElementsByClassName("day");
				var overview = JSON.parse(this.responseText);
				// add the media for each day to each day element in the calendar in an attribute
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
				console.warn(this.status);
			}
		});
		req.addEventListener("error", function(err) {
			requestsDone++;
			console.error(err);
		});
		req.send();
	}
	// continuously check if all requested programmes have been gathered
	// once so, remove the loading screen and stop checking
	checkReqDoneInterval = setInterval(function() {
		if (requestsDone == requestsTodo) {
			document.getElementById("loading").style.display = "none";
			clearInterval(checkReqDoneInterval);
			checkReqDoneInterval = null;
		}
	}, 250);
}

// function used to show a tooltip displaying the media of a programme when hovering over a day
function showProgTooltip(ev) {
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}

	// check if the element that caused to run this function from an event listener
	// has a data-media attribute, if not, stop immediately - then this function is not supposed to run
	var mediaAttr = ev.currentTarget.getAttribute("data-media");
	if (!mediaAttr) {
		return false;
	}

	// get the boundaries of the target element (hovered over day)
	var rect = ev.currentTarget.getBoundingClientRect();

	// get the media for this day from the media attribute of the day
	var media = JSON.parse(mediaAttr);
	if (media.length == 0) {
		return false;
	}

	// create a tooltip element and add positioning
	var tooltip = document.createElement("div");
	tooltip.className = "prog-tooltip";
	tooltip.style.top = (rect.top - 82) + "px";
	tooltip.style.left = (rect.left - 16) + "px";

	// populate the tooltip
	var defaultEnabledMsg = document.createElement("small");
	defaultEnabledMsg.className = "prog-tooltip-msg";
	if (ev.currentTarget.className.indexOf("default-disabled") > -1) {
		tooltip.style.background = "cyan";
		defaultEnabledMsg.innerHTML = "<b>Only the following</b> (no default programme):";
	}
	else if (ev.currentTarget.id == "default-link") {
		defaultEnabledMsg.innerHTML = "This programme gets prepended to any day's programme (where enabled):";
	}
	else {
		defaultEnabledMsg.innerHTML = "Default programme, plus:";
	}
	tooltip.appendChild(defaultEnabledMsg);

	// add all media to the tooltip
	var mediaItem;
	for (var i = 0; i < media.length; i++) {
		mediaItem = document.createElement("img");
		mediaItem.className = "prog-tooltip-media";
		mediaItem.setAttribute("src", "media/" + media[i]["file"]);
		tooltip.appendChild(mediaItem);
	}

	// add the tooltip to the webpage
	document.documentElement.appendChild(tooltip);
}

function hideProgTooltip(ev) {
	// if not hovered over a day, refuse to run the function
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}

	// remove all tooltips from the webpage (could be more on slow computers, thus removing in a for loop)
	var tooltips = document.getElementsByClassName("prog-tooltip");
	for (var i = 0; i < tooltips.length; i++) {
		tooltips[i].remove();
	}
}

function editProgramme(ev) {
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}
	var internalDate = ev.currentTarget.getAttribute("data-year") + "-" + ev.currentTarget.getAttribute("data-month") + "-" + ev.currentTarget.getAttribute("data-date");
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