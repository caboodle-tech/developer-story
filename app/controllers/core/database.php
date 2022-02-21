<?php
/**
 * The Database controller for the application.
 */

namespace Controller\Core;

class Database {

    private $connection = null;
    private $settings   = [];

    /**
     * Instantiate a new instance of the Database controller.
     *
     * @param array $settings An associative array of settings to use. Defaults to values set in the `config.php` file.
     */
    public function __construct($settings = null) {
        if ($settings) {
            $this->settings = (object) [];
            foreach ($settings as $key => $val) {
                $this->settings[$key] = $val;
            }
        } else {
            $settings = [
                'database' => getConstant('DB_NAME', ''),
                'host'     => getConstant('DB_HOST', 'localhost'),
                'password' => getConstant('DB_PASSWORD', ''),
                'port'     => getConstant('DB_PORT', false),
                'socket'   => getConstant('DB_SOCKET', false),
                'username' => getConstant('DB_USER', '')
            ];

            $this->settings = $settings;
        }
    }

    /**
     * When this class is being destructed make sure the database connection is closed. 
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Close the database connection.
     * 
     * @return void
     */
    public function close() {
        if ($this->$connection) {
            $this->$connection->close();
        }
    }

    /**
     * Attempt to connect to the database.
     *
     * @return object|boolean The `mysqli` connection object or false if the connection could not be opened.
     */
    public function connect() {
        $host = $this->settings['host'];
        $user = $this->settings['username'];
        $pass = $this->settings['password'];
        $db   = $this->settings['database'];
        $port = $this->settings['port'];
        $sock = $this->settings['socket'];

        if (!empty($this->settings['port']) && !empty($this->settings['socket'])) {
            $connection = @new \mysqli($host, $user, $pass, $db, $port, $sock);
        } else if (!empty($this->settings['port'])) {
            $connection = @new \mysqli($host, $user, $pass, $db, $port);
        } else {
            $connection = @new \mysqli($host, $user, $pass, $db);
        }
        
        if ($connection->connect_errno) {
            // TODO: Log this: $connection->connect_error;
            return false;
        }

        $this->connection = $connection;

        return $connection;
    }

    /**
     * Generate a unique time based ID for use as a unique key in the database.
     *
     * @param integer $length How long the ID should be. Defaults to 16 and can not be less than 16.
     * 
     * @return void
     */
    public function newId($length = 16) {
        if ($length < 16) {
            $length = 16;
        }
        $id  = '';
        $ary = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $now = date('n:j:Y:G:i:s');
        $now = explode(':', $now);
        $id .= $ary[intval($now[0])];
        $id .= $ary[intval($now[1])];
        $id .= $now[2];
        $id .= $ary[intval($now[3])];
        $id .= $now[4];
        $id .= $now[5];
        while (strlen($id) < $length) {
            shuffle($ary);
            $id .= $ary[rand(0, 35)];
        }
        return $id;
    }
}