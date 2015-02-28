<?php
/**
 * Vetmanage 2.0
 *
 * @author	Jacob Alberty <jacob.alberty@gmail.com>
 * @link	http://vetmanage.com
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
define('CORE_PATH', __DIR__);
/**
 * Path containing the php classes to load
 */
define('CLASS_DIR', CORE_PATH.'/class');
//set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);
//spl_autoload_register('spl_autoload');
require CORE_PATH.'/includes/SplClassLoader.php';
$classLoader = new SplClassLoader(null, CORE_PATH.'/class');
$classLoader->register();

$logger = new \Pearly\Core\Logger();

$eh = new \Pearly\Core\ExceptionHandler();
$eh->setLogger($logger);
$eh->register();

require CORE_PATH.'/includes/functions.inc.php';
set_error_handler("exception_error_handler", E_ALL);
setTimeZone("America/Chicago");
$router = new \Pearly\Core\Router();
$router->invoke();
