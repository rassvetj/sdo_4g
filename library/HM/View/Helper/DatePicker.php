<?php

require_once "Zend/Registry.php";
require_once "ZendX/JQuery/View/Helper/DatePicker.php";

class HM_View_Helper_DatePicker extends ZendX_JQuery_View_Helper_DatePicker
{
    public function datePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
        if (!isset($params['dateFormat']) && !isset($attribs['dateFormat'])) {
            $params['dateFormat'] = 'dd.mm.yy';
        }
        return parent::datePicker($id, $value, $params, $attribs);
    }
}