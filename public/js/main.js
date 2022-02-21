const Handlers = {};

const getCSSCustomProp = (propKey) => {
    let response = getComputedStyle(document.documentElement).getPropertyValue(propKey);

    if (response.length) {
        response = response.replace(/"/g, '').trim();
    }

    return response;
};

Handlers.joinLoad = (evt) => {
    const { status } = evt.target;
    if (status >= 200 && status < 300) {
        if (evt.target.form.dataset.redirect) {
            window.location = evt.target.form.dataset.redirect;
        }
    } else if (status >= 300 && status < 600) {
        if (status === 409) {
            // User already exists.
        } else {
            // Something doesn't match or is not filled out.
        }
    } else {
        console.warn('A proper status code was not returned from the server. The joining form does not know how to handle this response.');
    }
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
