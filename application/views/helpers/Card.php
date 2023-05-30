<?php
class HM_View_Helper_Card extends HM_View_Helper_Abstract
{

    public function card($item, $fields, $attribs = array())
    {
        if ($item->MID > 0) {
            $unitInfo = Zend_Registry::get('serviceContainer')->getService('User')->getUnitInfo($item->MID);
        }

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_USER_CARD_UNIT_INFO);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $unitInfo);
        $unitInfo = $event->getReturnValue();

        foreach($fields as $key => $title){

            if (!empty($item->$key) || method_exists($item, substr($key, 0, strpos($key, '(')))) {

                if (false !== strstr($key, '(')) {
                    $param = substr($key, strpos($key, '(')+1, strpos($key, ')')-strpos($key, '(')-1);
                    $funcname = substr($key, 0, strpos($key, '('));
                    if ($param) {
                        $item->$key = call_user_func(array($item, $funcname), $param);
                    } else {
                        $item->$key = $item->$funcname();
                    }
                }

            }else{
                $item->$key = '-';
            }
        }
        array_filter($fields, array(self, 'filterNotEmpty'));

        $this->view->info    = $unitInfo;
        $this->view->fields  = $fields;
        $this->view->item    = $item;
        if (!isset($attribs['title'])) {
            $attribs['title'] = _('Карточка');
        }
        $this->view->attribs = $attribs;
        return $this->view->render('card.tpl');
    }
    
    static public function filterNotEmpty($value)
    {
        return $value != '-';
    }
    
}