<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Factory;

/**
 * Registry Factory class.
 *
 * This class handles the creation of a new registry object based on the current configuration.
 */
class RegistryFactory
{
    /**
     * Build function.
     *
     * This function reads the configuration file to make sure the pkg is correct then
     * constructs a registry from that pkg name using the configuration file.
     *
     * @return \Pearly\Core\IRegistry The constructed registry object.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function build(\Psr\Http\Message\ServerRequestInterface $serverRequest)
    {
        $req = array_merge($serverRequest->getParsedBody(), $serverRequest->getQueryParams());
        $pkg = \Http::valueFrom($req, 'pkg', parse_ini_file(CORE_PATH.'/conf/pearly.inc.php', false)['pkg']);
        $conf = CORE_PATH . '/conf/' . \Http::valueFrom($req, 'conf', mb_strtolower($pkg)) . '.inc.php';
        if (!is_readable($conf)) {
            throw new \Exception("Couldn't read {$conf}");
        }
        $ini_data = parse_ini_file($conf, true);
        if (isset($ini_data['pearly']['pkg'])) {
            $pkg = $ini_data['pearly']['pkg'];
        }

        $rname = "\\{$pkg}\\Core\\Registry";
        return new $rname($ini_data, $serverRequest);
    }
}
