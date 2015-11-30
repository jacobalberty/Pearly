<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Session;

/**
 * This class wraps access to php sessions.
 */
class Member implements \ArrayAccess
{
    /** @var array A reference to our member in $_SESSION */
    private $member;
    /** @var string The key to our member in $_SESSION */
    private $key;

    /**
     * Constructor
     *
     * This function checks is $key exists in $_SESSION as well
     * as if it is an array and if not creates an array in that key.
     * If no session has been started it will set session_name to
     * \Pearly\Session::getName() and then call start_session();
     *
     * @param string $key Which key from $_SESSION to provide access to.
     */
    public function __construct($key, $registry = null)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            if ($registry !== null) {
                session_name($registry->sessionName);
            } else {
                session_name(\Pearly\Session::getName());
            }
            session_start();
        }
        if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
            $_SESSION[$key] = array();
        }
        $this->member = &$_SESSION[$key];
        $this->key = $key;
    }

    /**
     * Setter function
     *
     * @param string $name  Name of the property being interacted with.
     * @param mixed  $value Value to assign to the property.
     */
    public function __set($name, $value)
    {
        $this->member[$name] = $value;
    }

    /**
     * Getter function
     *
     * @param string $name Name of the property being interacted with.
     *
     * @return mixed Value of the property.
     */
    public function &__get($name)
    {
        return $this->member[$name];
    }

    /**
     * isset function
     *
     * This function determines if a property exists within the namespace.
     *
     * @param string $name The name of the property being interacted with.
     *
     * @return bool TRUE if property exists FALSE otherwise.
     */
    public function __isset($name)
    {
        return isset($this->member[$name]);
    }

    /**
     * Unsets data by property name.
     *
     *
     * @param string $name The name of the property to unset.
     */
    public function __unset($name)
    {
        unset($this->member[$name]);
    }

    /**
     * Whether an offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean TRUE on success FALSE on failure
     */
    public function offsetExists($offset)
    {
        return isset($this->member[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed The value at the specified offset
     */
    public function &offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            $this->member[$offset] = null;
        }
        return $this->member[$offset];
    }

    /**
     * Offset to set.
     *
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->member[] = $value;
            return;
        }
        $this->member[$offset] = $value;
    }

    /**
     * Offset to unset.
     *
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        unset($this->member[$offset]);
    }

    /**
     * Unsets the values.
     *
     * This function removes the member's contents from the session.
     */
    public function removeAll()
    {
        unset($_SESSION[$this->key]);
    }

    /**
     * Apply function.
     *
     * This function executes a callback against the array allowing native array functions to be used.
     *
     * @param callback $callback The callback to apply.
     *
     * @return array The result of the aciton
     */
    public function apply($callback)
    {
        $args = func_get_args();
        $args[0] = $this->member;
        return call_user_func_array($callback, $args);
    }

    /**
     * Apply and set function.
     *
     * this function executes a callback against the array then sets the value of the array to the result.
     *
     * @param callback $callback The callback to execute.
     *
     * @return array The result of the action.
     */
    public function applySet($callback)
    {
        $args = func_get_args();
        $args[0] = $this->member;
        $result = call_user_func_array($callback, $args);
        if (!is_array($result)) {
            throw new \Exception('Result must be array. Received: '.gettype($result));
        }
        $this->member = $result;
        $_SESSION[$this->key] = $result;
        return $result;
    }
}
