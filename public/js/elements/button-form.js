/* eslint-disable no-undef */
class ButtonForm extends HTMLButtonElement {

    altText = '';

    errorCallback = '';

    loadCallback = '';

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

    error(evt) {
        console.error(evt.target.responseText);
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

    load() {
        if (this.form.dataset.reload) {
            window.location.reload();

        }
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
        if (this.form.dataset.error) {
            this.errorCallback = this.form.dataset.error;
        }
        if (this.form.dataset.load) {
            this.loadCallback = this.form.dataset.load;
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
            const { method } = this.form;
            const { action } = this.form;
            const xhr = new XMLHttpRequest();
            const data = new FormData(this.form);
            if (Handlers[this.errorCallback]) {
                xhr.addEventListener('error', Handlers[this.errorCallback]);
            } else {
                xhr.addEventListener('error', this.error);
            }
            if (Handlers[this.loadCallback]) {
                xhr.addEventListener('load', Handlers[this.loadCallback]);
            } else {
                xhr.addEventListener('load', this.load);
            }
            xhr.form = this.form; // Reference the form in the XHR object.
            xhr.open(method, action);
            xhr.send(data);
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

customElements.define('button-form', ButtonForm, { extends: 'button' });
