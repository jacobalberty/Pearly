<?php
/**
 * Pearly 1.0
 *
 * @author     Jacob Alberty <jacob.alberty@gmail.com>
 */

/**
 * Simple Html helper class.
 *
 * This class implements several helper functions to make creating html forms cleaner.
 */
class Html
{
    /**
     * MessageDiv
     *
     * @param array $messages Array containing warning/error messages to display.
     * @return string a string containing the generated html.
     */
    public static function messageDiv($messages = array())
    {
        $ret = '';
        foreach ($messages as $message) {
            $ret .= "<div class=\"notebox\">{$message}</div>";
        }
        return $ret;
    }

    /**
     * CheckBox input helper.
     *
     * @param string $name The name and id of the html element.
     * @param boolean $ischecked An expession that evaluates to true or false.
     * @param Array $attributes An optional keyed array containing additional html attributes to be given to the element.
     * @return string A string containing the generated html.
     */
    public static function CheckBox($name, $ischecked, Array $attributes = null)
    {
        if ($ischecked) {
            $attributes['checked'] = 'checked';
        }
        $attributes['name'] = $name;
        $attributes['id'] = $name;
        $attributes['type'] = 'checkbox';
        return Html::Input($attributes);
    }

    /**
     * Input helper.
     *
     * @param Array $attributes A keyed array containing html attributes to be given to the element.
     * @return string A string containing the generated html.
     */
    public static function Input($attributes = null)
    {
        $attr = '';
        foreach ($attributes as $k => $v) {
            $attr .= "{$k}=\"${v}\" ";
        }
        return "<input {$attr} />";
    }

    /**
     * Drop Down List helper.
     *
     *
     * @param string $name The name and id of the html element.
     * @param Array $values A keyed array containing the values to include in the list.
     * @param string $blank If this does not evaluate to false then the contents will be provided as a "blank" default option.
     * @param string $match Optional value to have selected by default.
     * @param Array $attributes an optional keyed array containing additional html attributes to be given to the element.
     * @return string A string containing the generated html.
     */
    public static function DropDownList($name, $values = array(), $blank = false, $match = null, array $attributes = array())
    {
        $result = null;
        $term = "\r\n";
        $attr = '';
        $attributes['name'] = $name;
        $attributes['id']   = $name;
        foreach ($attributes as $k => $v) {
            $attr .= "{$k}=\"${v}\" ";
        }
        $result .= "<select {$attr}>" . $term;
        if (is_string($blank)) {
            $result .= "<option label=\"{$blank}\"></option>{$term}";
        } elseif ($blank) {
            $result .= "<option label=\"Select an option\"></option>{$term}";
        }
        foreach ($values as $k => $v) {
            if ($k == $match) {
                $result .= "<option value=\"{$k}\" selected=\"selected\">{$v}</option>{$term}";
            } else {
                $result .= "<option value=\"{$k}\">{$v}</option>{$term}";
            }
        }

        $result .= "</select>";

        return $result;
    }

    /**
     *
     * Query helper function
     * This function is used to process query strings to ensure the correct package is selected
     * for the resulting link.
     *
     * @param string $query the query string to process.
     * @param string $sep the seperator between parameters.
     * @return string a query string to be used in urls.
     */
    public static function mquery($query, $sep = '&amp;')
    {
        return \Http::mquery($query, $sep);
    }

    /**
     *
     * Form helper function
     * This function is used to create the hidden inputs for get forms to allow conf/package options to persist.
     *
     * @return string html data to be injected into get forms.
     */
    public static function mform()
    {
        $tmp = null;
        if (isset($_REQUEST['conf'])) {
            $tmp .= '<input name="conf" type="hidden" value="' . htmlspecialchars($_REQUEST['conf']) . '" />';
        } elseif (isset($_REQUEST['pkg'])) {
            $tmp .= '<input name="pkg" type="hidden" value="' . htmlspecialchars($_REQUEST['pkg']) . '" />';
        }
        return $tmp;
    }
}
