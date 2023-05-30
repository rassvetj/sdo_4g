<?php
class HM_Controller_Plugin_Session extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $s = new Zend_Session_Namespace('s');
        if ($userId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()) {
            Zend_Registry::get('serviceContainer')->getService('Session')->updateWhere(array(
                'stop' => date('Y-m-d H:i:s')        
            ), array(
                'mid = ?' => $userId,       
                'sessid = ?' => $s->sessid,       
            ));
        }
    }
}

