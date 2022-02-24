function openPopUpWin(url, windowName, w, h) {
	var y = window.top.outerHeight / 2 + window.top.screenY - ( h / 2);
	var x = window.top.outerWidth / 2 + window.top.screenX - ( w / 2);
	return (window.open(url, windowName, "location=no,menubar=no,status=no,toolbar=no,directories=no,scrollbars=no,width="+w+",height="+h+",top="+y+",left="+x));
}

function getParams(url) {
	var params = {};
	var parser = document.createElement('a');
	parser.href = url;
	var query = parser.search.substring(1);
	var vars = query.split('&');
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');
		params[pair[0]] = decodeURIComponent(pair[1]);
	}
	return (params);
}
