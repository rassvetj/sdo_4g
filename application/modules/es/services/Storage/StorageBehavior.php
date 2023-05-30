<?php
/**
 *
 * @author slava
 */
interface Es_Service_Storage_StorageBehavior {
    
    /**
     * Initiate connection to event data storage
     * @return boolean
     */
    public function initConnection();
    
    /**
     * Define connection configuration
     * @param \Zend_Config $config
     */
    public function setConnectionConfig(\Zend_Config $config);
    
    /**
     * Return connection cofiguration
     * @return Zend_Config
     */
    public function getConnectionConfig();
    
    /**
     * Define event dispatcher
     * @param \Es_Service_Dispatcher $dispatcher
     */
    public function setEsEventDispatcher(\Es_Service_Dispatcher $dispatcher);
    
    /**
     * Return event dispatcher
     * @return \Es_Service_Dispatcher
     */
    public function getEsEventDispatcher();
    
    /**
     * Push event into data storage
     * @param Es_Entity_AbstractEvent $event
     * @return boolean
     */
    public function push(Es_Entity_AbstractEvent $event);
    
    /**
     * Pull events from data storage by specific filter
     * @param Es_Entity_AbstractFilter $filter
     * @return Es_Entity_EventCollection
     */
    public function pull(Es_Entity_AbstractFilter $filter);
    
    /**
     * Remove events from data storage by specific filter
     * @param Es_Entity_AbstractFilter $filter
     * @return boolean
     */
    public function remove(Es_Entity_AbstractFilter $filter);
    
    /**
     * Mark events for user as 'has views' by specific filter
     * @param Es_Entity_AbstractFilter $filter
     * @return boolean
     */
    public function unsubscribe(Es_Entity_AbstractFilter $filter);
    
    /**
     * Pull event group stats byspecific filter
     * @param Es_Entity_AbstractFilter $filter
     * @return Es_Entity_GroupTypeList
     */
    public function pullStats(Es_Entity_AbstractFilter $filter);
    
    /**
     * Pull event notifies (e.g.: email notification) for all event types by specific filter (in fact: user_id)
     * @param Es_Entity_AbstractFilter $filter
     * @return Es_Entity_NotifiesList
     */
    public function pullNotifies(Es_Entity_AbstractFilter $filter);
    
    /**
     * Update specific user event notify (e.g.: switch on email notification for event by types 'forumAddMessage' and 'blogAddMessage')
     * @param Es_Entity_AbstractNotify $notify
     * @return boolean
     */
    public function updateNotify(Es_Entity_AbstractNotify $notify);

    /**
     * Return event group by group type and object instance id (e.g.: Forum Theme ID)
     * @param string $type
     * @param int $triggerInstanceId
     * @return Es_Entity_AbstractGroup
     */
    public function getGroupByUniqueName($type, $triggerInstanceId);
    
    /**
     * Return event types list
     * @return Es_Entity_EventTypeList
     */
    public function getEventTypesList();
    
    /**
     * Save (create) event group in storage
     * @param Es_Entity_AbstractGroup $group
     */
    public function createGroup(Es_Entity_AbstractGroup $group);

    /**
     * Callback which triggered before event pushing
     */
    public function prePushListener();
    
    /**
     * Callback which triggered before events list pulling
     */
    public function prePullListener();
    
    /**
     * Callback which triggered before events removeing from storage
     */
    public function preRemoveListener();
    
    
    /**
     * Callback which triggered before unsubscribing user from events
     */
    public function preUnsubscribeListener();
    
    /**
     * Callback which triggered before event types list pulling
     */
    public function preGetEventTypesListListener();
    
    /**
     * Callback which triggered before event group getting
     */
    public function preGetGroupByUniqueNameListener();
    
    /**
     * Callback which triggered before event group creating
     */
    public function preCreateGroupListener();
    
    /**
     * Callback which triggered before event group stats pulling
     */
    public function prePullStatsListener();
    
    /**
     * Callback which triggere before notifies pulling
     */
    public function prePullNotifies();
    
    /**
     * Callback which triggered before notify update
     */
    public function preUpdateNotify();
}

?>
