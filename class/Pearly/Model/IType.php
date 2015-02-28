<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

/**
 * Type interface.
 *
 * This interface defines the methods used to interact with the VO types.
 */
interface IType
{
    /**
     * Validate function
     *
     * This function validates data against the specified properties.
     *
     * @param mixed  $value Value of the data.
     * @param string $name  This contains either the name of the class or 'dname' if set from $props.
     * @param array  $props The properties to validate against.
     *
     * @return array Array indicating any validation failures.
     */
    public function validate($value, $name, $props);

    /**
     * Convert to display value function.
     *
     * This function converts the data to a human readable format suitable for display.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDisplayValue($value);

    /**
     * Convert to PHP value function.
     *
     * This function converts the data suitable for php's internal use.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToPHPValue($value);

    /**
     * Convert to database value function.
     *
     * This function returns the data in a format suitable for storing in the database.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDatabaseValue($value);

    /**
     * Get name function.
     *
     * This function simply returns the type to be registered.
     *
     * @return string The name of the type to register
     */
    public function getName();
}
