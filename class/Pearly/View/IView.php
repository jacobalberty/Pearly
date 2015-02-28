<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

use \Pearly\Core\IRegistry;
use \Pearly\Core\IBase;

/**
 * View Interface.
 *
 * This class is the base class that all views descend from.
 */
interface IView extends IBase
{
    /**
     * Constructor function
     *
     * @param \Pearly\Core\IRegistry $registry
     * @param Array $auth array containing acl for views and controllers.
     */
    public function __construct(IRegistry &$registry = null, array $auth = array());

    /**
     * Used to execute the view.
     *
     * This function is pretty basic, it is simply called and then the view takes over, nothing passed or returned.
     */
    public function invoke();
}
