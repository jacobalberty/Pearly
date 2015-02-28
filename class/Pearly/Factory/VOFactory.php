<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * VO Factory class.
 *
 * This class handles the creation of new Value Objects.
 */
class VOFactory
{
    private $registry;
    /**
     * Constructor function.
     *
     * @param \Pearly\Core\IRegistry $registry The registry to use for construction, if none is set then one will be created.
     */
    public function __construct(\Pearly\Core\IRegistry $registry = null)
    {
        if ($registry === null) {
            $rf = new RegistryFactory();
            $registry = $rf->build();
        }
        $this->registry = $registry;
    }

    /**
     * Build function.
     *
     * This function builds a new VO of type $type from the package namespace of the
     * registry used in creation of this factory and passes the registry to the VO's contstructor.
     *
     * @param string $type The name of the VO object to create.
     *
     * @return \Pearly\Model\IVO A VO object of the appropriate type.
     */
    public function build($type)
    {
        $voname = "\\{$this->registry->pkg}\\Model\\VO\\{$type}";
        $args = func_get_args();
        array_shift($args);
        $r = new \ReflectionClass($voname);
        return $r->newInstanceArgs($args);
//        return new $voname();
    }
}
