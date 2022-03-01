<?php
/**
 * The user controller that simplifies interactions with the current session user.
 * 
 * @see https://www.php.net/manual/en/function.session-start.php#102460
 */
namespace Module\Core;

class User {

    private $loggedIn = false;
    private $userId   = '';

    public function __construct() {
        $this->updateSessionStatus();
        $this->setupUserData();
    }

    public function &__get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function __set($name , $value) {
        $this->$name = $value;
    }

    public function __unset($name) {
        unset($this->name);
    }

    public function getUserId() {
        return $this->userId;
    }

    public function isLoggedIn() {
        return $this->loggedIn;
    }

    public function mustBeLoggedIn() {
        if ($this->loggedIn !== true) {
            header('Location: ' . SITE_ROOT);
            exit();
        }
    }

    public function setupUserData() {
        $properties = (object) [];

        // Do not waste time with the database if we know the user is not logged in.
        if ($this->loggedIn == true) {
            global $Database;

            // Combine various user tables from the database and get general info we need. 
            $db  = $Database->connect();
            $res = $db->query("SELECT u.id, u.email, up.first_name AS fname, up.middle_name AS mname, up.last_name AS lname, up.vanity, up.vanity_set_date AS vanityChanged, up.profile_picture AS profilePicture, uc.connected AS connections FROM user AS u LEFT JOIN user_profile AS up ON u.id=up.id LEFT JOIN user_connections AS uc ON u.id=uc.id WHERE u.id='$this->userId';");
            $res = $res->fetch_assoc();
            $db->close();

            // Unpack connected accounts from the database's JSON object.
            $properties              = (object) $res;
            $properties->connections = json_decode($properties->connections);
            $properties->connections = $properties->connections->connections;
        }

        // Attach all data as properties of this User class; these are all public!
        foreach ($properties as $prop => $val) {
            $this->$prop = $val;
        }
    }

    public function updateSessionStatus() {
        global $Session;
        if (isset($Session->userId) && isset($Session->loggedIn)) {
            $this->loggedIn = $Session->loggedIn;
            $this->userId   = $Session->userId;
        }
    }
}