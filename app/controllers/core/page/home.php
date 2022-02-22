<?php
/**
 * Controller for the Logout page.
 */

namespace Controller\Core\Page;

class Home extends \Controller\Core\Page {
    /*
     * This class is equivalent to the Page class and was added so we have a 
     * dedicated home controller for the routing table. This is needed because
     * home page requests can be empty () or simply a forward slash (/).
     */
}