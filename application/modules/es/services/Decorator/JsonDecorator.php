<?php
/**
 * Description of JsonDecorator
 *
 * @author slava
 */
class Es_Service_Decorator_JsonDecorator extends Es_Service_Decorator_AbstractDecorator {
    
    public function out() {
        $decorableEvent = $this->getDecorableEvent()->out();
        return Zend_Json::encode($decorableEvent->getParams());
    }
    
}

?>
