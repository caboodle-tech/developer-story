<?php

namespace Controller\Core;

class Sqli extends \mysqli {

    private $observers = [];

    public function __construct(
        $host = null,
        $user = null,
        $pass = null,
        $db = '',
        $port = null,
        $socket = null,
        $charset = null
    ) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (is_null($host)) {
            $host = ini_get('mysqli.default_host');
        }

        if (is_null($user)) {
            $user = ini_get('mysqli.default_user');
        }

        if (is_null($pass)) {
            $pass = ini_get('mysqli.default_pw');
        }

        if (is_null($port)) {
            $port = ini_get('mysqli.default_port');
        }

        if (is_null($socket)) {
            $socket = ini_get('mysqli.default_socket');
        }

        parent::__construct($host, $user, $pass, $db, $port, $socket);

        if (!is_null($charset)) {
            $this->set_charset($charset);
        }
    }

    public function close() {
        parent::close();
        foreach ($this->observers as $observer) {
            if (isset($observer)) {
                $observer->close();
            }
        }
    }

    public function registerObserver($class) {
        if (isset($class) && method_exists($class, 'close')) {
            array_push($this->observers, $class);
        }
    }
}