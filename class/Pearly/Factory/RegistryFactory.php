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
    /** @var string The package to build a registry from. */
    private $pkg;
    /** @var string The configuration file for the registry to be built. */
    private $conf;

    /**
     * Constructor function.
     *
     * This function checks $_REQUEST for pkg and conf. If pkg is set then the package is set to that value
     * otherwise it reads pearly.inc.php to determine the current package.
     * If conf is set then that value is used for the configuration file otherwise it uses the lowercase of pkg
     */
    public function __construct()
    {
        $this->pkg = isset($_REQUEST['pkg']) ? $_REQUEST['pkg'] : parse_ini_file(CORE_PATH.'/conf/pearly.inc.php', false)['pkg'];
        $this->conf = CORE_PATH . '/conf/' . (isset($_REQUEST['conf']) ? $_REQUEST['conf'] : mb_strtolower($this->pkg)) . '.inc.php';
    }

    /**
     * Set pkg function
     *
     * Changes the current package to build from.
     *
     * @param string $pkg The package name to use.
     */
    public function setPkg($pkg)
    {
        $this->pkg = $pkg;
    }

    /**
     * Set conf function
     *
     * Changes the current configuration file to build from.
     *
     * @param string $conf The configuration file to use.
     */
    public function setConf($conf)
    {
        $this->conf = $conf;
    }

    /**
     * Build function.
     *
     * This function reads the configuration file to make sure the pkg is correct then
     * constructs a registry from that pkg name using the configuration file.
     *
     * @return \Pearly\Core\IRegistry The constructed registry object.
     */
    public function build()
    {
        if (is_readable($this->conf)) {
            $ini_data = parse_ini_file($this->conf, true);
        } else {
            throw new \Exception("Couldn't read {$this->conf}");
        }
        if (isset($ini_data['pearly']['pkg'])) {
            $this->pkg = $ini_data['pearly']['pkg'];
        }

        $rname = "\\{$this->pkg}\\Core\\Registry";
        return new $rname($ini_data);
    }
}
