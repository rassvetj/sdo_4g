<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_FcbkComplete extends HM_View_Helper_Abstract
{
    public function fcbkComplete($id, $value = null, array $attribs = array())
    {
        // print_r($value);
        // exit;
        $elId = $id.'_fcbkComplete';

        if(!isset($attribs['cache'])) {
            $attribs['cache'] = false;
        }
        if(!isset($attribs['filter_case'])) {
            $attribs['filter_case'] = false;
        }
        if(!isset($attribs['filter_hide'])) {
            $attribs['filter_hide'] = false;
        }
        if(!isset($attribs['newel'])) {
            $attribs['newel'] = true;
        }
        if(!isset($attribs['complete_text'])) {
            $attribs['complete_text'] = _('Начните набирать текст...');
        }

        foreach($attribs as $attr_name => $attr_value) {
            if (strncmp($attr_name, 'on', 2) === 0 && !($attr_value instanceof Zend_Json_Expr)) {
                $attribs[$attr_name] = new Zend_Json_Expr($attribs[$attr_name]);
            }
        }

        $params = ZendX_JQuery::encodeJson($attribs);
        $js .= sprintf('%s("#%s").fcbkcomplete(%s);',
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $id.'_fcbkComplete',
            $params
        );

        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fcbkcomplete/jquery.fcbkcomplete.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/fcbkcomplete/style.css'));
        $this->view->jQuery()->addOnload($js);

        $style = isset($attribs['style']) ? " style='{$attribs['style']}'" : '';
        $html = '<select multiple="true" name="'.$id.'" class="fcbkcomplete" id="'.$elId.'" ' . $style . '>';
        if($value && is_array($value)) {
            foreach($value as $k => $v) {
                $html .= '<option class="selected" selected="selected" value="'.$k.'">'.$v.'</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}