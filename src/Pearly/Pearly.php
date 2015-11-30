<?php
/**
 * Pearly 1.0
 *
 * @author  Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly;

class Pearly
{
    public static function run($dir = __DIR__)
    {
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');

        if (!defined('CORE_PATH')) {
            define('CORE_PATH', $dir);
        }

        set_error_handler('\Pearly\Pearly::exceptionErrorHandler', E_ALL);
        self::setTimeZone("America/Chicago");
        $router = new \Pearly\Core\Router();
        $router->invoke();

    }

    public static function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    private static function setTimezone($default)
    {
        $timezone = "";

        // On many systems (Mac, for instance) "/etc/localtime" is a symlink
        // to the file with the timezone info
        if (is_link("/etc/localtime")) {
            // If it is, that file's name is actually the "Olsen" format timezone
            $filename = readlink("/etc/localtime");

            $pos = strpos($filename, "zoneinfo");
            $timezone = $default;
            if ($pos) {
                // When it is, it's in the "/usr/share/zoneinfo/" folder
                $timezone = substr($filename, $pos + strlen("zoneinfo/"));
            }
            date_default_timezone_set($timezone);
            return;
        }
        // On other systems, like Ubuntu, there's file with the Olsen time
        // right inside it.
        $timezone = trim(file_get_contents("/etc/timezone"));
        if (!strlen($timezone)) {
            $timezone = $default;
        }
        date_default_timezone_set($timezone);
    }

    public static function optimizejs($files, $tmpdir = '../tmp/', $lib_dir = '../3rdparty/gcc/')
    {
        /** @todo detect java to ensure this will run */
        $localcompile = is_readable("{$lib_dir}/compiler.jar");
        $closure = $localcompile;

        if ($closure) {
            return self::closurejs($files, $tmpdir, $lib_dir);
        }
        $jsfiles = array();
        foreach (explode('&', $files) as $part) {
            $part = explode('=', $part);
            if ($key = array_shift($part)) {
                if (empty($part)) {
                    $jsfiles[] = $key;
                }
            }
        }

        $jsc = '';        // code to compress
        $err = '';       // error string
        $jscomp = '';    // Final code
        // fetch JavaScript files
        for ($i = 0, $j = count($jsfiles); $i < $j; $i++) {
            $fname = $jsfiles[$i] . '.js';
            $jscode = @file_get_contents($fname);
            if ($jscode !== false) {
                $jsc .= "{$jscode}\n";
                continue;
            }
            $err .= $fname . '; ';
        }

        if ($err != '') {
            // error: missing files
            $jscomp = "alert('The following JavaScript files could not be read:\\n{$err}');";
        }
        if ($jsc != '') {
            $jscomp .= $jsc;
        }
        // output content
        header('Content-type: text/javascript');
        echo $jscomp;
    }

    private static function doRebuild($afiles, $tmpdir, $mtime, $lib_dir)
    {
        $hfname = hash('md5', implode($afiles), false) . '.js';
        $cache_file = "{$tmpdir}/{$hfname}";
        if (!is_readable($cache_file) || $mtime > filemtime($cache_file)) {
            $phpcc = new \tureki\PhpCc([
                'java_file'    => '/usr/bin/java -server -XX:+TieredCompilation',
                'jar_file'     => "{$lib_dir}/compiler.jar",
                'output_path'  => $tmpdir,
                'optimization' => 'SIMPLE_OPTIMIZATIONS',
            ]);
            $phpcc->add($afiles);
            $ret = $phpcc->exec($hfname);
            if ($ret['status'] !== 0) {
                foreach ($ret['out'] as $out) {
                    echo "console.error('" . str_replace("'", "\\'", $out) . "');" . PHP_EOL;
                }
            }
        }
        return $cache_file;
    }

    private static function closurejs($files, $tmpdir, $lib_dir)
    {
        $tmpdir = is_writable($tmpdir) ? $tmpdir : '/tmp/';
        $afiles = [];
        $mtime  = 0;
        foreach (explode('&', $files) as $key) {
            if (!strpos($key, '=')) {
                $kfn = "{$key}.js";
                $afiles[] = $kfn;
                $cfmtime = filemtime($kfn);
                if ($cfmtime > $mtime) {
                    $mtime = $cfmtime;
                }
            }
        }
        header('Content-Type: text/javascript');
        ob_start();
        $cache_file = self::doRebuild($afiles, $tmpdir, $mtime, $lib_dir);
        if (is_readable($cache_file)) {
            $cache_mtime = filemtime($cache_file);
            $etag = md5_file($cache_file);
            header("Last-Modified: ".gmdate("D, d M Y H:i:s", $cache_mtime)." GMT");
            header("Etag: \"{$etag}\"");
            if (@strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime ||
            @trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
                header("HTTP/1.1 304 Not Modified");
                return;
            }
            readfile($cache_file);
        }
        ob_end_flush();
    }
}
