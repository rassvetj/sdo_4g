<?php
class HM_View_Helper_HtmlTree extends Zend_View_Helper_FormElement
{

    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';

    private $format = HM_View_Helper_HtmlTree::FORMAT_HTML;

    protected function _getFormat()
    {
        return $this->format;
    }

    protected function _setFormat($format)
    {
        switch ($format) {
        case HM_View_Helper_HtmlTree::FORMAT_JSON:
            $this->format = HM_View_Helper_HtmlTree::FORMAT_JSON;
            break;
        case HM_View_Helper_HtmlTree::FORMAT_HTML:
        default:
            $this->format = HM_View_Helper_HtmlTree::FORMAT_HTML;
            break;
        }
        return $this;
    }

    private function _extendCallbackData($callback_data)
    {
        $default_data = HM_View_Callable_Empty::defaultData();
        if (!is_array($callback_data)) {
            $calback_data = array();
        }

        foreach ($default_data as $key => $value) {
            if (!isset($callback_data[$key]) || gettype($callback_data[$key]) !== gettype($value)) {
                $callback_data[$key] = $value;
            }
        }
        return $callback_data;
    }

    private function is_label(&$item)
    {
        $array_type = !is_array($item)
            ? 'is-not-array'
            : (is_string(key($item))
                ? 'assoc'
                : 'index');

        return (!is_array($item) || $array_type === 'assoc');
    }

    protected function _htmlTree(&$all, &$items, array $path, HM_View_Callable_Interface $callback)
    {
        $tree = array();
        $item_has_label = false;
        $i = -1;
        $format = $this->_getFormat();

        if (HM_View_Helper_HtmlTree::FORMAT_HTML === $format) {
            array_push($tree, '<ul>');
        }

        array_push($path, -1);
        foreach ($items as $item) {
            $path[count($path) - 1] = (++$i);
            $item_is_label = $this->is_label($item);

            if ($item_is_label || !$item_has_label) {
                if(!is_array($item) && $item_is_label){
                    continue;
                }
                
                
                $item_copy = $item_is_label ? $item : '';
                $meta = array(
                    'type' => ($item_is_label ? 'title' : 'fake-title'),
                    'format' => $format
                );
                $cb_data = $this->_extendCallbackData(
                    $callback->call($path, $item_copy, $all, $meta)
                );
                if ($format === HM_View_Helper_HtmlTree::FORMAT_HTML) {
                    array_push($tree, '<li' . ( $this->_htmlAttribs($cb_data['attrs']) ) . '>');
                    array_push($tree, $cb_data['title']);
                } else if ($format === HM_View_Helper_HtmlTree::FORMAT_JSON) {
                    // unset attrs which should not be set here
                    unset($cb_data['attrs'], $cb_data['children']);
                    array_push($tree, $cb_data);
                }
            }

            if (!$item_is_label) {
                             
                if (HM_View_Helper_HtmlTree::FORMAT_HTML === $format && $item_has_label) {
                    array_pop($tree);
                }

                if (HM_View_Helper_HtmlTree::FORMAT_HTML === $format) {
                    array_push($tree, $this->_htmlTree($all, $item, $path, $callback, $format));
                } else if (HM_View_Helper_HtmlTree::FORMAT_JSON === $format) {
                    $tree[count($tree) - 1]['children'] = $this->_htmlTree($all, $item, $path, $callback, $format);
                }
            }

            if (HM_View_Helper_HtmlTree::FORMAT_HTML === $format) {
                array_push($tree, '</li>');
            }

            $item_has_label = $item_is_label;
        }
        array_pop($path);

        if ($format === HM_View_Helper_HtmlTree::FORMAT_HTML) {
            array_push($tree, '</ul>');
        }

        return (HM_View_Helper_HtmlTree::FORMAT_HTML === $format)
            ? implode(self::EOL, $tree)
            : $tree;
    }

    /**
     * Generates a 'Tree' element.
     *
     * @param  array                        $items      Array with the elements of the tree
     * @param  HM_View_Callable_Interface   $callback   Class, which method `call` will be executed on every tree node (not container)
     * @param  string                       $format     Output format: JSON or HTML <ul>/<li>
     * @return string                                   depends on $format
     */
    public function htmlTree(array $items, $callback = "empty", $format = HM_View_Helper_HtmlTree::FORMAT_HTML)
    {
        if (!is_array($items)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('First param must be an array');
            $e->setView($this->view);
            throw $e;
        }

        $classname = "HM_View_Callable_" . ucfirst($callback);
        if (!class_exists($classname)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Callable class with name: "'. $classname . '" not found');
            $e->setView($this->view);
            throw $e;
        }

        $callback = new $classname();
        $callback->setView($this->view);

        $this->_setFormat($format);
        $tree = $this->_htmlTree($items, $items, array(), $callback);

        return HM_View_Helper_HtmlTree::FORMAT_JSON === $this->_getFormat()
            ? Zend_Json::encode($tree)
            : $tree;
    }
}