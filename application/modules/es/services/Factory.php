<?php
/**
 * Description of Factory
 *
 * @author slava
 */
class Es_Service_Factory extends HM_Service_Primitive {
    
    /**
     * Return storage object for events data
     * @return \Es_Service_Storage_StorageBehavior
     * @throws Es_Exception_InvalidConfiguration
     * @throws Es_Exception_InvalidStorage
     */
    public function getEventsStorage() {
        $config = Zend_Registry::get('config');
        if (!isset($config->events)) {
            throw new Es_Exception_InvalidConfiguration('Invalid EvetServer module configuration, \'events\' group required!');
        }
        $storageName = $config->events->storage;
        $storageClassName = 'Es_Service_Storage_'.ucfirst($storageName).'Storage';
        if (!class_exists($storageClassName)) {
            throw new Es_Exception_InvalidStorage('Invalid storage define on module configuration!');
        }
        /*@var $storage Es_Service_Storage_StorageBehavior */
        $storage = new $storageClassName;
        $storage->setConnectionConfig($config->events);
        return $storage;
    }
    
    public function newEvent(HM_Model_Abstract $model, array $fields = null, \Es_Entity_Trigger $userListGetter = null) {
        /*@var $event Es_Entity_AbstractEvent */
        $event = $this->getService('EventInstance');
        if ($userListGetter === null) {
            $event->setUserListGetter($model->getService());
        } else {
            $event->setUserListGetter($userListGetter);
        }
        $microtimeParts = explode(' ', microtime());
        $time = floatval($microtimeParts[1])+floatval($microtimeParts[0]);
        $event->setCreateTime($time);
        if (null !== $fields) {
            $event->setParams($model->getValues($fields));
        }
        $event->subjectId(intval($model->getPrimaryKey()));
        return $event;
    }

    public function newEventTypeList() {
        return $this->getService('EventTypeList');
    }

    public function newEventType() {
        return $this->getService('EventType');
    }
    
    public function newEventEmptyIstance() {
        return $this->getService('EventInstance');
    }
    
    public function newEventCollection() {
        return $this->getService('EventCollection');
    }
    
    public function newFilter() {
        $filter = $this->getService('FilterInstance');
        $config = Zend_Registry::get('config');
        if (!isset($config->events->listLimit)) {
            throw new Es_Exception_InvalidConfiguration('Invalid list default limit');
        }
        $filter->setLimit(intval($config->events->listLimit));
        $filter->setToTime(time());
        return $filter;
    }

    public function eventGroup($type, $triggerInstanceId) {
        $esDispatcher = $this->getService('EventServerDispatcher');
        $eventGroup = $esDispatcher->getEventActor()->getGroupByUniqueName($type, $triggerInstanceId);
        return $eventGroup;
    }

    public function getTriggerCallback($name) {
        $fullName = ucfirst($name).'Callback';
        if (!$this->getService($fullName)) {
            throw Es_Exception_InvalidArgument('Callback is not define');
        }
        return $this->getService($fullName);
    }
    
    /**
     * 
     * @param string $validatorName
     * @return \Es_Service_Validator_ValidatorBehavior
     */
    public function getValidator($validatorName) {
        $className = "Es_Service_Validator_".ucfirst($validatorName);
        return new $className;
    }
    
    public function newGroupType() {
        return $this->getService('EventGroupType');
    }
    
    public function newGroupTypeStat() {
        return $this->getService('EventGroupTypeStat');
    }
    
    public function newGroupTypeList() {
        return $this->getService('GroupTypeList');
    }
    
    public function newNotifiesList() {
        return $this->getService('NotifiesList');
    }
    
    public function newNotifyType() {
        return $this->getService('NotifyType');
    }
    
    public function newNotify() {
        return $this->getService('EventNotify');
    }
    
}

?>
