<?php
/**
 * Handler for the logout form.
 */
namespace Module\Core\Forms;

class Logout extends \Module\Core\Forms {
    
    /**
     * Instantiate a new Logout object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        parent::__construct($postData);
    }

    /**
     * The Logout controller will call this method when a
     * proper logout request is made.
     *
     * @return void
     */
    public function processRequest() {
        global $Session;
        $Session->stop();
        // If this request was from a web browser redirect to the home page.
        if (RESPONSE_TYPE === 'HTML') {
            header("Location: ./");
            exit();
        }
        // Respond to an API request.
        outputResponse('Logout successful.', 200);
    }

}