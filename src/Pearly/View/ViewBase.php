<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

use \Pearly\Core\IRegistry;
use Pearly\Session;

/**
 * Base view class.
 *
 * This class is the base class that all views descend from.
 */
abstract class ViewBase extends \Pearly\Core\Base implements IView
{
    /** @var array This contains a list of messages passed from other components to be displayed in the view */
    protected $messages = array();

    /** @var boolean This var determines if access to the view is authorized or not */
    protected $authorized = false;

    /** @var \Pearly\Factory\DAOFactory The DAOFactory used to build new Data Access Objects */
    private $daofactory;

    /**
     * Constructor function
     *
     * @param \Pearly\Core\IRegistry $registry
     * @param Array $auth array containing acl for views and controllers.
     */
    public function __construct(IRegistry &$registry = null, array $auth = array())
    {
        parent::__construct($registry);

        $classname = get_class($this);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }
        $this->authorized = (isset($auth['default']) && is_bool($auth['default']))
            ? $auth['default']
            : $this->authorized;
        $this->authorized = (isset($auth['viewdefault']) && is_bool($auth['viewdefault']))
            ? $auth['viewdefault']
            : $this->authorized;
        $this->authorized = (isset($auth[$classname]) && is_bool($auth[$classname]))
            ? $auth[$classname]
            : $this->authorized;

        $messages = new Session\Member('messages');
        $this->messages = $messages->apply('array_merge', $this->messages);
        $messages->removeAll();

        $this->daofactory = new \Pearly\Factory\DAOFactory($this->registry);
    }

    /**
     * Used to execute the view.
     *
     * This function is pretty basic, it is simply called and then the view takes over, nothing passed or returned.
     */
    abstract public function invoke();

    /**
     * Get DAO function.
     *
     * This function uses a DAOFactory to build a new DAO of type $term.
     *
     * @param string $term The type of DAO to return.
     *
     * @return \Pearly\Model\IDAO A new Data Access Object.
     */
    protected function getDAO($term)
    {
        $dao = $this->daofactory->build($term);
        if (isset($this->escapef) && is_callable($this->escapef)) {
            $dao->setEscape($this->escapef);
        }
        return $dao;
    }
}
