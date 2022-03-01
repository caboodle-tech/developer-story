/* eslint-disable no-undef */
class SubmitForm extends HTMLButtonElement {

    altText = '';

    handler = '';

    form = null;

    constructor() {
        super();
        this.classList.add('button-form');
    }

    connectedCallback() {
        setTimeout(() => {
            this.setup();
        });
    }

    enctype() {
        if (this.form) {
            switch (this.form.enctype) {
                case 'application/x-www-form-urlencoded':
                    return 'FORM';
                case 'multipart/form-data':
                    return 'FILE';
                case 'text/plain':
                    return 'PLAIN';
                default:
                    // eslint-disable-next-line no-case-declarations
                    const inputs = this.form.querySelectorAll('input');
                    for (let i = 0; i < inputs.length; i++) {
                        if (inputs[i].type.toUpperCase() === 'FILE') {
                            return 'FILE';
                        }
                    }
            }
        }
        return 'FORM';
    }

    getBoolean(check) {
        switch (check.toLowerCase()) {
            case 1:
            case '1':
            case 't':
            case 'true':
                return true;
            default:
                return false;
        }
    }

    hideCancelButton() {
        const buttons = this.form.querySelectorAll('.cancel-button');
        buttons.forEach((button) => {
            // eslint-disable-next-line no-param-reassign
            button.style.display = 'none';
        });
    }

    lockForm() {
        this.form.reset();
        const inputs = this.form.querySelectorAll('input');
        inputs.forEach((input) => {
            // eslint-disable-next-line no-param-reassign
            input.disabled = true;
        });
        this.form.dataset.disabled = true;
        const alt = this.altText;
        this.altText = this.innerHTML;
        this.innerHTML = alt;
        this.hideCancelButton();
    }

    setup() {
        this.form = this.closest('form');
        if (this.form.dataset.handler) {
            this.handler = this.form.dataset.handler;
        }

        const data = this.querySelector('data');
        if (data) {
            this.altText = data.innerHTML;
            data.remove();
        }

        if (this.form.dataset.disabled && this.getBoolean(this.form.dataset.disabled)) {
            const inputs = this.form.querySelectorAll('input');
            inputs.forEach((input) => {
                // eslint-disable-next-line no-param-reassign
                input.disabled = true;
            });

            this.addEventListener('click', this.unlockForm);

            const buttons = this.form.querySelectorAll('.cancel-button');
            buttons.forEach((button) => {
                button.addEventListener('click', this.lockForm.bind(this));
            });

            this.hideCancelButton();
        } else {
            this.addEventListener('click', this.submit);
        }
    }

    showCancelButton() {
        const buttons = this.form.querySelectorAll('.cancel-button');
        buttons.forEach((button) => {
            // eslint-disable-next-line no-param-reassign
            button.style.display = 'initial';
        });
    }

    submit() {
        if (this.form) {
            if (!this.form.reportValidity()) {
                return;
            }

            const options = {
                contentType: this.enctype(),
                method: this.form.method
            };

            const req = new FetchIt(options);
            req.setBody(new FormData(this.form));
            req.fetch(this.form.action, { form: this.form })
                .then((resp) => {
                    if (Handlers[this.handler]) {
                        Handlers[this.handler](resp);
                    } else if (this.form.dataset.reload) {
                        window.location.reload();
                    } else {
                        // eslint-disable-next-line no-console
                        console.log('Form request was successful but there was no handler for the response.', resp);
                    }
                })
                .catch((error) => {
                    if (Handlers[this.handler]) {
                        Handlers[this.handler](error);
                    } else {
                        // eslint-disable-next-line no-console
                        console.error('There has been a problem with your fetch operation:', error);
                    }
                });
        }
    }

    unlockForm() {
        if (this.getBoolean(this.form.dataset.disabled)) {
            const inputs = this.form.querySelectorAll('input');
            inputs.forEach((input) => {
                // eslint-disable-next-line no-param-reassign
                input.disabled = false;
            });
            this.form.dataset.disabled = false;
            const alt = this.altText;
            this.altText = this.innerHTML;
            this.innerHTML = alt;
            this.showCancelButton();
        } else {
            this.submit();
        }
    }

}

customElements.define('submit-form', SubmitForm, { extends: 'button' });
