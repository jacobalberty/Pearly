<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Exception Handler class.
 *
 * This class registers a global exception handler to clean up exceptions for the error log.
 */
class ExceptionHandler implements LoggerAwareInterface
{
    /** @var \Psr\Log\LoggerInterface A PSR-3 compliant logger. */
    protected $logger;

    /**
     * Set logger function.
     *
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register function.
     *
     * This function registers the exception handler.
     */
    public function register()
    {
        set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * Exception handler function.
     *
     * This function sanitizes the stacktrace by converting variables
     * to their types then uses $this->logger->error() to save a log of the exception.
     *
     * @param \Exception $ex the exception to log.
     */
    public function exceptionHandler(\Exception $ex)
    {
        // these are our templates
        $traceline = "#%s %s(%s): %s(%s)";
        $msg = <<<EOM
PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s
Stack trace:
%s
Request: %s
EOM;
        // alter your trace as you please, here
        $trace = $ex->getTrace();
        foreach ($trace as $key => $stackPoint) {
            // I'm converting arguments to their type
            // (prevents passwords from ever getting logged as anything other than 'string')
            $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
        }

        // build your tracelines
        $result = array();
        foreach ($trace as $key => $stackPoint) {
            $result[] = sprintf(
                $traceline,
                $key,
                isset($stackPoint['file']) ? $stackPoint['file'] : '[internal function]',
                isset($stackPoint['line']) ? $stackPoint['line'] : '',
                $stackPoint['function'],
                implode(', ', $stackPoint['args'])
            );
        }
        // trace always ends with {main}
        $result[] = '#' . ++$key . ' {main}';

        $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        // write tracelines into main template
        $msg = sprintf(
            $msg,
            get_class($ex),
            $ex->getMessage(),
            $ex->getFile(),
            $ex->getLine(),
            implode(PHP_EOL, $result),
            $request
        );

        $this->logger->critical($msg);
    }
}
