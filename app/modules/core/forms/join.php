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
        
        // Check that profile information is all valid:
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
            outputResponse('One or more required form fields are empty.', 400);
        }

        global $Sanitize;

        if (!$Sanitize->validName($fname) || !$Sanitize->validName($mname) || !$Sanitize->validName($lname)) {
            outputResponse('Invalid characters detected in your name.', 400);
        }

        if (!$Sanitize->validEmail($email1)) {
            outputResponse('Invalid email provided.', 400);
        }

        if ($email1 !== $email2) {
            outputResponse('Emails do not match.', 400);
        }

        if (strlen($pass1) < 8) {
            outputResponse('Password must be 8 characters or more.', 400);
        }

        if ($pass1 !== $pass2) {
            outputResponse('Passwords do not match.', 400);
        }

        // Open a connection to the database and add this new user:
        global $Database;
        $db = $Database->connect();

        // Prep all the information for inserting into the database.
        $id    = $Database->newId();
        $fname = trim($fname);
        $mname = trim($mname);
        $lname = trim($lname);
        $email = strip_tags($email1);
        $pass  = $Sanitize->hashPassword($Sanitize->pepperPassword($pass1));
        
        // Make sure this user does not exist first:
        $stmt = $db->prepare("SELECT COUNT(email) AS count FROM `user` WHERE `email`=?;");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            outputResponse('There was a database error, please try again later.', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();
        if (intval($result['count']) > 0) {
            outputResponse('User already exists.', 409);
        }
        $stmt->free_result();
        
        // All checks have passed, insert the user into the database:
        $db->begin_transaction();
        
        // User table.
        $stmt = $db->prepare("INSERT INTO `user` (`id`, `email`, `password`) VALUES (?, ?, ?);");
        $stmt->bind_param('sss', $id, $email, $pass);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $db->rollback();
            outputResponse('There was an error attempting to save the user.', 500);
        }

        // User Profile table.
        $stmt = $db->prepare("INSERT INTO `user_profile` (`id`, `first_name`, `middle_name`, `last_name`) VALUES (?, ?, ?, ?);");
        $stmt->bind_param('ssss', $id, $fname, $mname, $lname);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $db->rollback();
            outputResponse('There was an error attempting to save the user profile.', 500);
        }

        // User Connections table.
        $stmt = $db->prepare("INSERT INTO `user_connections` (`id`, `connected`) VALUES (?, '{\"connections\": {}}');");
        $stmt->bind_param('s', $id);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $db->rollback();
            outputResponse('There was an error attempting to save the user connections.', 500);
        }

        // Everything was successful, commit the queries and close the database connection.
        $stmt->free_result();
        $db->commit();

        // Login the user immediately and redirect them to their dashboard.
        global $Session;
        $Session->userId   = $id;
        $Session->loggedIn = true;
        outputResponse('User created successfully.', 201);
    }

}