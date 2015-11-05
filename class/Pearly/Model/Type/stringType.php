<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model\Type;

use Pearly\Model\IType;
use Pearly\Model\TypeBase;

/**
 * String type class
 */
class StringType extends TypeBase implements IType
{
    /**
     * Validate function
     *
     * This function checks the length of $value against 'maxlength' in $props and returns
     * a validation error if $value is too long.
     *
     * @param mixed  $value Value of the data.
     * @param string $name  This contains either the name of the class or 'dname' if set from $props.
     * @param array  $props The properties to validate against.
     *
     * @return array Array indicating any validation failures.
     */
    public function validate($value, $name, $props)
    {
        $messages = array();
        if (isset($props['maxlength']) && mb_strlen($value) > $props['maxlength']) {
            $messages[] = "Length of '{$name}' is greater than maximum allowed length of '{$props['maxlength']}'";
        }
        return $messages;
    }

    /**
     * Convert to display value function.
     *
     * This function converts the data to a human readable format suitable for display.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDisplayValue($value)
    {
        return $value;
    }

    /**
     * Convert to PHP value function.
     *
     * This function converts the data suitable for php's internal use.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToPHPValue($value)
    {
        return $value;
    }

    /**
     * Convert to database value function.
     *
     * This function returns the data in a format suitable for storing in the database.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDatabaseValue($value)
    {
        return $value;
    }

    /**
     * Get name function.
     *
     * This function simply returns the type to be registered.
     *
     * @return string The name of the type to register
     */
    public function getName()
    {
        return 'string';
    }
}
