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
        switch ($level) {
            case Log\LogLevel::EMERGENCY:
                $priority = LOG_EMERG;
                // NO BREAK
            case Log\LogLevel::ALERT:
                $priority = LOG_ALERT;
                // NO BREAK
            case Log\LogLevel::CRITICAL:
                $priority = LOG_CRIT;
                // NO BREAK
            case Log\LogLevel::ERROR:
                $priority = LOG_ERR;
                // NO BREAK
            case Log\LogLevel::WARNING:
                $priority = LOG_WARNING;
                // NO BREAK
            case Log\LogLevel::NOTICE:
                $priority = LOG_NOTICE;
                // NO BREAK
            case Log\LogLevel::INFO:
                $priority = LOG_INFO;
                // NO BREAK
            case Log\LogLevel::DEBUG:
                $priority = LOG_DEBUG;
                // NO BREAK
            default:
                if (is_null($priority)) {
                    throw new Log\InvalidArgumentException(
                        'log only accepts the types defined in \Psr\Log\LogLevel received: '. $level
                    );
                } else {
                    openlog("Pearly", LOG_PID | LOG_CONS | LOG_NDELAY, LOG_USER);
                    $referer = '';
                    if (!empty($_SERVER['HTTP_REFERER'])) {
                        $referer = ", referer: {$_SERVER['HTTP_REFERER']}";
                    }
                    $client = '';
                    if (!empty($_SERVER['REMOTE_ADDR'])) {
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
                break;
        }
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
