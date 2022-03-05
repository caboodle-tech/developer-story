<?php
/**
 * Turn the lights on.
 */

require '../config.php';
require '../app/includes/helpers.php';
require '../app/controllers/core/autoloader.php';

define('REQUEST_TYPE', strtoupper($_SERVER['REQUEST_METHOD']));
define('RESPONSE_TYPE', responseType());
define('ROOT', dirname(__DIR__));
define('SEP', DIRECTORY_SEPARATOR);

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', siteRoot());
}

$Page   = new Controller\Core\Page();
$Router = new Controller\Core\Router();

$Database = new Module\Core\Database();
$Sanitize = new Module\Core\Sanitizer();
$Session  = Module\Core\Session::getInstance();
$User     = new Module\Core\User();

$Router->route('', 'Controller\Core\Page\Home');
$Router->route('/', 'Controller\Core\Page\Home');
$Router->route('dashboard', 'Controller\Core\Page');
$Router->route('form/*', 'Controller\Core\Form');
$Router->route('api/*', 'Controller\Core\Api');
$Router->route('join', 'Controller\Core\Page');
$Router->route('login', 'Controller\Core\Page\Login');
$Router->route('logout', 'Controller\Core\Page\Logout');
// $Router->route('cv/:vanity', 'Controller\Core');
// $Router->route('story/:vanity', 'Controller\Core');
// $Router->route('user/:id', 'Controller\Core');

// print_r($Router->getRoutingTable());
$Session->start();

$Router->navigate();