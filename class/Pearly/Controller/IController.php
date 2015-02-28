<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Controller;

use Pearly\Core\IRegistry;
use Pearly\Core\IBase;
use Psr\Log\LoggerInterface;

/**
 * Base class for controllers.
 *
 * This class is used to handle posting data to the database.
 * The public functions are called to handle the data.
 */
interface IController extends IBase
{
    /**
     * Constructor function.
     *
     * @param \Pearly\Core\IRegistry   $registry Registry object to use for this request.
     * @param array                    $auth     Array containing acl for views and controllers.
     * @param \Psr\Log\LoggerInterface $logger   A PSR-3 compliant logger object.
     */
    public function __construct(IRegistry &$registry = null, array $auth = array(), LoggerInterface $logger = null);

    /**
     * Authorization function.
     *
     * This function ensures you are authorized to access the object.
     * If you are authorized it executes it, if not it aborts the query.
     *
     * @param string $action The action to be executed.
     * @param array  $params Optional array of parameters ot be passed.
     *
     * @throws \ErrorException if another \ErrorException is caught and debug mode is on.
     *
     * @return string a URL to redirect to.
     */
    public function invoke($action, array $params);
}
