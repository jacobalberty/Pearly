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
        $mode = \Http::valueFrom($_GET, '_MODE_', \Http::valueFrom($_GET, '__MODE__', 'html'));
        if ($mode === 'html' && \Http::valueFrom($_SERVER, 'HTTP_X_REQUESTED_WITH', null) === 'XMLHttpRequest') {
            $mode = 'xml';
        }

        if ($this->create() === false) {
            return;
        }


        switch ($mode) {
            case 'json':
                $keys = \Http::valueFrom($_GET, '_KEYS_[]', array(), false);

                if (empty($keys)) {
                    $data = $this->vars;
                } else {
                    foreach ($keys as $key) {
                        $data[$key] = $this->vars[$key];
                    }
                }

                if (is_array($this->messages)) {
                    $data['_MESSAGES_'] = $this->messages;
                }
                header('Content-Type: application/json');
                if ($this->authorized) {
                    echo json_encode($data);
                }
                break;
            case 'xml':
                $keys = \Http::valueFrom($_GET, '_KEYS_[]', array(), false);

                if (empty($keys)) {
                    $data = $this->vars;
                } else {
                    foreach ($keys as $key) {
                        $data[$key] = $this->vars[$key];
                    }
                }

                if (is_array($this->messages)) {
                    $data['_MESSAGES_'] = $this->messages;
                }

                $xml = new TplToXmlView($this->registry, array());
                $xml->setData($data);
                if ($this->authorized) {
                    return $xml->invoke();
                }
                break;
            case 'html':
                extract($this->vars);

                include $this->getFragm($this->template, 'tpl/');
                break;
        }
    }

    /** This function sets up the breadcrumb trail */
    protected function create()
    {
        // Dummy function
    }
}
