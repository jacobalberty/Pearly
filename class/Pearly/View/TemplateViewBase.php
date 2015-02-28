<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

use Pearly\Core\IRegistry;

/**
 * Base class for templated views.
 *
 * This class is used for templated html views.
 */
abstract class TemplateViewBase extends HtmlViewBase
{
    /** @var string The template to use for displaying this view. */
    protected $template;

    /**
     * The title of the page
     */
    public $title;

    /**
     * Constructor function
     *
     * @param \Pearly\Core\IRegistry $registry
     * @param Array $auth array containing acl for views and controllers.
     */
    public function __construct(IRegistry &$registry = null, array $auth = array())
    {
        parent::__construct($registry, $auth);

        $sestpl = isset($_SESSION['template']) ? $_SESSION['template'] : 'default';
        $this->template = isset($_REQUEST['tpl']) ? $_REQUEST['tpl'] : $sestpl;
    }

    /**
     * Called to display the view.
     */
    public function invoke()
    {
        $registry = $this->registry;

        $this->create();

        if (\Http::valueFrom($_GET, '_MODE_', \Http::valueFrom($_GET, '__MODE__', 'html')) === 'xml' && $this->authorized) {
            $keys = \Http::valueFrom($_GET, '_KEYS_', \Http::valueFrom($_GET, '__KEYS__', array()));

            if (empty($keys)) {
                $data = $this->vars;
            } else {
                if (is_array($keys)) {
                    foreach ($keys as $key) {
                        $data[$key] = $this->vars[$key];
                    }
                } else {
                    $data[$keys] = $this->vars[$keys];
                }
            }
            $xml = new TplToXmlView($this->registry, array());
            $xml->setData($data);
            return $xml->invoke();
        }

        extract($this->vars);

        include $this->getFragm($this->template, 'tpl/');
    }

    /** This function sets up the breadcrumb trail */
    protected function create()
    {
        // Dummy function
    }
}
