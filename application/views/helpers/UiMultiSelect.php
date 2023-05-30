<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_UiMultiSelect extends ZendX_JQuery_View_Helper_UiWidget
{
    public function uiMultiSelect($id, $value = null, array $params = array(), array $attribs = array())
    {
        $attribs = $this->_prepareAttributes($id, $value, $attribs);
        if (isset($attribs['depends'])) {
            $params['depends'] = $attribs['depends'];
        }
        if (isset($attribs['Disabled'])) {
            $params['disabled'] = true;
        }
        $params = ZendX_JQuery::encodeJson($params);
        $js = sprintf('%s("#%s").multiselect(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $params
        );

        $js .= ' $("#students").multiselect().bind("multiselectcreate", function(event, ui){
            alert(1);
        }); ';
        
        $this->jquery->addOnLoad($js);
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery-ui.multiselect.js');
        $this->jquery->addStylesheet(Zend_Registry::get('config')->url->base.'css/jquery-ui/jquery-ui.multiselect.css');
        $this->view->inlineScript()->offsetSetScript(__CLASS__, "$.extend($.ui.multiselect.locale, ".Zend_Json::encode(array(
            "addAll"     => _("Добавить все"),
            "removeAll"  => _("Удалить все"),
            "itemsCount" => _("Выделенные"),
            "itemsAll"   => _("Все"),
        )).");");

        $multiOptions = array();
        if (is_array($attribs) && count($attribs)) {
            foreach($attribs as $key => $v) {
                if (strtolower($key) == 'multioptions') {
                    $multiOptions = $v;
                    unset($attribs[$key]);
                    break;
                }
            }
        }
        return $this->view->formSelect($id, $value, $attribs, $multiOptions);
    }
}