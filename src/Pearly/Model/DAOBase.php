<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

use Pearly\Core\Base;

/**
 * The base class for Data Access Objects.
 */
class DAOBase extends Base implements IDAO
{
    private $escapef;

    /**
     * Constructor function
     *
     * @param \Pearly\Core\IRegistry $registry
     */
    public function __construct(\Pearly\Core\IRegistry &$registry = null)
    {
        parent::__construct($registry);

        $this->vofactory = new \Pearly\Factory\VOFactory($this->registry);
    }

    /**
     * Set Escape function.
     */
    final public function setEscape($escapef)
    {
        if (!is_callable($escapef)) {
            throw new \Exception('Invalid Callback passsed to VOBase::setEscape');
        }

        $this->escapef = $escapef;
    }

    /**
     * Get VO function.
     *
     * This function uses a VOFactory to build a new VO of type $type.
     *
     * @param string $type The type of VO to return.
     *
     * @return \Pearly\Model\IVO A new Value Object.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    protected function getVO($type)
    {
        $vobj = call_user_func_array(array($this->vofactory, 'build'), func_get_args());
        if (isset($this->escapef) && is_callable($this->escapef)) {
            $vobj->setEscape($this->escapef);
        }
        return $vobj;
    }
}
