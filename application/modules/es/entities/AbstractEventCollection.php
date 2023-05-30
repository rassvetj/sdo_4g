<?php
/**
 * Description of AbstractEventCollection
 *
 * @author slava
 */
abstract class Es_Entity_AbstractEventCollection extends HM_Collection_Primitive implements Es_Service_Decorator_DecoratorBehavior {
    
    /**
     *
     * @var Es_Service_Dispatcher
     */
    private $esEventDispatcher = null;
    
    /**
     * 
     * @return Es_Service_Dispatcher
     */
    public function getEsEventDispatcher() {
        return $this->esEventDispatcher;
    }

    /**
     * 
     * @param Es_Service_Dispatcher $esEventDispatcher
     */
    public function setEsEventDispatcher(Es_Service_Dispatcher $esEventDispatcher) {
        $this->esEventDispatcher = $esEventDispatcher;
    }
    
    public function addEvent(Es_Entity_AbstractEvent $event) {
        $this->getEsEventDispatcher()->trigger(
            Es_Service_EventActor::EVENT_ADD_TO_RESULT_COLLECTION_PRE,
            $this,
            array(
                'notNullParam' => $event->getId(),
                'exceptionMessage' => 'Event identity has not be empty!'
            )
        );
        return $this->add($event, $event->getId());
    }
    
    /**
     * 
     * @param Es_Entity_AbstractEvent $object
     * @param mixed $key
     * @return void
     * @deprecated
     */
    public function add($object, $key = null) {
        if (!($object instanceof Es_Entity_AbstractEvent)) {
            throw new Es_Exception_InvalidArgument('Invalid event object has been added on collection');
        }
        return parent::add($object, $key);
    }
    
    public function out() {
        return $this;
    }
    
}

?>
