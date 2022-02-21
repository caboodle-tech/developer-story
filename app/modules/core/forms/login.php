<?php
/**
 * Handler for the login form.
 */

namespace Module\Core\Forms;

class Login extends \Module\Core\Forms {

    /**
     * Instantiate a new Login object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        parent::__construct($postData);
    }

    /**
     * Handle form submissions to this module.
     *
     * @return void
     */
    public function processRequest() {
        // Do not process request if we are not supposed to.
        parent::processRequest();

    }

}

/**
 * When accessed directly and not by a controller we need to instantiate the class
 * ourselves and call the `processRequest` method. This is for API calls.
 */
$Login = new \Module\Core\Forms\Login();

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $Login->processRequest();
}