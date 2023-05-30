<?php
/**
 * Description of EventTypeNameDefiner
 *
 * @author slava
 */
class Es_Service_Callback_EventTypeNameDefiner implements Es_Service_Callback_CallbackBehavior {
    
    public function getCallback(array $params = array()) {
        return function($ev) use ($params) {
            /*@var $ev sfEvent */
            $parameters = $ev->getParameters();
            /*@var $actor Es_Service_EventActor */
            $actor = $ev->getSubject();
            /*@var $event Es_Entity_Event */
            $event = $parameters['event'];
            if ($event && $event->getEventTypeStr() === null) {
                $eventTypesList = $actor->getEventTypesList();
                foreach ($eventTypesList as $eventType) {
                    if ($event->getEventType() == $eventType->getId()) {
                        $event->setEventTypeStr($eventType->getName());
                        break;
                    }
                }
            }
            $ev->offsetSet('event', $event);
        };
    }
    
}

?>
