<?php

namespace Controller\Core\Page;

class Join extends \Controller\Core\Page {
    
    public function __construct() {
        $this->setTemplate('app/views/core/page/join.phtml');
    }
}