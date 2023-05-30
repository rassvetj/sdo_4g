<?php

/**
 * Description of AbstractDecorator
 *
 * @author slava
 */
abstract class Es_Service_Decorator_AbstractDecorator implements Es_Service_Decorator_DecoratorBehavior {
    
    /**
     *
     * @var Es_Entity_AbstractEvent
     */
    protected $decorableEvent = null;
    
    public function __construct(Es_Entity_AbstractEvent $event) {
        $this->setDecorableEvent($event);
    }
    
    /**
     * 
     * @return Es_Entity_AbstractEvent
     */
    public function getDecorableEvent() {
        return $this->decorableEvent;
    }

    /**
     * 
     * @param Es_Entity_AbstractEvent $decorableEvent
     */
    public function setDecorableEvent(Es_Entity_AbstractEvent $decorableEvent) {
        $this->decorableEvent = $decorableEvent;
    }
    
}

?>
