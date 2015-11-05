<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Model;

/**
 * This class is used to make parsing of collections of VOs easier.
 */
class VOCollection implements \Iterator
{
    /** @var Current position in the collection */
    protected $position = 0;

    /** @var Collection of VOs */
    protected $array = array();

    /**
     * Constructor function
     *
     * @param array $array The array of VOs to handle.
     */
    public function __construct(Array $array)
    {
        $this->array = $array;
    }

    /**
     * Rewind function.
     *
     * Sets position back to beginning.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Current function.
     *
     * Returns the value for the current position.
     */
    public function current()
    {
        return $this->array[$this->position];
    }

    /**
     * Key function.
     *
     * Returns the key for the current position.
     */
    public function key()
    {
        return $this->array[$this->position]->id;
    }

    /**
     * Next function.
     *
     * Advances the position to the next position.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Valid function.
     *
     * Tells if the current position is valid.
     *
     * @return boolean TRUE if valid FALSE otherwise
     */
    public function valid()
    {
        return isset($this->array[$this->position]);
    }
}
