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
                $dom = new \DOMDocument();
                $cookie = $this->registry->cookie;
                if (empty($cookie['token'])) {
                    $token = bin2hex(random_bytes(32));
                    setcookie(
                        'token',
                        $token,
                        0,
                        "/",
                        "",
                        false,
                        true
                    );
                }
                $token = isset($cookie['token']) ? $cookie['token'] : $token;
                $html = ob_get_clean();
                try {
                    $dom->loadXml($html);
                } catch (\Exception $e) {
                    $title = "Error Parsing Document";
                    $message = $e->getMessage();
                    include 'html/error.php';
                    $tmpnam = tempnam('tmp/', 'docparse-');
                    file_put_contents($tmpnam, $html);
                    throw new \Exception("Document Parsing exception: {$e->getMessage()}, Saved document at: {$tmpnam}", 0, $e);
                }
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('html','http://www.w3.org/1999/xhtml');
                $forms = $dom->getElementsByTagName('form');
                foreach ($forms as $form) {
                    $inputs = $xpath->query('.//html:input[@name="controller"]', $form);
                    if ($inputs->length > 0) {
                        $cName = $inputs[$inputs->length-1]->getAttribute('value');
                        $tHash = hash_hmac('sha256', $cName, $token);
                        $input = $dom->createElementNS('http://www.w3.org/1999/xhtml', 'input');
                        $input->setAttribute('name', '__CSRF_TOKEN__');
                        $input->setAttribute('type', 'hidden');
                        $input->setAttribute('value', $tHash);
                        $form->appendChild($input);
                    }
                    $input = $dom->createElementNS('http://www.w3.org/1999/xhtml', 'input');
                    $input->setAttribute('name', 'conf');
                    $input->setAttribute('type', 'hidden');
                    $input->setAttribute('value', $this->registry->cfg);
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
