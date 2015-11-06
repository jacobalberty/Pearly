<?php
include_once "../vendor/autoload.php";
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

function optimizejs($files, $tmpdir = '../tmp/', $lib_dir = '../3rdparty/gcc/') {
    /** @todo detect java to ensure this will run */
    $localcompile = is_readable("{$lib_dir}/compiler.jar");
    $closure = $localcompile;

    if ($closure) {
        $tmpdir = is_writable($tmpdir) ? $tmpdir : '/tmp/';
        $afiles = [];
        $mtime  = 0;
        foreach(explode('&', $files) as $part) {
            $part = explode('=', $part);
            if ($key = array_shift($part)) {
                if (empty($part)) {
                    $kfn = "{$key}.js";
                    $afiles[] = $kfn;
                    $cfmtime = filemtime($kfn);
                    if ($cfmtime > $mtime) {
                        $mtime = $cfmtime;
                    }
                }
            }
        }
        $hfname = hash('md5', implode($afiles), false) . '.js';
        $cache_file = "{$tmpdir}/{$hfname}";
        if (!is_readable($cache_file) || $mtime > filemtime($cache_file)) {
            $c = new tureki\PhpCc([
                'java_file'    => '/usr/bin/java -server -XX:+TieredCompilation',
                'jar_file'     => "{$lib_dir}/compiler.jar",
                'output_path'  => $tmpdir,
                'optimization' => 'SIMPLE_OPTIMIZATIONS',
                'sort'         => false,
            ]);
            $c->add($afiles);
            $c->exec($hfname);
        }
        $cache_mtime = filemtime($cache_file);
        $etag = md5_file($cache_file);
        header('Content-Type: text/javascript');
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $cache_mtime)." GMT");
        header("Etag: \"{$etag}\"");
        if (
            @strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime ||
            @trim(@$_SERVER['HTTP_IF_NONE_MATCH']
        ) == $etag) {
            header("HTTP/1.1 304 Not Modified");
        } else {
            readfile($cache_file);
        }
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
