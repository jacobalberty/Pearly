<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * Logger Factory class.
 *
 * This class handles the creation of a new PSR-3 compliant logger object.
 */
class LoggerFactory
{
    public static function build()
    {
        return new \Pearly\Core\Logger();
    }
}
