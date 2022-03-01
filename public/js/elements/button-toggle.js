/* eslint-disable no-undef */
class ButtonToggle extends HTMLButtonElement {

    constructor() {
        super();
        this.type = 'button';
        this.classList.add('button-toggle');
        this.addEventListener('click', this.toggle.bind(this));
    }

    connectedCallback() {
        setTimeout(() => {
            const set = this.querySelector('.visible');
            if (!set) {
                const fix = this.querySelector('.on');
                if (fix) {
                    fix.classList.add('visible');
                }
            }
            if (this.classList.contains('toggle')) {
                const on = this.querySelector('.on');
                if (on) {
                    on.style.display = 'none';
                }
                const off = this.querySelector('.off');
                if (off) {
                    off.style.display = 'none';
                }
                const slider = document.createElement('span');
                slider.classList.add('slider');
                this.appendChild(slider);
            }
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
        const on = this.querySelectorAll('.on');
        on.forEach((elem) => {
            elem.classList.toggle('visible');
        });
        const off = this.querySelectorAll('.off');
        off.forEach((elem) => {
            elem.classList.toggle('visible');
        });
        this.setAria();
        if (this.classList.contains('toggle')) {
            this.classList.toggle('checked');
        }
        if (this.dataset.callback && ignoreCallback !== true) {
            if (Handlers[this.dataset.callback]) {
                Handlers[this.dataset.callback]();
            }
        }
    }
}

customElements.define('button-toggle', ButtonToggle, { extends: 'button' });
