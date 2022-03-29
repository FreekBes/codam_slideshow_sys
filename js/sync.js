let source = new EventSource("sync.php");

source.addEventListener("message", function(ev) {
	console.log("Sync", JSON.parse(ev.data));
});

source.addEventListener("open", function(ev) {
	console.log("Sync connection was opened");
});

source.addEventListener("error", function(ev) {
	if (ev.readyState == EventSource.CLOSED) {
		console.log("Sync connection was closed");
	}
});