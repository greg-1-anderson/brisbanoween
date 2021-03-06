class PrivacyManager {
	constructor(enabled, privacyCookieName, sessionCookieName, title, message, acceptButton, rejectButton, reloadAfterAccept, pageContainer) {
		this.i_enabled = enabled;
		this.i_privacy_cookie_name = privacyCookieName;
		this.i_session_cookie_name = sessionCookieName;
		this.i_title_text = title;
		this.i_message_text = message;
		this.i_accept_button_text = acceptButton;
		this.i_reject_button_text = rejectButton;
		this.i_page_container = pageContainer;
		this.i_reload_after_accept = reloadAfterAccept;

		// Expire all cookies after 12 days.
		this.i_exp = new Date();
		this.i_exp.setTime(this.i_exp.getTime() + (60 * 60 * 24 * 12 * 1000));

		let cookies = this.getCookies();
		this.i_first_open = true;
		this.i_closed = !!cookies[this.i_privacy_cookie_name];

		PrivacyManager.i_last_active = this;
	}

	static open() {
		if (PrivacyManager.i_last_active != null) {
			PrivacyManager.i_last_active.open();
		}
	}

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

	open() {
		this.i_closed = false;
		this.update();
	}

	generateSessionId(length) {
		 var result           = '';
		 var characters       = 'abcdefghjkmnpqrtuvwxyz234689';
		 var charactersLength = characters.length;
		 for ( var i = 0; i < length; i++ ) {
				result += characters.charAt(Math.floor(Math.random() * charactersLength));
		 }
		 return result;
	}

	hasAnswered() {
		// See if the user has accepted or rejected the cookie dialog (at least once)
		return !!this.getCookies()[this.i_privacy_cookie_name];
	}

	issueSession(force) {
		// Issue a session cookie if they accepted cookies and dont have a session cookie yet
		let existingSession = this.getCookies()[this.i_session_cookie_name];
		if ((existingSession == null || existingSession == "") && (force || this.getCookies()[this.i_privacy_cookie_name] == "2")) {
			document.cookie = this.i_session_cookie_name + "=" + this.generateSessionId(6) + "; path=/; expires=" + this.i_exp.toGMTString();
		}
	}

	accept() {
		this.i_closed = true;
		document.cookie = this.i_privacy_cookie_name + "=2; path=/; expires=" + this.i_exp.toGMTString();
		this.issueSession(true);
		this.update();

		if (this.i_reload_after_accept) {
			setTimeout(() => {
				document.location.reload();
			}, 100);
		}
	}

	reject() {
		this.i_closed = true;
		document.cookie = this.i_privacy_cookie_name + "=0; path=/; expires=" + this.i_exp.toGMTString();
		document.cookie = this.i_session_cookie_name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT";
		this.update();
	}

	update() {
		if (this.i_element != null) {
			if (this.i_closed !== true) {
				if (this.i_visible != true) {
					this.i_visible = true;
					this.i_element.style.display = this.i_enabled ? "" : "none";
					if (this.i_page_container != null && this.i_enabled) {
						let classes = this.i_page_container.className.split(" ");
						classes = classes.filter((i) => i != "PrivacyManager_hide_body");
						classes.push("PrivacyManager_hide_body");
						this.i_page_container.className = classes.join(" ");
					}
					this.i_real_wrapper.className = "PrivacyManager_wrapper" + (this.i_first_open != true ? " PrivacyManager_wrapper_open" : "");
					this.i_notice.style.display = "none";
					this.i_first_open = false;
				}
			}
			else {
				if (this.i_visible == true) {
					this.i_visible = false;
					this.i_real_wrapper.className = "PrivacyManager_wrapper PrivacyManager_wrapper_close";
					if (this.i_page_container != null && this.i_enabled) {
						let classes = this.i_page_container.className.split(" ");
						classes = classes.filter((i) => i != "PrivacyManager_hide_body");
						this.i_page_container.className = classes.join(" ");
					}
					window.scrollTo(0,0);

					let cookies = this.getCookies();
					this.i_notice.style.display = (!this.i_enabled || cookies[this.i_privacy_cookie_name] == "2") ? "none" : "";
				}
			}
		}
	}

	getButton() {
		if (this.i_button == null) {
			this.i_button = document.createElement('DIV');
			this.i_button.className = "PrivacyManager_page_button";
			this.i_button.innerHTML = "Open Privacy Settings";
			this.i_button.addEventListener("click", () => {
				this.i_enabled = true;
				this.i_visible = false;
				this.open();
			});
		}
		return this.i_button;
	}

	attach(component) {
		if (this.i_element == null) {
			let cookies = this.getCookies();
			this.i_element = document.createElement('DIV');
			this.i_element.className = "PrivacyManager";
			this.i_element.style.display = (!this.i_enabled || !!cookies[this.i_privacy_cookie_name]) ? "none" : "";

				this.i_notice = document.createElement('DIV');
				this.i_notice.className = "PrivacyManager_notice";
				this.i_notice.style.display = (this.i_enabled && cookies[this.i_privacy_cookie_name] == "0") ? "" : "none";
				this.i_notice.title = "Privacy Settings";
				this.i_notice.addEventListener("click", () => {
					this.open();
				});

					this.i_notice_content = document.createElement('DIV');
					this.i_notice_content.className = "PrivacyManager_notice_content";
					this.i_notice_content.innerHTML = "OPT-IN TO PLAY";
					this.i_notice.appendChild(this.i_notice_content);

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

							this.i_accept_button = document.createElement('DIV');
							this.i_accept_button.className = "PrivacyMangaer_accept";
							this.i_accept_button.innerHTML = this.i_accept_button_text;
							this.i_accept_button.addEventListener("click", () => {
								this.accept();
							});
							this.i_button_wrapper.appendChild(this.i_accept_button);


							this.i_reject_button = document.createElement('DIV');
							this.i_reject_button.className = "PrivacyMangaer_reject";
							this.i_reject_button.innerHTML = this.i_reject_button_text;
							this.i_reject_button.addEventListener("click", () => {
								this.reject();
							});
							this.i_button_wrapper.appendChild(this.i_reject_button);

			this.update();
		}
		if (this.i_element.parentNode != null) {
			this.i_notice.parentNode.removeChild(this.i_notice);
			this.i_element.parentNode.removeChild(this.i_element);
		}
		component.appendChild(this.i_notice);
		component.appendChild(this.i_element);
	}
}


(function ($, Drupal) {
	let orig = Drupal.behaviors.myModuleBehavior ? Drupal.behaviors.myModuleBehavior.attach : null;
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
    	if (orig) {
    		orig(context, settings);
    	}
    	if (context == document) {
				let privacy_dialog = new PrivacyManager(
					settings.privacy.config.enabled !== false,
					settings.privacy.config.cookieName,
					settings.privacy.config.sessionCookieName,
					settings.privacy.config.title,
					settings.privacy.config.message,
					settings.privacy.config.acceptButton,
					settings.privacy.config.rejectButton,
					settings.privacy.config.reloadAfterAccept,
					document.getElementById('page-wrapper')
				);
				if (!privacy_dialog.hasAnswered() && settings.privacy.config.privacyAutoAccept) {
					privacy_dialog.accept();
				}
				privacy_dialog.issueSession();
				privacy_dialog.attach(document.body);

				if (settings.privacy.config.privacyPolicyURL && document.location.href.indexOf(settings.privacy.config.privacyPolicyURL) >= 0) {
					let contentBoxes = Array.prototype.map.call(document.getElementsByTagName('DIV'), (i) => i).filter((i) => i.getAttribute("property") == "schema:text");
					if (contentBoxes.length == 1) {
						contentBoxes[0].appendChild(privacy_dialog.getButton());
					}
					else {
						console.error("Privacy policy button injection failed because the content body could not be found");
					}
				}
			}
		}
	}
})(jQuery, Drupal);

