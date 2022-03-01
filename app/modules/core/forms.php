<?php
/**
 * Parent Forms class that contains basic methods all form handlers will need.
 */

namespace Module\Core;

class Forms {

    /**
     * Instantiate a new Forms object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        if (isset($postData)) {
            foreach ($postData as $key => $val) {
                $_POST[$key] = $val;
            }
        }
    }

    /**
     * Handle form submissions to this module. Will redirect the user to the
     * sites home page if they accessed this handler directly.
     *
     * @return void
     */
    public function processRequest() {
        /**
         * Do not allow direct access to form handlers. Silently redirect the
         * user back to the home page and do not alert them of anything.
         */
        if (REQUEST_TYPE === 'GET') {
            header('Location: ' . SITE_ROOT);
            exit();
        }
    }

}