<?php
/**
 * Description of CollectionJsonDecorator
 *
 * @author slava
 */
class Es_Service_Decorator_CollectionJsonDecorator extends Es_Service_Decorator_AbstractCollectionDecorator {
    
    public function out() {
        $collection = $this->getDecorableEventCollection();
        $resultArray = array();
        /*@var $event Es_Entity_AbstractEvent */
        foreach ($collection as $event) {
            $resultArray[] = array(
                'eventId' => $event->getId(),
                'eventType' => $event->getEventType(),
                'eventTypeStr' => $event->getEventTypeStr(),
                'eventTriggerId' => $event->subjectId(),
                'description' => $event->getParams(),
            );
        }
        return Zend_Json::encode($resultArray);
    }
    
}

?>
