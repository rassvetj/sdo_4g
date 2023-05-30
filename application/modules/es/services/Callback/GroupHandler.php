<?php

class Es_Service_Callback_GroupHandler implements Es_Service_Callback_CallbackBehavior {

    public function getCallback(array $params = array()) {
        return function($ev) use($params) {
            $parameters = $ev->getParameters();
            /*@var $groupOwner Es_Entity_HasGroupProperty */
            $groupOwner = $parameters['groupOwner'];
            $storage = $ev->getSubject();
            $group = $groupOwner->getGroup();
            if ($group instanceof Es_Entity_AbstractGroup) {
                if ($group->getId() === null) {
                    $group = $storage->createGroup($group);
                    $groupOwner->setGroup($group);
                    if ($groupOwner instanceof Es_Entity_AbstractFilter) {
                        $groupOwner->setGroupId($group->getId());
                    }
                }
            } elseif ($groupOwner instanceof Es_Entity_AbstractEvent) {
                throw new \Es_Exception_Runtime('Group is not define');
            }
            $ev->setReturnValue($groupOwner);
        };
    }

}

?>
