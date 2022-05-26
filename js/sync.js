function shouldStillSync() {
	return new Promise(function(resolve, reject) {
		const req = new XMLHttpRequest();
		req.open("GET", "/programmes/default/.mirror?c" + Math.random(), true);
		req.overrideMimeType("text/plain");
		req.addEventListener("loadend", function (fEv) {
			if (this.status == 200) {
				resolve(this.responseText);
			}
			else {
				reject();
			}
		});
		req.addEventListener("error", function(err) {
			reject();
		});
		req.send();
	});
}

function startSync(domain) {
	const eventSource = new EventSource("http://" + domain + "/sync.php");
	const progressBar = new CodamProgressBar();
	let curMedia = null;
	let curSource = domain;
	let hideTimeout = null;
	let hideInterval = null;

	eventSource.addEventListener("message", function(ev) {
		const json = JSON.parse(ev.data);
		// console.log(json);
		if (curMedia != json["current_media"]) {
			console.log("Switching media...", json);
			curMedia = json["current_media"];
			if (hideTimeout) {
				clearTimeout(hideTimeout);
			}
			if (hideInterval) {
				clearInterval(hideInterval);
			}
			const curMediaElem = document.getElementById(json["media_type"]);
			const otherMediaElems = document.getElementsByClassName("media");
			for (let i = 0; i < otherMediaElems.length; i++) {
				otherMediaElems[i].style.display = "none";
			}
			progressBar.stop();

			curMediaElem.style.display = "block";
			curMediaElem.src = "http://" + domain + "/" + json["current_media"];
			if (curMediaElem.load && typeof curMediaElem.load == "function") {
				curMediaElem.load();
				curMediaElem.play();
			}

			const showFor = Math.floor(json["show_until"] - json["server_time"]);
			if (showFor < 0) {
				console.warn("It looks like the source screen hasn't been displaying anything for a while (" + showFor + ")! Reloading in half a minute to force a reconnect.");
				progressBar.start(30000);
				hideTimeout = setTimeout(function() {
					console.log("Reloading now...");
					window.location.replace("show.php?day=today&num=0");
				}, 29700);
			}
			else {
				console.log("showFor", showFor);
				if (json["total"] > 1) {
					progressBar.start(showFor);
					hideTimeout = setTimeout(function() {
						document.getElementById("container").className = "hide-fade";
						if (json["num"] + 1 == json["total"]) {
							// redirect back to show.php to check if we should still mirror
							// if we should still mirror, we end up over here again by another redirect from there
							console.log("Redirecting to show.php to check if we should still mirror...");
							window.location.replace("show.php?day=today&num=0");
						}
					}, showFor - 300);
					document.getElementById("container").className = "show-fade";
				}
				else {
					hideInterval = setInterval(function() {
						if (json["num"] + 1 == json["total"]) {
							// check if we should still mirror
							shouldStillSync().then(function(newSource) {
								console.log("Should still mirror");
								if (newSource != curSource) {
									console.log("New source found!");
									window.location.replace("show.php?day=today&num=0");
								}
								// no need to check for new media, as this eventListener will still get called continuously and listen for new media
							}).catch(function(err) {
								console.error(err);
								window.location.replace("show.php?day=today&num=0");
							});
						}
					}, 30000);
					document.getElementById("container").className = "show";
				}
			}
		}
	});

	eventSource.addEventListener("open", function(ev) {
		console.log("Sync connection was opened");
	});

	eventSource.addEventListener("error", function(ev) {
		if (ev.readyState == EventSource.CLOSED) {
			console.log("Sync connection was closed");
		}
	});

	return eventSource;
}

let somethingShownLastCheck = true;
setInterval(function() {
	if (document.getElementById("container").className.indexOf("show") == -1) {
		if (somethingShownLastCheck == false) {
			console.warn("Nothing has been showing for a while! Reload.");
			window.location.replace("show.php?day=today&num=0");
		}
		somethingShownLastCheck = false;
	}
	else {
		somethingShownLastCheck = true;
	}
}, 10000);
