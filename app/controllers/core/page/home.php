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

    /**
     * Block API calls from this page and run the original render method only
     * for web based requests.
     *
     * @return void
     */
    public function render() {
        global $Session;
        if (RESPONSE_TYPE !== 'HTML') {
            outputResponse('This is a web page and not a valid API endpoint.', 400);
        }
        parent::render();
    }
}