<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */

use Pearly\Core\ValidationException;

/**
 * Simple Http helper class.
 *
 * This class implements helper functions to make handling http easier.
 */
class Http
{
    /** Used to indicate parameter wasn't passed */
    const PARAM_UNSET = "\0\0";

    /**
     * Query helper function.
     *
     * This function is used to process query strings to ensure the correct package is selected
     * for the resulting link.
     *
     * @param string $query  The query string to process.
     * @param string $sep    The seperator between parameters.
     * @param array  $parray Array containing additional parameters to add to the query.
     *
     * @return string a query string to be used in urls.
     */
    public static function mquery($query, $sep = '&', array $parray = array())
    {
        $marray = array();
        if (isset($_REQUEST['conf'])) {
            $marray['conf'] = $_REQUEST['conf'];
        } elseif (isset($_REQUEST['pkg'])) {
            $marray['pkg'] = $_REQUEST['pkg'];
        }
            parse_str($query, $qarray);
            $retval = http_build_query(array_merge($qarray, $marray, $parray), 'var_', $sep);
            return $retval;
    }

    /**
     * Request value.
     *
     * This function checks if $key exists in the $_REQUEST array.
     * If it does it returns the value, if not it returns $default
     *
     * @param string $key     The key to search for.
     * @param mixed  $default The default value to return if $key doesn't exist.
     *
     * @throws \Pearly\Core\ValidationException if $key is not found and no $default is set.
     *
     * @return either the value of $_REQUEST[$key] or $default if $key doesn't exist in $_REQUEST
     */
    public static function request($key, $default = \Http::PARAM_UNSET)
    {
        $_REQUEST['__NAME__'] = '$_REQUEST';
        return \Http::valueFrom($_REQUEST, $key, $default);
    }

    /**
     * Post value.
     *
     * This function checks if $key exists in the $_POST array.
     * If it does it returns the value, if not it returns $default
     *
     * @param string $key     The key to search for.
     * @param mixed  $default The default value to return if $key doesn't exist.
     *
     * @throws \Pearly\Core\ValidationException if $key is not found and no $default is set.
     *
     * @return either the value of $_POST[$key] or $default if $key doesn't exist in $_POST
     */
    public static function post($key, $default = \Http::PARAM_UNSET)
    {
        $_POST['__NAME__'] = '$_POST';
        return \Http::valueFrom($_POST, $key, $default);
    }

    /**
     * Get value.
     *
     * This function checks if $key exists in the $_GET array.
     * If it does it returns the value, if not it returns $default
     *
     * @param string $key     The key to search for.
     * @param mixed  $default The default value to return if $key doesn't exist.
     *
     * @throws \Pearly\Core\ValidationException if $key is not found and no $default is set.
     *
     * @return either the value of $_GET[$key] or $default if $key doesn't exist in $_GET
     */
    public static function get($key, $default = \Http::PARAM_UNSET)
    {
        $_GET['__NAME__'] = '$_GET';
        return \Http::valueFrom($_GET, $key, $default);
    }

    /**
     * Value From.
     *
     * This function checks if $key exists in the $array array.
     * If it does it returns the value, if not it returns $default.
     *
     * @param array  $array   The array to search for $key in.
     * @param string $key     The key to search for.
     * @param mixed  $default The default value to return if $key doesn't exist.
     * @param bool   $logit   Log Type Mismatches.
     *
     * @throws \Pearly\Core\ValidationException if $key is not found and no $default is set.
     *
     * @return either the value of $_array[$key] or $default if $key doesn't exist in $_array
     */
    public static function valueFrom($array, $key, $default = \Http::PARAM_UNSET, $logit = true)
    {
        $ardepth = substr_count($key, '[]');
        $key = $ardepth !== 0 ? substr($key, 0, -2*$ardepth) : $key;
        if (isset($array[$key])) {
            return self::array_process($ardepth, $array[$key], $key, $logit);
        }
        if ($default !== \Http::PARAM_UNSET) {
            return (is_object($default) && ($default instanceof Closure)) ? $default() : $default;
        }
        if (isset($array['__NAME__'])) {
            throw new ValidationException("Could not find key: '{$key}' in '{$array['__NAME__']}'", 1);
        } else {
            throw new ValidationException("Could not find key: '{$key}'", 1);
        }
    }

    private static function array_process($depth, $value, $key, $logit) {
        $val = $value;
        $isarray = is_array($val);
        if ($depth !== 0) {
            if ($isarray) {
                return $val;
            }
            if ($logit) {
                $logger = new \Pearly\Core\Logger();
                $logger->warning("Expected Array for '{$key}' but got " . gettype($val));
            }
            return [$val];
        }
        if ($isarray && $logit) {
            $logger = new \Pearly\Core\Logger();
            $logger->warning("Expected Scalar for '{$key}' but got array");
        }
        while (is_array($val)) {
            $val = array_shift($val);
        }
        return $val;
    }
}
