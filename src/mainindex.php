<?php
/**
 * Pearly 1.0
 *
 * @author  Jacob Alberty <jacob.alberty@gmail.com>
 */
if (get_magic_quotes_gpc()) {
    die('Please ensure magic_quotes_gpc is set to off in your php.ini');
}

if (ini_get('register_globals')) {
    die('Please ensure register_globals is set to off in your php.ini');
}

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$included = include file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : __DIR__ . '/../../../autoload.php';

if (! $included) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL
       . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
       . 'php composer.phar install' . PHP_EOL;

    exit(1);
}

include_once 'mainconst.php';

set_error_handler("exception_error_handler", E_ALL);
setTimeZone("America/Chicago");
$router = new \Pearly\Core\Router();
$router->invoke();
