<?php
include_once "../vendor/autoload.php";
include_once "jsfunctions.php";
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
