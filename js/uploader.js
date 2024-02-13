window.onbeforeunload = function(ev) {
	document.getElementById("loading").style.display = "block";
};

function handleURL(ev) {
	// wait for the input to be changed, then submit the form
	setTimeout(function() {
		ev.target.closest("form").submit();
	}, 200);
}
