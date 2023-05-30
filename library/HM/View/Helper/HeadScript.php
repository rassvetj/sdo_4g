<?php
require_once 'Zend/View/Helper/HeadScript.php';

class HM_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    public function headScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return parent::headScript($mode, $spec, $placement, $attrs, $type);
    }
    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        if ((null == $content) && !isset($attributes['charset']))
        {
            $attributes['charset'] = 'UTF-8';
        }
        return parent::createData($type, $attributes, $content);
    }
}
