<?php
class HM_Controller_Plugin_Messenger extends Zend_Controller_Plugin_Abstract
{
    /**
     * пока этот плагин не используется так как при редиректе (gotoUrl gotoSimple) postDispatch не вызывается
     * @todo переделать на Zend_Queue.
     * @param Zend_Controller_Request_Abstract $request
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {

        $serviceContainer = Zend_Registry::get('serviceContainer');
        $messenger = $serviceContainer->getService('Messenger');
        $messenger->sendAllFromChannels();
    }
}
