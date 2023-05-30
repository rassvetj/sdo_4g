<?php

class Es_Service_Callback_EventViewTrigger implements Es_Service_Callback_CallbackBehavior
{

    public function getCallback(array $params = array())
    {
        return function($ev) use ($params) {
            $evParams = $ev->getParameters();
            $subject = $ev->getSubject();
            $filter = $evParams['filter'];
            $actor = $params['actor'];
            $result = false;
            if ($filter->getEventId() !== null || $filter->getGroupId() !== null || $filter->getTypes() !== null || $filter->getEventType() !== null) {
                $result = $actor->unsubscribe($filter);
            }
            $ev->setReturnValue($result);
        };
    }

}

?>
