<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

/**
 * Type management class.
 */
class Type
{
    /** @var array Internal array of supported types. */
    protected static $types = [];

    /**
     * Add type function.
     *
     * This function creates a new instance of $name and passes it along to self::registerType($type)
     *
     * @param string $name The classpath to add.
     */
    public static function addType($name)
    {
        self::registerType(new $name());
    }

    /**
     * Get type function.
     *
     * This function returns the object associated with $name.
     *
     * @param string $name The type to return.
     *
     * @return object self::$types[$name] if name exists in the array otherwise self::$types['string'].
     */
    public static function getType($name)
    {
        return isset(self::$types[$name]) ? self::$types[$name] : self::$types['string'];
    }

    /**
     * Register type function.
     *
     * This function stores an instance of $type in self::$types using $type->getName() as the aray key.
     *
     * @param \Pearly\Model\IType $type Instance of a conforming object to store.
     */
    private static function registerType(IType $type)
    {
        self::$types[$type->getName()] = $type;
    }
}
