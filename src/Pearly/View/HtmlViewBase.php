<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

/**
 * Html View base class.
 *
 * This class houses Html helper functions for views.
 */
abstract class HtmlViewBase extends ViewBase
{
    /** @var array Variables to be passed via explode to the view fragments */
    protected $vars = array();

    /** @var array List of javascript files that have been loaded already */
    protected $jscripts = array();

    /** @var array List of cascading style sheets that have been loaded already */
    protected $cssheets = array();

    /**
     * Constructor function
     *
     * @param \Pearly\Core\IRegistry $registry
     * @param Array $auth array containing acl for views and controllers.
     */
    public function __construct(\Pearly\Core\IRegistry &$registry = null, array $auth = array()) {
        parent::__construct($registry, $auth);
        $this->escapef = function($value) { return $value !== null ? htmlspecialchars($value, ENT_COMPAT | ENT_XHTML, 'UTF-8') : null; };
    }

    /**
     * CSS helper function.
     *
     * This function is used to load css into the head of Html pages.
     *
     * @param string $css the css file to load.
     *
     * @return string a html string to load the external stylesheet.
     */
    protected function getcss($css)
    {
        $retval = '';
        $fname = "css/{$css}.css";
        if (file_exists(CORE_PATH."/css/{$css}.php")) {
            include CORE_PATH."/css/{$css}.php";

            if (isset($cssheets) && is_array($cssheets)) {
                foreach ($cssheets as $cssheet) {
                    $retval .= $this->getcss($cssheet);
                }
            }

            if (isset($jscripts) && is_array($jscripts)) {
                foreach ($jscripts as $jscript) {
                    $retval .= $this->getjs($jscript);
                }
            }
        }

        $csspath = "{$fname}";

        if (!isset($this->cssheets[$csspath]) && file_exists(CORE_PATH."/{$csspath}")) {
            $cssts = is_readable($csspath) ? '?' . filemtime(CORE_PATH."/{$csspath}") : '';
            $this->cssheets[$csspath] = $cssts;
            $query = $this->registry->staticQuery ? $cssts : '' ;
            $retval .="<link type=\"text/css\" href=\"{$this->registry->site}/{$csspath}{$query}\" rel=\"stylesheet\" />\r\n";
        }
        return $retval;
    }


    /**
     * Javascript helper function.
     *
     * This function is used to load javascript into the head of Html pages.
     * It automatically checks for dependencies and includes the relevant javascript and/or css dependencies.
     *
     * @param string $js the javascript file to load.
     * @return string a html string to load the external javascript and css files.
     */
    protected function getjs($js)
    {
        $retval = '';
        $fname = "js/{$js}.js";
        if (file_exists(CORE_PATH."/js/{$js}.php")) {
            include CORE_PATH."/js/{$js}.php";

            if (isset($cssheets) && is_array($cssheets)) {
                foreach ($cssheets as $cssheet) {
                    $retval .= $this->getcss($cssheet);
                }
            }

            if (isset($jscripts) && is_array($jscripts)) {
                foreach ($jscripts as $jscript) {
                    $retval .= $this->getjs($jscript);
                }
            }
        }

        $jspath = "{$fname}";

        if (!isset($this->jscripts[$jspath])) {
            $jsts = is_readable($jspath) ? '?' . filemtime(CORE_PATH."/{$jspath}") : '';
            $this->jscripts[$jspath] = $jsts;
            $query = $this->registry->staticQuery ? $jsts : '';
            $retval .= "<script type=\"text/javascript\" src=\"{$jspath}{$query}\"></script>\r\n";
        }
        return $retval;
    }

    /**
     * Javascript helper function.
     *
     * This function is used to load javascript into the head of Html pages.
     * It automatically checks for dependencies and includes the relevant javascript and/or css dependencies.
     *
     * @param string $js the javascript file to load.
     * @return string a html string to load the external javascript and css files.
     */
    protected function addjs($js)
    {
        $retval = '';
        $mode = 0b101;
        $fname = "js/{$js}.js";
        if (file_exists(CORE_PATH."/js/{$js}.php")) {
            include CORE_PATH."/js/{$js}.php";

            if (isset($cssheets) && is_array($cssheets)) {
                foreach ($cssheets as $cssheet) {
                    $retval .= $this->getcss($cssheet);
                }
            }

            if (isset($jscripts) && is_array($jscripts)) {
                foreach ($jscripts as $jscript) {
                    $retval .= $this->addJs($jscript);
                }
            }
        }

        $jspath = "{$fname}";

        if (!isset($this->jscripts[$jspath]) && !empty($jspath)) {
//            $jsts = is_readable($jspath) ? '?' . filemtime(CORE_PATH."/{$jspath}") : '';
            $value = $mode;

            $this->jscripts[$jspath] = $value;
        }
        return $retval;
    }

    protected function getAllJs($isasync = true)
    {
        if (!is_bool($isasync)) {
            $message = "Argument \$isasync passed to getallJs() must be of type float";
            if (is_object($isasync)) {
                $message .=', '. get_class($isasync) . ' given';
            } else {
                $message .=', '. gettype($isasync) . ' given';
            }
            throw new \InvalidArgumentException($message);
        }
        $async=$isasync ? 'async="async"' : '';
        $retval = '';
        $scripts = array();
        $ats = '';
        foreach ($this->jscripts as $fname => $ts) {
            if (empty($ts) || is_numeric($ts)) {
                if ($ts & 0b001) {
                    $js = substr($fname, 3, strlen($fname)-6);
                    $fts = filemtime(CORE_PATH."/${fname}");
                    if ($fts > $ats || empty($ats)) {
                        $ats = $fts;
                    }
                    $scripts[] = $js;
                } elseif ($ts & 0b010) {
                    $js = substr($fname, 3, strlen($fname)-6);
                    $lasync = $ts & 0b100 ? $async : '';
                    $retval .= "<script type=\"text/javascript\" src=\"js/?{$js}\" {$lasync}></script>".PHP_EOL;
                } else {
                    $ts = filemtime(CORE_PATH."/{$fname}");
                    $query = $this->registry->staticQuery ? "?{$ts}" : '' ;
                    $retval .= "<script type=\"text/javascript\" src=\"{$fname}{$query}\" {$async}></script>".PHP_EOL;
                }
                $this->jscripts[$fname] = '?' . filemtime(CORE_PATH."/{$fname}");
            }
        }
        if (!empty($scripts)) {
            if (!empty($ats)) {
                $scripts[] = "_TS_={$ats}";
            }
            $squery = implode('&amp;', $scripts);
            $retval .= "<script type=\"text/javascript\" src=\"js/?{$squery}\" {$async}></script>".PHP_EOL;
        }
        return $retval;
    }

    /**
     * Section retrieval function.
     *
     * This function checks if the relevant function exists in the class and calls it if it does.
     *
     * @param string $section The name of the section to call, the function is name "def{$section}".
     */
    protected function showSection($section)
    {
        if (!$this->authorized) {
            if ($section === 'Content') {
                include $this->getFragm('unauthorized');
            }
            return;
        }
        $funcName = "def{$section}";
        if (is_callable(array($this, $funcName))) {
            $this->$funcName();
        }
        $funcName = "sect{$section}";
        if (is_callable(array($this, $funcName))) {
            $this->$funcName();
        }
    }

    /**
     * Fragment localization function.
     *
     * This function handles localization for html fragments.
     *
     * @param string $name Name of the fragment.
     * @param string $dir  The directory to look for the fragment in.
     *
     * @return string a filename suitable for including in a page.
     */
    protected function getFragm($name, $dir = 'fragm/')
    {
        $dlang = $this->registry->dlang;
        $ppath = CORE_PATH.'/html/' . mb_strtolower($this->registry->pkg) . "/{$dir}{$name}.";
        header("Vary: Accept-Language");

        $hac = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en-US';
        /**
         * code to autonegotiate the localization based on BSD licensed code from http://www.dyeager.org/blog/2008/10/getting-browser-default-language-php.html
         */
        if (!empty($hac)) {
            $x = explode(",", $hac);
            foreach ($x as $v) {
                if (preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i", $v, $matches)) {
                    $langar[$matches[1]] = (float)$matches[2];
                } else {
                    $langar[$v] = 1.0;
                }
            }
            $qval = 0.0;
            foreach ($langar as $k => $v) {
                if (($v > $qval) && (file_exists($ppath . mb_strtolower($k) . ".php"))) {
                    $qval = (float)$v;
                    $lang =  $k;
                }
            }
        }

        if (isset($lang) && file_exists($ppath . $lang . '.php')) {
            return $ppath . $lang . '.php';
        }
        if (isset($dlang) && file_exists($ppath . $dlang . '.php')) {
            return $ppath . $dlang . '.php';
        }
        return $ppath . 'php';
    }

    /**
     * Converts arrays (and nested arrays) to &gt;ul&lt; tags.
     *
     * @param array  $array_item The array to convert.
     * @param string $match      An optional key to bold to indicate that it is matched.
     *
     * @return string string of html containing the list.
     */
    protected function array2ul($array_item, $match = null)
    {
        $xml = '';
        $xml .= "<ul>";
        foreach ($array_item as $id => $value) {
            if (is_array($value)) {
                $xml .= "<li><a>{$id}</a>" . $this->array2ul($value, $match) . "</li>\r\n";
            } elseif ($id === $match) {
                $xml .= "<li id=\"{$id}\"><b class=\"selected\">{$value}</b></li>\r\n";
            } else {
                $xml .= "<li id=\"{$id}\">{$value}</li>\r\n";
            }
        }
        $xml .= "</ul>";
        return $xml;
    }

    /**
     * Makes the a crumb for the breadcrumb navigation trail.
     *
     * @param string $url   The url to use.
     * @param string $title The title to use.
     *
     * @return string Html suitable for inclusion in the breadcrumb.
     */
    protected function makeCrumb($url, $title)
    {
        if (mb_substr($url, 0, 1) === '?') {
            $url = '?' . \Html::mquery(html_entity_decode(mb_substr($url, 1)));
        }
        return <<<HTML
<a href="{$url}" itemprop="url">
<span itemprop="title">{$title}</span>
</a>
HTML;
    }

    /**
     * This function converts a xhtml document to html.
     *
     * @param string $buffer The xhtml document to convert.
     *
     * @return string A xhtml document converted to valid html.
     */
    public static function fixCode($buffer)
    {
        return (preg_replace("!\s*/>!", ">", $buffer));
    }

    /**
     * This function detects application/xhtml+xml support in the browser and either sends the document with the proper application/xhtml+xml mimetype or
     * automatically converts valid xhtml to regular html and sends the document as text/html.
     */
    protected function xmlHead()
    {
        $charset = "utf-8";
        $mime = "text/html";

        if (isset($_SERVER["HTTP_ACCEPT"]) && stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            if (preg_match("/application\/xhtml\+xml;q=([01]|0\.\d{1,3}|1\.0)/i", $_SERVER["HTTP_ACCEPT"], $matches)) {
                $xhtml_q = $matches[1];
                if (preg_match("/text\/html;q=([01]|0\.\d{1,3}|1\.0)/i", $_SERVER["HTTP_ACCEPT"], $matches)) {
                    $html_q = $matches[1];
                    if ((float)$xhtml_q >= (float)$html_q) {
                        $mime = "application/xhtml+xml";
                    }
                }
            } else {
                $mime = "application/xhtml+xml";
            }
        }
        if ($mime == "application/xhtml+xml") {
            ob_start();
            $prolog_type = "<?xml version=\"1.0\" encoding=\"$charset\" ?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n<html class=\"no-js\" lang=\"en\" xml:lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\" >\n";
        } else {
            ob_start(array(get_class($this), "fixCode"));
            $prolog_type = "<!DOCTYPE html>\n<html class=\"no-js\" lang=\"en\">\n";
        }

        @header("Content-Type: $mime;charset=$charset");
        @header("Vary: Accept");
        print $prolog_type;

        @header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        @header("Cache-Control: no-store, no-cache, must-revalidate");
        @header("Cache-Control: post-check=0, pre-check=0", false);
        @header("Pragma: no-cache");
    }
}
