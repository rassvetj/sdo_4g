<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_UiDynaTree extends ZendX_JQuery_View_Helper_UiWidget
{
    public function uiDynaTree($id, $value = null, array $params = array(), array $attribs = array())
    {
        $attribs = $this->_prepareAttributes($id, $value, $attribs);

        // Create Zend_Json_Expr from anonymous functions (handlers)
        $paramKeys = array_keys($params);
        foreach ($paramKeys as $paramName) {
            // TODO: skip onLazyRead (we should allow adding come code to onLazyRead)
            if ($paramName == 'onLazyRead') {
                unset($params[$paramName]);
                continue;
            }
            if (strpos($paramName, "on") === 0) {
                $params[$paramName] = new Zend_Json_Expr($params[$paramName]);
            }
        }
        if (is_array($params['dnd'])) {
            $paramKeys = array_keys($params['dnd']);
            foreach ($paramKeys as $paramName) {
                if (strpos($paramName, "on") === 0) {
                    $params['dnd'][$paramName] = new Zend_Json_Expr($params['dnd'][$paramName]);
                }
            }
        }

        if (isset($params['title'])) {
            //$params['fx'] = array(
            //    'height' => 'toggle',
            //    'duration' => 200
            //);
            unset($params['title']);
        }

        if (isset($params['remoteUrl'])) {
            $params['onLazyRead'] = new Zend_Json_Expr("function (dtnode) {
                dtnode.appendAjax({
                    url: '".($params['remoteUrl'])."',
                    data: {key: dtnode.data.key}
                });
            }");
            unset($params['remoteUrl']);
        }

        $params['imagePath'] = Zend_Registry::get('config')->url->base.'css/jquery-ui/dynatree-1.2.0/skin-vista';
        $params = ZendX_JQuery::encodeJson($params);

        $js = sprintf('%s("#%s").dynatree(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $params
        );

        $this->jquery->addOnLoad($js);
        if (APPLICATION_ENV === "development") {
            $this->jquery->addJavascriptFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.dynatree-1.2.0.js');
        } else {
            $this->jquery->addJavascriptFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.dynatree-1.2.0.min.js');
        }
        $this->jquery->addStylesheet(Zend_Registry::get('config')->url->base.'css/jquery-ui/dynatree-1.2.0/skin-vista/ui.dynatree.css');
            
        return '<div id="'.$id.'">'.$value.'</div>';
    }
}