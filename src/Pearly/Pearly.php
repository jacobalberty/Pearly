<?php
/**
 * Pearly 1.0
 *
 * @author  Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly;

class Pearly
{
    public static function run()
    {
        if (get_magic_quotes_gpc()) {
            die('Please ensure magic_quotes_gpc is set to off in your php.ini');
        }

        if (ini_get('register_globals')) {
            die('Please ensure register_globals is set to off in your php.ini');
        }

        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');

        if (!defined('CORE_PATH')) {
            define('CORE_PATH', __DIR__);
        }

        set_error_handler('\Pearly\Pearly::exceptionErrorHandler', E_ALL);
        self::setTimeZone("America/Chicago");
        $router = new \Pearly\Core\Router();
        $router->invoke();

    }

    public static function exceptionErrorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
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
            if ($pos) {
                // When it is, it's in the "/usr/share/zoneinfo/" folder
                $timezone = substr($filename, $pos + strlen("zoneinfo/"));
            } else {
                // If not, bail
                $timezone = $default;
            }
        } else {
            // On other systems, like Ubuntu, there's file with the Olsen time
            // right inside it.
            $timezone = trim(file_get_contents("/etc/timezone"));
            if (!strlen($timezone)) {
                $timezone = $default;
            }
        }
        date_default_timezone_set($timezone);
    }

    public static function optimizejs($files, $tmpdir = '../tmp/', $lib_dir = '../3rdparty/gcc/')
    {
        /** @todo detect java to ensure this will run */
        $localcompile = is_readable("{$lib_dir}/compiler.jar");
        $closure = $localcompile;

        if ($closure) {
            $tmpdir = is_writable($tmpdir) ? $tmpdir : '/tmp/';
            $afiles = [];
            $mtime  = 0;
            foreach (explode('&', $files) as $part) {
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
            header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 5)));
            if (@strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime ||
            @trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
                header("HTTP/1.1 304 Not Modified");
            } else {
                readfile($cache_file);
            }
        } else {
            $jsfiles = array();
            foreach (explode('&', $files) as $part) {
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
                } else {
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
}
