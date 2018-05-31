<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Report;

use \Pearly\Core\Base;

/**
 * Abstract Report Class.
 *
 * This base class provides a constructor and a getDAO(string) method to
 * make producing reports easier.
 */
abstract class ReportBase extends Base implements IReport
{
    /** @var \Pearly\Factory\DAOFactory Used to build new Data Access Objects */
    private $daofactory;

    protected $authorized;

    /**
     * Constructor function.
     *
     * This function takes a Registry as an optional parameter and stores it in
     * the class. If $registry is null it creates a new registry object using
     * \Pearly\Factory\RegistryFactory
     *
     * @param \Pearly\Core\IRegistry $registry Registry object for this request.
     */
    public function __construct(\Pearly\Core\IRegistry &$registry = null)
    {
        parent::__construct($registry);

        $refc = new \ReflectionClass($this);
        $classname = $refc->getShortName();

        $auth = $this->registry->perms;
        $this->authorized = isset($auth['default'])
            ? $auth['default']
            : $this->authorized;
        $this->authorized = isset($auth['reportdefault'])
            ? $auth['reportdefault']
            : $this->authorized;
        $this->authorized = isset($auth[$classname])
            ? $auth[$classname]
            : $this->authorized;

        if (!$this->authorized) {
            throw new \Exception('You are not authorized to access this report');
        }

        $this->daofactory = new \Pearly\Factory\DAOFactory($registry);
    }

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
        return $this->daofactory->build($term);
    }

    /**
     * Invoke function.
     *
     * This function takes an array of parameters as input, typically you would
     * pass either $_GET or $_POST directly however you can build your own array
     * in the calling class if needed.
     *
     * @param array $params Array containing parameters to execute the report.
     *
     * @return string Path to a temporary file containing the report output in pdf format.
     */
    abstract public function invoke(array $params);
}
