<?php

/**
 * Description of AbstractCollectionDecorator
 *
 * @author slava
 */
abstract class Es_Service_Decorator_AbstractCollectionDecorator implements Es_Service_Decorator_DecoratorBehavior {
    
    /**
     *
     * @var Es_Entity_AbstractEventCollection
     */
    protected $decorableEventCollection = null;
    
    public function __construct(Es_Entity_AbstractEventCollection $eventCollection) {
        $this->setDecorableEventCollection($eventCollection);
    }
    
    /**
     * 
     * @return Es_Entity_AbstractEventCollection
     */
    public function getDecorableEventCollection() {
        return $this->decorableEventCollection;
    }

    /**
     * 
     * @param Es_Entity_AbstractEventCollection $decorableEvent
     */
    public function setDecorableEventCollection(Es_Entity_AbstractEventCollection $eventCollection) {
        $this->decorableEventCollection = $eventCollection;
    }
    
}

?>
