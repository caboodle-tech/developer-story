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
        if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
            header('Location: ' . SITE_ROOT);
            exit();
        }
    }

    /**
     * Output an error message to the user.
     *
     * @param mixed   $msg        The message or data to output to the user.
     * @param integer $statusCode The HTTP status code to set for this response.
     * 
     * @return void
     */
    public function returnError($msg, $statusCode = 400) {
        outputResponse($msg, $statusCode);
    }

    /**
     * Output a normal response message to the user.
     *
     * @param mixed   $msg        The message or data to output to the user.
     * @param integer $statusCode The HTTP status code to set for this response.
     * 
     * @return void
     */
    public function returnNotice($msg, $statusCode = 200) {
        outputResponse($msg, $statusCode);
    }

    /**
     * Output a warning message to the user.
     *
     * @param mixed   $msg        The message or data to output to the user.
     * @param integer $statusCode The HTTP status code to set for this response.
     * 
     * @return void
     */
    public function returnWarning($msg, $statusCode = 200) {
        outputResponse($msg, $statusCode);
    }

}