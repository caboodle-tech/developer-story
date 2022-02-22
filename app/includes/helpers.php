<?php
/**
 * Global utility functions that help simplify tasks.
 */

if (!function_exists('__')) {
    /**
     * Helper function that calls Controller\Core\Sanitizer->print for you.
     *
     * @param string $unsanitized The string you wish to sanitize.
     * 
     * @return void Sanitized value is printed the page.
     */
    function __($unsanitized) {
        global $Sanitize;
        $Sanitize->print($unsanitized);
    }
}

if (!function_exists('absPath')) {
    /**
     * Helper function that takes a relative path and converts it to the system
     * appropriate absolute file path.
     *
     * @param string $str The relative path to a file.
     * 
     * @return void The system appropriate absolute file path.
     */
    function absPath($str) {
        $prefix = '';
        $root   = ROOT;
        $pos    = strpos($root, ':');
        if ($pos != false && $pos < 4) {
            $prefix = substr($root, 0, $pos + 1);
            $root   = substr($root, $pos + 1);
        }
        $str = $root . SEP . $str;
        $str = str_replace('\\\\', '/', $str);
        $str = str_replace('\\', '/', $str);
        $str = str_replace('///', '/', $str);
        $str = str_replace('//', '/', $str);
        if (strlen($prefix) > 0) {
            if ($str[0] == '/' || $str[0] == '\\') {
                $str = substr($str, 1);
            }
            $ary = explode('/', $prefix . '/' . $str);
        } else {
            $ary = explode('/', $str);
        }
        return implode(SEP, $ary);
    }
}

if (!function_exists('arrayToXml')) {
    /**
     * Transform an associative array into a simple XML document.
     *
     * @param array   $ary   An associative (key value pair) array to transform into an XML document.
     * @param boolean $start Should the starting tag be included; default to true.
     * 
     * @return void
     */
    function arrayToXml($ary, $start =  true) {
        $xml = '';
        if ($start) {
            $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        }
        foreach ($ary as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $xml .= '<' . $key . '>';
                $xml .= arrayToXml($val, false);
                $xml .= '</' . $key . '>';
            } else if (is_numeric($val)) {
                $xml .= '<' . $key . ' number="true">' . $val . '</' . $key . '>';
            } else if (is_bool($val)) {
                $xml .= '<' . $key . ' boolean="true">' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }
        return $xml;
    }
}

if (!function_exists('getConstant')) {
    /**
     * Return the value of a PHP constant or default to a set value.
     *
     * @param string  $name    The name of the constant.
     * @param boolean $default The default value to use if the constant does not exist.
     * 
     * @return void
     */
    function getConstant($name, $default = false) {
        if (defined($name)) {
            return constant($name);
        }
        return $default;
    }
}

if (!function_exists('outputResponse')) {
    /**
     * Output a message to the user in the correct format (HTML, JSON, XML) and
     * set the appropriate HTTP status code.
     *
     * @param mixed   $data       The data to include in the response.
     * @param integer $statusCode The HTTP status code to set for this response.
     *                            Warning this defaults to a 500 server error if omitted.
     * 
     * @return void
     */
    function outputResponse($data = '', $statusCode = 500) {
        http_response_code($statusCode);

        if (empty($data)) {
            echo '';
        } else {
            switch(responseType()) {
                case 'JSON':
                    header('Content-Type: application/json');
                    if (!is_array($data) && !is_object($data)) {
                        $data = [
                            'message' => $data
                        ];
                    }
                    echo json_encode($data);
                    break;
                case 'XML':
                    header('Content-Type: application/xml');
                    if (!is_array($data) && !is_object($data)) {
                        $data = [
                            'message' => $data
                        ];
                    }
                    // Wrap in root element.
                    $data = [
                        'response' => $data
                    ];
                    // Convert array to XML.
                    echo arrayToXml($data);
                    break;
                default:
                    if (is_array($data) || is_object($data)) {
                        header('Content-Type: application/json');
                        echo json_encode($data);
                    } else {
                        header('Content-Type: text/html');
                        echo $data;
                    }
            }
        }

        exit();
    }
}

if (!function_exists('requireToBeLoggedIn')) {
    function requireToBeLoggedIn() {
        global $Session;
        if (!isset($Session->userId) && !isset($Session->loggedIn)) {
            header('Location: ' . SITE_ROOT);
            exit();
        }
        if ($Session->loggedIn !== true) {
            header('Location: ' . SITE_ROOT);
            exit();
        }
        global $Database;
        $db = $Database->connect();
        $result = $db->query("SELECT u.id, u.email, up.first_name AS fname, up.middle_name AS mname, up.last_name AS lname, up.vanity, up.vanity_set_date AS vanityChanged, up.profile_picture AS profilePicture FROM users AS u LEFT JOIN users_profile AS up ON u.id=up.id;");
        $result = $result->fetch_assoc();
        $db->close();
        return (object) $result;
    }
}

if (!function_exists('siteRoot')) {
    /**
     * Determine the sites root URL. Normally this is just the domain name but
     * it may include a full path to several subdirectories as well. It will
     * always include at least the `public` directory. 
     *
     * @return string The sites root URL not including `public`.
     */
    function siteRoot() {
        // Determine protocol.
        $protocol = 'https';
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $protocol = $_SERVER['REQUEST_SCHEME'];
        }
        $protocol .= '://';
        // Determine the host.
        $host = 'localhost';
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        $host .= '/';
        // Determine any additional path.
        $path = $_SERVER['PHP_SELF'];
        $path = htmlspecialchars(substr($path, 0, stripos($path, 'public/')));
        // Sites root URL not including the public directory.
        return $protocol . $host . $path;
    }
}

if (!function_exists('responseType')) {
    /**
     * Check the $_SERVER headers to determine the response type the client is
     * expecting back: HTML, JSON, or XML.
     *
     * @return string The expected response type: HTML, JSON, or XML.
     */
    function responseType() {
        $accept = '';
        if (isset($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_ACCEPT'])) {
            $accept = $_SERVER['HTTP_ACCEPT'];
        } else if (isset($_SERVER['ACCEPT']) && !empty($_SERVER['ACCEPT'])) {
            $accept = $_SERVER['ACCEPT'];
        }
        $accept = strtoupper($accept);
        if (strlen($accept) > 0) {
            if (stripos($accept, 'JSON') !== false) {
                return 'JSON';
            }
            if (stripos($accept, 'XML') !== false) {
                return 'XML';
            }
        }
        return 'HTML';
    }
}

if (!function_exists('getUserObj')) {
    function getUserObj() {
        global $Session;
        if (!isset($Session->userId) && !isset($Session->loggedIn)) {
            outputResponse('You are not authenticated!', 400);
        }
        global $Database;
        $db = $Database->connect();
        $result = $db->query("SELECT u.id, u.email, up.first_name AS fname, up.middle_name AS mname, up.last_name AS lname, up.vanity, up.vanity_set_date AS vanityChanged, up.profile_picture AS profilePicture FROM users AS u LEFT JOIN users_profile AS up ON u.id=up.id;");
        $result = $result->fetch_assoc();
        $db->close();
        return (object) $result;
    }
}