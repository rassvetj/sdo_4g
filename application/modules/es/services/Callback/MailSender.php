<?php

class Es_Service_Callback_MailSender implements Es_Service_Callback_CallbackBehavior
{

    public function getCallback(array $params = array()) {
        return function($ev) use($params) {
            $parameters = $ev->getParameters();
            /*@var $event Es_Entity_Event */
            $event = $parameters['event'];
            if (!$event || !$event->getRelatedUserList()) {
                return false;
            }
            $eventActor = $ev->getSubject();
            
            $evResult = $eventActor->getStorage()->getEsEventDispatcher()->trigger(
                Es_Service_Dispatcher::EVENT_MAIL_RENDER,
                $eventActor,
                array('event' => $event)    
            );
            $messageHtml = $evResult->getReturnValue();
            
            $subjectRenderResult = $eventActor->getStorage()->getEsEventDispatcher()->trigger(
                Es_Service_Dispatcher::EVENT_MAIL_SUBJECT_RENDER,
                $eventActor,
                array('event' => $event)    
            );
            $messageSubject = $subjectRenderResult->getReturnValue();
            /*@var $mailer Zend_Mail */
            $mailer = /*$eventActor->getService('Mailer')*/new Zend_Mail('UTF-8');
            $mailer->setSubject($messageSubject);
            $mailer->setType(Zend_Mime::MULTIPART_ALTERNATIVE);
            $mailer->setBodyHtml($messageHtml, 'UTF-8');
            
            $userList = $event->getRelatedUserList();
            $users = $eventActor->getService('User')->getUsersByIds($userList);
            
            /*@var $factory Es_Service_Factory */
            $factory = Zend_Registry::get('serviceContainer')->getService('ESFactory');
            /*@var $notifiesFilter Es_Entity_Filter */
            $notifiesFilter = $factory->newFilter();
            $notifiesFilter->setUserId($userList);
            
            /*@var $notifyType Es_Entity_AbstractNotifyType */
            $notifyType = $factory->newNotifyType();
            $notifyType->setId(Es_Entity_NotifyType::NOTIFY_TYPE_EMAIL);
            $notifiesFilter->setNotifyType($notifyType);
            
            $eventType = $factory->newEventType();
            $eventType->setId($event->getEventType());
            $notifiesFilter->setEventType($eventType);
            
            $notifiesPullEventResult = $eventActor->getStorage()->getEsEventDispatcher()->trigger(
                Es_Service_Dispatcher::EVENT_PULL_NOTIFIES,
                $eventActor,
                array('filter' => $notifiesFilter)
            );
            $notifies = $notifiesPullEventResult->getReturnValue();
            
            $i = 0;
            $validator = new Zend_Validate_EmailAddress();
            foreach ($users as $user) {
                foreach ($notifies as $notify) {
                    if ((int)$user->getPrimaryKey() == $notify->getUserId()) {
                        if ($notify->isActive() && $validator->isValid($user->EMail)) {
                            $mailer->addTo($user->EMail);
                            $i++;
                        }
                        break;
                    }
                }
            }
            if ($i > 0) {
                try {
                    $mailer->send();
                } catch (Exception $e) {
//                    Zend_Registry::get('log_system')->log("E-Mail sending failed.", Zend_Log::ERR);
                }
            }
        };
    }

}
