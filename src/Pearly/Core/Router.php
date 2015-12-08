<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

use Pearly\Factory\ControllerFactory;
use Pearly\Factory\LoggerFactory;
use Pearly\Factory\ViewFactory;
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
            $auth = $factoryclass::build($this->registry);
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
     * <li>Initialize Logger.</li>
     * <li>Exception Handling.</li>
     * <li>Session Configuration.</li>
     * <li>Type Initialization.</li>
     * <li>Controller routing and redirection.</li>
     * <li>View routing.</li>
     * </ol>
     * Eventually this will be broken up into seperate functions.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function invoke()
    {
        $serverRequest = $this->registry->serverRequest;
        // Debug Initialization.
        if ($this->registry->debug) {
            $server = $serverRequest->getServerParams();
            ini_set('display_errors', 'On');
            ini_set('xdebug.show_local_vars', 'On');
            ini_set('xdebug.dump.SERVER', 'HTTP_HOST, SERVER_NAME');
            ini_set('xdebug.dump_globals', 'On');
            ini_set('xdebug.collect_params', '4');
            ini_set('xdebug.remote_host', $server['REMOTE_ADDR']);
            ini_set('xdebug.remote_port', '9000');
            ini_set('xdebug.remote_handler', 'dbgp');
            ini_set('xdebug.remote_enable', 'On');
            ini_set('xdebug.default_enable', 1);
        }

        // Initialize Logger.
        LoggerFactory::$registry = $this->registry;

        // Exception Handling.
        $exh = new \Pearly\Core\ExceptionHandler();
        $exh->setLogger(LoggerFactory::build());
        $exh->register();

        // Session Configuration.
        $this->registry->sessionName = hash('crc32', CORE_PATH.'/'.$this->registry->cfg);

        Type::setRegistry($this->registry);

        // Type initialization.
        Type::addType("\\Pearly\\Model\\Type\\stringType");

        $auth = $this->getAuth();

        // Controller Routing and Redirection.
        $post = $serverRequest->getParsedBody();
        if (!empty($post['controller'])) {
            $action = \Http::valueFrom($post, 'action');
            $cinst = ControllerFactory::build($this->registry, \Http::valueFrom($post, 'controller'), $auth);

            $f_arr = explode(';', $action);
            $aname = array_shift($f_arr);
            $url = $cinst->invoke($aname, $f_arr);
            if (mb_substr($url, 0, 1) == '?') {
                $parray = $post;
                $parray = $this->preg_grep_keys('/^_[a-zA-Z0-9]/', $parray);

                $url = '?' . \Http::mquery(mb_substr($url, 1), '&', $parray);
            }
            if (!empty($url)) {
                require 'html/redirect.en-us.php';
            }
            return;
        }

        $view = ViewFactory::build($this->registry, \Http::valueFrom($serverRequest->getQueryParams(), 'page', null), $auth);
        $view->invoke();
    }

    /** @ignore */
    private function preg_grep_keys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }
}
