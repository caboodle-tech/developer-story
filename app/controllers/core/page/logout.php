<?php
/**
 * Controller for the Logout page.
 */

namespace Controller\Core\Page;

class Logout extends \Controller\Core\Page {
    
    /**
     * Instantiate page controller and set path to pages view (template) file.
     */
    public function __construct() {
        $this->setTemplate('');
    }

    /**
     * Overwrite the render method to perform the logout action instead of
     * showing a page view. This will redirect the user to the home page.
     * 
     * @return void.
     */
    public function render() {
        $Logout = new \Module\Core\Forms\Logout();
        $Logout->processRequest();
    }

}