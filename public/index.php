<?php
/**
 * Turn the lights on.
 */

require '../config.php';
require '../app/includes/helpers.php';
require '../app/controllers/core/autoloader.php';

define('ROOT', dirname(__DIR__));
define('SEP', DIRECTORY_SEPARATOR);

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', siteRoot());
}

$Database = new Controller\Core\Database();
$Page     = new Controller\Core\Page();
$Router   = new Controller\Core\Router();
$Sanitize = new Module\Core\Sanitizer();
$Session  = Controller\Core\Session::getInstance();

$Router->route('', 'Controller\Core\Page\Home');
$Router->route('/', 'Controller\Core\Page\Home');
$Router->route('dashboard', 'Controller\Core\Page');
$Router->route('form/*', 'Controller\Core\Form');
$Router->route('join', 'Controller\Core\Page');
$Router->route('login', 'Controller\Core\Page\Login');
$Router->route('logout', 'Controller\Core\Page\Logout');
$Router->route('user', 'Controller\Core\Page\User');
// $Router->route('cv/:vanity', 'Controller\Core');
// $Router->route('story/:vanity', 'Controller\Core');
// $Router->route('user/:id', 'Controller\Core');

// print_r($Router->getRoutingTable());
$Session->start();

$Router->navigate();