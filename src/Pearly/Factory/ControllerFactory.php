<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * Controller Factory class.
 *
 * This class handles the creation of a new controller object based on
 * the selected controller and current configuration.
 */
class ControllerFactory
{
    /** @var \Pearly\Core\IRegistry The registry to be used when building new Controllers. */
    private $registry;
    /** @var string The name of the controller to build. */
    private $controller;
    /** @var array Authorization information */
    private $auth;

    /**
     * Constructor function.
     *
     *
     * @param \Pearly\Core\IRegistry $registry   Registry object to pass to the controller.
     * @param string                 $controller String containing name of the controller to load.
     * @param array                  $auth       Array containing authorization information.
     */
    public function __construct(
        \Pearly\Core\IRegistry $registry,
        $controller,
        $auth = array('default' => true)
    ) {
        $this->registry   = $registry;
        $this->controller = $controller;
        $this->auth       = $auth;
    }

    /**
     * Build function.
     *
     * This function tries to load the specified controller out of the current package.
     *
     * @return \Pearly\Controller\IController New controller object.
     */
    public function build()
    {
        $cname = "\\{$this->registry->pkg}\\Controller\\{$this->controller}Controller";
        $cinst = new $cname($this->registry, $this->auth);

        return $cinst;
    }
}
