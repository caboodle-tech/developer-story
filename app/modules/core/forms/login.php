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

        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        if (empty($email) || empty($pass)) {
            $this->returnError('One or more required form fields are empty.');
        }

        global $Sanitize;

        if (!$Sanitize->validEmail($email)) {
            $this->returnError('Invalid email provided.');
        }

        if (strlen($pass) < 8) {
            $this->returnError('Password must be 8 characters or more.');
        }

        global $Database;
        $db = $Database->connect();

        if ($db === false) {
            $this->returnError('Could not connect to the database.', 500);
        }

        $stmt = $db->prepare("SELECT `id`, `password`, `totp` FROM `users` WHERE `email`=? LIMIT 1;");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $this->returnError('There was an error attempting to query the database.', 500);
        }

        $data = $stmt->get_result();

        if (intval($data->num_rows) < 1) {
            $this->returnError('Email or password does not match our records.');
        }

        $data = $data->fetch_assoc();

        $pass = $Sanitize->pepperPassword($pass);

        if (!password_verify($pass, $data['password'])) {
            $this->returnError('Email or password does not match our records.');
        }

        // TODO: Perform OTP first instead of logging in if that is enabled.

        global $Session;

        $Session->userId   = $data['id'];
        $Session->loggedIn = true;

        outputResponse('Signed in successfully.', 200);
    }

}

/**
 * When accessed directly and not by a controller we need to instantiate the class
 * ourselves and call the `processRequest` method. This is for API calls.
 */
$Login = new \Module\Core\Forms\Login();

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $Login->processRequest();
}