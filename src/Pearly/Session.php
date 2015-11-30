<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly;

/**
 * This class holds configuration options for Sessions
 */
class Session
{
    /** @var array Holds session configuration values */
    private static $values = array();

    /**
     * Set session name function.
     *
     * @param string $name The name to use for the session.
     */
    public static function setName($name)
    {
        static::$values['name'] = $name;
    }

    /**
     * Get session name function.
     *
     * @return string The name to use for the session.
     */
    public static function getName()
    {
        return static::$values['name'];
    }
}
