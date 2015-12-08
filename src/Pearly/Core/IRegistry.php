<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * Registry Interface.
 *
 * This interface is used to handle registry classes for different packages.
 * Actual implementation of the registry class itself is left up to the
 * package maintainer.
 */
interface IRegistry
{
    /**
     * Default constructor
     *
     * This defines the default constructor to be used for all
     * registry objects.
     *
     * @param array $conf_data Array containing configuration data.
     */
    public function __construct($conf_data, \Psr\Http\Message\ServerRequestInterface $serverRequest);
}
