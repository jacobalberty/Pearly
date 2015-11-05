<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

use Pearly\Factory\ControllerFactory;
use Pearly\Factory\ViewFactory;
use Pearly\Session;
use Pearly\Model\Type;

/**
 * Router class to choose the proper controller/view.
 */
class Router extends Base
{
    /**
     * Authorization list call.
     *
     * This function is used for the authorization subsystem.
     *
     * @return array an array containing the authorization list for each view/controller to indicate authorized or not.
     */
    private function getAuth()
    {
        $factoryclass = "\\{$this->registry->pkg}\\Factory\\AuthFactory";

        if (class_exists($factoryclass)) {
            $af = new $factoryclass();
            $auth = $af->build($this->registry);
            $this->registry->auth = $auth;

            return $auth->getPerms();
        }

        $authclass = "\\{$this->registry->pkg}\\Core\\{$this->registry->auth}Auth";
        if (class_exists($authclass)) {
            $auth = new $authclass($this->registry);
            $this->registry->auth = $auth;
            return $auth->getPerms();
        }
        return [
            'default' => false,
            'viewdefault' => true,
        ];
    }

    /**
     * Function to launch the Router.
     *
     * This function does all of the initialization.
     * Including:
     * <ol>
     * <li>Debug Initialization.</li>
     * <li>Session Configuration.</li>
     * <li>Type Initialization.</li>
     * <li>Controller routing and redirection.</li>
     * <li>View routing.</li>
     * </ol>
     * Eventually this will be broken up into seperate functions.
     */
    public function invoke()
    {
        // Debug Initialization.
        if ($this->registry->debug) {
            ini_set('display_errors', 'On');
            ini_set('xdebug.show_local_vars', 'On');
            ini_set('xdebug.dump.SERVER', 'HTTP_HOST, SERVER_NAME');
            ini_set('xdebug.dump_globals', 'On');
            ini_set('xdebug.collect_params', '4');
            ini_set('xdebug.remote_host', $_SERVER['REMOTE_ADDR']);
            ini_set('xdebug.remote_port', '9000');
            ini_set('xdebug.remote_handler', 'dbgp');
            ini_set('xdebug.remote_enable', 'On');
            ini_set('xdebug.default_enable', 1);
        }

        // Session Configuration.
        Session::setName(hash('crc32', CORE_PATH.'/'.$this->registry->cfg));

        Type::setRegistry($this->registry);

        // Type initialization.
        Type::addType("\\Pearly\\Model\\Type\\stringType");

        $auth = $this->getAuth();

        // Controller Routing and Redirection.
        if (!empty($_POST['controller'])) {
            $action = \Http::post('action');
            $cf = new ControllerFactory($this->registry, \Http::post('controller'), $auth);
            $cinst = $cf->build();

            $f_arr = explode(';', $action);
            $aname = array_shift($f_arr);
            $url = $cinst->invoke($aname, $f_arr);
            if (mb_substr($url, 0, 1) == '?') {
                $parray = $_POST;
                $preg_grep_keys = function ($pattern, $input, $flags = 0)
                    {
                        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
                    };
                $parray = $preg_grep_keys('/^_[a-zA-Z0-9]/', $parray);

                $url = '?' . \Http::mquery(mb_substr($url, 1), '&', $parray);
            }
            if (!empty($url)) {
                require 'html/redirect.en-us.php';
            }
            return;
        }

        $vf = new ViewFactory($this->registry, \Http::get('page', null), $auth);
        $view = $vf->build();
        $view->invoke();
    }

    /**
     * Get classes from directory function.
     *
     * This function searches a directory and returns a list of filenames
     * to be used for loading classes in the directory.
     * For example to initialize all types within the specified directory.
     *
     * @param string $dir The directory to search.
     *
     * @return array A list of filenames in the specified directory.
     */
    private function getClassesFromDir($dir)
    {
        $result = array();
        if (!is_readable($dir)) {
            return $result;
        }
        $files = scandir($dir);

        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $result[] = pathinfo($file, PATHINFO_FILENAME);
        }
        return $result;
    }
}
