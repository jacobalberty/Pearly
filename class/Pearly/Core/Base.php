<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

use Pearly\Factory\RegistryFactory;

/**
 * This is the base class from which all other classes in the project should descend.
 */
abstract class Base implements IBase
{
    /** @var Registry This variable contains the global registry instance passed down from the class instantiating this class. */
    protected $registry;

    /**
     * Constructor function.
     *
     * This function takes a Registry as an optional parameter and stores it in
     * the class. If $registry is null it creates a new registry object using
     * \Pearly\Factory\RegistryFactory
     *
     * @param \Pearly\Core\IRegistry $registry Registry object for this request.
     */
    public function __construct(IRegistry &$registry = null)
    {
        if ($registry === null) {
            $rf = new RegistryFactory();
            $registry = $rf->build();
        }
        $this->registry = $registry;
    }
}
