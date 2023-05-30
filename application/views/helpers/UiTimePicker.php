<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_UiTimePicker extends ZendX_JQuery_View_Helper_UiWidget
{
    public function uiTimePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
        $attribs = $this->_prepareAttributes($id, $value, $attribs);
        $params['format'] = "%H:%i";
        $params['labelTitle'] = iconv(Zend_Registry::get('config')->charset, 'utf-8', _('Укажите время'));
        $params['labelHour']  = iconv(Zend_Registry::get('config')->charset, 'utf-8', _('Часы'));
        $params['labelMinute'] = iconv(Zend_Registry::get('config')->charset, 'utf-8', _('Минуты'));
        $params = ZendX_JQuery::encodeJson($params);
        
        $js = sprintf('%s("#%s").AnyTime_picker(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $params
        );

        $this->jquery->addOnLoad($js);
        $this->jquery->addJavascriptFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/anytime.js');
        $this->jquery->addStylesheet(Zend_Registry::get('config')->url->base.'css/jquery/anytime.css');
        
        return $this->view->formText($id, $value, $attribs);
    }
}