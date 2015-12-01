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
    /**
     * Build function.
     *
     * This function tries to load the specified controller out of the current package.
     *
     * @return \Pearly\Controller\IController New controller object.
     */
    public static function build(
        \Pearly\Core\IRegistry $registry,
        $controller,
        $auth = array('default' => true)
    ) {
        $cname = "\\{$registry->pkg}\\Controller\\{$controller}Controller";
        $cinst = new $cname($registry, $auth);

        return $cinst;
    }
}
