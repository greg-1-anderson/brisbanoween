class CountdownDisplay {
	constructor(startTime, targetPath, openInNewWindow) {
		let localize = new Date();
		localize.setTime(startTime * 1000);
		this.i_startTime = localize.getTime();
		this.i_targetPath = targetPath;
		this.i_openInNewWindow = openInNewWindow;
	}

	update() {
		if (this.i_element != null) {
			let remSeconds = Math.ceil((this.i_startTime - (new Date()).getTime()) / 1000);
			if (remSeconds > 0) {
				let remDays = Math.floor(remSeconds / (60 * 60 * 24));
				remSeconds-=(remDays * (60 * 60 * 24));
				let remHours = Math.floor(remSeconds / (60 * 60));
				remSeconds-=(remHours * (60 * 60));
				let remMinutes = Math.floor(remSeconds / 60);
				remSeconds-=remMinutes * 60;

				let parts = [];
				if (remDays > 0) {
					parts.push(remDays + " Day" + (remDays != 1 ? "s" : ""));
				}
				if (remHours > 0 || parts.length > 0) {
					parts.push(remHours + " Hour" + (remHours != 1 ? "s" : ""));
				}
				if (remMinutes > 0 || parts.length > 0) {
					parts.push(remMinutes + " Minute" + (remMinutes != 1 ? "s" : ""));
				}
				if (remSeconds > 0 || parts.length > 0) {
					parts.push(remSeconds + " Second" + (remSeconds != 1 ? "s" : ""));
				}

				if (parts.length > 1) {
					parts.splice(parts.length - 1, 0, 'and');
				}

				let newLabel = (parts.length > 0 ? parts.join(" ") : "Now!");
				if (this.i_last_label != newLabel) {
					this.i_last_label = newLabel;
					this.i_counter.innerHTML = newLabel;
					this.i_counter.className = "CountdownDisplay_counter CountdownDisplay_counter_change_animation";
				}

				this.i_waiting_box.style.display = "";
				this.i_redirecting_box.style.display = "none";
			}
			else {
				this.i_waiting_box.style.display = "none";
				this.i_redirecting_box.style.display = "";

				if (this.i_update_timer != null) {
					clearInterval(this.i_update_timer);
					this.i_update_timer = null;

					if (this.i_targetPath != null) {
						if (this.i_openInNewWindow) {
							window.open(this.i_targetPath);
						}
						else {
							document.location = this.i_targetPath;
						}
					}
				}
			}
		}
	}

	attach(component) {
		if (this.i_element == null) {
			this.i_element = document.createElement('DIV');
			this.i_element.className = "CountdownDisplay";

				this.i_waiting_box = document.createElement('DIV');
				this.i_waiting_box.className - "CountdownDisplay_waiting";
				this.i_element.appendChild(this.i_waiting_box);

					this.i_time_pending_header = document.createElement('DIV');
					this.i_time_pending_header.className = "CountdownDisplay_header";
					this.i_time_pending_header.innerHTML = "The game will start in";
					this.i_waiting_box.appendChild(this.i_time_pending_header);

					this.i_counter = document.createElement('DIV');
					this.i_counter.className = "CountdownDisplay_counter";
					this.i_counter.addEventListener("animationend", () => {
						this.i_counter.className = "CountdownDisplay_counter";
					});
					this.i_waiting_box.appendChild(this.i_counter);

				this.i_redirecting_box = document.createElement('DIV');
				this.i_redirecting_box.className = "CountdownDisplay_redirecting";
				this.i_redirecting_box.innerHTML = "The game has begun, one moment please...";
				this.i_redirecting_box.style.display = "none";
				this.i_element.appendChild(this.i_redirecting_box);


			this.i_update_timer = setInterval(() => {
				this.update();
			}, 200);
			this.update();
		}
		if (this.i_element.parentNode != null) {
			this.i_element.parentNode.removeChild(this.i_element);
		}
		component.appendChild(this.i_element);
	}
}