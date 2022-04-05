function startSync(domain) {
	const eventSource = new EventSource("http://" + domain + "/sync.php");
	const progressBar = new CodamProgressBar();
	let curMedia = null;
	let hideTimeout = null;

	eventSource.addEventListener("message", function(ev) {
		const json = JSON.parse(ev.data);
		if (curMedia != json["current_media"]) {
			console.log("Switching media...", json);
			curMedia = json["current_media"];
			if (hideTimeout) {
				clearTimeout(hideTimeout);
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
				console.warn("It looks like the source screen hasn't been displaying anything for a while! Reloading in half a minute to force a reconnect.");
				progressBar.start(30000);
				hideTimeout = setTimeout(function() {
					console.log("Reloading now...");
					window.location.replace("show.php?day=today&num=0");
				}, 29700);
			}
			else {
				if (json["total"] > 1) {
					progressBar.start(showFor);
				}
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