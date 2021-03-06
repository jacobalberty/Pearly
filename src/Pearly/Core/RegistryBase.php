<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * This class provides an abstract implementation of the registry providing a few required functions.
 *
 * @property pkg  string The name of the package this object is initialized for.
 * @property cfg  string The configuration name this object is initialized for.
 * @property auth string Default authorization module.
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
abstract class RegistryBase implements IRegistry
{
    /**
     * Contains configuration data.
     *
     * @public
     */
    public $conf_data;

    /**
     * @var Array data storage
     */
    private $vars = array();

    /**
     * @var Array data storage
     */
    private $objlist = array();

    /**
     * Setter method.
     *
     * Used to store data in self::$vars.
     *
     * @param string $name  The name of the property being interacted with.
     * @param mixed  $value The value to be assigned to the property.
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Getter method.
     *
     * Retrieves data from self::$vars or creates it using the magic methods.
     *
     * @param string $name The name of the property being interacted with.
     *
     * @return mixed The value of the property.
     */
    public function __get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        if (is_callable(array($this, 'new'.$name))) { // Lazy object loading
            $tmp = $this->{'new'.$name}();
            $this->vars[$name] = $tmp;
            $this->objlist[] = $name;
            return $tmp;
        }
        if (is_callable(array($this, 'get'.$name))) {
            return $this->{'get'.$name}();
        }
    }

    /**
     * Constructor
     *
     * Stores $conf_data in $this->conf_data
     *
     * @param array $conf_data List of retrieved configuration parameters and their value in an associative array.
     */
    public function __construct($conf_data, \Psr\Http\Message\ServerRequestInterface $serverRequest)
    {
        $this->conf_data = $conf_data;
        $this->serverRequest = $serverRequest;
    }

    /**
     * Cleanup function
     *
     * Removes all variables named in $this->objlist to ensure they are all recreated new.
     */
    public function __wakeup()
    {
        foreach ($this->objlist as $obj) {
            unset($this->vars[$obj]);
        }
        $this->objlist = array();
    }

    /** @ignore */
    public function getPkg()
    {
        return $this->conf_data['pearly']['pkg'];
    }

    /** @ignore */
    public function getCfg()
    {
        $request = $this->query + $this->parsedBody;
        return \Http::valueFrom($request, 'conf', mb_strtolower($this->Pkg));
    }

    /** @ignore */
    public function newAuth()
    {
        return \Http::valueFrom($this->conf_data['main'], 'auth', '');
    }

    /** @ignore */
    public function newLocale()
    {
        return \Http::valueFrom($this->cookie, "{$this->cfg}-locale", $this->blocale);
    }

    /** @ignore */
    public function newBLocale()
    {
        $server = $this->server;
        $def = 'en_US';
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $server)) {
            $locales = explode(',', $server['HTTP_ACCEPT_LANGUAGE']);
            return str_replace('-', '_', $locales[0]);
        }
        return $def;
    }

    /** @ignore */
    public function newISO8601()
    {
        return \Http::valueFrom($this->cookie, "{$this->cfg}-iso8601", false) === "1";
    }

    /** @ignore */
    protected function newStaticQuery()
    {
        return !isset($this->conf_data['main']['staticquery'])
            || filter_var($this->conf_data['main']['staticquery'], FILTER_VALIDATE_BOOLEAN);
    }

    protected function newCookie()
    {
        return $this->serverRequest->getCookieParams();
    }

    protected function newParsedBody()
    {
        return $this->serverRequest->getParsedBody();
    }

    protected function newServer()
    {
        return $this->serverRequest->getServerParams();
    }

    protected function newQuery()
    {
        return $this->serverRequest->getQueryParams();
    }
}
