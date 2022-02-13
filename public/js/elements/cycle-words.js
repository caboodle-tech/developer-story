/* eslint-disable no-param-reassign */
class CycleWords extends HTMLSpanElement {

    index = 0;

    timer = 3000;

    words = [];

    constructor() {
        super();
        this.classList.add('cycle-words');
    }

    connectedCallback() {
        setTimeout(() => {
            const data = this.querySelector('data');
            if (data) {
                this.style.position = 'relative';
                data.remove();
                this.setup(data);
                this.start();
            }
        });
    }

    setup(elem) {
        elem.style.display = 'none';

        let timer = elem.dataset.change;
        if (!timer) {
            timer = 3000;
        }
        if (timer < 3000) {
            timer = 3000;
        }
        this.timer = parseInt(timer, 10);

        let words = `${elem.innerHTML.replaceAll(/\\,/g, '&#44;')}, ${this.innerHTML}`;
        words = words.split(',');
        words.forEach((val, index) => {
            words[index] = val.trim();
        });
        this.words = words;
    }

    start() {
        if (this.words.length < 1) {
            return;
        }
        this.interval = setInterval(() => {
            let opacity = 0;
            let top = 5;
            this.style.opacity = '0';
            this.innerHTML = ` ${this.words[this.index]} `;
            this.index += 1;
            if (this.index > this.words.length - 1) {
                this.index = 0;
            }
            const nestedInterval = setInterval(() => {
                this.style.opacity = opacity;
                this.style.top = `-0.${top}em`;
                if (opacity < 1) {
                    opacity += 0.10;
                } else {
                    this.style.opacity = '1';
                }
                if (top > 0) {
                    top -= 1;
                } else {
                    this.style.top = '0';
                }
                if (opacity >= 1 && top <= 0) {
                    clearInterval(nestedInterval);
                }
            }, 90);
        }, this.timer);
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

}

customElements.define('cycle-words', CycleWords, { extends: 'span' });
