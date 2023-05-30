<?php
/**
 * Description of EventActor
 *
 * @author slava
 */
class Es_Service_EventActor extends HM_Service_Primitive {
    
    const EVENT_INTERNAL_PUSH_PRE = "actorEventPush.pre";
    const EVENT_INTERNAL_PUSH_POST = "actorEventPush.post";
    
    const EVENT_INTERNAL_PULL_PRE = "actorEventPull.pre";
    const EVENT_INTERNAL_PULL_POST = "actorEventPull.post";
    
    const EVENT_INTERNAL_PULL_STATS_PRE = "actorEventPullStats.pre";
    const EVENT_INTERNAL_PULL_STATS_POST = "actorEventPullStats.post";
    
    const EVENT_INTERNAL_REMOVE_PRE = "actorEventRemove.pre";
    const EVENT_INTERNAL_REMOVE_POST = "actorEventRemove.post";
    
    const EVENT_INTERNAL_UNSUBSCRIBE_PRE = "actorEventUnsubscribe.pre";
    const EVENT_INTERNAL_UNSUBSCRIBE_POST = "actorEventUnsubscribe.post";
    
    const EVENT_ADD_TO_RESULT_COLLECTION_PRE = "eventAddToResultCollection.pre";

    const EVENT_INTERNAL_GET_EVENT_TYPES_LIST_PRE = 'eventTypesListGetPre';
    const EVENT_INTERNAL_GET_GROUP_BY_NAME_PRE = 'eventGroupByNameGetPre';
    
    const EVENT_INTERNAL_PULL_NOTIFIES_PRE = 'notifiesPullPre';
    const EVENT_INTERNAL_PULL_NOTIFIES_POST = 'notifiesPullPost';
    
    const EVENT_INTERNAL_UPDATE_NOTIFY_PRE = 'notifyUpdatePre';
    const EVENT_INTERNAL_UPDATE_NOTIFY_POST = 'notifyUpdatePost';

    /**
     *
     * @var Es_Service_Storage_StorageBehavior
     */
    protected $storage = null;
    
    public function __construct($mapperClass = null, $modelClass = null, $adapterClass = null) {
        return parent::__construct($mapperClass, $modelClass, $adapterClass);
    }
    
    /**
     * 
     * @return Es_Service_Storage_StorageBehavior
     */
    public function getStorage() {
        return $this->storage;
    }

    /**
     * 
     * @param Es_Service_Storage_StorageBehavior $storage
     */
    public function setStorage(Es_Service_Storage_StorageBehavior $storage) {
        $this->storage = $storage;
    }
    
    public function push(Es_Entity_AbstractEvent $event) {
        $this->getStorage()->getEsEventDispatcher()->trigger(
            Es_Service_Dispatcher::EVENT_PUSH_PRE,
            $this
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PUSH_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_ARRAY)
                     ->getValidatorCallback($event->getRelatedUserList(), 'Event related user list must be an Array')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PUSH_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_DOUBLE)
                     ->getValidatorCallback($event->getCreateTime(), 'Event create time must be a double value')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PUSH_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_INTEGER)
                     ->getValidatorCallback($event->subjectId(), 'Event trigger identity must be an integer!')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PUSH_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTZERO)
                     ->getValidatorCallback($event->subjectId(), 'Event trigger identity could not equals to zero')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PUSH_PRE, $this->getStorage()->prePushListener()
            );
        $this->getStorage()->getEsEventDispatcher()->attach(
            self::EVENT_INTERNAL_PUSH_PRE,
            $this->getService('ESFactory')
                 ->getTriggerCallback('groupHandler')
                 ->getCallback()
             );
        $result = $this->getStorage()->push($event);
        $this->getStorage()->getEsEventDispatcher()->trigger(
            Es_Service_Dispatcher::EVENT_PUSH_POST,
            $this,
            array('event' => $result)
        );
        return $result;
    }
    
    /**
     * Pull event list by filter
     * @param Es_Entity_AbstractFilter $filter
     * @return Es_Entity_AbstractEventCollection
     */
    public function pull(Es_Entity_AbstractFilter $filter) {
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_ADD_TO_RESULT_COLLECTION_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTNULL)
                     ->getValidatorCallback(null, 'Event identity has not be empty!')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PULL_PRE, $this->getStorage()->prePullListener()
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
            self::EVENT_INTERNAL_PULL_PRE,
            $this->getService('ESFactory')
                 ->getTriggerCallback('groupHandler')
                 ->getCallback()
        );
        return $this->getStorage()->pull($filter);
    }
    
    public function pullStats(Es_Entity_AbstractFilter $filter) {
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PULL_STATS_PRE, $this->getStorage()->prePullStatsListener()
        );
        return $this->getStorage()->pullStats($filter);
    }
    
    public function pullNotifies(Es_Entity_AbstractFilter $filter) {
        /**
         * @todo implement user id validation (int or array of int)
         */
        /*
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PULL_NOTIFIES_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_INTEGER)
                     ->getValidatorCallback($filter->getUserId(), 'Event filter user identifier must be an integer value')
                
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PULL_NOTIFIES_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTZERO)
                     ->getValidatorCallback($filter->getUserId(), 'Event filter user identifier must be more then zero')
                
        );
         */
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_PULL_NOTIFIES_PRE, $this->getStorage()->prePullNotifies()
        );
        return $this->getStorage()->pullNotifies($filter);
    }
    
    public function updateNotify(\Es_Entity_AbstractNotify $notify) {
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UPDATE_NOTIFY_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTNULL)
                     ->getValidatorCallback($notify->getEventType(), 'Event type for notify should not be empty')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UPDATE_NOTIFY_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTNULL)
                     ->getValidatorCallback($notify->getNotifyType(), 'Notify type object for notify should not be empty')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UPDATE_NOTIFY_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_INTEGER)
                     ->getValidatorCallback($notify->getUserId(), 'User identifier for notify must be an integer value')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UPDATE_NOTIFY_PRE,
                $this->getService('ESFactory')
                     ->getValidator(Es_Service_Validator_ValidatorBehavior::VALIDATOR_NOTZERO)
                     ->getValidatorCallback($notify->getUserId(), 'User identifier for notify must be more than zero')
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UPDATE_NOTIFY_PRE, $this->getStorage()->preUpdateNotify()
        );
        return $this->getStorage()->updateNotify($notify);
    }
    
    public function remove(Es_Entity_AbstractFilter $filter) {
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_REMOVE_PRE, $this->getStorage()->preRemoveListener()
        );
        return $this->getStorage()->remove($filter);
    }
    
    public function unsubscribe(Es_Entity_AbstractFilter $filter) {
        $this->getStorage()->getEsEventDispatcher()->attach(
                self::EVENT_INTERNAL_UNSUBSCRIBE_PRE, $this->getStorage()->preUnsubscribeListener()
        );
        $this->getStorage()->getEsEventDispatcher()->attach(
            self::EVENT_INTERNAL_UNSUBSCRIBE_PRE,
            $this->getService('ESFactory')
                ->getTriggerCallback('groupHandler')
                ->getCallback()
        );
        return $this->getStorage()->unsubscribe($filter);
    }

    public function getEventTypesList() {
        $this->getStorage()->getEsEventDispatcher()->attach(
            self::EVENT_INTERNAL_GET_EVENT_TYPES_LIST_PRE, $this->getStorage()->preGetEventTypesListListener()
        );
        return $this->getStorage()->getEventTypesList();
    }

    public function getGroupByUniqueName($type, $triggerInstanceId) {
        $this->getStorage()->getEsEventDispatcher()->attach(
            self::EVENT_INTERNAL_GET_GROUP_BY_NAME_PRE, $this->getStorage()->preGetGroupByUniqueNameListener() 
        );
        return $this->getStorage()->getGroupByUniqueName($type, $triggerInstanceId);
    }
    
}

?>
