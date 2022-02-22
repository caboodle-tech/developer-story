<?php
/**
 * Controller for the login page.
 */

namespace Controller\Core\Page;

class Login extends \Controller\Core\Page {
    
    /**
     * Instantiate page controller and set path to pages view (template) file.
     */
    public function __construct() {
        $this->setTemplate('app/views/core/page/login.phtml');
    }

    /**
     * Make sure the user is not already logged in first then call the original
     * render method from the parent.
     *
     * @return void
     */
    public function render() {
        global $Session;
        /**
         * Do not let logged in users view the login page again.
         */
        if (isset($Session->loggedIn)) {
            header('Location: ' . SITE_ROOT);
            exit();
        }
        parent::render();
    }
    
}