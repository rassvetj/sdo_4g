<?php

class Es_Entity_AbstractEventTypeList extends HM_Collection_Primitive
{

    public function addType(\Es_Entity_AbstractEventType $eventType) {
        return parent::add($eventType, $eventType->getId());
    }

}
