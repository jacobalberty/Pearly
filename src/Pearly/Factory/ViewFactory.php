<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * View Factory class.
 *
 * This class handles the creation of a new view objects based on the selected page and current configuration.
 */
class ViewFactory
{
    /** @var \Pearly\Core\IRegistry Registry object to pass to the view. */
    private $registry;
    /** @var string The name of the view to create.*/
    private $page;
    /** @var array Authorization information. */
    private $auth;

    /**
     * Constructor function.
     *
     *
     * @param \Pearly\Core\IRegistry $registry Registry object to pass to the view.
     * @param string                 $page     String containing name of the view to load.
     * @param array                  $auth     Array containing authorization information.
     */
    public function __construct(
        \Pearly\Core\IRegistry $registry,
        $page = null,
        $auth = array('default' => true)
    ) {
        $this->registry = $registry;
        $this->page     = $page;
        $this->auth     = $auth;
    }

    /**
     * Build function.
     *
     * This function tries to load the specified view. If it can't find the class in the current package.
     * Then it defaults to IndexView in the current package.
     *
     * @return \Pearly\View\IView New view object.
     */
    public function build()
    {
        $vname = "\\{$this->registry->pkg}\\View\\{$this->page}View";

        if (!class_exists($vname)) {
            $vname = "\\{$this->registry->pkg}\View\\IndexView";
        }

        $view = new $vname($this->registry, $this->auth);
        return $view;
    }
}
