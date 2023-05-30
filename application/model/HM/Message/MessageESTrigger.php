<?php

class HM_Message_MessageESTrigger extends HM_Messenger_Service_Db implements 
    Es_Entity_Trigger
{

    const EVENT_GROUP_NAME_PREFIX = 'PERSONAL_MESSAGE_SEND';

    protected $recieverId = null;

    public function update(SplSubject $message) {
        $item = parent::update($message);
        if ((int)$item->from > 0) {
            $event = $this->getService('EventDispatcher')->notify(
                new sfEvent(
                    $this,
                    __CLASS__.'::esPushTrigger',
                    array('message' => $item)
                )
            );
            return $event;
        }
    }

    public function createEvent(HM_Model_Abstract $model)
    {
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'message', 'created'
        ), $this);
        return $event;
    }

    public function getRelatedUserList($id)
    {
        return array($this->getRecieverId());  
    }

    public function triggerPushCallback() {
        return function($ev) {
            $params = $ev->getParameters();
            $message = $params['message'];
            $service = $ev->getSubject();
            $service->setRecieverId(intval($message->to));
            $author = $service->getService('User')->getById(intval($message->from));
            $avatar = '/'.ltrim($author->getPhoto(),'/');
            $event = $service->createEvent($message);
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_PERSONALL_MESSAGE_SEND);
            $event->setParam('author', $author->getName());
            $event->setParam('author_id', (int)$message->from);
            $event->setParam('author_avatar', $avatar);
            
            $subjectId = (int)$message->subject_id;
            if ($subjectId != 0) {
                $subject = $service->getService('Subject')->find($subjectId)->current();
                $event->setParam('course_id', $subjectId);
                $event->setParam('course_name', $subject->name);
            }
            
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Message_MessageESTrigger::EVENT_GROUP_NAME_PREFIX, intval($message->from)
            );
            $eventGroup->setData(json_encode(array(
                'author_name' => $author->getName(),
                'author_id' => $event->getParam('author_id'),
                'author_avatar' => $avatar
            )));
            $event->setGroup($eventGroup);
            $esService = $service ->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }

    
    /**
     * Get recieverId.
     *
     * @return recieverId.
     */
    public function getRecieverId()
    {
        return $this->recieverId;
    }
    
    /**
     * Set recieverId.
     *
     * @param recieverId the value to set.
     */
    public function setRecieverId($recieverId)
    {
        $this->recieverId = $recieverId;
    }
}

?>
