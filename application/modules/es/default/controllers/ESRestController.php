<?php
/**
 * Description of ESRestController
 *
 * @author slava
 */
abstract class Es_Controller_ESRestController extends HM_Controller_Action_Rest {
    
    public function init() {
        $getRequestPreTriggers = $this->getEventDispatcher()->getListeners(self::EVENT_GET_REQUEST_PRE);
        if (sizeof($getRequestPreTriggers) > 0) {
            foreach ($getRequestPreTriggers as $trigger) {
                $this->getEventDispatcher()->disconnect(self::EVENT_GET_REQUEST_PRE, $trigger);
            }
        }
        parent::init();
    }
    
}

?>
