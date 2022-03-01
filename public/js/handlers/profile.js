Handlers.profile = (resp) => {
    // Success:
    if (resp.ok === true && (resp.status >= 200 || resp.status < 300)) {
        window.location.reload();
        return;
    }
    // Error:
    resp.text()
        .then((error) => {
            // TODO: Show this error on the frontend to the user.
            console.error(error);
        });
};
