<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * This is the base class from which all other classes in the project should descend.
 */
interface IBase
{
    /**
     * Constructor function.
     *
     * This function takes a Registry as an optional parameter and stores it in
     * the class. If $registry is null it creates a new registry object by detecting
     * the current configuration and loading the appropriate class for that configuration..
     *
     * @param \Pearly\Core\IRegistry $registry Registry object for this request.
     */
    public function __construct(IRegistry &$registry = null);
}
