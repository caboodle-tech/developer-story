/* eslint-disable no-undef */
class ButtonToggle extends HTMLButtonElement {

    constructor() {
        super();
        this.classList.add('button-toggle');
        this.addEventListener('click', this.toggle);
    }

    connectedCallback() {
        setTimeout(() => {
            this.setAria();
        });
    }

    setAria() {
        const visible = this.querySelector('.visible');
        let label = '';
        let title = '';
        if (visible) {
            if (visible.dataset.aria) {
                label = visible.dataset.aria;
            }
            if (visible.dataset.title) {
                title = visible.dataset.title;
            }
        }
        if (label === '') {
            label = title;
        }
        if (title === '') {
            title = label;
        }
        this.ariaLabel = label;
        this.title = title;
    }

    toggle(ignoreCallback = false) {
        if (this.dataset.callback && ignoreCallback !== true) {
            if (Handlers[this.dataset.callback]) {
                Handlers[this.dataset.callback]();
            }
        }
        const on = this.querySelectorAll('.on');
        on.forEach((elem) => {
            elem.classList.toggle('visible');
        });
        const off = this.querySelectorAll('.off');
        off.forEach((elem) => {
            elem.classList.toggle('visible');
        });
        this.setAria();
    }
}

customElements.define('button-toggle', ButtonToggle, { extends: 'button' });
