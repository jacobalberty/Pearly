<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

use Pearly\Core\ValidationException;

// source http://latrine.dgx.cz/property-setters-and-getters-final-solution
// define new "like-keyword"
// value is short and unusual string

/**
 * Base class for Value Objects.
 * @property string $mode used to indicate whether we are accessing from a model or view.
 */
class VOBase implements \Iterator, \Serializable, \JsonSerializable
{
    /** Used to indicate we are accessing data from a model. */
    const MODE_MODEL = 1; // 0b001

    /** Used to indicate we are accessing from a view. */
    const MODE_VIEW  = 2; // 0b010

    /** Used to indicate escaping using $this->escapef. */
    const MODE_ESCAPE  = 4; // 0b100

    /** Used to indicate view as well as escape data. Defaults to VOBase::MODE_VIEW | VOBase::MODE_ESCAPE. */
    const MODE_DEFAULT = 6; // 0b010

    /**
     * Constant to indicate generic property.
     */
    const PROPERTY = "\0\0";

    /**
     * @var Array Stores column properties.
     */
    protected $val_props = [];

    /**
     * @var Array Default column properties.
     */
    private $val_prop_defaults = ['type' => 'string'];

    /**
     * @var Array stores which variables are properties to be handled by the magic setter/getter
     */
    private $props;

    /**
     * @var Array stores any handled variables without a magic function.
     */
    private $values;

    /**
     * @var callback Output escaping callback.
     */
    private $escapef;

    /**
     * @var Array stores the original values to determine what values have been modified.
     */

    private $origValues;

    /**
     * Constructor automatically intiializes the properties list
     */
    public function __construct()
    {
        $this->props = $this->property($this);
        $this->values = array();
    }

    /**
     * Property initialization.
     *
     * @param mixed $obj The class to process (should always be $this).
     *
     * @return Array an array of variables to be handled
     */
    private function property($obj)
    {
        // use cache (analyze object only once)
        static $cache;
        $item = & $cache[ get_class($obj) ];

        // look for properties
        // 1) get_object_vars prevents ObjectIteration
        // 2) outside of objects returns only public members (speed-up)

        if ($item === null) {
            $item = array();
            foreach (get_object_vars($obj) as $name => $value) {
                if ($value == $this::PROPERTY) {
                    $item[$name] = $value;
                    unset($obj->$name);
                }
            }
            return $item;
        }

        foreach ($item as $name => $foo) {
            unset($obj->$name);
        }

        return $item;
    }

    /**
     * Universal setter.
     *
     * @param string $name  Name of the property being interacted with.
     * @param mixed  $value Value to assign to the property.
     */
    final public function __set($name, $value)
    {
        if (isset($this->props[$name])) {
//            if (is_callable(array($this, 'set'.$name))) {
            if (method_exists($this, 'set'.$name)) {
                $this->{'set'.$name}($value);
            } else {
                $this->values[$name] = $this->parseSet($name, $value);
            }
        } else {
            throw new \Exception("Undefined property '$name'.");
        }
    }

    /**
     * Universal getter.
     *
     * If property 'abc' is declared,
     * there must be getAbc() getter,
     * otherwise variable $this->values[$name] is used
     *
     * @param string $name Name of the property being interacted with.
     *
     * @return mixed The value of the property.
     */
    final public function __get($name)
    {
        if (isset($this->props[$name])) {
            if (is_callable(array($this, "get{$name}"))) {
                return $this->{"get{$name}"}();
            }
            return $this->parseGet($name);
        } else {
            throw new \Exception("Undefined property '$name'.");
        }
    }

    /* end of code from latrine.dgx.cz */

    /**
     * isset Function
     *
     * @param string $name The name of the property being interacted with.
     *
     * @return bool True if property exists false otherwise
     */
    final public function __isset($name)
    {
        if (isset($this->props[$name]) && isset($this->values[$name])) {
            return true;
        }
        return false;
    }


    /** @var The mode in which the VO is being accessed */
    public $mode = VOBase::MODE_DEFAULT;

    /**
     * This function checks for a validator function in the class and calls it if it exists.
     *
     * @throws \Exception if an invalid format was returned from the validator function.
     * @throws \Pearly\Core\ValidationException if there are returned validation errors.
     */
    final public function validate()
    {
        $messages = array();
        $result = array();
        $tmp = $this->mode;
        $this->mode = $this::MODE_MODEL;
        if (is_callable(array($this, 'validator'))) {
            $messages = $this->validator();
            if (!is_array($messages)) {
                throw new \Exception('VO Validator must return an array');
            }
        }
        foreach ($this->val_props as $name => $props) {
            $props = $props + $this->val_prop_defaults;
            $dname = isset($props['dname']) ? $props['dname'] : $name;
            $type = Type::getType($props['type']);
            $messages = array_merge($messages, $type->validate($this->{$name}, $dname, $props));
        }
        $this->mode = $tmp;
        if (!empty($messages)) {
            throw new ValidationException('Validation Exception', 1, null, $messages);
        }
    }

    /**
     * Get Value function
     *
     * This function allows raw access to the properties
     * to be used by magic getter functions in child classes.
     *
     * @param string $name The name of the property being interacted with.
     *
     * @return mixed Value of the property.
     */
    final protected function getValue($name)
    {
        return $this->values[$name];
    }

     /**
     * Set Value function
     *
     * This function allows raw access to the properties
     * to be used by magic setter functions in child classes.
     *
     * @param string $name  The name of the property being interacted with.
     * @param mixed  $value The value to be assigned to the property.
     */
    final protected function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Set Escape function.
     */
    final public function setEscape($escapef)
    {
        if (!is_callable($escapef)) {
            throw new \Exception('Invalid Callback passsed to VOBase::setEscape');
        }

        $this->escapef = $escapef;
    }

    /**
     * Parse set function.
     *
     * This function parses the value of the named property according to the properties
     * defined in $this->val_props[$name] and $this->val_prop_defaults before storing
     * the result internally.
     *
     * @param string $name  The name of the property.
     * @param mixed  $value The value of the property.
     *
     * @return mixed The final value to be stored.
     */
    private function parseSet($name, $value)
    {
        $props = $this->val_prop_defaults;
        if (isset($this->val_props[$name])) {
            $props = $this->val_props[$name] + $props;
        }

        $type = Type::getType($props['type']);
        $value = $type->convertToPHPValue($value);

        return $value;
    }

    /**
     * Parse get function.
     *
     * This function parses the value of the named property according to the properties
     * defined in $this->val_props[$name] and $this->val_prop_defaults before returning
     * the result.
     *
     * @param string $name The name of the property to be parsed.
     *
     * @return mixed The final value returned.
     */
    private function parseGet($name)
    {
        $value = isset($this->values[$name]) ? $this->values[$name] : null;

        $props = $this->val_prop_defaults;
        if (isset($this->val_props[$name])) {
            $props = $this->val_props[$name] + $props;
        }

        $type = Type::getType($props['type']);

        if ($this->mode & $this::MODE_VIEW) {
            $value = $type->convertToDisplayValue($value);
        }

        if (($this->mode & $this::MODE_ESCAPE) && is_callable($this->escapef)) {
            $escapef = $this->escapef;
            return $escapef($value);
        }

        if ($this->mode & $this::MODE_MODEL) {
            $value = $type->convertToDatabaseValue($value);
        }
        return $value;
    }

    /**
     * Rewind.
     *
     * Rewinds back to the first element of the Iterator.
     */
    public function rewind()
    {
        reset($this->values);
    }

    /**
     * Current.
     *
     * Returns the current element.
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->__get(key($this->values));
    }

    /**
     * Key.
     *
     * Return the key of the current element.
     *
     * @return scalar on success, or NULL on failure.
     */
    public function key()
    {
        return key($this->values);
    }

    /**
     * Next.
     *
     * Moves the current position to the next element.
     */
    public function next()
    {
        $next = next($this->values);
        return $next ? $this->current() : false;
    }

    /**
     * Valid.
     *
     * This method checks if the current position is valid.
     * @return boolean The return value will be casted to boolean and then evaluated. Returns TRUE on success or FALSE on failure.
     */
    public function valid()
    {
        return key($this->values) !== null;
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->props as $key => $val) {
            $value = $this->__get($key);
            $data[strtolower($key)] = $value !== null ? $this->__get($key) : '';
        }
        return $data;
    }

    public function serialize()
    {
        return serialize(array(
            'val_props' => $this->val_props,
            'val_prop_defaults' => $this->val_prop_defaults,
            'props' => $this->props,
            'values' => $this->values,
            'mode' => $this->mode,
        ));
    }

    public function unserialize($data)
    {
        $arr = unserialize($data);
        $this->val_props = $arr['val_props'];
        $this->val_prop_defaults = $arr['val_prop_defaults'];
        $this->props = $arr['props'];
        $this->values = $arr['values'];
        $this->mode = $arr['mode'];
        $this->setEscape(function($value) { return $value; });
        foreach ($this->props as $k => $v) {
            unset($this->$k);
        }
    }

    public function cleanValues()
    {
        $this->origValues = $this->values;
    }

    public function dirty()
    {
        $keys = func_get_args();
        foreach ($keys as $key) {
            unset($this->origValues[$key]);
        }
    }

    public function clean()
    {
        $keys = func_get_args();
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->values)) {
                $this->origValues[$key] = $this->values[$key];
            }
        }
    }

    public function getDirty()
    {
        $result = array();
        foreach ($this->values as $key => $value) {
            if (!array_key_exists($key, $this->origValues) || $this->origValues[$key] != $value) {
                $result[$key] = $this->$key;
            }
        }

        return $result;
    }
}
