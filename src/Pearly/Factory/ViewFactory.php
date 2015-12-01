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
    /**
     * Build function.
     *
     * This function tries to load the specified view. If it can't find the class in the current package.
     * Then it defaults to IndexView in the current package.
     *
     * @return \Pearly\View\IView New view object.
     */
    public static function build(
        \Pearly\Core\IRegistry $registry,
        $page = null,
        $auth = array('default' => true)
    ) {
        $vname = "\\{$registry->pkg}\\View\\{$page}View";

        if (!class_exists($vname)) {
            $vname = "\\{$registry->pkg}\View\\IndexView";
        }

        $view = new $vname($registry, $auth);
        return $view;
    }
}
