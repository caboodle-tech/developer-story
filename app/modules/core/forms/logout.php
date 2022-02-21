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
     * Handle form submissions to this module.
     *
     * @return void
     */
    public function processRequest() {
        // Do not process request if we are not supposed to.
        parent::processRequest();
        
        global $Session;
        $Session->stop();
    }

}

/**
 * When accessed directly and not by a controller we need to instantiate the class
 * ourselves and call the `processRequest` method. This is for API calls.
 */
$Logout = new \Module\Core\Forms\Logout();

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'DELETE') {
    $Logout->processRequest();
}