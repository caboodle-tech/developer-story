<?php
/**
 * Handler for the join (sign up) form.
 */

namespace Module\Core\Forms;

class Join extends \Module\Core\Forms {

    /**
     * Instantiate a new Join object.
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
        
        $fname  = trim($_POST['first-name']);
        $mname  = trim($_POST['middle-name']);
        $lname  = trim($_POST['last-name']);
        $email1 = trim($_POST['email-1']);
        $email2 = trim($_POST['email-2']);
        $pass1  = $_POST['password-1'];
        $pass2  = $_POST['password-2'];

        if (empty($fname) || empty($lname) || empty($email1)
            || empty($email2) || empty($pass1) || empty($pass2)
        ) {
            $this->returnError('One or more required form fields are empty.');
        }

        global $Sanitize;

        if (!$Sanitize->validName($fname) || !$Sanitize->validName($mname) || !$Sanitize->validName($lname)) {
            $this->returnError('Invalid characters detected in your name.');
        }

        if (!$Sanitize->validEmail($email1)) {
            $this->returnError('Invalid email provided.');
        }

        if ($email1 !== $email2) {
            $this->returnError('Emails do not match.');
        }

        if (strlen($pass1) < 8) {
            $this->returnError('Password must be 8 characters or more.');
        }

        if ($pass1 !== $pass2) {
            $this->returnError('Passwords do not match.');
        }

        global $Database;
        $db = $Database->connect();

        if ($db === false) {
            $this->returnError('Could not connect to the database.', 500);
        }

        $id    = $Database->newId();
        $fname = trim($fname);
        $mname = trim($mname);
        $lname = trim($lname);
        $email = strip_tags($email1);
        $pass  = $Sanitize->encryptPassword($pass1);

        $stmt = $db->prepare("SELECT COUNT(email) AS count FROM `users` WHERE `email`=?;");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $this->returnError('There was an error attempting to connect to the database.', 500);
        }

        $count = $stmt->get_result()->fetch_assoc();
        if (intval($count['count']) > 0) {
            $this->returnError('User already exists.', 409);
        } 

        $stmt = $db->prepare("INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `middle_name`, `last_name`) VALUES (?, ?, ?, ?, ?, ?);");
        $stmt->bind_param('ssssss', $id, $email, $pass, $fname, $mname, $lname);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $this->returnError('There was an error attempting to save the user.', 500);
        }

        global $Session;

        $Session->userId   = $id;
        $Session->loggedIn = true;

        outputResponse('User created successfully.', 201);
    }

}

/**
 * When accessed directly and not by a controller we need to instantiate the class
 * ourselves and call the `processRequest` method. This is for API calls.
 */
$Join = new \Module\Core\Forms\Join();

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $Join->processRequest();
}