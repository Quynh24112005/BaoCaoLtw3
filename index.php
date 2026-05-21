<?php
/**
 * HR Management System
 * Front Controller - Single entry point for all requests
 */

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
$baseUrl = ($baseDir === '/') ? '' : $baseDir;
define('BASE_URL', $baseUrl);

// Autoload core files
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Session.php';

// Start session
Session::start();

// Route the request
$router = new Router();
$router->dispatch();
