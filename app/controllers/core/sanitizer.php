<?php

namespace Controller\Core;

class Sanitizer {

    /**
     * Ensures a given variable will be returned as a specific type.
     * Defaults to `string`.
     *
     * @param mixed  $var  Any type of data you wish to force into a specific type.
     * @param string $type (Types include: int, float, bool, object, array, string)
     *
     * @return mixed
     */
    public static function cast($var, string $type = 'string') {
        switch ($type) {
            case 'int':
                return (int)$var;
            case 'float':
                return (float)$var;
            case 'bool':
                return (bool)$var;
            case 'object':
                return (object)$var;
            case 'array':
                return (array)$var;
            default:
                return (string)$var; // string
        }
    }

    public static function default($string) {
        return htmlspecialchars(strip_tags(trim($string)));
    }

    public static function email($email) {
        $email = trim($email);

        // Test for the minimum length the email can be.
        if (strlen($email) < 6) {
            return false;
        }

        // Test for an @ character after the first position.
        if (strpos($email, '@', 1) === false) {
            return false;
        }

        // Split out the local and domain parts.
        list($local, $domain) = explode('@', $email, 2);
    
        // LOCAL PART:
        // Test for invalid characters.
        $local = preg_replace('/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]/', '', $local);
        if ('' === $local) {
            return false;
        }
    
        // DOMAIN PART:
        // Test for sequences of periods.
        $domain = preg_replace('/\.{2,}/', '', $domain);
        if ('' === $domain) {
            return false;
        }
    
        // Test for leading and trailing periods and whitespace.
        $domain = trim($domain, " \t\n\r\0\x0B.");
        if ('' === $domain) {
            return false;
        }
    
        // Split the domain into subs.
        $subs = explode('.', $domain);
    
        // Assume the domain will have at least two subs.
        if (count($subs) < 2) {
            return false;
        }
    
        // Create an array that will contain valid subs.
        $new_subs = array();
    
        // Loop through each sub.
        foreach ($subs as $sub) {
            // Test for leading and trailing hyphens.
            $sub = trim($sub, " \t\n\r\0\x0B-");
    
            // Test for invalid characters.
            $sub = preg_replace('/[^a-z0-9-]+/i', '', $sub);
    
            // If there's anything left, add it to the valid subs.
            if ('' !== $sub) {
                $new_subs[] = $sub;
            }
        }
    
        // If there are not 2 or more valid subs.
        if (count($new_subs) < 2) {
            return false;
        }
    
        // Join valid subs into the new domain.
        $domain = implode('.', $new_subs);
    
        // Put the email back together.
        $sanitized_email = $local . '@' . $domain;

        // One last check.
        return filter_var($sanitized_email, FILTER_SANITIZE_EMAIL);
    }

    public static function print($string, $filter = 'default') {
        if (strlen($string) === 0) {
            echo '';
            return;
        }
        $sanitized = $string;
        switch($filter) {
            default:
                $sanitized = self::default($string);
        }
        echo $sanitized;
    }

    public static function url($url) {
        $url = trim($url);

        // Test for the reasonable minimum length.
        if (strlen($url) < 10) {
            return false;
        }

        // Perform basic URL sanitization.
        $url = str_replace(' ', '%20', $url);
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);
        $url = str_replace(';//', '://', $url);

        // Test for the new reasonable minimum length; scheme could be missing.
        if (strlen($url) < 4) {
            return false;
        }

        /*
         * If the URL doesn't appear to contain a scheme, we presume
         * it needs https:// prepended (unless it's a relative link
         * starting with /, # or ?, or a PHP file).
         */
        if (strpos($url, ':') === false
            && ! in_array($url[0], array('/', '#', '?'), true)
            && ! preg_match('/^[a-z0-9-]+?\.php/i', $url)
        ) {
            $url = 'https://' . $url;
        }

        // One last check.
        return filter_var($url, FILTER_SANITIZE_URL);
    }

}