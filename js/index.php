<?php
define('CORE_PATH', '../');
require CORE_PATH.'/includes/SplClassLoader.php';
$classLoader = new SplClassLoader(null, CORE_PATH.'/class');
$classLoader->register();

if (PHP_SAPI == 'cli') {
    if ($argc < 2) {
        die("must be called like: {$argv[0]} <query>" . PHP_EOL);
    }
    $files = $argv[1];
    $tmpdir = isset($argv[2]) ? $argv[2] : '../tmp/';
    $lib_dir = isset($argv[3]) ? $argv[3] : '../3rdparty/php-closure/lib/';
    ob_start();
    optimizejs($files, $tmpdir, $lib_dir);
    ob_end_clean();
} else {
    $files = $_SERVER['QUERY_STRING'];
    optimizejs($files);
}

function optimizejs($files, $tmpdir = '../tmp/', $lib_dir = '../3rdparty/php-closure/lib/') {
    define('LIB_DIR', $lib_dir);
    $closurelib = "${lib_dir}third-party/php-closure.php";

    $closure = is_readable($closurelib);

    /** @todo detect java to ensure this will run */
    $localcompile = is_readable("{$lib_dir}third-party/compiler.jar");

    if ($closure) {
        $tmpdir = is_writable($tmpdir) ? $tmpdir : '/tmp/';
        include($closurelib);
        $c = new PhpClosure();
        foreach(explode('&', $files) as $part) {
            $part = explode('=', $part);
            if ($key = array_shift($part)) {
                if (empty($part)) {
                    $c->add("{$key}.js");
                }
            }
        }
        if ($localcompile) {
            $c->localCompile();
        }
        $c->cacheDir($tmpdir)
            ->quiet()
//            ->whitespaceOnly()
            ->simpleMode();
        if ($c->_isRecompileNeeded($c->_getCacheFileName()) && !(PHP_SAPI === 'cli')) {
            $log = new \Pearly\Core\Logger();
            $log->warning("Recompiling: {$files}");
        }
        $c->write();
    } else {
        $jsfiles = array();
        foreach(explode('&', $files) as $part) {
            $part = explode('=', $part);
            if ($key = array_shift($part)) {
                if (empty($part)) {
                    $jsfiles[] = $key;
                }
            }
        }

        $js = '';        // code to compress
        $err = '';       // error string
        $jscomp = '';    // Final code
        // fetch JavaScript files
        for ($i = 0, $j = count($jsfiles); $i < $j; $i++) {
            $fn = $jsfiles[$i] . '.js';
            $jscode = @file_get_contents($fn);
            if ($jscode !== false) {
                $js .= "{$jscode}\n";
            }  else {
                $err .= $fn . '; ';
            }
        }

        if ($err != '') {
            // error: missing files
            $jscomp = "alert('The following JavaScript files could not be read:\\n{$err}');";
        }
        if ($js != '') {
            $jscomp .= $js;
        }
        // output content
        header('Content-type: text/javascript');
        echo $jscomp;
    }
}
