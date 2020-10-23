class PrivacyManager {
	constructor(cookieName, title, message, acceptButton, rejectButton) {
		this.i_cookie_name = cookieName;
		this.i_title_text = title;
		this.i_message_text = message;
		this.i_accept_button_text = acceptButton;
		this.i_reject_button_text = rejectButton;
	}

	/**
	 *	Get a map of cookie values
	 *
	 *	@private
	 *
	 *	@return {Object} a map of all the cookies
	 */
	getCookies() {
		// Get the document's cookies
		let existingCookies = document.cookie, cookies = {};
		if (existingCookies) {
			// Parse the cookies into each name/value pair
			existingCookies = existingCookies.split(";");

			// For each pair, map the cookies name to it's value in the cookies collection
			for (let x = 0; x < existingCookies.length; x++) {
				let cookieParts = existingCookies[x].trim().split("=");
				cookies[cookieParts.shift()] = decodeURIComponent(cookieParts.join("="));
			}
		}

		// Return the cookies collection
		return cookies;
	}

	accept() {
		document.cookie = this.i_cookie_name + "=2; path=/";
		this.update();
	}

	reject() {
		document.cookie = this.i_cookie_name + "=0; path=/";
		this.update();
	}

	update() {
		if (this.i_element != null) {
			let cookies = this.getCookies();
			if (!cookies[this.i_cookie_name]) {
				this.i_visible = true;
				this.i_element.style.display = "";
				this.i_real_wrapper.className = "PrivacyManager_wrapper PrivacyManager_wrapper_open";
			}
			else {
				if (this.i_visible == true) {
					this.i_visible = false;
					this.i_real_wrapper.className = "PrivacyManager_wrapper PrivacyManager_wrapper_close";
				}
			}
		}
	}

	attach(component) {
		if (this.i_element == null) {
			this.i_element = document.createElement('DIV');
			this.i_element.className = "PrivacyManager";

				this.i_real_wrapper = document.createElement('DIV');
				this.i_real_wrapper.className = "PrivacyManager_wrapper";
				this.i_real_wrapper.addEventListener("animationend", () => {
					if (this.i_visible == false) {
						this.i_element.style.display = "none";
					}
					this.i_real_wrapper.className = "PrivacyManager_wrapper";
				});
				this.i_element.appendChild(this.i_real_wrapper);

				let lastElement = this.i_real_wrapper;
				for (let x = 0; x < 12; x++) {
					let nextLayer = document.createElement('DIV');
					nextLayer.className = "PrivacyManager_border_" + x;
					lastElement.appendChild(nextLayer);
					lastElement = nextLayer;
				}

					this.i_centered_wrapper = document.createElement('DIV');
					this.i_centered_wrapper.className = "PrivacyManager_center_wrapper";
					lastElement.appendChild(this.i_centered_wrapper);

						this.i_title = document.createElement('DIV');
						this.i_title.className = "PrivacyManager_title";
						this.i_title.innerHTML = this.i_title_text;
						this.i_centered_wrapper.appendChild(this.i_title);

						this.i_message = document.createElement('DIV');
						this.i_message.className = "PrivacyManager_message";
						this.i_message.innerHTML = this.i_message_text;
						this.i_centered_wrapper.appendChild(this.i_message);

						this.i_button_wrapper = document.createElement('DIV');
						this.i_button_wrapper.className = "PrivacyManager_button_wrapper";
						this.i_centered_wrapper.appendChild(this.i_button_wrapper);

							this.i_reject_button = document.createElement('DIV');
							this.i_reject_button.className = "PrivacyMangaer_reject";
							this.i_reject_button.innerHTML = this.i_reject_button_text;
							this.i_reject_button.addEventListener("click", () => {
								this.reject();
							});
							this.i_button_wrapper.appendChild(this.i_reject_button);

							this.i_accept_button = document.createElement('DIV');
							this.i_accept_button.className = "PrivacyMangaer_accept";
							this.i_accept_button.innerHTML = this.i_accept_button_text;
							this.i_accept_button.addEventListener("click", () => {
								this.accept();
							});
							this.i_button_wrapper.appendChild(this.i_accept_button);


		}
		if (this.i_element.parentNode != null) {
			this.i_element.parentNode.removeChild(this.i_element);
		}
		component.appendChild(this.i_element);
	}
}