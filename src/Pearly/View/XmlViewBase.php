<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\View;

/**
 * This class converts a multidimensional associative array to xml data and presents the data to the useragent.
 */
abstract class XmlViewBase extends ViewBase
{
    /** @var Array A multidimensional associative array containing the data to convert to xml. */
    protected $data = array();

    /** Function to fetch the data and put it into $this->data. */
    abstract protected function create();

    /**
     * Function to create and display the xml data.
     *
     * This function takes the created array transforms it to xml then outputs it to the web browser.
     */
    public function invoke()
    {
        $this->escapef = function ($value) {
            return htmlspecialchars($value, ENT_XML1, 'UTF-8');

        };
        $this->create();

        $data = $this->data;

        header("Content-type: text/xml;charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<data>', $this->array2xml($data), '</data>';
    }

    /**
     * This function converts a multidimensional associative array to valid xml
     *
     * @param array $array_item the array to be transformed into xml.
     *
     * @return string The transformed xml.
     */
    private function array2xml($array_item)
    {
        $xml = '';
        foreach ($array_item as $element => $value) {
            if (is_numeric($element)) {
                $element = "row{$element}";
            }
            if (is_array($value)) {
                $xml .= "<$element>" . $this->array2xml($value) . "</$element>\r\n";
            } elseif (is_object($value) && $value instanceof \Iterator) {
                $xml .= "<$element>" . $this->array2xml(iterator_to_array($value)) . "</$element>\r\n";
            } elseif ($value == '') {
                $xml .= "<$element />";
            } else {
                $xml .= "<{$element}>{$value}</{$element}>";
            }
        }
        return $xml;
    }
}
