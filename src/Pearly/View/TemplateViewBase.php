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

        $query = $registry->serverRequest->getQueryParams();
        $server = $registry->serverRequest->getserverParams();
        $mode = \Http::valueFrom($query, '_MODE_', 'html');
        if ($mode === 'html' && \Http::valueFrom($server, 'HTTP_X_REQUESTED_WITH', null) === 'XMLHttpRequest') {
            $mode = 'xml';
        }

        if ($this->create() === false) {
            return;
        }


        switch ($mode) {
            case 'json':
                $keys = \Http::valueFrom($query, '_KEYS_[]', array(), false);

                if (empty($keys)) {
                    $data = $this->vars;
                    array_walk($data, function(&$val, $key) {
                        if (is_object($val) && $val instanceof \Traversable) {
                            $val = iterator_to_array($val);
                        }
                    });
                } else {
                    foreach ($keys as $key) {
                        $val = $this->vars[$key];
                        if (is_object($val) && $val instanceof \Traversable) {
                            $val = iterator_to_array($val);
                        }
                        $data[$key] = $val;
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
                $keys = \Http::valueFrom($query, '_KEYS_[]', array(), false);

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
                $html = ob_get_clean();
                $dom = new \DOMDocument("1.0", 'UTF-8');
                $dom->loadXml($html);
                $input = $dom->createElement('input');
                $input->setAttribute('name', 'conf');
                $input->setAttribute('type', 'hidden');
                $input->setAttribute('value', $this->registry->cfg);
                $forms = $dom->getElementsByTagName('form');
                foreach ($forms as $form) {
                    $form->appendChild($input);
                }
                echo $dom->saveXml();
                break;
        }
    }

    /** This function sets up the breadcrumb trail */
    protected function create()
    {
        // Dummy function
    }
}

