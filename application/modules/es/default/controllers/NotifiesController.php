<?php

/**
 * Description of NotifiesController
 *
 * @author slava
 */
class NotifiesController extends Es_Controller_ESRestController {
    
    public function listAction() {
        /*@var $filter Es_Entity_AbstractFilter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());
        $ev = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_PULL_NOTIFIES,
            $this,
            array('filter' => $filter)
        );
        $notifies = $ev->getReturnValue();
        $response = array();
        if ($notifies->count() > 0) {
            $data = array();
            foreach ($notifies as $notify) {
                $data[$notify->getEventType()->getName()][] = array(
                    'eventTypeName' => $notify->getEventType()->getName(),
                    'eventTypeName_LC' => _($notify->getEventType()->getName()),
                    'eventTypeId' => $notify->getEventType()->getId(),
                    'notifyTypeName' => $notify->getNotifyType()->getName(),
                    'notifyTypeName_LC' => _($notify->getNotifyType()->getName()),
                    'notifyTypeId' => $notify->getNotifyType()->getId(),
                    'isActive' => $notify->isActive()
                );
            }
            $response['data'] = $data;
            $response['status'] = 'ok';
        }
        return $this->_helper->json($response);
    }
    
    public function updateAction() {
        $params = $this->getRequest()->getParams();
        $userId = (int)$this->getService('User')->getCurrentUserId();
        $response = array();
        $response['state'] = 'ok';
        $response['data'] = array();
        if (!array_key_exists('notify_type_id', $params)) {
            $response['state'] = 'error';
            $response['data'][] = 'Notify type parameter (\'notify_type_id\') doesn\'t define';
        }
        if (!array_key_exists('event_type_id', $params)) {
            $response['state'] = 'error';
            $response['data'][] = 'Event type parameter (\'event_type_id\') doesn\'t define';
        }
        if (!array_key_exists('is_active', $params)) {
            $response['state'] = 'error';
            $response['data'][] = 'Notify status parameter (\'is_active\') doesn\'t define';
        }
        if ($response['state'] == 'error') {
            return $this->_helper->json($response);
        } else {
            /*@var $eventType Es_Entity_AbstractEventType */
            $eventType = $this->getService('ESFactory')->newEventType();
            $eventType->setId((int)$params['event_type_id']);
            
            /*@var $notifyType Es_Entity_AbstractNotifyType */
            $notifyType = $this->getService('ESFactory')->newNotifyType();
            $notifyType->setId((int)$params['notify_type_id']);
            
            /*@var $notify Es_Entity_AbstractNotify */
            $notify = $this->getService('ESFactory')->newNotify();
            $notify->setEventType($eventType);
            $notify->setNotifyType($notifyType);
            $notify->setIsActive((int)$params['is_active']);
            $notify->setUserId($userId);
            $ev = $this->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_UPDATE_NOTIFY,
                $this,
                array('notify' => $notify)
            );
            $result = $ev->getReturnValue();
            if (!$result) {
                $result['state'] = 'error';
                $result['data'][] = 'Update query executing has some error';
            }
            return $this->_helper->json($response);
        }
    }
    
}

?>
