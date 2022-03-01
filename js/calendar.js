var today = new Date();
var cacheId = Math.random();

function initCalendar() {
	var calendarCode = getCalendar(today.getFullYear(), today.getMonth(), 3);
	document.getElementsByTagName("main")[0].innerHTML = calendarCode;

	var months = document.getElementsByClassName("month");
	var req;
	req = new XMLHttpRequest();
	req.open("GET", "int/get.php?day=default&c" + cacheId, true);
	req.addEventListener("loadend", function (fEv) {
		if (this.status == 200) {
			var defaultLink = document.getElementById("default-link");
			var overview = JSON.parse(this.responseText);

			defaultLink.setAttribute("data-media", JSON.stringify(overview["media"]));
		}
	});
	req.addEventListener("error", function(err) {
		console.error(err);
	});
	req.send();

	for (var i = 0; i < months.length; i++) {
		req = new XMLHttpRequest();
		req.open("GET", "int/getmonth.php?month=" + months[i].getAttribute("data-month") + "&year=" + months[i].getAttribute("data-year") + "&c=" + cacheId, true);
		req.addEventListener("loadend", function(fEv) {
			if (this.status == 200) {
				var month = getParameterByName("month", this.responseURL);
				var year = getParameterByName("year", this.responseURL);
				var monthCal = document.querySelector(".month-"+year+"-"+month);
				if (!monthCal) {
					console.warn("Could not found month element!");
					return;
				}

				var days = monthCal.getElementsByClassName("day");
				var overview = JSON.parse(this.responseText);
				// console.log(overview);
				// console.log(days);
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
			console.error(err);
		});
		req.send();
	}
}

function showProgTooltip(ev) {
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}

	var rect = ev.currentTarget.getBoundingClientRect();
	var mediaAttr = ev.currentTarget.getAttribute("data-media");
	if (!mediaAttr) {
		return false;
	}
	var media = JSON.parse(mediaAttr);
	if (media.length == 0) {
		return false;
	}
	var tooltip = document.createElement("div");
	tooltip.className = "prog-tooltip";
	tooltip.style.top = (rect.top - 82) + "px";
	tooltip.style.left = (rect.left - 16) + "px";

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

	var mediaItem;
	for (var i = 0; i < media.length; i++) {
		mediaItem = document.createElement("img");
		mediaItem.className = "prog-tooltip-media";
		mediaItem.setAttribute("src", "media/" + media[i]["file"]);
		tooltip.appendChild(mediaItem);
	}

	document.documentElement.appendChild(tooltip);
}

function hideProgTooltip(ev) {
	if (ev.currentTarget.className.indexOf("day") == -1) {
		return false;
	}

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

window.onload = initCalendar;