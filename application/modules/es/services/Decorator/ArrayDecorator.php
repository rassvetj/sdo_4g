<?php
/**
 * Description of ArrayDecorator
 *
 * @author slava
 */
class Es_Service_Decorator_ArrayDecorator extends Es_Service_Decorator_AbstractDecorator {
    
    public function out() {
        $decorableEvent = $this->getDecorableEvent()->out();
        return array(
            'eventId' => $decorableEvent->getId(),
            'eventCreateTime' => $decorableEvent->getCreateTime(),
            'eventType' => $decorableEvent->getEventType(),
            'eventTypeStr' => $decorableEvent->getEventTypeStr(),
            'eventTriggerId' => $decorableEvent->subjectId(),
            'description' => $decorableEvent->getParams()
        );
    }
    
}

?>
