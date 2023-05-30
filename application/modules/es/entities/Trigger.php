<?php
/**
 * Interface that define behavior for event push triggerring services
 * @author slava
 */
interface Es_Entity_Trigger {
    
    /**
     * Return user identifiers for subscribing to event
     * @param int $event Object instance ID which trigger event pushing
     * @return array
     */
    public function getRelatedUserList($event);
    
    /**
     * Retrun event object
     * @param HM_Model_Abstract $model Data model for creating event object
     * @return Es_Entity_AbstractEvent
     */
    public function createEvent(HM_Model_Abstract $model);
    
    /**
     * Return callback for pushing event into data storage
     * Method contains trigger calling for Es_Service_Dispatcher::EVENT_PUSH named event
     * @return \Closure
     */
    public function triggerPushCallback();

}

?>
