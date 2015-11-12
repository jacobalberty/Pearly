<?php
$included = include file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : __DIR__ . '/../../../autoload.php';

include_once "jsfunctions.php";

if (! $included) {
    header('Content-Type: text/javascript');
    echo "alert('You must set up the project dependencies, run the following commands:\\n"
       . "curl -sS https://getcomposer.org/installer | php\\n"
       . "php composer.phar install')";

    exit(1);
}

if (PHP_SAPI == 'cli') {
    if ($argc < 2) {
        die("must be called like: {$argv[0]} <query>" . PHP_EOL);
    }
    $files = $argv[1];
    $tmpdir = isset($argv[2]) ? $argv[2] : '../tmp/';
    $lib_dir = isset($argv[3]) ? $argv[3] : '../3rdparty/gcc/';
    ob_start();
    optimizejs($files, $tmpdir, $lib_dir);
    ob_end_clean();
} else {
    $files = $_SERVER['QUERY_STRING'];
    optimizejs($files);
}
