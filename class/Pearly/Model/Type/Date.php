<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model\Type;

use Pearly\Model\IType;

/**
 * Date type class.
 */
class Date implements IType
{
    /**
     * Validate function
     *
     * The Date type currently does not support any validation.
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
        return $messages;
    }

    /**
     * Convert to display value function.
     *
     * This function uses date() to convert the internal timestamp to a human readable date.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDisplayValue($value)
    {
        return (!is_null($value) && !empty($value)) ? date('Y-m-d', $value) : null;
    }

    /**
     * Convert to PHP value function.
     *
     * This function uses strtotime to convert $value into a timestamp to handle internally.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToPHPValue($value)
    {
        return !is_null($value) ? strtotime($value) : null;
    }

    /**
     * Convert to database value function.
     *
     * This function uses date() to convert the internal timestamp to a native database format.
     *
     * @param mixed $value The data to be converted
     *
     * @return mixed The results of the conversion.
     */
    public function convertToDatabaseValue($value)
    {
        return (!is_null($value) && !empty($value)) ? date('Y-m-d', $value) : null;
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
        return 'date';
    }
}
