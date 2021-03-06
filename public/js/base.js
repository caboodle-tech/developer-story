const Handlers = {};

const getCSSCustomProp = (propKey) => {
    let response = getComputedStyle(document.documentElement).getPropertyValue(propKey);

    if (response.length) {
        response = response.replace(/"/g, '').trim();
    }

    return response;
};

Handlers.toggleDarkMode = () => {
    let current = '';
    if (document.documentElement.dataset.darkMode) {
        current = document.documentElement.dataset.darkMode;
    } else {
        current = Cookies.get('dark-mode');
    }
    switch (current) {
        case null:
            current = getCSSCustomProp('--darkMode') === 'off' ? 'off' : 'on';
            break;
        case 'on':
            current = 'off';
            break;
        default:
            current = 'on';
            break;
    }
    Cookies.set('dark-mode', current);
    document.documentElement.setAttribute('data-dark-mode', current);
};

Handlers.profileConnect = () => {
    const evt = event;
    setTimeout(() => {
        // eslint-disable-next-line no-restricted-globals
        evt.target.closest('form').submit();
    }, 500);
};

Handlers.profileDisconnect = () => {
    const form = event.target.closest('form');
    const { method } = form;
    const { action } = form;
    const redirect = form.dataset.load;
    const xhr = new XMLHttpRequest();
    const data = new FormData(form);
    xhr.addEventListener('error', (evt) => {
        console.error(evt.target.responseText);
    });
    xhr.addEventListener('load', (evt) => {
        const { status } = evt.target;
        if (status >= 200 && status < 300) {
            const json = JSON.parse(evt.target.response);
            console.log(json);
            const token = json.items[0].access_token;
            const form = document.createElement('FORM');
            form.style.display = 'none';
            form.method = 'post';
            form.action = redirect;
            const input = document.createElement('INPUT');
            input.name = 'access_token';
            input.value = token;
            document.body.appendChild(form);
            form.submit();
        } else if (status >= 300 && status < 600) {
            //
        } else {
            console.warn('A proper status code was not returned from the server.');
        }
    });
    xhr.open(method, action);
    xhr.send(data);
};
