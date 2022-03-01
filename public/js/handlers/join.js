Handlers.join = (resp) => {
    // Success:
    if (resp.ok === true && (resp.status >= 200 || resp.status < 300)) {
        if (resp.form && resp.form.dataset.redirect) {
            window.location.href = resp.form.dataset.redirect;
            return;
        }
    }
    // Error:
    resp.text()
        .then((error) => {
            // TODO: Show this error on the frontend to the user.
            console.error(error);
        });
};
