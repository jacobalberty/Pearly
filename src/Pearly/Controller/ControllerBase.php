<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Controller;

use Pearly\Core\IRegistry;
use Pearly\Core\Base;
use Pearly\Core;
use Pearly\Session;
use Psr\Log\LoggerInterface;

/**
 * Base class for controllers.
 *
 * This class is used to handle posting data to the database.
 * The public functions are called to handle the data.
 */
abstract class ControllerBase extends Base implements IController
{
    /** @var \Psr\Log\LoggerInterface A PSR-3 compliant logger interface to send messages to */
    protected $logger;

    /** @var boolean This var determines if access to the controller is authorized or not */
    protected $authorized;

    /** @var string Url to go back */
    protected $back;

    /** @var \Pearly\Factory\DAOFactory Used to build new Data Access Objects */
    private $daofactory;

    /**
     * Constructor function.
     *
     * @param \Pearly\Core\IRegistry   $registry Registry object to use for this request.
     * @param array                    $auth     Array containing acl for views and controllers.
     * @param \Psr\Log\LoggerInterface $logger   LoggerInterface to use for this instance.
     */
    public function __construct(IRegistry &$registry = null, array $auth = array(), LoggerInterface $logger = null)
    {
        $this->logger = is_null($logger) ? \Pearly\Factory\LoggerFactory::build() : $logger;
        $classname = get_class($this);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }
        $this->authorized = (isset($auth['default']) && is_bool($auth['default']))
            ? $auth['default'] : $this->authorized;
        $this->authorized = (isset($auth['controllerdefault']) && is_bool($auth['controllerdefault']))
            ? $auth['controllerdefault'] : $this->authorized;
        $this->authorized = (isset($auth[$classname]) && is_bool($auth[$classname]))
            ? $auth[$classname] : $this->authorized;

        $this->back = '?' . $_SERVER['QUERY_STRING'];

        parent::__construct($registry);

        $this->daofactory = new \Pearly\Factory\DAOFactory($this->registry);
    }

    /**
     * Authorization function.
     *
     * This function ensures you are authorized to access the object.
     * If you are authorized it executes it, if not it aborts the query.
     * $action will be mapped to {$action}Action in the current class
     * then checked if it is a public function and then finally checked
     * against the authorization array before it is called with the
     * contents of $_POST as the first parameter and any parameters
     * passed in $params given as additional parameters to the function.
     *
     * @param string $action The action to be executed.
     * @param array  $params Optional array of parameters ot be passed.
     *
     * @throws \ErrorException if another \ErrorException is caught and debug mode is on.
     *
     * @return string a URL to redirect to.
     */
    public function invoke($action, array $params)
    {
        $fname = "{$action}Action";
        if (!method_exists($this, $fname)) {
            $this->addMessage("Action: '{$action}' does not exist");
            $this->logger->error("Non-existant action: '{$action}' called in class: ".get_class($this));
            return $this->back;
        }
        $check = new \ReflectionMethod($this, $fname);

        if (!$check->isPublic()) {
            $this->addMessage('You are not authorized to perform that action at this time');
            $this->logger->error('Non-public function passed to doAction');
            return $this->back;
        }
        if (!$this->authorized) {
            $this->addMessage('You are not authorized to perform that action at this time');
            return $this->back;
        }
        try {
            if (is_callable(array($this,'create'))) {
                $this->create();
            }
            $_POST['__NAME__'] = '$_POST';
            $url = call_user_func_array(array($this, $fname), array_merge(array($_POST), $params));
        } catch (\ErrorException $e) {
            if (is_callable(array($this, 'exceptionMessage'))) {
                $excM = $this->exceptionMessage($e);
            } else {
                $excM = 'Caught Error: '.$e->getMessage();
            }
            $this->addMessage('['.strftime('%c')."] {$excM}\n");
            $this->logger->error(
                "Caught Error: '" . $e->getMessage()
                . "' In file: '" . $e->getFile()
                . "' On Line: '" . $e->getLine()
                . "' with controller='" . get_class($this)
                . "' and action='${fname}' "
                . "and _POST=".var_export($_POST, true)
            );

            $url = $this->back;

            /** @todo Refactor this */
            $parray = $_POST;
            $preg_grep_keys = function ($pattern, $input, $flags = 0) {
                
                    return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
            };
            $parray = $preg_grep_keys('/^_[a-zA-Z0-9]/', $parray);
            $query = parse_url($url, PHP_URL_QUERY);
            if ($query) {
                $url = '?' . \Http::mquery($query, '&', $parray);
            }

            if ($this->registry->debug) {
                throw new \ErrorException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e->getSeverity(),
                    $e->getFile(),
                    $e->getLine(),
                    $e
                );
                $url = null;
            }
        } catch (Core\ValidationException $e) {
            foreach ($e->getMessages() as $message) {
                if ($e->getCode() == 1) {
                    // Log the validation error
                    $this->logger->warning("Validation Error in '".get_class($this)."': {$message}");
                }
                $this->addMessage("Validation Error: {$message}");
            }
            $url = $this->back;
        }
        return $url;
    }

    /**
     * Add Message.
     *
     * This function allows controller actions to add messages to be displayed by the next view accessed.
     *
     * @param string $message The message add to the list.
     */
    protected function addMessage($message)
    {
        $messages = new Session\Member('messages', $this->registry);
        $messages[] = $message;
    }

    /**
     * Add Message.
     *
     * This function works identically to ControllerBase::addMessage .
     * except that it takes an array containing multiple messages to be displayed.
     *
     * @param array $messages An array containing multiple messages.
     *
     * @see ControllerBase::addMessage
     */
    protected function addMessages(Array $messages)
    {
        $smessages = new Session\Member('messages', $this->registry);
        $smessages->applySet('array_merge', $messages);
    }

    /**
     * Get DAO function.
     *
     * @param string $type
     *
     * @return \Pearly\Model\IDAO
     */
    protected function getDAO($type)
    {
        return $this->daofactory->build($type);
    }
}
