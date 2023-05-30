<?php

class HM_State_Action_Link extends HM_State_Action
{
    public function _render($params)
    {
            if(is_array($params['url']) == true){
                $url = Zend_Registry::get('view')->url($params['url']);
            }else{
                $url = $params['url'];
            }

            return  '<a href="' . $url . '">' . $params['title'] . '</a>';
    }

}
