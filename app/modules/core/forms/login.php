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

        // Check that login information is all valid:
        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        if (empty($email) || empty($pass)) {
            outputResponse('One or more required form fields are empty.', 400);
        }

        global $Sanitize;

        if (!$Sanitize->validEmail($email)) {
            outputResponse('Invalid email provided.', 400);
        }

        if (strlen($pass) < 8) {
            outputResponse('Password must be 8 characters or more.', 400);
        }

        // Connect to database and check users authenticity.
        global $Database;
        $db = $Database->connect();

        $stmt = $db->prepare("SELECT `id`, `password`, `totp` FROM `user` WHERE `email`=? LIMIT 1;");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            outputResponse('There was a database error, please try again later.', 500);
        }

        // If we do not have a result object something was incorrect.
        $result = $stmt->get_result();
        if (intval($result->num_rows) < 1) {
            outputResponse('Email or password does not match our records.', 400);
        }
        $result = $result->fetch_assoc();

        // Add the sites pepper to the password and check its validity.
        $password = $Sanitize->pepperPassword($pass);
        if (!password_verify($password, $result['password'])) {
            outputResponse('Email or password does not match our records.', 400);
        }

        // TODO: Perform OTP first instead of logging in if that is enabled.
        $stmt->free_result();
        $db->close();

        // Setup session and redirect.
        global $Session;
        $Session->userId   = $result['id'];
        $Session->loggedIn = true;
        outputResponse('Signed in successfully.', 200);
    }

}