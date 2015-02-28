<?php
/**
 * Pearly 1.0 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * This is a validation exception class supporting an array of messages.
 */
class ValidationException extends \Exception
{
    /** @var array Holds the exceptions messages to be returned later */
    private $messages;

    /**
     * Constructor
     *
     * This constructor functions almost identically to \Exception except for a few cases:
     * <ol>
     * <li>If $messages is empty then $message is added as a single entry to the internal array of messages.</li>
     * <li>If $messages isn't empty then its contents are assigned to the internal array of messages.</li>
     * </ol>
     *
     * @param string    $message  The Exception message to throw.
     * @param int       $code     The Exception code.
     * @param Exception $previous The previous exception used for exception chaining.
     * @param array     $messages The list of validation errors.
     */
    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null,
        $messages = array()
    ) {
        parent::__construct($message, $code, $previous);

        $this->messages = $messages;
        if (empty($this->messages)) {
            $this->messages[] = $message;
        }
    }

    /**
     * Get Messages function
     *
     * This function returns a list of messages held by the exception.
     * By holding the messages as an array the function can return multiple
     * Validation errors in a single exception.
     *
     * @return array An array containing a text list of all messages held by the exception.
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
