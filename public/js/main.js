const FormHandlers = {};

FormHandlers.joinLoad = (evt) => {
    console.log(evt.target.responseText);
    window.location = './dashboard';
};

FormHandlers.joinError = (evt) => {
    // TODO: Error message on page.
};
