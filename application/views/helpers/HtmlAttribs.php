<?php
class HM_View_Helper_HtmlAttribs extends Zend_View_Helper_Abstract
{

    public function htmlAttribs($attribs)
    {
         
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            $key = $this->view->escape($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    require_once 'Zend/Json.php';
                    $val = Zend_Json::encode($val);
                }
                $val = preg_replace('/"([^"]*)":/', '$1:', $val);
                
                
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $val = $this->view->escape($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }
        return $xhtml;
    }

}