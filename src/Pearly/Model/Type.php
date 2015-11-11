<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

use \Pearly\Core\IRegistry;

/**
 * Type management class.
 */
class Type
{
    /** @var array Internal array of supported types. */
    protected static $types = [];

    protected static $registry;

    /**
     * Add type function.
     *
     * This function creates a new instance of $name and passes it along to self::registerType($type)
     *
     * @param string $name The classpath to add.
     */
    public static function addType($name)
    {
        if (is_null(self::$registry)) {
            self::registerType(new $name());
        } else {
            self::registerType(new $name(self::$registry));
        }
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
        $pkg = self::$registry->pkg;
        if (!isset(self::$types[$name])) {
            if (class_exists("\\{$pkg}\\Model\\Type\\{$name}Type")) {
                self::addType("\\{$pkg}\\Model\\Type\\{$name}Type");
            } else if (class_exists("\\Pearly\\Model\\Type\\{$name}Type")) {
                self::addType("\\Pearly\\Model\\Type\\{$name}Type");
            }
        }
        return isset(self::$types[$name]) ? self::$types[$name] : self::$types['string'];
    }

    /**
     * Set Registry function.
     *
     * This function sets the registry to be used for object construction.
     *
     * @param IRegistry $registry The registry to use.
     */
    public static function setRegistry(IRegistry &$registry)
    {
        self::$registry = $registry;
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
