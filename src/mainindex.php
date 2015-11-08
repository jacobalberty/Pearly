<?php
/**
 * Pearly 1.0
 *
 * @author	Jacob Alberty <jacob.alberty@gmail.com>
 */
if (get_magic_quotes_gpc()) {
    die('Please ensure magic_quotes_gpc is set to off in your php.ini');
}

if (ini_get('register_globals')) {
    die('Please ensure register_globals is set to off in your php.ini');
}

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

/**
 * Path to root installation directory
 */
if (!defined('CORE_PATH')) {
    define('CORE_PATH', __DIR__);
}
include_once './vendor/autoload.php';

set_error_handler("exception_error_handler", E_ALL);
$router = new \Pearly\Core\Router();
$router->invoke();
