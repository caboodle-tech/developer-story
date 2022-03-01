<?php
/**
 * The Router for the Dev Story application.
 */

namespace Controller\Core;

class Router {

    private $appUrl = '';    
    private $reqUrl = '';
    private $routes;

    /**
     * Creates a new instance of the Router and records the request page.
     */
    public function __construct() {
        $this->reqUrl = $_SERVER['REQUEST_URI'];
        $this->appUrl = $_GET['p'];
        $this->routes = (object) [];
        // Remove the initial forward slash form the URI: /page => page
        if ($this->reqUrl[0] === '/') {
            $this->reqUrl = substr($this->reqUrl, 1);
        }
    }

    /**
     * Getter to retrieve the applications URL; everything from ?p=...
     *
     * @return string The application specific URL.
     */
    public function getAppUrl() {
        return $this->appUrl;
    }

    /**
     * Getter to retrieve the originally requested URL; full original request.
     *
     * @return string The full originally requested URL.
     */
    public function getRequestUrl() {
        return $this->reqUrl;
    }

    /**
     * Creates a regular expression that matches this specific URL parts.
     *
     * @param array $parts An array of URL parts to transform into a matching RegExp.
     * 
     * @return string The RegExp string for this URL.
     */
    private function getRouteRegexp($parts) {
        $regex = '/^';
        foreach ($parts as $part) {
            if (strpos($part, '*') !== false) {
                $regex .= '.*';
            } else if (isset($part[0]) && $part[0] === ':') {
                $regex .= '[\w\d\s\-\_]{1,}?';
            } else {
                $regex .= $part;
            }
            $regex .= '\/';
        }
        $regex = substr($regex, 0, strlen($regex) - 2) . '$/';
        return $regex;
    }

    /**
     * Getter to retrieve the applications current routing table.
     *
     * @return object The current routing table; sorted into routing lengths.
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Getter to retrieve or immediately print the current routing table.
     * 
     * This differs from getRoutes because it converts the routing table into a
     * string and places it inside pre tags for outputting.
     *
     * @param boolean $print Should the result be printed instead of returned; default false.
     * 
     * @return string|void
     */
    public function getRoutingTable($print = false) {
        ob_start();
        echo '<pre>';
        print_r($this->routes);
        echo '</pre>';
        $table = ob_get_clean();
        if ($print === true) {
            echo $table;
        } else {
            return $table;
        }
    }

    /**
     * Attempt to navigate to the requested page or resource. If a matching route
     * is found, the routes controller is instantiated and the render method is called.
     *
     * @param string $uri The application URL the user was attempting to navigate to.
     * 
     * @return void
     */
    public function navigate($uri = null) {
        if (!$uri) {
            $uri = $this->appUrl;
        }
        $parts = explode('/', $uri);
        if (strpos($uri, '*') === false) {
            $index = 'R' . count($parts);
        }
        // Do we have any routes that match this routes length?
        if ($this->routes->$index) {
            // Yes. Check each route with the same length in the table...
            foreach ($this->routes->$index as $route) {
                // Against each routes RegExp. Do we have a match?
                if (preg_match($route->regex, $uri)) {
                    // Yes. Instantiate its controller and call the render method.
                    $parsed = $this->parseUrl($route->parts, $parts);
                    // Pass the render method all the data it might want or need.
                    $controller = new $route->controller(
                        (object) [
                            'appUrl'  => $this->appUrl,
                            'reqUrl'  => $this->reqUrl,
                            'trimUrl' => $parsed->trimUrl,
                            'params'  => $parsed->params
                        ]
                    );
                    $controller->render();
                    return;
                }
            }
        }
        // No match found.
        // TODO: Add the 404 page logic into the class and then load the page here.
        echo '404';
    }

    /**
     * Some routes are Express like where parameters are part of the URL. Create a new
     * trimmed URL that contains only the URL parts with parameters separated out.
     *
     * @param array $routeParts The original URI parts registered with the router.
     * @param array $uriParts   The URI parts from the route the user just requested.
     * 
     * @return object
     */
    public function parseUrl($routeParts, $uriParts) {
        $trimmedUrl = '';
        $params     = (object) [];
        foreach ($routeParts as $index => $value) {
            if (isset($value[0]) && $value[0] === ':') {
                // This route part should be considered a parameter.
                $name          = substr($value, 1);
                $params->$name = $uriParts[$index];
            } else {
                $trimmedUrl .= $uriParts[$index] . '/';
            }
        }
        return (object) [
            'params'  => $params,
            'trimUrl' => substr($trimmedUrl, 0, strlen($trimmedUrl) - 1)
        ];
    }

    /**
     * Register a new route with the routing table.
     *
     * @param string $route      A URI to treat as a valid route.
     * @param string $controller The controller (including namespace) that will handle this route.
     * 
     * @return void
     */
    public function route($route, $controller) {
        // $index = 'ALL';
        $parts = explode('/', $route);
        // if (strpos($route, '*') === false) {
            $index = 'R' . count($parts);
        // }
        if (!isset($this->routes->$index)) {
            $this->routes->$index = [];
        }
        array_push(
            $this->routes->$index,
            (object) [
                'regex'      => $this->getRouteRegexp($parts),
                'route'      => $route,
                'parts'      => $parts,
                'controller' => $controller
            ]
        );
    }
}