function CodamProgressBar() {
	this.start = function(duration) {
		if (this.progressBar) {
			this.progressBar.remove();
		}
		this.progressBar = document.createElement("div");
		this.progressBar.style.position = "fixed";
		this.progressBar.style.top = "0";
		this.progressBar.style.left = "0";
		this.progressBar.style.width = "0px";
		this.progressBar.style.width = "0px";
		this.progressBar.style.zIndex = "101";
		this.progressBar.style.transitionTimingFunction = "linear";

		this.progressBar.style.height = "6px";
		this.progressBar.style.background = "#E52A2D";
		this.progressBar.style.transitionDuration = duration + "ms";

		document.body.appendChild(this.progressBar);
		const _this = this;
		setTimeout(function() {
			_this.progressBar.style.width = "100%";
		}, 10);
	};

	this.stop = function() {
		if (this.progressBar) {
			this.progressBar.remove();
			this.progressBar = null;
		}
	};
}