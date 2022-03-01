// eslint-disable-next-line no-unused-vars
class FetchIt {

    #options = {
        cache: 'default',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        method: 'GET',
        mode: 'cors',
        redirect: 'follow'
    };

    constructor(options = {}) {
        this.setOptions(options);
    }

    setBody(body) {
        this.#options.body = body;
    }

    setContentType(type) {
        // If the user provided a full string use it as the content-type.
        if (type.length > 8) {
            this.#options.headers['Content-Type'] = type;
            return;
        }
        // Anything shorter should be a predefined shorthand:
        switch (type.toUpperCase()) {
            case 'PLAIN':
                this.#options.headers['Content-Type'] = 'text/plain';
                break;
            case 'HTML':
                this.#options.headers['Content-Type'] = 'text/html';
                break;
            case 'FORM':
                this.#options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                break;
            case 'FILE':
            case 'FILES':
                this.#options.headers['Content-Type'] = 'multipart/form-data';
                break;
            case 'XML':
                this.#options.headers['Content-Type'] = 'application/xml';
                break;
            default:
                this.#options.headers['Content-Type'] = 'application/json';
        }
    }

    setOptions(options = {}) {
        // Attach the users options to init options object.
        Object.keys(options).forEach((key) => {
            this.#options[key] = options[key];
        });
        // If they provided a content-type apply it to the header and remove the property.
        if (this.#options.contentType) {
            this.setContentType(this.#options.contentType);
            delete this.#options.contentType;
        }
    }

    async fetch(url, attach = {}) {
        if (['GET', 'HEAD'].includes(this.#options.method)) {
            if (this.#options.body) {
                // eslint-disable-next-line no-console
                console.warn('The `fetch` method does not allow a `body` to be set on GET or HEAD requests.');
                delete this.#options.body;
            }
        }

        // Is the body a `FormData` object?
        if (typeof this.#options.body === 'object') {
            if (this.#options.body.constructor.name === 'FormData') {
                /*
                 * Yes. It does not play nice with preset content-type headers so
                 * remove what we have and let the browser set it automatically.
                 */
                delete this.#options.headers['Content-Type'];
            }
        }

        const resp = await fetch(url, this.#options);
        const skip = ['body', 'bodyUsed', 'headers', 'ok', 'redirected', 'status', 'statusText', 'type', 'url'];
        Object.keys(attach).forEach((key) => {
            if (!skip.includes(key)) {
                resp[key] = attach[key];
            }
        });
        return resp;
    }
}
