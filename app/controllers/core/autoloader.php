<?php
/**
 * The Dev Story class autoloader.
 */

namespace Controller\Core;

class Autoloader {

    /**
     * Create the absolute file path to a class based on its namespace.
     *
     * @param string $class The namespace and name of a class.
     * 
     * @return string The absolute file path to a class.
     */
    public function getPath($class) {
        $class = strtolower($class);
        $parts = explode('\\', $class);
        $base  = '';
        switch($parts[0]) {
            case 'controller':
            case 'controllers':
                $base  = 'app/controllers';
                $parts = array_slice($parts, 1);
                break;
            // Should be avoided but may be needed in the future.
            case 'include':
            case 'includes':
                $base  = 'app/includes';
                $parts = array_slice($parts, 1);
                break;
            case 'module':
            case 'modules':
                $base  = 'app/modules';
                $parts = array_slice($parts, 1);
                break;
            // Not currently used but added in case we use outside libraries.
            default:
                $base = 'app/vendors';
        }
        return absPath($base . SEP . implode(SEP, $parts)) . '.php';
    }
    
    /**
     * Attempt to autoload a class. This is what should be registered with PHP's
     * spl_autoload_register function.
     * 
     * @param string $class The namespace and name of a class to attempt to load.
     * 
     * @return boolean True when the class was loaded and false otherwise.
     */
    public static function load($class) {
        $path = self::getPath($class);
        if (file_exists($path)) {
            // phpcs:ignore PEAR.Files.IncludingFile.UseInclude
            require $path;
            return true;
        }
        return false;
    }

}

// Go ahead and register the autoloader here. It's clean and out of the way.
spl_autoload_register("Controller\Core\Autoloader::load");