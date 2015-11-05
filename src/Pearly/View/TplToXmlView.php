<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

use Pearly\Core\IRegistry;

/**
 * Base class for templated views.
 *
 * This class is used for templated html views.
 */
class TplToXmlView extends XmlViewBase
{
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /** This function does nothing as we are using setData to pull our data instead */
    protected function create()
    {
        // Dummy function
    }
}
