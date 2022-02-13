<?php
/**
 * Turn the lights on.
 */

define('ROOT', dirname(__DIR__));
define('SEP', DIRECTORY_SEPARATOR);

require '../app/includes/helpers.php';
require '../app/controllers/core/autoloader.php';

$Router   = new Controller\Core\Router();
$Page     = new Controller\Core\Page();
$Sanitize = new Controller\Core\Sanitizer();

$Router->route('', 'Controller\Core\Page\Home');
$Router->route('/', 'Controller\Core\Page\Home');
$Router->route('join', 'Controller\Core\Page\Join');
$Router->route('form/*', 'Controller\Core\Form');
// $Router->route('cv/:vanity', 'Controller\Core');
// $Router->route('story/:vanity', 'Controller\Core');
// $Router->route('user/:id', 'Controller\Core');

// print_r($Router->getRoutingTable());

$Router->navigate();