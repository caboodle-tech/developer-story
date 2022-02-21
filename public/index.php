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

$Session->start();

$Router->route('', 'Controller\Core\Page\Home');
$Router->route('/', 'Controller\Core\Page\Home');
$Router->route('join', 'Controller\Core\Page\Join');
$Router->route('logout', 'Controller\Core\Page\Logout');
$Router->route('form/*', 'Controller\Core\Form');
// $Router->route('cv/:vanity', 'Controller\Core');
// $Router->route('story/:vanity', 'Controller\Core');
// $Router->route('user/:id', 'Controller\Core');

// print_r($Router->getRoutingTable());

$Router->navigate();