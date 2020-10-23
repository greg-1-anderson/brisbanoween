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

				this.i_counter.style.display = "";
				this.i_title.innerHTML = "You're Early!";
				this.i_message.innerHTML = "The game will begin in";

				let newLabel = (parts.length > 0 ? parts.join(" ") : "Now");
				if (this.i_last_label != newLabel) {
					this.i_last_label = newLabel;
					this.i_counter.innerHTML = newLabel;
					this.i_counter.className = "CountdownDisplay_counter CountdownDisplay_counter_flash";
				}
			}
			else {
				this.i_counter.style.display = "none";
				this.i_title.innerHTML = "Starting...";
				this.i_message.innerHTML = "The game has begun.";

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

				this.i_real_wrapper = document.createElement('DIV');
				this.i_real_wrapper.className = "CountdownDisplay_wrapper";
				this.i_element.appendChild(this.i_real_wrapper);

				let lastElement = this.i_real_wrapper;
				for (let x = 0; x < 12; x++) {
					let nextLayer = document.createElement('DIV');
					nextLayer.className = "CountdownDisplay_border_" + x;
					lastElement.appendChild(nextLayer);
					lastElement = nextLayer;
				}

					this.i_centered_wrapper = document.createElement('DIV');
					this.i_centered_wrapper.className = "CountdownDisplay_center_wrapper";
					lastElement.appendChild(this.i_centered_wrapper);

						this.i_title = document.createElement('DIV');
						this.i_title.className = "CountdownDisplay_title";
						this.i_title.innerHTML = "You're Early!";
						this.i_centered_wrapper.appendChild(this.i_title);

						this.i_countdown_wrapper = document.createElement('DIV');
						this.i_countdown_wrapper.className = "CountdownDisplay_counter_wrapper";
						this.i_centered_wrapper.appendChild(this.i_countdown_wrapper);

							this.i_message = document.createElement('DIV');
							this.i_message.className = "CountdownDisplay_message";
							this.i_message.innerHTML = "The game will begin in";
							this.i_countdown_wrapper.appendChild(this.i_message);

							this.i_counter = document.createElement('DIV');
							this.i_counter.className = "CountdownDisplay_counter";
							this.i_counter.addEventListener("animationend", () => {
								this.i_counter.className = "CountdownDisplay_counter";
							});
							this.i_countdown_wrapper.appendChild(this.i_counter);

						this.i_button_wrapper = document.createElement('DIV');
						this.i_button_wrapper.className = "CountdownDisplay_button_wrapper";
						this.i_centered_wrapper.appendChild(this.i_button_wrapper);

							this.i_home_button = document.createElement('DIV');
							this.i_home_button.className = "CountdownDisplay_learn_more";
							this.i_home_button.innerHTML = "Learn More";
							this.i_home_button.addEventListener("click", () => {
								if (this.i_openInNewWindow) {
									window.open("/");
								}
								else {
									document.location = "/";
								}
							});
							this.i_button_wrapper.appendChild(this.i_home_button);

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