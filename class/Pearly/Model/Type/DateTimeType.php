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
 * Date and Time type class.
 */
class DateTimeType extends TypeBase implements IType
{
    protected static $intl;

    /**
     * Validate function
     *
     * The Date and Time type currently does not support any validation.
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
        if (is_null(self::$intl)) {
            self::$intl = new \IntlDateFormatter(
                $this->registry->locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::MEDIUM
            );
            if ($this->registry->iso8601) {
                self::$intl->setPattern("YYYY-MM-dd'T'HH:mm:ssZ");
            }
        }
        return (!is_null($value) && !empty($value)) ? self::$intl->format($value) : null;
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
        return (!is_null($value) && !empty($value)) ? date(DATE_ISO8601, $value) : null;
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
        return 'datetime';
    }
}
