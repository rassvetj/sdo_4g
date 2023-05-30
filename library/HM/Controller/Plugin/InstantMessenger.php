<?php
class HM_Controller_Plugin_InstantMessenger extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller = Zend_Controller_Front::getInstance();
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    
        $currentMessages = array();
        $messages = $flashMessenger->getCurrentMessages();
        $flashMessenger->clearCurrentMessages();
        if (is_array($messages) && count($messages)) {
            foreach ($messages as $message) {
                if (isset($message['type']) && ($message['type'] == HM_Notification_NotificationModel::TYPE_INSTANT)) {
                    $currentMessages[] = $message;
                } else {
                    $flashMessenger->addMessage($message);
                }
            }
        }
        if (count($currentMessages)) {
            Zend_Registry::get('view')->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/jquery/gritter.css');
            Zend_Registry::get('view')->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.gritter.min.js');
            Zend_Registry::get('view')->Notifications($currentMessages);
        }        
    }
}

