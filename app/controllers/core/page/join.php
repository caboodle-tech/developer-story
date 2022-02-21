<?php
/**
 * Controller for the Join (sign up) page.
 */

namespace Controller\Core\Page;

class Join extends \Controller\Core\Page {
    
    /**
     * Instantiate page controller and set path to pages view (template) file.
     */
    public function __construct() {
        $this->setTemplate('app/views/core/page/join.phtml');
    }
    
}