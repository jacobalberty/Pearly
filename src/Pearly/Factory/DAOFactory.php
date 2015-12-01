<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * DAO Factory class.
 *
 * This class handles the creation of new Data Access Objects.
 */
class DAOFactory
{
    /** @var \Pearly\Core\IRegistry The registry object to provide to the DAO. */
    private $registry;

    /**
     * Constructor function.
     *
     * @param \Pearly\Core\IRegistry $registry
     *  The registry to use for construction, if none is set then one will be created.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(\Pearly\Core\IRegistry $registry = null)
    {
        if ($registry === null) {
            $registry = RegistryFactory::build();
        }
        $this->registry = $registry;
    }

    /**
     * Build function.
     *
     * This function builds a new dao of type $type from the package namespace of the
     * registry used in creation of this factory and passes the registry to the DAO's contstructor.
     *
     * @param string $type The name of the DAO object to create.
     *
     * @return \Pearly\Model\IDAO A DAO object of the appropriate type.
     */
    public function build($type)
    {
        $daoname = "\\{$this->registry->pkg}\\Model\\DAO\\{$type}";
        return new $daoname($this->registry);
    }
}
