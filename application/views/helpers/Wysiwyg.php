<?php
class HM_View_Helper_Wysiwyg extends HM_View_Helper_Abstract
{
    public function Wysiwyg($id, $value = null, $attribs = null)
    {
        $editor = Zend_Registry::get('config')->wysiwyg->editor;

        return $this->view->{$editor}($id, $value, $attribs);
    }
}