<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

use \Pearly\Core\IRegistry;

/**
 * Logger Factory class.
 *
 * This class handles the creation of a new PSR-3 compliant logger object.
 */
class LoggerFactory
{
    public static $registry = null;

    public static function build()
    {
        $registry = self::$registry;
        if (($registry instanceof IRegistry) && class_exists("\\{$registry->pkg}\\Factory\\LoggerFactory")) {
            $lfn = "\\{$registry->pkg}\\Factory\\LoggerFactory";
            return $lfn::build();
        }
        return new \Pearly\Core\Logger();
    }
}
