window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
};

function handleURL(ev) {
	// set value to input
	ev.target.value = ev.value;
	// submit the form
	ev.target.closest("form").submit();
}
