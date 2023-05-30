<?php

class HM_Controller_Action_Helper_Notificator extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($messages)
    {
        $this->getActionController()->view->Notifications($messages);
    }
}