const Handlers = {};

const getCSSCustomProp = (propKey) => {
    let response = getComputedStyle(document.documentElement).getPropertyValue(propKey);

    if (response.length) {
        response = response.replace(/"/g, '').trim();
    }

    return response;
};

Handlers.joinLoad = (evt) => {
    console.log(evt.target.responseText);
    window.location = './dashboard';
};

Handlers.joinError = (evt) => {
    // TODO: Error message on page.
};

Handlers.toggleDarkMode = () => {
    let current = '';
    if (document.documentElement.dataset.darkMode) {
        current = document.documentElement.dataset.darkMode;
    } else {
        current = localStorage.getItem('dark-mode');
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
    localStorage.setItem('dark-mode', current);
    document.documentElement.setAttribute('data-dark-mode', current);
};

Handlers.setDarkMode = () => {
    let current = localStorage.getItem('dark-mode');
    if (!current) {
        current = getCSSCustomProp('--darkMode');
        localStorage.setItem('dark-mode', current);
    }
    document.documentElement.setAttribute('data-dark-mode', current);
    let timeout = 5000;
    const interval = setInterval(() => {
        const toggle = document.getElementById('dark-mode-toggle');
        if (toggle) {
            if (current === 'on') {
                toggle.toggle(true);
            }
            clearInterval(interval);
            return;
        }
        timeout -= 100;
        if (timeout <= 0) {
            clearInterval(interval);
        }
    }, 100);
};
Handlers.setDarkMode();
