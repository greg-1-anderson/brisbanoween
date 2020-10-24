class TipManagerPanel {
	constructor() {
		this.i_title = null;
		this.i_message = null;
		this.i_image = null;
		this.i_is_first = false;
	}

	setConfig(title, image, message, position, nextCallback, backCallback) {
		this.i_title_text = title;
		this.i_image_url = image;
		this.i_message_text = message;
		this.i_position = position;
		this.i_next_callback = nextCallback;
		this.i_back_callback = backCallback;
		this.i_close = false;

		this.update();
	}

	close(callback) {
		this.i_close_callback = callback;
		this.i_close = true;
		this.update();
	}

	update() {
		if (this.i_element != null) {
			if (this.i_close) {
				this.i_element.className = "TipManagerPanel_wrapper TipManagerPanel_wrapper_close";
			}
			else {
				this.i_element.className = "TipManagerPanel_wrapper";
			}

			this.i_title.style.display = (this.i_title_text ? "" : "none");
			this.i_title.innerHTML = this.i_title_text;

			this.i_image_wrapper.style.display = (this.i_image_url ? "" : "none");
			if (this.i_image_url != null) {
				this.i_image.src = this.i_image_url;
			}

			this.i_message.style.display = (this.i_message_text ? "" : "none");
			this.i_message.innerHTML = this.i_message_text;

			this.i_back_button.className = "TipManagerPanel_back" + (this.i_position == 'first' ? " TipManagerPanel_back_disabled" : "");
			this.i_next_button.innerHTML = this.i_position == "last" ? "Close" : "Next";
		}
	}

	getElement() {
		if (this.i_element == null) {
			this.i_element = document.createElement('DIV');
			this.i_element.className = "TipManagerPanel_wrapper";
			this.i_element.addEventListener("animationend", () => {
				if (this.i_close_callback != null) {
					this.i_close_callback(this);
					this.i_close_callback = null;
				}
				this.i_element.className = "TipManagerPanel_wrapper";
			});

			let lastElement = this.i_element;
			for (let x = 0; x < 12; x++) {
				let nextLayer = document.createElement('DIV');
				nextLayer.className = "TipManagerPanel_border_" + x;
				lastElement.appendChild(nextLayer);
				lastElement = nextLayer;
			}

				this.i_centered_wrapper = document.createElement('DIV');
				this.i_centered_wrapper.className = "TipManagerPanel_center_wrapper";
				lastElement.appendChild(this.i_centered_wrapper);

					this.i_title = document.createElement('DIV');
					this.i_title.className = "TipManagerPanel_title";
					this.i_title.innerHTML = this.i_title_text;
					this.i_centered_wrapper.appendChild(this.i_title);

					this.i_image_wrapper = document.createElement('DIV');
					this.i_image_wrapper.className = "TipManagerPanel_image_wrapper";
					this.i_centered_wrapper.appendChild(this.i_image_wrapper);

						this.i_image = document.createElement('IMG');
						this.i_image.className = "TipManagerPanel_image";
						this.i_image_wrapper.appendChild(this.i_image);

					this.i_message = document.createElement('DIV');
					this.i_message.className = "TipManagerPanel_message";
					this.i_message.innerHTML = this.i_message_text;
					this.i_centered_wrapper.appendChild(this.i_message);

					this.i_button_wrapper = document.createElement('DIV');
					this.i_button_wrapper.className = "TipManagerPanel_button_wrapper";
					this.i_centered_wrapper.appendChild(this.i_button_wrapper);

						this.i_back_button = document.createElement('DIV');
						this.i_back_button.className = "TipManagerPanel_back";
						this.i_back_button.innerHTML = "Back";
						this.i_back_button.addEventListener("click", () => {
							if (this.i_is_first != true && this.i_back_callback) {
								this.i_back_callback(this);
							}
						});
						this.i_button_wrapper.appendChild(this.i_back_button);


						this.i_next_button = document.createElement('DIV');
						this.i_next_button.className = "TipManagerPanel_next";
						this.i_next_button.innerHTML = "Next";
						this.i_next_button.addEventListener("click", () => {
							if (this.i_next_callback) {
								this.i_next_callback(this);
							}
						});
						this.i_button_wrapper.appendChild(this.i_next_button);

			this.update();
		}
		return this.i_element;
	}
}



class TipManager {
	constructor(siteContainer) {
		this.i_siteContainer = siteContainer;
		let prevInstructions = JSON.parse(localStorage.getItem("instructions"));
		this.i_instructions = prevInstructions ? prevInstructions : [];
		this.i_ptr = 0;
		for (let x = 0; x < this.i_instructions.length; x++) {
			if (this.i_instructions[x].read == true) {
				this.i_ptr = x + 1;
			}
		}
	}

	addInstructions(instructions) {
		let allExisting = {};
		this.i_instructions.forEach((i) => {
			allExisting[i.id] = i;
		});
		instructions.forEach((i) => {
			if (!allExisting[i.id]) {
				this.i_instructions.push(i);
			}
		});
		this.update();
	}

	next() {
		this.i_instructions[this.i_ptr].read = true;
		localStorage.setItem("instructions", JSON.stringify(this.i_instructions));
		this.i_ptr++;
		this.update();
	}

	back() {
		if (this.i_ptr > 0) {
			this.i_ptr--;
			this.update();
		}
	}

	update() {
		if (this.i_element != null) {
			let currentInstruction = this.i_instructions[this.i_ptr];
			if (currentInstruction != null && currentInstruction.id != this.i_last_instruction_id) {
				this.i_last_instruction_id = currentInstruction.id;


				let usePanel = 1;
				if (this.i_panels[0].i_attached) {
					this.i_panels[0].close(() => {
						this.i_element.removeChild(this.i_panels[0].getElement());
						this.i_panels[0].i_attached = false;
					});
				}
				else if (this.i_panels[1].i_attached) {
					usePanel = 0;
					this.i_panels[1].close(() => {
						this.i_element.removeChild(this.i_panels[1].getElement());
						this.i_panels[1].i_attached = false;
					});
				}

				this.i_panels[usePanel].setConfig(currentInstruction.title, currentInstruction.image, currentInstruction.message, this.i_ptr == 0 ? 'first' : this.i_ptr == this.i_instructions.length - 1 ? 'last' : 'normal', this.next.bind(this), this.back.bind(this));
				if (this.i_panels[(usePanel + 1) % 2].i_attached) {
					this.i_element.insertBefore(this.i_panels[usePanel].getElement(), this.i_panels[(usePanel + 1) % 2].getElement());
				}
				else {
					this.i_element.appendChild(this.i_panels[usePanel].getElement());
				}
				this.i_panels[usePanel].i_attached = true;
				this.i_element.style.display = "";
				if (this.i_siteContainer != null) {
					this.i_siteContainer.style.display = "none";
				}
			}
			else if (currentInstruction == null) {
				this.i_element.style.display = "none";
				if (this.i_siteContainer != null) {
					this.i_siteContainer.style.display = "inherit";
				}
			}
		}
	}

	attach(component) {
		if (this.i_element == null) {
			this.i_element = document.createElement('DIV');
			this.i_element.className = "TipManager";

			this.i_panels = [
				new TipManagerPanel(),
				new TipManagerPanel()
			];

			this.update();
		}
		if (this.i_element.parentNode != null) {
			this.i_element.parentNode.removeChild(this.i_element);
		}
		component.appendChild(this.i_element);
	}
}


