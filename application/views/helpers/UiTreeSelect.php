<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_UiTreeSelect extends ZendX_JQuery_View_Helper_UiWidget
{
    public function uiTreeSelect($id, $value = null, array $params = array(), array $attribs = array())
    {
        $attribs = $this->_prepareAttributes($id, $value, $attribs);
        /**
         *
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        if ($value && !isset($params['ignoreDefaultSelectedValue'])) {
            $params['selected'] = $value;
        }
        $params = ZendX_JQuery::encodeJson($params);

        $js = sprintf('%s("#%s").treeselect(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $params
        );

        $this->jquery->addOnLoad($js);
        $this->jquery->addJavascriptFile($this->view->serverUrl('/js/lib/jquery/jquery-ui.treeselect.js'));
        $this->jquery->addStylesheet($this->view->serverUrl('/css/jquery-ui/jquery-ui.treeselect.css'));
        
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