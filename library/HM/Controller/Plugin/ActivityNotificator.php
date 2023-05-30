<?php
// @todo: таким образом можно информировать о всех активностях
class HM_Controller_Plugin_ActivityNotificator extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $s = new Zend_Session_Namespace('s');
        $userId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        
        if (($request->getModuleName() == 'chat') || !$userId) return true;
        
        $collection = Zend_Registry::get('serviceContainer')->getService('Session')->fetchAll(array(
            'mid = ?' => $userId,       
            'sessid = ?' => $s->sessid,       
        ));     

        if (count($collection)) {
            $lastSession = $collection->current();
            $lastActivities = Zend_Registry::get('serviceContainer')->getService('ChatHistory')->fetchAllDependence(array('Sender', 'ChatRefUser'), array('created > ?' => $lastSession->stop));
            
            if (count($lastActivities)) {
                foreach ($lastActivities as $lastActivity) {

                    // пользователь уже удалён
                    if (!is_object($lastActivity->sender)) {
                        continue;
                    }

                    $sender = $lastActivity->sender->current();

                    if($lastActivity->chatRefUsers)
                        $recipients = $lastActivity->chatRefUsers->getList('user_id');
                    else
                        $recipients = array();

                    if (!in_array($userId, $recipients) || ($sender->MID == $userId)) {
                        continue;
                    }
                    
                    $message = (strlen($lastActivity->message) > 100) ? substr($lastActivity->message, 0, 100) . '...' : $lastActivity->message;
                    Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_INSTANT,
                            'html' => true, 
                            //'message' => $lastActivity->message, 
                            'message' => sprintf(
                                '<a href="%s">%s</a>', 
                                Zend_Registry::get('view')->url(array('module' => 'chat', 'controller' => 'index', 'action' => 'index', 'channel_id' => $lastActivity->channel_id)), 
                                Zend_Registry::get('view')->escape(str_replace("'", '', $message))
                            ), 
                            'instantTitle' => $sender->getName(),//_('Новое сообщение в чате'),
                            'instantImage' => Zend_Registry::get('config')->url->base . $sender->getPhoto(),
                    ));    
                }
            }
        }
    }
}

