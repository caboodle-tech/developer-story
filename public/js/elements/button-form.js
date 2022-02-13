/* eslint-disable no-undef */
class ButtonForm extends HTMLButtonElement {

    errorCallback = '';

    loadCallback = '';

    form = null;

    constructor() {
        super();
        this.classList.add('button-form');
        this.addEventListener('click', this.submit);
    }

    connectedCallback() {
        setTimeout(() => {
            this.form = this.closest('form');
            if (this.form.dataset.error) {
                this.errorCallback = this.form.dataset.error;
            }
            if (this.form.dataset.load) {
                this.loadCallback = this.form.dataset.load;
            }
        });
    }

    // eslint-disable-next-line class-methods-use-this
    error(evt) {
        console.error(evt);
    }

    // eslint-disable-next-line class-methods-use-this
    load(evt) {
        console.log(evt.target.responseText);
    }

    submit() {
        if (this.form) {
            const { method } = this.form;
            const { action } = this.form;
            const xhr = new XMLHttpRequest();
            const data = new FormData(this.form);
            if (FormHandlers[this.errorCallback]) {
                xhr.addEventListener('error', FormHandlers[this.errorCallback]);
            } else {
                xhr.addEventListener('error', this.error);
            }
            if (FormHandlers[this.loadCallback]) {
                xhr.addEventListener('load', FormHandlers[this.loadCallback]);
            } else {
                xhr.addEventListener('load', this.load);
            }
            xhr.open(method, action);
            xhr.send(data);
        }
    }

}

customElements.define('button-form', ButtonForm, { extends: 'button' });
