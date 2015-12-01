<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

use Psr\Log;

/**
 * Default Pearly logger.
 *
 * This logger simply interpolates $context with $message and writes it out to syslog.
 */
class Logger extends Log\AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws \Psr\Log\InvalidArgumentException if $level is an unsupported type.
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $priority = null;
        $levels = [
            Log\LogLevel::EMERGENCY => LOG_EMERG,
            Log\LogLevel::ALERT => LOG_ALERT,
            Log\LogLevel::CRITICAL => LOG_CRIT,
            Log\LogLevel::ERROR => LOG_ERR,
            Log\LogLevel::WARNING => LOG_WARNING,
            Log\LogLevel::NOTICE => LOG_NOTICE,
            Log\LogLevel::INFO => LOG_INFO,
            Log\LogLevel::DEBUG => LOG_DEBUG,
        ];
        $priority = $levels[$level];
        if (is_null($priority)) {
            throw new Log\InvalidArgumentException(
                'log only accepts the types defined in \Psr\Log\LogLevel received: '. $level
            );
        }
        openlog("Pearly", LOG_PID | LOG_CONS | LOG_NDELAY, LOG_USER);
        $referer = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referer = ", referer: {$_SERVER['HTTP_REFERER']}";
        }
        $client = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client = "[client {$_SERVER['HTTP_X_FORWARDED_FOR']}] ";
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $client .= "[proxy {$_SERVER['REMOTE_ADDR']}] ";
            }
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $client = "[client {$_SERVER['REMOTE_ADDR']}] ";
        }

        syslog(
            $priority,
            "[{$level}] {$client}"
                . $this->interpolate($message, $context)
                . $referer
        );
        closelog();
    }

    /**
     * Interpolates context values into the message placeholders.
     * Taken from PSR-3's example implementation.
     *
     * @param string $message Plain text message to be logged.
     * @param array  $context Data to be interpolated into the message.
     *
     * @return string $message with $context successfully interpolated.
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context
        // keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
